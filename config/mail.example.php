<?php

declare(strict_types=1);

/**
 * Copiez ce fichier vers mail.local.php et renseignez les valeurs.
 * mail.local.php ne doit pas être versionné (voir .gitignore).
 *
 * En production, privilégiez un fichier `.env` à la racine (copie de `.env.example`)
 * : il est chargé automatiquement par config/mail.php avant les variables serveur.
 *
 * Sinon, variables d’environnement (Apache SetEnv, panneau hébergeur, Docker), sans fichier :
 *
 * MAIL_ENABLED=true
 * SMTP_HOST=goo-bridge.com
 * SMTP_PORT=465
 * SMTP_ENCRYPTION=ssl
 * SMTP_USERNAME=contact@goo-bridge.com
 * SMTP_PASSWORD=mot_de_passe_de_la_boite_mail
 * MAIL_FROM_EMAIL=contact@goo-bridge.com
 * MAIL_FROM_NAME="Goo-Bridge — formulaire"
 * SMTP_HOSTNAME=goo-bridge.com
 * MAIL_TO_EMAIL=contact@goo-bridge.com
 * MAIL_TO_NAME="Goo-Bridge Contact"
 *
 * Si SMTP_USERNAME et SMTP_PASSWORD sont renseignés, MAIL_ENABLED peut être omis (envoi activé par défaut).
 */
return [
    'enabled' => false,
    'host' => 'goo-bridge.com',
    'port' => 465,
    /** ssl pour le port 465 (SMTPS) */
    'encryption' => 'ssl',
    'username' => 'no-reply@votredomaine.com',
    'password' => '',
    'from_email' => 'no-reply@votredomaine.com',
    'from_name' => 'Goo-Bridge — formulaire',
    /** Nom d’hôte annoncé en EHLO (délivrabilité). Souvent identique au domaine du mail. */
    'smtp_hostname' => 'goo-bridge.com',
    /** Destinataire des messages du formulaire contact */
    'to_email' => 'contact@goo-bridge.com',
    'to_name' => 'Goo-Bridge Contact',
];
