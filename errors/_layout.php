<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/paths.php';

/** @var int $errorCode */
/** @var string $errorTitle */
/** @var string $errorMessage */

$errorCode = $errorCode ?? 500;
$errorTitle = $errorTitle ?? 'Erreur';
$errorMessage = $errorMessage ?? 'Une erreur est survenue.';
$homeUrl = url('index.php');
$is503 = $errorCode === 503;

?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= (int) $errorCode ?> — <?= htmlspecialchars($errorTitle, ENT_QUOTES, 'UTF-8') ?> | Goo-Bridge</title>
  <?php if ($is503 || $errorCode >= 500): ?>
  <meta name="robots" content="noindex, nofollow">
  <?php endif; ?>
  <link rel="icon" type="image/webp" href="<?= htmlspecialchars(url('images/favicon-goo-bridge.webp'), ENT_QUOTES, 'UTF-8') ?>">
  <link rel="stylesheet" href="<?= htmlspecialchars(url('style.css'), ENT_QUOTES, 'UTF-8') ?>">
  <style>
    .err-page{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem;background:linear-gradient(160deg,#0b1c33 0%,#122a4a 45%,#1a3a5c 100%);color:#f4f7fb;font-family:Poppins,system-ui,sans-serif}
    .err-card{max-width:34rem;width:100%;text-align:center}
    .err-code{font-size:clamp(3.5rem,10vw,5.5rem);font-weight:800;letter-spacing:-.04em;color:#f07a28;line-height:1;margin:0 0 .5rem}
    .err-title{font-size:1.35rem;font-weight:700;margin:0 0 .75rem}
    .err-msg{opacity:.85;line-height:1.55;margin:0 0 1.75rem}
    .err-actions{display:flex;flex-wrap:wrap;gap:.75rem;justify-content:center}
    .err-actions a{display:inline-flex;align-items:center;justify-content:center;padding:.7rem 1.25rem;border-radius:999px;font-weight:600;text-decoration:none}
    .err-actions .btn-home{background:#f07a28;color:#fff}
    .err-actions .btn-home:hover{filter:brightness(1.05)}
    .err-brand{display:inline-block;margin-bottom:1.5rem}
    .err-brand img{height:48px;width:auto}
  </style>
</head>
<body class="err-page">
  <div class="err-card">
    <a class="err-brand" href="<?= htmlspecialchars($homeUrl, ENT_QUOTES, 'UTF-8') ?>" aria-label="GOO-BRIDGE">
      <img src="<?= htmlspecialchars(url('images/logo-goo-bridge.webp'), ENT_QUOTES, 'UTF-8') ?>" alt="GOO-BRIDGE" width="148" height="60" decoding="async">
    </a>
    <p class="err-code"><?= (int) $errorCode ?></p>
    <h1 class="err-title"><?= htmlspecialchars($errorTitle, ENT_QUOTES, 'UTF-8') ?></h1>
    <p class="err-msg"><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></p>
    <div class="err-actions">
      <a class="btn-home" href="<?= htmlspecialchars($homeUrl, ENT_QUOTES, 'UTF-8') ?>">Retour à l’accueil</a>
    </div>
  </div>
</body>
</html>
