<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/paths.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

require_admin();

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title>Diagnostic - Goo-Bridge</title>
  <style>
    body { font-family: system-ui, sans-serif; max-width: 800px; margin: 40px auto; padding: 0 20px; }
    .ok { color: #15803d; font-weight: bold; }
    .error { color: #b91c1c; font-weight: bold; }
    .info { background: #f3f4f6; padding: 15px; border-radius: 8px; margin: 10px 0; }
    h1 { color: #0f1d3d; }
    h2 { margin-top: 30px; border-bottom: 2px solid #e5e7eb; padding-bottom: 5px; }
    code { background: #f9fafb; padding: 2px 6px; border-radius: 4px; }
  </style>
</head>
<body>
  <h1>Diagnostic Goo-Bridge</h1>
  <p><a href="<?= htmlspecialchars(url('admin/dashboard.php'), ENT_QUOTES, 'UTF-8') ?>">← Dashboard</a></p>

  <h2>État de la session</h2>
  <div class="info">
    <p class="ok">✓ Connecté</p>
    <p>ID Admin: <?= (int) (current_admin_id() ?? 0) ?></p>
    <p>Email: <?= htmlspecialchars(current_admin_email() ?? '', ENT_QUOTES, 'UTF-8') ?></p>
    <p>Super-admin: <?= current_admin_is_super() ? 'oui' : 'non' ?></p>
  </div>

  <h2>Base de données</h2>
  <div class="info">
    <?php
    try {
      $pdo = db();
      echo '<p class="ok">✓ Connexion MySQL OK</p>';
      $db = $pdo->query('SELECT DATABASE()')->fetchColumn();
      echo '<p>Base active: <code>' . htmlspecialchars((string) $db, ENT_QUOTES, 'UTF-8') . '</code></p>';

      if (current_admin_is_super()) {
        $admins = $pdo->query('SELECT id, email FROM admins ORDER BY id')->fetchAll();
        echo '<p>Comptes administrateurs (' . count($admins) . '):</p><ul>';
        foreach ($admins as $a) {
          echo '<li>' . htmlspecialchars((string) $a['email'], ENT_QUOTES, 'UTF-8') . ' (ID: ' . (int) $a['id'] . ')</li>';
        }
        echo '</ul>';
      } else {
        echo '<p>Liste des comptes réservée aux super-administrateurs.</p>';
      }
    } catch (Throwable $e) {
      echo '<p class="error">✗ Erreur MySQL: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
    }
    ?>
  </div>

  <h2>Configuration</h2>
  <div class="info">
    <p>BASE_URL: <code><?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?></code></p>
    <p>PHP Version: <code><?= htmlspecialchars(PHP_VERSION, ENT_QUOTES, 'UTF-8') ?></code></p>
  </div>
</body>
</html>
