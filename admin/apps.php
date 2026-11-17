<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/paths.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/showcase_apps.php';

require_admin();

$pageTitle = 'Applications — Goo-Bridge Admin';
$pageHeading = 'Applications';
$pageSub = 'Section « Nos applications » de la landing';
$activeNav = 'apps';

$pdo = db();
showcase_apps_ensure_schema($pdo);

$flash = (string) ($_GET['flash'] ?? '');
$flashOk = [
    'created' => 'Application ajoutée.',
    'updated' => 'Mise à jour enregistrée.',
    'deleted' => 'Élément supprimé.',
    'float_saved' => 'Pastille enregistrée.',
];
$flashErr = [
    'csrf' => 'Session expirée : réessayez.',
    'error' => 'Une erreur est survenue.',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['_csrf'] ?? null)) {
        header('Location: ' . url('admin/apps.php?flash=csrf'), true, 302);
        exit;
    }

    $action = (string) ($_POST['action'] ?? '');

    try {
        if ($action === 'delete_app') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                $row = showcase_apps_find($pdo, $id);
                if ($row) {
                    if (!empty($row['image_path']) && str_starts_with((string) $row['image_path'], 'uploads/showcase_apps/')) {
                        showcase_apps_delete_upload_files($id);
                    }
                    $pdo->prepare('DELETE FROM showcase_apps WHERE id = :id LIMIT 1')->execute(['id' => $id]);
                }
            }
            header('Location: ' . url('admin/apps.php?flash=deleted'), true, 303);
            exit;
        }

        if ($action === 'toggle_app') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                $pdo->prepare(
                    'UPDATE showcase_apps SET is_published = IF(is_published = 1, 0, 1) WHERE id = :id LIMIT 1'
                )->execute(['id' => $id]);
            }
            header('Location: ' . url('admin/apps.php?flash=updated'), true, 303);
            exit;
        }

        if ($action === 'move_app') {
            $id = (int) ($_POST['id'] ?? 0);
            $dir = (string) ($_POST['dir'] ?? '');
            $items = showcase_apps_list_all($pdo);
            $idx = -1;
            foreach ($items as $i => $it) {
                if ((int) $it['id'] === $id) {
                    $idx = $i;
                    break;
                }
            }
            $swapWith = $dir === 'up' ? $idx - 1 : ($dir === 'down' ? $idx + 1 : -1);
            if ($idx >= 0 && $swapWith >= 0 && $swapWith < count($items)) {
                $a = $items[$idx];
                $b = $items[$swapWith];
                $up = $pdo->prepare('UPDATE showcase_apps SET sort_order = :s WHERE id = :id LIMIT 1');
                $up->execute(['s' => (int) $b['sort_order'], 'id' => (int) $a['id']]);
                $up->execute(['s' => (int) $a['sort_order'], 'id' => (int) $b['id']]);
            }
            header('Location: ' . url('admin/apps.php?flash=updated'), true, 303);
            exit;
        }

        if ($action === 'save_float') {
            $fid = (int) ($_POST['id'] ?? 0);
            $label = trim((string) ($_POST['label'] ?? ''));
            $color = showcase_normalize_hex((string) ($_POST['color_hex'] ?? '#2563eb'));
            $pos = (string) ($_POST['position_slot'] ?? 'tl');
            if (!in_array($pos, ['tl', 'bl', 'tr', 'mr', 'br'], true)) {
                $pos = 'tl';
            }
            $sort = (int) ($_POST['sort_order'] ?? 0);
            $published = isset($_POST['is_published']) ? 1 : 0;
            if ($label === '') {
                header('Location: ' . url('admin/apps.php?flash=error'), true, 302);
                exit;
            }
            if ($fid > 0) {
                $pdo->prepare(
                    'UPDATE showcase_app_floats
                     SET label = :label, color_hex = :color, position_slot = :pos, sort_order = :sort, is_published = :pub
                     WHERE id = :id LIMIT 1'
                )->execute([
                    'label' => $label,
                    'color' => $color,
                    'pos' => $pos,
                    'sort' => $sort,
                    'pub' => $published,
                    'id' => $fid,
                ]);
            } else {
                $pdo->prepare(
                    'INSERT INTO showcase_app_floats (label, color_hex, position_slot, sort_order, is_published)
                     VALUES (:label, :color, :pos, :sort, :pub)'
                )->execute([
                    'label' => $label,
                    'color' => $color,
                    'pos' => $pos,
                    'sort' => $sort,
                    'pub' => $published,
                ]);
            }
            header('Location: ' . url('admin/apps.php?flash=float_saved#floats'), true, 303);
            exit;
        }

        if ($action === 'delete_float') {
            $fid = (int) ($_POST['id'] ?? 0);
            if ($fid > 0) {
                $pdo->prepare('DELETE FROM showcase_app_floats WHERE id = :id LIMIT 1')->execute(['id' => $fid]);
            }
            header('Location: ' . url('admin/apps.php?flash=deleted#floats'), true, 303);
            exit;
        }
    } catch (Throwable $e) {
        header('Location: ' . url('admin/apps.php?flash=error'), true, 302);
        exit;
    }

    header('Location: ' . url('admin/apps.php'), true, 302);
    exit;
}

$apps = showcase_apps_list_all($pdo);
$floats = showcase_floats_list_all($pdo);
$editFloatId = (int) ($_GET['float_id'] ?? 0);
$editFloat = $editFloatId > 0 ? showcase_floats_find($pdo, $editFloatId) : null;

require __DIR__ . '/inc/header.php';
?>

<?php if (isset($flashOk[$flash])): ?>
  <p class="admin-alert admin-alert--ok"><?= htmlspecialchars($flashOk[$flash], ENT_QUOTES, 'UTF-8') ?></p>
<?php elseif (isset($flashErr[$flash])): ?>
  <p class="admin-alert admin-alert--error"><?= htmlspecialchars($flashErr[$flash], ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>

<div class="admin-toolbar">
  <p class="admin-muted" style="margin:0"><?= count($apps) ?> application(s) affichée(s) dans « Nos applications ».</p>
  <a class="btn-primary" href="<?= htmlspecialchars(url('admin/apps_edit.php'), ENT_QUOTES, 'UTF-8') ?>">+ Ajouter une app</a>
</div>

<div class="admin-card" style="overflow:auto;margin-bottom:28px">
  <table class="admin-table">
    <thead>
      <tr>
        <th>Aperçu</th>
        <th>Nom</th>
        <th>Stores / Web</th>
        <th>Taille</th>
        <th>Ordre</th>
        <th>Statut</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php if ($apps === []): ?>
        <tr><td colspan="7" class="admin-muted">Aucune application. Ajoutez ARIA, Colobanes, etc.</td></tr>
      <?php endif; ?>
      <?php foreach ($apps as $app): ?>
        <?php
          $id = (int) $app['id'];
          $imgUrl = showcase_apps_image_public_url($app['image_path'] ?? null);
          $published = ((int) ($app['is_published'] ?? 0)) === 1;
        ?>
        <tr>
          <td>
            <?php if ($imgUrl): ?>
              <img src="<?= htmlspecialchars($imgUrl, ENT_QUOTES, 'UTF-8') ?>" alt="" width="36" height="72" style="object-fit:cover;border-radius:8px;background:#0f1d3d">
            <?php else: ?>
              <span class="admin-muted">—</span>
            <?php endif; ?>
          </td>
          <td>
            <strong><?= htmlspecialchars((string) $app['title'], ENT_QUOTES, 'UTF-8') ?></strong><br>
            <span class="admin-muted"><?= htmlspecialchars(mb_strimwidth((string) $app['description'], 0, 72, '…', 'UTF-8'), ENT_QUOTES, 'UTF-8') ?></span>
          </td>
          <td class="admin-muted" style="font-size:.82rem">
            <?= !empty($app['app_store_url']) ? 'App Store · ' : '' ?>
            <?= !empty($app['play_store_url']) ? 'Play · ' : '' ?>
            <?= htmlspecialchars((string) ($app['domain_label'] ?: $app['website_url']), ENT_QUOTES, 'UTF-8') ?>
          </td>
          <td><?= (int) $app['image_width'] ?>×<?= (int) $app['image_height'] ?></td>
          <td>
            <div style="display:flex;gap:6px">
              <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="move_app"><input type="hidden" name="id" value="<?= $id ?>"><input type="hidden" name="dir" value="up"><button class="admin-btn admin-btn--ghost" type="submit">↑</button></form>
              <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="move_app"><input type="hidden" name="id" value="<?= $id ?>"><input type="hidden" name="dir" value="down"><button class="admin-btn admin-btn--ghost" type="submit">↓</button></form>
            </div>
          </td>
          <td>
            <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="toggle_app"><input type="hidden" name="id" value="<?= $id ?>">
              <button type="submit" class="admin-btn <?= $published ? 'admin-btn--ok' : 'admin-btn--ghost' ?>"><?= $published ? 'Publié' : 'Masqué' ?></button>
            </form>
          </td>
          <td style="white-space:nowrap;text-align:right">
            <a class="admin-btn" href="<?= htmlspecialchars(url('admin/apps_edit.php?id=' . $id), ENT_QUOTES, 'UTF-8') ?>">Modifier</a>
            <form method="post" style="display:inline" onsubmit="return confirm('Supprimer cette application ?');">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="delete_app">
              <input type="hidden" name="id" value="<?= $id ?>">
              <button type="submit" class="admin-btn admin-btn--danger">Supprimer</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<h3 id="floats" style="margin:0 0 12px">Pastilles flottantes</h3>
<p class="admin-muted" style="margin-top:0">Les étiquettes autour des téléphones (ex. « Marketplace »).</p>

<div class="admin-card" style="margin-bottom:20px;max-width:720px">
  <form method="post" class="admin-form">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save_float">
    <input type="hidden" name="id" value="<?= $editFloat ? (int) $editFloat['id'] : 0 ?>">
    <div class="admin-field">
      <label for="label">Libellé</label>
      <input type="text" id="label" name="label" required maxlength="80"
        value="<?= htmlspecialchars((string) ($editFloat['label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <div class="admin-field" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px">
      <div>
        <label for="color_hex">Couleur</label>
        <input type="color" id="color_hex" name="color_hex"
          value="<?= htmlspecialchars(showcase_normalize_hex((string) ($editFloat['color_hex'] ?? '#2563eb')), ENT_QUOTES, 'UTF-8') ?>">
      </div>
      <div>
        <label for="position_slot">Position</label>
        <?php $pos = (string) ($editFloat['position_slot'] ?? 'tl'); ?>
        <select id="position_slot" name="position_slot">
          <option value="tl" <?= $pos === 'tl' ? 'selected' : '' ?>>Haut gauche</option>
          <option value="bl" <?= $pos === 'bl' ? 'selected' : '' ?>>Bas gauche</option>
          <option value="tr" <?= $pos === 'tr' ? 'selected' : '' ?>>Haut droite</option>
          <option value="mr" <?= $pos === 'mr' ? 'selected' : '' ?>>Milieu droite</option>
          <option value="br" <?= $pos === 'br' ? 'selected' : '' ?>>Bas droite</option>
        </select>
      </div>
      <div>
        <label for="sort_order">Ordre</label>
        <input type="number" id="sort_order" name="sort_order" value="<?= (int) ($editFloat['sort_order'] ?? 100) ?>">
      </div>
    </div>
    <div class="admin-field">
      <label style="display:flex;gap:8px;align-items:center;font-weight:600">
        <input type="checkbox" name="is_published" value="1" <?= ((int) ($editFloat['is_published'] ?? 1)) === 1 ? 'checked' : '' ?>>
        Publiée
      </label>
    </div>
    <button type="submit" class="btn-primary"><?= $editFloat ? 'Mettre à jour la pastille' : 'Ajouter une pastille' ?></button>
    <?php if ($editFloat): ?>
      <a class="admin-btn admin-btn--ghost" href="<?= htmlspecialchars(url('admin/apps.php#floats'), ENT_QUOTES, 'UTF-8') ?>">Annuler</a>
    <?php endif; ?>
  </form>
</div>

<div class="admin-card" style="overflow:auto">
  <table class="admin-table">
    <thead>
      <tr><th>Libellé</th><th>Couleur</th><th>Position</th><th>Ordre</th><th>Statut</th><th></th></tr>
    </thead>
    <tbody>
      <?php foreach ($floats as $f): ?>
        <tr>
          <td><strong><?= htmlspecialchars((string) $f['label'], ENT_QUOTES, 'UTF-8') ?></strong></td>
          <td>
            <span style="display:inline-block;width:14px;height:14px;border-radius:50%;background:<?= htmlspecialchars((string) $f['color_hex'], ENT_QUOTES, 'UTF-8') ?>;vertical-align:middle"></span>
            <?= htmlspecialchars((string) $f['color_hex'], ENT_QUOTES, 'UTF-8') ?>
          </td>
          <td><?= htmlspecialchars((string) $f['position_slot'], ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= (int) $f['sort_order'] ?></td>
          <td><?= ((int) $f['is_published']) === 1 ? 'Publiée' : 'Masquée' ?></td>
          <td style="text-align:right;white-space:nowrap">
            <a class="admin-btn" href="<?= htmlspecialchars(url('admin/apps.php?float_id=' . (int) $f['id'] . '#floats'), ENT_QUOTES, 'UTF-8') ?>">Modifier</a>
            <form method="post" style="display:inline" onsubmit="return confirm('Supprimer cette pastille ?');">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="delete_float">
              <input type="hidden" name="id" value="<?= (int) $f['id'] ?>">
              <button type="submit" class="admin-btn admin-btn--danger">Supprimer</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/inc/footer.php'; ?>
