<?php

declare(strict_types=1);

if (!headers_sent()) {
    http_response_code(404);
    header('Cache-Control: no-cache');
}

$errorCode = 404;
$errorTitle = 'Page introuvable';
$errorMessage = 'Cette page n’existe pas ou a été déplacée. Vérifiez l’URL ou revenez à l’accueil.';

require __DIR__ . '/_layout.php';
