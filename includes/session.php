<?php

declare(strict_types=1);

require_once __DIR__ . '/security.php';
require_once __DIR__ . '/rate_limit.php';
require_once __DIR__ . '/app_log.php';
require_once __DIR__ . '/maintenance.php';

security_send_headers();
app_log_register_handlers();

if (session_status() === PHP_SESSION_NONE) {
    // Durée de vie côté serveur et cookie (30 jours) — rester connecté sans expiration courte.
    $lifetime = 60 * 60 * 24 * 30;
    ini_set('session.gc_maxlifetime', (string) $lifetime);
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');

    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (string) $_SERVER['SERVER_PORT'] === '443')
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string) $_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https');

    session_set_cookie_params([
        'lifetime' => $lifetime,
        'path' => '/',
        'domain' => '',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

// Mode maintenance (503) — après session pour autoriser les admins connectés.
maintenance_enforce();

// Soft rate-limit global sur les POST (hors admin).
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $script = (string) ($_SERVER['SCRIPT_NAME'] ?? '');
    if (!str_contains($script, '/admin/')) {
        $rl = rate_limit_hit('global_post', 90, 600);
        if (!$rl['allowed']) {
            app_log_write('security', 'Rate limit global POST dépassé', [
                'retry_after' => $rl['retry_after'],
            ]);
            if (!headers_sent()) {
                http_response_code(429);
                header('Retry-After: ' . (string) $rl['retry_after']);
                header('Cache-Control: no-store');
                header('Content-Type: text/plain; charset=UTF-8');
            }
            echo 'Trop de requêtes. Réessayez plus tard.';
            exit;
        }
    }
}
