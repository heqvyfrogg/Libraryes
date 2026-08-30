<?php
/**
 * Environment Variable Loader (.env parser)
 * Libraryes Core
 */

class Env {
    private static array $vars = [];
    private static bool $loaded = false;

    public static function load(?string $filePath = null): void {
        if (self::$loaded) return;
        $path = $filePath ?: __DIR__ . '/../.env';

        if (file_exists($path)) {
            $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#')) {
                    continue;
                }

                if (strpos($line, '=') !== false) {
                    [$key, $val] = explode('=', $line, 2);
                    $key = trim($key);
                    $val = trim($val);

                    // Remove wrapping quotes
                    if ((str_starts_with($val, '"') && str_ends_with($val, '"')) ||
                        (str_starts_with($val, "'") && str_ends_with($val, "'"))) {
                        $val = substr($val, 1, -1);
                    }

                    self::$vars[$key] = $val;
                    putenv("{$key}={$val}");
                    $_ENV[$key] = $val;
                    $_SERVER[$key] = $val;
                }
            }
        }
        self::$loaded = true;
    }

    public static function get(string $key, mixed $default = null): mixed {
        self::load();
        return self::$vars[$key] ?? getenv($key) ?: $default;
    }
}

// Auto-load on include
Env::load();

function env(string $key, mixed $default = null): mixed {
    return Env::get($key, $default);
}
