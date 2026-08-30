<?php
/**
 * REST API Router
 * Libraryes API
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/kobe_client.php';
require_once __DIR__ . '/ai_engine.php';
require_once __DIR__ . '/worker.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

function jsonResponse(array $data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function getJsonInput(): array {
    $raw = file_get_contents('php://input');
    if (!$raw) return $_POST;
    $parsed = json_decode($raw, true);
    return is_array($parsed) ? array_merge($_POST, $parsed) : $_POST;
}

try {
    $db = DB::get();

    switch ($action) {
        case 'status':
            $stmtAccounts = $db->query("SELECT id, usercode, name, is_default, created_at FROM accounts ORDER BY id ASC");
            $accounts = $stmtAccounts->fetchAll();

            $stmtTasks = $db->query("
                SELECT t.*, a.usercode, a.name as account_name 
                FROM tasks t 
                LEFT JOIN accounts a ON t.account_id = a.id 
                ORDER BY t.id DESC 
                LIMIT 50
            ");
            $tasks = $stmtTasks->fetchAll();

            $stmtLogs = $db->query("SELECT * FROM logs ORDER BY id DESC LIMIT 50");
            $logs = $stmtLogs->fetchAll();

            jsonResponse([
                'success' => true,
                'areas' => KobeLibraryClient::AREAS,
                'corners' => KobeLibraryClient::CORNERS,
                'accounts' => $accounts,
                'tasks' => $tasks,
                'logs' => $logs,
                'server_time' => date('Y-m-d H:i:s'),
            ]);
            break;

        case 'save_account':
            $input = getJsonInput();
            $usercode = trim($input['usercode'] ?? '');
            $password = trim($input['password'] ?? '');
            $name = trim($input['name'] ?? '');

            if (empty($usercode) || empty($password)) {
                jsonResponse(['success' => false, 'message' => '利用者番号とパスワードを入力してください。'], 400);
            }

            // Verify login on Kobe system
            $client = new KobeLibraryClient();
            $verifyLogin = $client->login($usercode, $password);
            if (!$verifyLogin) {
                jsonResponse(['success' => false, 'message' => '神戸市立図書館予約システムへのログインに失敗しました。利用者番号またはパスワードを確認してください。'], 400);
            }

            $stmt = $db->prepare("
                INSERT INTO accounts (usercode, password, name, is_default, updated_at) 
                VALUES (:usercode, :password, :name, 1, CURRENT_TIMESTAMP)
                ON CONFLICT(usercode) DO UPDATE SET password = :password, name = :name, updated_at = CURRENT_TIMESTAMP
            ");
            $stmt->execute([
                ':usercode' => $usercode,
                ':password' => $password,
                ':name' => $name ?: "利用者 {$usercode}"
            ]);

            DB::log("Account saved & verified: {$usercode}", 'success');
            jsonResponse(['success' => true, 'message' => 'アカウントが正常に認証・保存されました。']);
            break;

        case 'delete_account':
            $input = getJsonInput();
            $id = (int)($input['id'] ?? 0);
            $stmt = $db->prepare("DELETE FROM accounts WHERE id = :id");
            $stmt->execute([':id' => $id]);
            jsonResponse(['success' => true, 'message' => 'アカウントを削除しました。']);
            break;

        case 'public_vacancies':
            $date = $_GET['date'] ?? date('Y-m-d');
            $area = $_GET['area'] ?? '60000';
            $corner = $_GET['corner'] ?? null;

            $client = new KobeLibraryClient();
            $data = $client->getPublicVacancies($date, $area, $corner);
            jsonResponse(['success' => true, 'data' => $data]);
            break;

        case 'ai_recommend':
            $date = $_GET['date'] ?? date('Y-m-d');
            $corner = $_GET['corner'] ?? '62000';
            $purpose = $_GET['purpose'] ?? 'focus';
            $preferredTime = $_GET['preferred_time'] ?? null;
            $accountId = (int)($_GET['account_id'] ?? 0);

            $client = new KobeLibraryClient();
            $slots = [];
            if ($accountId > 0) {
                $stmt = $db->prepare("SELECT * FROM accounts WHERE id = :id");
                $stmt->execute([':id' => $accountId]);
                $acc = $stmt->fetch();
                if ($acc) {
                    $client->login($acc['usercode'], $acc['password']);
                    $matrix = $client->getReservationMatrix($corner, $date);
                    $slots = $matrix['slots'] ?? [];
                }
            }

            // Fallback to public vacancies if unauthenticated or matrix empty
            if (empty($slots)) {
                $pub = $client->getPublicVacancies($date, '60000', $corner);
                foreach ($pub['slots'] ?? [] as $ps) {
                    $time = '';
                    if (preg_match('/(\d{1,2}:\d{2})/', $ps['label'], $tm)) {
                        $time = $tm[1];
                    }
                    $slots[] = [
                        'date' => $ps['date'],
                        'slot_id' => $ps['slot_id'],
                        'time' => $time ?: $ps['label'],
                        'raw_label' => $ps['label'],
                        'confirm_url' => $ps['url'],
                        'available' => true
                    ];
                }
            }

            $scoredSlots = AIEngine::evaluateSlots($slots, $purpose, $preferredTime);

            jsonResponse([
                'success' => true,
                'date' => $date,
                'corner_code' => $corner,
                'purpose' => $purpose,
                'total_candidates' => count($slots),
                'top_recommendations' => $scoredSlots,
                'best_slot' => $scoredSlots[0] ?? null
            ]);
            break;

        case 'create_task':
            $input = getJsonInput();
            $accountId = (int)($input['account_id'] ?? 0);
            $type = $input['type'] ?? 'ai_optimal';
            $areaCode = $input['area_code'] ?? '60000';
            $cornerCode = $input['corner_code'] ?? '62000';
            $targetDate = $input['target_date'] ?? 'TODAY';
            $targetTimeSlot = $input['target_time_slot'] ?? '';
            $purpose = $input['purpose'] ?? 'focus';
            $executeAt = !empty($input['execute_at']) ? $input['execute_at'] : null;
            $maxRetries = (int)($input['max_retries'] ?? 50);

            if ($accountId <= 0) {
                $stmtAcc = $db->query("SELECT id FROM accounts LIMIT 1");
                $firstAcc = $stmtAcc->fetch();
                if (!$firstAcc) {
                    jsonResponse(['success' => false, 'message' => 'アカウントが登録されていません。先にアカウントを登録してください。'], 400);
                }
                $accountId = (int)$firstAcc['id'];
            }

            $stmt = $db->prepare("
                INSERT INTO tasks (account_id, type, area_code, corner_code, target_date, target_time_slot, purpose, execute_at, max_retries, status)
                VALUES (:account_id, :type, :area_code, :corner_code, :target_date, :target_time_slot, :purpose, :execute_at, :max_retries, 'pending')
            ");
            $stmt->execute([
                ':account_id' => $accountId,
                ':type' => $type,
                ':area_code' => $areaCode,
                ':corner_code' => $cornerCode,
                ':target_date' => $targetDate,
                ':target_time_slot' => $targetTimeSlot,
                ':purpose' => $purpose,
                ':execute_at' => $executeAt,
                ':max_retries' => $maxRetries
            ]);
            $taskId = (int)$db->lastInsertId();

            DB::log("Created new task #{$taskId} ({$type})", 'info', $taskId);

            // If requested immediate run
            $immediate = !empty($input['run_now']);
            if ($immediate) {
                $result = Worker::runTaskById($taskId);
                jsonResponse(['success' => true, 'task_id' => $taskId, 'execution' => $result]);
            }

            jsonResponse(['success' => true, 'task_id' => $taskId, 'message' => '自動予約タスクを登録しました。']);
            break;

        case 'run_task':
            $input = getJsonInput();
            $taskId = (int)($input['task_id'] ?? 0);
            if ($taskId <= 0) {
                jsonResponse(['success' => false, 'message' => '無効なタスクIDです。'], 400);
            }
            $result = Worker::runTaskById($taskId);
            jsonResponse(['success' => true, 'result' => $result]);
            break;

        case 'cancel_task':
            $input = getJsonInput();
            $taskId = (int)($input['task_id'] ?? 0);
            $stmt = $db->prepare("UPDATE tasks SET status = 'cancelled', result_message = 'ユーザーにより手動キャンセル', updated_at = CURRENT_TIMESTAMP WHERE id = :id");
            $stmt->execute([':id' => $taskId]);
            DB::log("Task #{$taskId} cancelled by user", 'warn', $taskId);
            jsonResponse(['success' => true, 'message' => 'タスクをキャンセルしました。']);
            break;

        case 'delete_task':
            $input = getJsonInput();
            $taskId = (int)($input['task_id'] ?? 0);
            $stmt = $db->prepare("DELETE FROM tasks WHERE id = :id");
            $stmt->execute([':id' => $taskId]);
            jsonResponse(['success' => true, 'message' => 'タスクを削除しました。']);
            break;

        case 'my_reservations':
            $accountId = (int)($_GET['account_id'] ?? 0);
            if ($accountId <= 0) {
                $stmtAcc = $db->query("SELECT * FROM accounts LIMIT 1");
                $acc = $stmtAcc->fetch();
            } else {
                $stmtAcc = $db->prepare("SELECT * FROM accounts WHERE id = :id");
                $stmtAcc->execute([':id' => $accountId]);
                $acc = $stmtAcc->fetch();
            }

            if (!$acc) {
                jsonResponse(['success' => false, 'message' => '有効なアカウントがありません。'], 400);
            }

            $client = new KobeLibraryClient();
            $client->login($acc['usercode'], $acc['password']);
            $list = $client->getActiveReservations();
            jsonResponse(['success' => true, 'account' => $acc['usercode'], 'reservations' => $list]);
            break;

        case 'cancel_reservation':
            $input = getJsonInput();
            $accountId = (int)($input['account_id'] ?? 0);
            $slotId = (string)($input['slot_id'] ?? '0');

            if ($accountId <= 0) {
                $stmtAcc = $db->query("SELECT * FROM accounts LIMIT 1");
                $acc = $stmtAcc->fetch();
            } else {
                $stmtAcc = $db->prepare("SELECT * FROM accounts WHERE id = :id");
                $stmtAcc->execute([':id' => $accountId]);
                $acc = $stmtAcc->fetch();
            }

            if (!$acc) {
                jsonResponse(['success' => false, 'message' => 'アカウントが見つかりません。'], 400);
            }

            $client = new KobeLibraryClient();
            $client->login($acc['usercode'], $acc['password']);
            $cancelled = $client->cancelReservation($slotId);

            if ($cancelled) {
                DB::log("Reservation cancelled for user {$acc['usercode']} (Slot: {$slotId})", 'success');
                jsonResponse(['success' => true, 'message' => '予約を正常に取り消しました。']);
            } else {
                jsonResponse(['success' => false, 'message' => '予約の取り消しに失敗しました。既に取消済みの可能性があります。'], 500);
            }
            break;

        case 'quick_reserve':
            $input = getJsonInput();
            $accountId = (int)($input['account_id'] ?? 0);
            $date = $input['date'] ?? date('Y-m-d');
            $slotId = (string)($input['slot_id'] ?? '0');
            $cornerCode = $input['corner_code'] ?? '62000';

            if ($accountId <= 0) {
                $stmtAcc = $db->query("SELECT * FROM accounts LIMIT 1");
                $acc = $stmtAcc->fetch();
            } else {
                $stmtAcc = $db->prepare("SELECT * FROM accounts WHERE id = :id");
                $stmtAcc->execute([':id' => $accountId]);
                $acc = $stmtAcc->fetch();
            }

            if (!$acc) {
                jsonResponse(['success' => false, 'message' => 'アカウントが見つかりません。'], 400);
            }

            $client = new KobeLibraryClient();
            $client->login($acc['usercode'], $acc['password']);
            $result = $client->reserveSlot($date, $slotId, $cornerCode);

            if ($result['success']) {
                DB::log("Quick reservation success: {$date} Slot {$slotId} (No. " . ($result['reservation_number'] ?? 'OK') . ")", 'success');
                jsonResponse(['success' => true, 'message' => '予約が正常に完了しました！', 'data' => $result]);
            } else {
                jsonResponse(['success' => false, 'message' => '予約に失敗しました。既に満席の可能性があります。'], 500);
            }
            break;

        default:
            jsonResponse(['success' => false, 'message' => "Unknown action: {$action}"], 404);
            break;
    }
} catch (Exception $e) {
    DB::log("API Error [{$action}]: " . $e->getMessage(), 'error');
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
}
