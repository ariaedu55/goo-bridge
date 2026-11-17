<?php

declare(strict_types=1);

function app_log_dir(): string
{
    $dir = dirname(__DIR__) . '/storage/logs';
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }

    return $dir;
}

function app_log_file(): string
{
    return app_log_dir() . '/errors.jsonl';
}

/**
 * Réduit les chemins absolus du serveur dans le contexte (pas de fuite hors admin).
 *
 * @param array<string, mixed> $context
 * @return array<string, mixed>
 */
function app_log_sanitize_context(array $context): array
{
    $root = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
    $root = str_replace('\\', '/', $root);

    $sanitize = static function ($value) use (&$sanitize, $root) {
        if (is_array($value)) {
            $out = [];
            foreach ($value as $k => $v) {
                $out[$k] = $sanitize($v);
            }

            return $out;
        }
        if (!is_string($value)) {
            return $value;
        }
        $norm = str_replace('\\', '/', $value);
        if ($root !== '' && str_starts_with($norm, $root)) {
            return ltrim(substr($norm, strlen($root)), '/');
        }
        // Masquer chemins type /Applications/... ou /home/...
        if (preg_match('#^/(?:Applications|Users|home|var/www|opt)/#', $norm) === 1) {
            return basename($norm);
        }

        return $value;
    };

    /** @var array<string, mixed> $clean */
    $clean = $sanitize($context);

    return $clean;
}

/**
 * Enregistre une entrée dans le journal centralisé.
 *
 * @param array<string, mixed> $context
 */
function app_log_write(string $level, string $message, array $context = []): void
{
    $level = strtolower($level);
    if (!in_array($level, ['debug', 'info', 'warning', 'error', 'critical', 'security'], true)) {
        $level = 'error';
    }

    $context = app_log_sanitize_context($context);

    $entry = [
        't' => date('c'),
        'level' => $level,
        'message' => mb_substr($message, 0, 2000),
        'ip' => function_exists('rate_limit_client_ip') ? rate_limit_client_ip() : ($_SERVER['REMOTE_ADDR'] ?? ''),
        'uri' => (string) ($_SERVER['REQUEST_URI'] ?? ''),
        'method' => (string) ($_SERVER['REQUEST_METHOD'] ?? ''),
        'context' => $context,
    ];

    $json = json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        return;
    }

    @file_put_contents(app_log_file(), $json . "\n", FILE_APPEND | LOCK_EX);
}

/**
 * @return list<array<string, mixed>>
 */
function app_log_read(int $limit = 200): array
{
    $file = app_log_file();
    if (!is_file($file)) {
        return [];
    }

    $lines = @file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!is_array($lines) || $lines === []) {
        return [];
    }

    $slice = array_slice($lines, -$limit);
    $out = [];
    foreach (array_reverse($slice) as $line) {
        $decoded = json_decode($line, true);
        if (is_array($decoded)) {
            $out[] = $decoded;
        }
    }

    return $out;
}

function app_log_clear(): bool
{
    $file = app_log_file();
    if (!is_file($file)) {
        return true;
    }

    return @file_put_contents($file, '') !== false;
}

function app_log_count(): int
{
    $file = app_log_file();
    if (!is_file($file)) {
        return 0;
    }
    $lines = @file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    return is_array($lines) ? count($lines) : 0;
}

function app_log_register_handlers(): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;

    set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        $level = match ($severity) {
            E_WARNING, E_USER_WARNING, E_CORE_WARNING, E_COMPILE_WARNING => 'warning',
            E_NOTICE, E_USER_NOTICE, E_DEPRECATED, E_USER_DEPRECATED => 'info',
            default => 'error',
        };
        app_log_write($level, $message, ['file' => $file, 'line' => $line, 'errno' => $severity]);

        return false;
    });

    set_exception_handler(static function (Throwable $e): void {
        app_log_write('critical', $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'type' => $e::class,
        ]);
        if (!headers_sent()) {
            http_response_code(500);
        }
        $errorPage = dirname(__DIR__) . '/errors/500.php';
        if (is_file($errorPage)) {
            require $errorPage;
        } else {
            echo 'Une erreur est survenue.';
        }
        exit;
    });

    register_shutdown_function(static function (): void {
        $err = error_get_last();
        if ($err === null) {
            return;
        }
        $fatal = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];
        if (!in_array($err['type'], $fatal, true)) {
            return;
        }
        app_log_write('critical', (string) $err['message'], [
            'file' => (string) ($err['file'] ?? ''),
            'line' => (int) ($err['line'] ?? 0),
            'errno' => (int) $err['type'],
            'shutdown' => true,
        ]);
    });
}
