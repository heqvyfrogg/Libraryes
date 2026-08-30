<?php
/**
 * Database Management Class (SQLite)
 * Libraryes - Kobe Library AI Auto Reservation System
 */

require_once __DIR__ . '/env.php';

class DB {
    private static ?PDO $instance = null;
    private static string $dbPath = __DIR__ . '/../data/libraryes.db';

    public static function get(): PDO {
        if (self::$instance === null) {
            $dataDir = dirname(self::$dbPath);
            if (!is_dir($dataDir)) {
                mkdir($dataDir, 0755, true);
            }

            self::$instance = new PDO('sqlite:' . self::$dbPath, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_TIMEOUT => 10,
            ]);

            self::initTables();
        }
        return self::$instance;
    }

    private static function initTables(): void {
        $sql = "
        CREATE TABLE IF NOT EXISTS accounts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            usercode TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            name TEXT,
            is_default INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS tasks (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            account_id INTEGER NOT NULL,
            type TEXT NOT NULL, /* 'ai_optimal', 'instant_snipe', 'absolute_sniper', 'schedule' */
            area_code TEXT NOT NULL,
            corner_code TEXT NOT NULL,
            target_date TEXT NOT NULL, /* 'YYYY-MM-DD' or 'TODAY', 'TOMORROW', 'WEEKEND', 'ALL_OPEN' */
            target_time_slot TEXT, /* '10:10', '14:20', 'ANY', etc. */
            purpose TEXT DEFAULT 'focus', /* 'focus', 'pc_work', 'quick_read', 'long_study' */
            status TEXT DEFAULT 'pending', /* 'pending', 'monitoring', 'success', 'failed', 'cancelled' */
            retry_count INTEGER DEFAULT 0,
            max_retries INTEGER DEFAULT 50,
            interval_sec INTEGER DEFAULT 5,
            execute_at DATETIME, /* For absolute sniper: start at exact time */
            last_run_at DATETIME,
            result_message TEXT,
            reservation_info TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (account_id) REFERENCES accounts(id)
        );

        CREATE TABLE IF NOT EXISTS logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            task_id INTEGER,
            level TEXT DEFAULT 'info', /* 'info', 'success', 'warn', 'error' */
            message TEXT NOT NULL,
            context TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS settings (
            key TEXT PRIMARY KEY,
            value TEXT NOT NULL
        );
        ";
        self::$instance->exec($sql);

        // Auto-seed default credentials from .env if defined and accounts table is empty
        $defaultUser = env('LIBRARY_DEFAULT_USERCODE');
        $defaultPass = env('LIBRARY_DEFAULT_PASSWORD');
        if (!empty($defaultUser) && !empty($defaultPass)) {
            $check = self::$instance->query("SELECT COUNT(*) FROM accounts")->fetchColumn();
            if ($check == 0) {
                $stmt = self::$instance->prepare("INSERT INTO accounts (usercode, password, name, is_default) VALUES (:u, :p, 'Default User', 1)");
                $stmt->execute([':u' => $defaultUser, ':p' => $defaultPass]);
            }
        }
    }

    public static function log(string $message, string $level = 'info', ?int $taskId = null, array $context = []): void {
        try {
            $stmt = self::get()->prepare("INSERT INTO logs (task_id, level, message, context) VALUES (:task_id, :level, :message, :context)");
            $stmt->execute([
                ':task_id' => $taskId,
                ':level' => $level,
                ':message' => $message,
                ':context' => !empty($context) ? json_encode($context, JSON_UNESCAPED_UNICODE) : null
            ]);
        } catch (Exception $e) {
            // suppress DB log errors
        }
    }
}
