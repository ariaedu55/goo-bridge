<?php

declare(strict_types=1);

/**
 * Configuration mail.
 *
 * Ordre de chargement :
 * 1. Fichier `.env` à la racine du site (optionnel, non versionné) — voir `.env.example`.
 * 2. Variables d’environnement déjà définies par le serveur (recommandé en production).
 * 3. `config/mail.local.php` si présent (pratique en local ou hébergement mutualisé).
 * 4. `config/mail.example.php` avec envoi désactivé.
 */
require_once __DIR__ . '/../includes/load_env_file.php';
goo_bridge_load_env_file(__DIR__ . '/../.env');

$env = static function (string $key, ?string $default = null): ?string {
    $value = getenv($key);
    if ($value === false || $value === '') {
        return $default;
    }

    return $value;
};

$smtpUser = (string) ($env('SMTP_USERNAME') ?? '');
$smtpPass = (string) ($env('SMTP_PASSWORD') ?? '');
$smtpHost = $env('SMTP_HOST');

$mailEnabledRaw = getenv('MAIL_ENABLED');
$mailEnabledExplicit = $mailEnabledRaw !== false && $mailEnabledRaw !== '';

$useEnvBlock = $mailEnabledExplicit
    || $smtpUser !== ''
    || $smtpPass !== ''
    || ($smtpHost !== null && $smtpHost !== '');

if ($useEnvBlock) {
    if ($mailEnabledExplicit) {
        $enabled = filter_var($mailEnabledRaw, FILTER_VALIDATE_BOOLEAN);
    } else {
        // En production : si identifiants complets sans MAIL_ENABLED explicite → envoi activé
        $enabled = $smtpUser !== '' && $smtpPass !== '';
    }

    $username = $smtpUser;

    $fromDefault = $username !== '' ? $username : 'contact@goo-bridge.com';

    return [
        'enabled' => $enabled,
        'host' => $smtpHost ?? 'goo-bridge.com',
        'port' => (int) $env('SMTP_PORT', '465'),
        /** ssl pour le port 465 (SMTPS), tls pour le port 587 */
        'encryption' => $env('SMTP_ENCRYPTION', 'ssl') ?: 'ssl',
        'username' => $username,
        'password' => $smtpPass,
        'from_email' => $env('MAIL_FROM_EMAIL', $fromDefault) ?: $fromDefault,
        'from_name' => $env('MAIL_FROM_NAME', 'Goo-Bridge — formulaire') ?: 'Goo-Bridge — formulaire',
        'smtp_hostname' => $env('SMTP_HOSTNAME', 'goo-bridge.com') ?: 'goo-bridge.com',
        'to_email' => $env('MAIL_TO_EMAIL', 'contact@goo-bridge.com') ?: 'contact@goo-bridge.com',
        'to_name' => $env('MAIL_TO_NAME', 'Goo-Bridge Contact') ?: 'Goo-Bridge Contact',
    ];
}

$local = __DIR__ . '/mail.local.php';

return is_readable($local) ? require $local : require __DIR__ . '/mail.example.php';
