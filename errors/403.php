<?php

declare(strict_types=1);

if (!headers_sent()) {
    http_response_code(403);
    header('Cache-Control: no-store');
    header('X-Robots-Tag: noindex');
}

$errorCode = 403;
$errorTitle = 'Accès refusé';
$errorMessage = 'Vous n’avez pas l’autorisation d’accéder à cette ressource.';

require __DIR__ . '/_layout.php';
