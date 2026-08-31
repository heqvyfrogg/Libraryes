<?php
/**
 * Background Task & Sniper Worker Engine
 * Libraryes Automation Core
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/kobe_client.php';

class Worker {
    public static function runPendingTasks(): array {
        $db = DB::get();
        $stmt = $db->query("SELECT * FROM tasks WHERE status IN ('pending', 'monitoring') ORDER BY id ASC");
        $tasks = $stmt->fetchAll();

        $results = [];
        foreach ($tasks as $task) {
            $results[] = self::executeTask($task);
        }
        return $results;
    }

    public static function runTaskById(int $taskId): array {
        $db = DB::get();
        $stmt = $db->prepare("SELECT * FROM tasks WHERE id = :id");
        $stmt->execute([':id' => $taskId]);
        $task = $stmt->fetch();

        if (!$task) {
            return ['success' => false, 'message' => 'Task not found'];
        }

        return self::executeTask($task);
    }

    public static function executeTask(array $task): array {
        $db = DB::get();
        $taskId = (int)$task['id'];

        // Get account details
        $stmt = $db->prepare("SELECT * FROM accounts WHERE id = :id");
        $stmt->execute([':id' => $task['account_id']]);
        $account = $stmt->fetch();

        if (!$account) {
            self::updateTaskStatus($taskId, 'failed', 'Account credentials not found.');
            return ['success' => false, 'message' => 'Account credentials not found.'];
        }

        DB::log("Starting task #{$taskId} [Type: {$task['type']}, Date: {$task['target_date']}]", 'info', $taskId);

        $client = new KobeLibraryClient();

        try {
            // Login
            $loggedIn = $client->login($account['usercode'], $account['password']);
            if (!$loggedIn) {
                throw new Exception("Login failed for user {$account['usercode']}. Check credentials.");
            }

            switch ($task['type']) {

                case 'instant_snipe':
                    return self::handleInstantSnipeTask($task, $client);

                case 'absolute_sniper':
                    return self::handleAbsoluteSniperTask($task, $client);

                default:
                    return self::handleInstantSnipeTask($task, $client);
            }
        } catch (Exception $e) {
            $errMsg = $e->getMessage();
            $retryCount = (int)$task['retry_count'] + 1;
            $maxRetries = (int)$task['max_retries'];

            if ($retryCount >= $maxRetries) {
                self::updateTaskStatus($taskId, 'failed', "Max retries reached: {$errMsg}", $retryCount);
                DB::log("Task #{$taskId} permanently failed: {$errMsg}", 'error', $taskId);
            } else {
                self::updateTaskStatus($taskId, 'monitoring', "Retry #{$retryCount}: {$errMsg}", $retryCount);
                DB::log("Task #{$taskId} retry {$retryCount}/{$maxRetries}: {$errMsg}", 'warn', $taskId);
            }

            return ['success' => false, 'message' => $errMsg, 'retry_count' => $retryCount];
        }
    }

    /**
     * Instant Snipe Handler (Grab immediate opening as soon as it appears)
     */
    private static function handleInstantSnipeTask(array $task, KobeLibraryClient $client): array {
        $taskId = (int)$task['id'];
        $cornerCode = $task['corner_code'] ?: '62000';
        $targetDate = self::resolveTargetDate($task['target_date']);

        $matrix = $client->getReservationMatrix($cornerCode, $targetDate);
        $slots = $matrix['slots'] ?? [];

        if (empty($slots)) {
            $msg = "Sniper scanning: No open seats right now. Standing by for cancellations...";
            self::updateTaskStatus($taskId, 'monitoring', $msg, ((int)$task['retry_count'] + 1));
            return ['success' => false, 'monitoring' => true, 'message' => $msg];
        }

        // Take the earliest or preferred open slot immediately
        $chosenSlot = $slots[0];
        if (!empty($task['target_time_slot']) && $task['target_time_slot'] !== 'ANY') {
            foreach ($slots as $s) {
                if (strpos($s['time'], $task['target_time_slot']) !== false || $s['slot_id'] === $task['target_time_slot']) {
                    $chosenSlot = $s;
                    break;
                }
            }
        }

        DB::log("Sniper triggered! Grabbing slot {$chosenSlot['date']} ID:{$chosenSlot['slot_id']} ({$chosenSlot['time']})", 'info', $taskId);

        $res = $client->reserveSlot($chosenSlot['date'], $chosenSlot['slot_id'], $cornerCode, $task['area_code'] ?? '60000');
        if ($res['success']) {
            $successMsg = "Sniped seat successfully: {$chosenSlot['date']} {$chosenSlot['time']} (No. " . ($res['reservation_number'] ?? 'OK') . ")";
            self::updateTaskStatus($taskId, 'success', $successMsg, (int)$task['retry_count'], json_encode($res, JSON_UNESCAPED_UNICODE));
            DB::log("Task #{$taskId} SNIPED! {$successMsg}", 'success', $taskId);
            return ['success' => true, 'message' => $successMsg, 'data' => $res];
        } else {
            throw new Exception("Snipe attempt failed at reservation submission.");
        }
    }

    /**
     * Absolute Target Sniper Handler (Pinpoint exact slot with high frequency)
     */
    private static function handleAbsoluteSniperTask(array $task, KobeLibraryClient $client): array {
        $taskId = (int)$task['id'];
        $cornerCode = $task['corner_code'] ?: '62000';
        $targetDate = self::resolveTargetDate($task['target_date']);
        $targetTime = $task['target_time_slot']; // e.g. '10:10' or slot index '0'

        // Check if execute_at is in future
        if (!empty($task['execute_at'])) {
            $execTimestamp = strtotime($task['execute_at']);
            $now = time();
            if ($execTimestamp > $now + 5) {
                $waitSec = $execTimestamp - $now;
                $msg = "Absolute Sniper waiting for scheduled launch time: {$task['execute_at']} ({$waitSec}s remaining)";
                self::updateTaskStatus($taskId, 'pending', $msg);
                return ['success' => false, 'pending' => true, 'message' => $msg];
            }
        }

        DB::log("Absolute Sniper LAUNCHED for {$targetDate} Slot: {$targetTime}!", 'info', $taskId);

        // Perform fast multi-probe
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $matrix = $client->getReservationMatrix($cornerCode, $targetDate);
            $slots = $matrix['slots'] ?? [];

            $targetSlot = null;
            foreach ($slots as $s) {
                if ($s['time'] === $targetTime || $s['slot_id'] === $targetTime || strpos($s['raw_label'], $targetTime) !== false) {
                    $targetSlot = $s;
                    break;
                }
            }

            if ($targetSlot) {
                DB::log("Target slot detected! Attempting instant lock (Attempt {$attempt})...", 'info', $taskId);
                $res = $client->reserveSlot($targetSlot['date'], $targetSlot['slot_id'], $cornerCode, $task['area_code'] ?? '60000');
                if ($res['success']) {
                    $msg = "ABSOLUTE LOCK SUCCESS: {$targetSlot['date']} {$targetSlot['time']} (No. " . ($res['reservation_number'] ?? 'OK') . ")";
                    self::updateTaskStatus($taskId, 'success', $msg, $attempt, json_encode($res, JSON_UNESCAPED_UNICODE));
                    DB::log("Task #{$taskId} {$msg}", 'success', $taskId);
                    return ['success' => true, 'message' => $msg, 'data' => $res];
                }
            }

            usleep(200000); // 200ms rapid poll between attempts
        }

        $msg = "Target slot {$targetTime} on {$targetDate} was not open during rapid probe. Monitoring continues.";
        self::updateTaskStatus($taskId, 'monitoring', $msg, ((int)$task['retry_count'] + 5));
        return ['success' => false, 'monitoring' => true, 'message' => $msg];
    }

    private static function resolveTargetDate(string $targetDate): string {
        $t = strtoupper(trim($targetDate));
        if ($t === 'TODAY' || empty($t)) {
            return date('Y-m-d');
        }
        if ($t === 'TOMORROW') {
            return date('Y-m-d', strtotime('+1 day'));
        }
        if ($t === 'THIS_WEEKEND') {
            return date('Y-m-d', strtotime('next Saturday'));
        }
        return date('Y-m-d', strtotime($targetDate));
    }

    private static function updateTaskStatus(int $taskId, string $status, string $message, int $retryCount = 0, ?string $reservationInfo = null): void {
        $db = DB::get();
        $sql = "UPDATE tasks SET status = :status, result_message = :msg, retry_count = :retries, last_run_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP";
        $params = [
            ':id' => $taskId,
            ':status' => $status,
            ':msg' => $message,
            ':retries' => $retryCount
        ];

        if ($reservationInfo !== null) {
            $sql .= ", reservation_info = :res_info";
            $params[':res_info'] = $reservationInfo;
        }

        $sql .= " WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
    }
}

// CLI runner support
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['PHP_SELF'] ?? '')) {
    $options = getopt('', ['task_id:', 'run_pending']);
    if (isset($options['task_id'])) {
        $res = Worker::runTaskById((int)$options['task_id']);
        echo json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    } else {
        $res = Worker::runPendingTasks();
        echo json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    }
}
