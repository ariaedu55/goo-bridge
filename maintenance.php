<?php

declare(strict_types=1);

/**
 * Point d’entrée maintenance (503 + Retry-After).
 * Peut être appelé directement ou via rewrite.
 */
require_once __DIR__ . '/includes/paths.php';

if (!headers_sent()) {
    http_response_code(503);
    header('Retry-After: 3600');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('X-Robots-Tag: noindex, nofollow');
}

require __DIR__ . '/errors/503.php';
