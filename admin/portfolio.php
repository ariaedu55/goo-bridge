<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/paths.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/portfolio.php';

require_admin();

$pageTitle = 'Réalisations — Goo-Bridge Admin';
$pageHeading = 'Réalisations';
$pageSub = 'Gérez les sites et apps affichés sur la landing';
$activeNav = 'portfolio';

$pdo = db();
portfolio_ensure_schema($pdo);

$flash = (string) ($_GET['flash'] ?? '');
$flashOk = [
    'created' => 'Réalisation ajoutée.',
    'updated' => 'Réalisation mise à jour.',
    'deleted' => 'Réalisation supprimée.',
    'reordered' => 'Ordre mis à jour.',
];
$flashErr = [
    'csrf' => 'Session expirée : réessayez.',
    'error' => 'Une erreur est survenue.',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['_csrf'] ?? null)) {
        header('Location: ' . url('admin/portfolio.php?flash=csrf'), true, 302);
        exit;
    }

    $action = (string) ($_POST['action'] ?? '');

    try {
        if ($action === 'delete') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                $row = portfolio_find($pdo, $id);
                if ($row) {
                    if (!empty($row['image_path']) && str_starts_with((string) $row['image_path'], 'uploads/portfolio/')) {
                        portfolio_delete_upload_files($id);
                    }
                    $pdo->prepare('DELETE FROM portfolio_items WHERE id = :id LIMIT 1')->execute(['id' => $id]);
                }
            }
            header('Location: ' . url('admin/portfolio.php?flash=deleted'), true, 303);
            exit;
        }

        if ($action === 'toggle') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                $pdo->prepare(
                    'UPDATE portfolio_items SET is_published = IF(is_published = 1, 0, 1) WHERE id = :id LIMIT 1'
                )->execute(['id' => $id]);
            }
            header('Location: ' . url('admin/portfolio.php?flash=updated'), true, 303);
            exit;
        }

        if ($action === 'move') {
            $id = (int) ($_POST['id'] ?? 0);
            $dir = (string) ($_POST['dir'] ?? '');
            $items = portfolio_list_all($pdo);
            $idx = -1;
            foreach ($items as $i => $it) {
                if ((int) $it['id'] === $id) {
                    $idx = $i;
                    break;
                }
            }
            if ($idx >= 0) {
                $swapWith = $dir === 'up' ? $idx - 1 : ($dir === 'down' ? $idx + 1 : -1);
                if ($swapWith >= 0 && $swapWith < count($items)) {
                    $a = $items[$idx];
                    $b = $items[$swapWith];
                    $sortA = (int) $a['sort_order'];
                    $sortB = (int) $b['sort_order'];
                    if ($sortA === $sortB) {
                        $sortA = ($idx + 1) * 10;
                        $sortB = ($swapWith + 1) * 10;
                    }
                    $up = $pdo->prepare('UPDATE portfolio_items SET sort_order = :s WHERE id = :id LIMIT 1');
                    $up->execute(['s' => $sortB, 'id' => (int) $a['id']]);
                    $up->execute(['s' => $sortA, 'id' => (int) $b['id']]);
                }
            }
            header('Location: ' . url('admin/portfolio.php?flash=reordered'), true, 303);
            exit;
        }
    } catch (Throwable $e) {
        header('Location: ' . url('admin/portfolio.php?flash=error'), true, 302);
        exit;
    }

    header('Location: ' . url('admin/portfolio.php'), true, 302);
    exit;
}

$items = portfolio_list_all($pdo);

require __DIR__ . '/inc/header.php';
?>

<?php if (isset($flashOk[$flash])): ?>
  <p class="admin-alert admin-alert--ok"><?= htmlspecialchars($flashOk[$flash], ENT_QUOTES, 'UTF-8') ?></p>
<?php elseif (isset($flashErr[$flash])): ?>
  <p class="admin-alert admin-alert--error"><?= htmlspecialchars($flashErr[$flash], ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>

<div class="admin-toolbar">
  <div>
    <p class="admin-muted" style="margin:0"><?= count($items) ?> projet(s) — visibles sur la section « Nos réalisations » du site.</p>
  </div>
  <a class="btn-primary" href="<?= htmlspecialchars(url('admin/portfolio_edit.php'), ENT_QUOTES, 'UTF-8') ?>">+ Ajouter un projet</a>
</div>

<div class="admin-card" style="overflow:auto">
  <table class="admin-table">
    <thead>
      <tr>
        <th>Aperçu</th>
        <th>Titre</th>
        <th>Type</th>
        <th>Tags</th>
        <th>Taille</th>
        <th>Ordre</th>
        <th>Statut</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php if ($items === []): ?>
        <tr><td colspan="8" class="admin-muted">Aucun projet. Ajoutez votre première réalisation.</td></tr>
      <?php endif; ?>
      <?php foreach ($items as $it): ?>
        <?php
          $id = (int) $it['id'];
          $imgUrl = portfolio_image_public_url($it['image_path'] ?? null);
          $kind = (string) ($it['project_kind'] ?? 'site');
          $published = ((int) ($it['is_published'] ?? 0)) === 1;
        ?>
        <tr>
          <td>
            <?php if ($imgUrl): ?>
              <img src="<?= htmlspecialchars($imgUrl, ENT_QUOTES, 'UTF-8') ?>" alt="" width="40" height="72" style="object-fit:cover;border-radius:8px;background:#0f1d3d">
            <?php else: ?>
              <span class="admin-muted">—</span>
            <?php endif; ?>
          </td>
          <td>
            <strong><?= htmlspecialchars((string) $it['title'], ENT_QUOTES, 'UTF-8') ?></strong><br>
            <span class="admin-muted"><?= htmlspecialchars((string) ($it['domain_label'] ?: $it['website_url']), ENT_QUOTES, 'UTF-8') ?></span>
          </td>
          <td><?= $kind === 'app' ? 'Application' : 'Site' ?></td>
          <td><?= htmlspecialchars((string) $it['tags'], ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= (int) $it['image_width'] ?>×<?= (int) $it['image_height'] ?></td>
          <td>
            <div style="display:flex;gap:6px;align-items:center">
              <form method="post" action="">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="move">
                <input type="hidden" name="id" value="<?= $id ?>">
                <input type="hidden" name="dir" value="up">
                <button type="submit" class="admin-btn admin-btn--ghost" title="Monter">↑</button>
              </form>
              <form method="post" action="">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="move">
                <input type="hidden" name="id" value="<?= $id ?>">
                <input type="hidden" name="dir" value="down">
                <button type="submit" class="admin-btn admin-btn--ghost" title="Descendre">↓</button>
              </form>
            </div>
          </td>
          <td>
            <form method="post" action="">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="toggle">
              <input type="hidden" name="id" value="<?= $id ?>">
              <button type="submit" class="admin-btn <?= $published ? 'admin-btn--ok' : 'admin-btn--ghost' ?>">
                <?= $published ? 'Publié' : 'Masqué' ?>
              </button>
            </form>
          </td>
          <td style="white-space:nowrap;text-align:right">
            <a class="admin-btn" href="<?= htmlspecialchars(url('admin/portfolio_edit.php?id=' . $id), ENT_QUOTES, 'UTF-8') ?>">Modifier</a>
            <form method="post" action="" style="display:inline" onsubmit="return confirm('Supprimer cette réalisation ?');">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= $id ?>">
              <button type="submit" class="admin-btn admin-btn--danger">Supprimer</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/inc/footer.php'; ?>
