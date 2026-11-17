<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/paths.php';
require_once __DIR__ . '/includes/honeypot.php';
require_once __DIR__ . '/includes/rate_limit.php';
require_once __DIR__ . '/includes/app_log.php';

$form_error = '';
$form_success = isset($_GET['sent']) && $_GET['sent'] === '1';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    return;
}

if (!csrf_verify($_POST['_csrf'] ?? null)) {
    $form_error = 'Session expirée : rechargez la page et réessayez.';
    return;
}

if (honeypot_triggered()) {
    app_log_write('security', 'Honeypot contact déclenché', ['form' => 'contact']);
    // Fausse réussite pour ne pas informer le bot.
    $redirect = security_safe_request_path(url('index.php'));
    header('Location: ' . $redirect . '?sent=1#contact', true, 303);
    exit;
}

$rl = rate_limit_hit('contact_form', 8, 3600);
if (!$rl['allowed']) {
    app_log_write('security', 'Rate limit contact', ['retry_after' => $rl['retry_after']]);
    $form_error = 'Trop de messages envoyés. Réessayez dans ' . $rl['retry_after'] . ' seconde(s).';
    return;
}

$now = time();
$last = (int) ($_SESSION['contact_last_submit'] ?? 0);
if ($last > 0 && ($now - $last) < 20) {
    $form_error = 'Veuillez patienter quelques secondes avant de renvoyer un message.';
    return;
}

$name = trim((string) ($_POST['name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$phone = trim((string) ($_POST['phone'] ?? ''));
$service = trim((string) ($_POST['service'] ?? ''));
$message = trim((string) ($_POST['message'] ?? ''));

if (mb_strlen($name) > 120) {
    $name = mb_substr($name, 0, 120);
}
if (mb_strlen($email) > 255) {
    $email = mb_substr($email, 0, 255);
}
if (mb_strlen($service) > 120) {
    $service = mb_substr($service, 0, 120);
}
if (mb_strlen($message) > 5000) {
    $message = mb_substr($message, 0, 5000);
}

if ($name === '' || $email === '' || $message === '') {
    $form_error = 'Veuillez remplir tous les champs obligatoires.';
    return;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $form_error = 'Adresse email invalide.';
    return;
}

if ($phone !== '') {
    if (strlen($phone) > 32 || !preg_match('/^[\d\s().+-]{6,32}$/', $phone)) {
        $form_error = 'Numéro de téléphone invalide.';
        return;
    }
}

$storageDir = __DIR__ . '/storage';
if (!is_dir($storageDir) && !mkdir($storageDir, 0755, true) && !is_dir($storageDir)) {
    $form_error = 'Impossible d\'enregistrer votre message pour le moment. Réessayez plus tard.';
    return;
}

$record = [
    't' => date('c'),
    'name' => $name,
    'email' => $email,
    'phone' => $phone,
    'service' => $service,
    'message' => $message,
];
$json = json_encode($record, JSON_UNESCAPED_UNICODE);
if ($json === false) {
    $form_error = 'Erreur lors de l\'enregistrement du message.';
    return;
}
$line = $json . "\n";

if (file_put_contents($storageDir . '/contacts.log', $line, FILE_APPEND | LOCK_EX) === false) {
    $form_error = 'Impossible d\'enregistrer votre message pour le moment. Réessayez plus tard.';
    return;
}

$_SESSION['contact_last_submit'] = $now;

require_once __DIR__ . '/includes/contact_mail.php';
send_contact_mail($record);

$redirect = security_safe_request_path(url('index.php'));
header('Location: ' . $redirect . '?sent=1#contact', true, 303);
exit;
