<?php

declare(strict_types=1);

if (!headers_sent()) {
    http_response_code(503);
    header('Retry-After: 3600');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('X-Robots-Tag: noindex, nofollow');
}

$errorCode = 503;
$errorTitle = 'Maintenance en cours';
$errorMessage = 'Goo-Bridge est temporairement indisponible pour une maintenance planifiée. Google et les moteurs comprennent le 503 : votre SEO n’est pas pénalisé. Revenez bientôt.';

require __DIR__ . '/_layout.php';
