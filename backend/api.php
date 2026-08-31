<?php
/**
 * REST API Router with Session Tenant Isolation
 * Libraryes API
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$sessionId = session_id();

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
            // Filter strictly by current session_id
            $stmtAccounts = $db->prepare("SELECT id, usercode, name, is_default, created_at FROM accounts WHERE session_id = :sid ORDER BY id ASC");
            $stmtAccounts->execute([':sid' => $sessionId]);
            $accounts = $stmtAccounts->fetchAll();

            $stmtTasks = $db->prepare("
                SELECT t.*, a.usercode, a.name as account_name 
                FROM tasks t 
                LEFT JOIN accounts a ON t.account_id = a.id 
                WHERE t.session_id = :sid 
                ORDER BY t.id DESC 
                LIMIT 50
            ");
            $stmtTasks->execute([':sid' => $sessionId]);
            $tasks = $stmtTasks->fetchAll();

            $stmtLogs = $db->prepare("SELECT * FROM logs WHERE session_id = :sid ORDER BY id DESC LIMIT 50");
            $stmtLogs->execute([':sid' => $sessionId]);
            $logs = $stmtLogs->fetchAll();

            jsonResponse([
                'success' => true,
                'session_id' => substr($sessionId, 0, 8) . '...',
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

            // Check if already exists for this session
            $stmtCheck = $db->prepare("SELECT id FROM accounts WHERE session_id = :sid AND usercode = :usercode");
            $stmtCheck->execute([':sid' => $sessionId, ':usercode' => $usercode]);
            $existingId = $stmtCheck->fetchColumn();

            if ($existingId) {
                $stmt = $db->prepare("UPDATE accounts SET password = :password, name = :name, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
                $stmt->execute([':password' => $password, ':name' => $name ?: "利用者 {$usercode}", ':id' => $existingId]);
            } else {
                $stmt = $db->prepare("
                    INSERT INTO accounts (session_id, usercode, password, name, is_default, updated_at) 
                    VALUES (:sid, :usercode, :password, :name, 1, CURRENT_TIMESTAMP)
                ");
                $stmt->execute([
                    ':sid' => $sessionId,
                    ':usercode' => $usercode,
                    ':password' => $password,
                    ':name' => $name ?: "利用者 {$usercode}"
                ]);
            }

            DB::log("Account saved & verified: {$usercode}", 'success', null, [], $sessionId);
            jsonResponse(['success' => true, 'message' => 'アカウントが正常に認証・保存されました。']);
            break;

        case 'delete_account':
            $input = getJsonInput();
            $id = (int)($input['id'] ?? 0);
            $stmt = $db->prepare("DELETE FROM accounts WHERE id = :id AND session_id = :sid");
            $stmt->execute([':id' => $id, ':sid' => $sessionId]);
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

        case 'create_task':
            $input = getJsonInput();
            $type = $input['type'] ?? 'immediate';
            $areaCode = $input['area_code'] ?? '60000';
            $cornerCode = $input['corner_code'] ?? '62000';
            $targetDate = $input['target_date'] ?? 'TODAY';
            $targetTimeSlot = $input['target_time_slot'] ?? '';
            $purpose = $input['purpose'] ?? 'focus';
            $executeAt = !empty($input['execute_at']) ? $input['execute_at'] : null;
            $maxRetries = (int)($input['max_retries'] ?? 999999);

            $stmtAcc = $db->prepare("SELECT id FROM accounts WHERE session_id = :sid ORDER BY is_default DESC LIMIT 1");
            $stmtAcc->execute([':sid' => $sessionId]);
            $firstAcc = $stmtAcc->fetch();

            if (!$firstAcc) {
                jsonResponse(['success' => false, 'message' => 'アカウントが登録されていません。先にアカウントを設定してください。'], 400);
            }
            $accountId = (int)$firstAcc['id'];

            $stmt = $db->prepare("
                INSERT INTO tasks (session_id, account_id, type, area_code, corner_code, target_date, target_time_slot, purpose, execute_at, max_retries, status)
                VALUES (:sid, :account_id, :type, :area_code, :corner_code, :target_date, :target_time_slot, :purpose, :execute_at, :max_retries, 'pending')
            ");
            $stmt->execute([
                ':sid' => $sessionId,
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

            DB::log("Created new task #{$taskId} ({$type})", 'info', $taskId, [], $sessionId);

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
            $result = Worker::runTaskById($taskId);
            jsonResponse(['success' => true, 'result' => $result]);
            break;

        case 'cancel_task':
            $input = getJsonInput();
            $taskId = (int)($input['task_id'] ?? 0);
            $stmt = $db->prepare("UPDATE tasks SET status = 'cancelled', result_message = 'ユーザーにより手動キャンセル', updated_at = CURRENT_TIMESTAMP WHERE id = :id AND session_id = :sid");
            $stmt->execute([':id' => $taskId, ':sid' => $sessionId]);
            jsonResponse(['success' => true, 'message' => 'タスクをキャンセルしました。']);
            break;

        case 'delete_task':
            $input = getJsonInput();
            $taskId = (int)($input['task_id'] ?? 0);
            $stmt = $db->prepare("DELETE FROM tasks WHERE id = :id AND session_id = :sid");
            $stmt->execute([':id' => $taskId, ':sid' => $sessionId]);
            jsonResponse(['success' => true, 'message' => 'タスクを削除しました。']);
            break;

        case 'my_reservations':
            $stmtAcc = $db->prepare("SELECT * FROM accounts WHERE session_id = :sid ORDER BY is_default DESC LIMIT 1");
            $stmtAcc->execute([':sid' => $sessionId]);
            $acc = $stmtAcc->fetch();

            if (!$acc) {
                jsonResponse(['success' => false, 'message' => 'アカウントが登録されていません。'], 400);
            }

            $client = new KobeLibraryClient();
            $client->login($acc['usercode'], $acc['password']);
            $list = $client->getActiveReservations();
            jsonResponse(['success' => true, 'account' => $acc['usercode'], 'reservations' => $list]);
            break;

        case 'cancel_reservation':
            $input = getJsonInput();
            $slotId = (string)($input['slot_id'] ?? '0');

            $stmtAcc = $db->prepare("SELECT * FROM accounts WHERE session_id = :sid ORDER BY is_default DESC LIMIT 1");
            $stmtAcc->execute([':sid' => $sessionId]);
            $acc = $stmtAcc->fetch();

            if (!$acc) {
                jsonResponse(['success' => false, 'message' => 'アカウントが見つかりません。'], 400);
            }

            $client = new KobeLibraryClient();
            $client->login($acc['usercode'], $acc['password']);
            $cancelled = $client->cancelReservation($slotId);

            if ($cancelled) {
                DB::log("Reservation cancelled for user {$acc['usercode']} (Slot: {$slotId})", 'success', null, [], $sessionId);
                jsonResponse(['success' => true, 'message' => '予約を正常に取り消しました。']);
            } else {
                jsonResponse(['success' => false, 'message' => '予約の取り消しに失敗しました。'], 500);
            }
            break;

        case 'quick_reserve':
            $input = getJsonInput();
            $date = $input['date'] ?? date('Y-m-d');
            $slotId = (string)($input['slot_id'] ?? '0');
            $cornerCode = $input['corner_code'] ?? '62000';
            $areaCode = $input['area_code'] ?? '60000';

            // Filter accounts strictly by this session
            $stmtAccs = $db->prepare("SELECT * FROM accounts WHERE session_id = :sid ORDER BY is_default DESC, id ASC");
            $stmtAccs->execute([':sid' => $sessionId]);
            $accounts = $stmtAccs->fetchAll();

            if (empty($accounts)) {
                jsonResponse(['success' => false, 'message' => '登録されたアカウントがありません。先にアカウントを設定してください。'], 400);
            }

            $acc = $accounts[0];
            $client = new KobeLibraryClient();
            try {
                $client->login($acc['usercode'], $acc['password']);
                $res = $client->reserveSlot($date, $slotId, $cornerCode, $areaCode);

                if ($res['success']) {
                    DB::log("Reserved seat for {$acc['usercode']}: {$date} Slot {$slotId} (No. " . ($res['reservation_number'] ?? 'OK') . ")", 'success', null, [], $sessionId);
                    jsonResponse([
                        'success' => true,
                        'message' => "予約を確保しました！ (予約番号: " . ($res['reservation_number'] ?? '受領済') . ")",
                        'data' => $res
                    ]);
                } else {
                    jsonResponse(['success' => false, 'message' => '予約受付されませんでした。既に満席の可能性があります。'], 500);
                }
            } catch (Exception $e) {
                jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
            }
            break;

        default:
            jsonResponse(['success' => false, 'message' => "Unknown action: {$action}"], 404);
            break;
    }
} catch (Exception $e) {
    DB::log("API Error [{$action}]: " . $e->getMessage(), 'error', null, [], $sessionId);
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
}
