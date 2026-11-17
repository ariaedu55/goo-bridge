<?php

declare(strict_types=1);

function maintenance_flag_file(): string
{
    return dirname(__DIR__) . '/storage/maintenance.flag';
}

function maintenance_is_enabled(): bool
{
    return is_file(maintenance_flag_file());
}

function maintenance_enable(): bool
{
    $dir = dirname(maintenance_flag_file());
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }

    return @file_put_contents(
        maintenance_flag_file(),
        json_encode(['enabled_at' => date('c')], JSON_UNESCAPED_UNICODE)
    ) !== false;
}

function maintenance_disable(): bool
{
    $file = maintenance_flag_file();
    if (!is_file($file)) {
        return true;
    }

    return @unlink($file);
}

function maintenance_request_is_bypassed(): bool
{
    $script = (string) ($_SERVER['SCRIPT_NAME'] ?? '');
    $base = basename($script);

    // Admin, auth, assets d’erreur et page maintenance elle-même
    if (str_contains($script, '/admin/')) {
        return true;
    }
    if (str_contains($script, '/errors/')) {
        return true;
    }
    if (in_array($base, ['maintenance.php', 'login.php', 'logout.php', 'install.php'], true)) {
        return true;
    }

    // Déjà connecté en admin
    if (!empty($_SESSION['admin_id'])) {
        return true;
    }

    return false;
}

/**
 * Affiche la page 503 SEO-friendly si le mode maintenance est actif.
 */
function maintenance_enforce(): void
{
    if (!maintenance_is_enabled() || maintenance_request_is_bypassed()) {
        return;
    }

    require_once __DIR__ . '/paths.php';
    if (!headers_sent()) {
        http_response_code(503);
        header('Retry-After: 3600');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('X-Robots-Tag: noindex, nofollow');
    }
    require dirname(__DIR__) . '/errors/503.php';
    exit;
}
