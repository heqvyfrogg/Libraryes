<?php
/**
 * Database Management Class (SQLite)
 * Libraryes - Kobe Library Auto Reservation System
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
            session_id TEXT,
            usercode TEXT NOT NULL,
            password TEXT NOT NULL,
            name TEXT,
            is_default INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(session_id, usercode)
        );

        CREATE TABLE IF NOT EXISTS tasks (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            session_id TEXT,
            account_id INTEGER NOT NULL,
            type TEXT NOT NULL,
            area_code TEXT NOT NULL,
            corner_code TEXT NOT NULL,
            target_date TEXT NOT NULL,
            target_time_slot TEXT,
            purpose TEXT DEFAULT 'focus',
            status TEXT DEFAULT 'pending',
            retry_count INTEGER DEFAULT 0,
            max_retries INTEGER DEFAULT 50,
            interval_sec INTEGER DEFAULT 5,
            execute_at DATETIME,
            last_run_at DATETIME,
            result_message TEXT,
            reservation_info TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (account_id) REFERENCES accounts(id)
        );

        CREATE TABLE IF NOT EXISTS logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            session_id TEXT,
            task_id INTEGER,
            level TEXT DEFAULT 'info',
            message TEXT NOT NULL,
            context TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS vacancy_cache (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            area_code TEXT NOT NULL,
            corner_code TEXT NOT NULL,
            date TEXT NOT NULL,
            slot_id TEXT NOT NULL,
            is_available INTEGER NOT NULL DEFAULT 0,
            raw_data TEXT,
            scanned_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS settings (
            key TEXT PRIMARY KEY,
            value TEXT NOT NULL
        );
        ";
        self::$instance->exec($sql);

        // Auto migrations for existing sqlite databases
        try {
            self::$instance->exec("ALTER TABLE accounts ADD COLUMN session_id TEXT");
        } catch (Exception $e) {}
        try {
            self::$instance->exec("ALTER TABLE tasks ADD COLUMN session_id TEXT");
        } catch (Exception $e) {}
        try {
            self::$instance->exec("ALTER TABLE logs ADD COLUMN session_id TEXT");
        } catch (Exception $e) {}

        // Migrate away from global usercode UNIQUE constraint
        try {
            $stmt = self::$instance->query("SELECT sql FROM sqlite_master WHERE type='table' AND name='accounts'");
            $schema = $stmt->fetchColumn();
            if ($schema && stripos($schema, 'UNIQUE(session_id, usercode)') === false) {
                self::$instance->exec("BEGIN TRANSACTION");
                self::$instance->exec("ALTER TABLE accounts RENAME TO accounts_old");
                self::$instance->exec("
                    CREATE TABLE accounts (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        session_id TEXT,
                        usercode TEXT NOT NULL,
                        password TEXT NOT NULL,
                        name TEXT,
                        is_default INTEGER DEFAULT 0,
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        UNIQUE(session_id, usercode)
                    )
                ");
                self::$instance->exec("INSERT OR IGNORE INTO accounts SELECT * FROM accounts_old");
                self::$instance->exec("DROP TABLE accounts_old");
                self::$instance->exec("COMMIT");
            }
        } catch (Exception $e) {
            error_log("Migration failed: " . $e->getMessage());
            if (self::$instance->inTransaction()) {
                self::$instance->exec("ROLLBACK");
            }
        }
    }

    public static function log(string $message, string $level = 'info', ?int $taskId = null, array $context = [], ?string $sessionId = null): void {
        try {
            $stmt = self::get()->prepare("INSERT INTO logs (session_id, task_id, level, message, context) VALUES (:sid, :task_id, :level, :message, :context)");
            $stmt->execute([
                ':sid' => $sessionId ?: (session_id() ?: null),
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
