<?php

declare(strict_types=1);

if (!headers_sent()) {
    http_response_code(500);
    header('Cache-Control: no-store');
    header('X-Robots-Tag: noindex');
}

$errorCode = 500;
$errorTitle = 'Erreur serveur';
$errorMessage = 'Un problème technique est survenu. Notre équipe a été alertée — réessayez dans quelques instants.';

require __DIR__ . '/_layout.php';
