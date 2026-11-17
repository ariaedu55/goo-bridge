<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/paths.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/showcase_apps.php';

require_admin();

$pdo = db();
showcase_apps_ensure_schema($pdo);

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$isEdit = $id > 0;
$item = $isEdit ? showcase_apps_find($pdo, $id) : null;

if ($isEdit && $item === null) {
    header('Location: ' . url('admin/apps.php?flash=error'), true, 302);
    exit;
}

$pageTitle = ($isEdit ? 'Modifier' : 'Ajouter') . ' une application — Goo-Bridge Admin';
$pageHeading = $isEdit ? 'Modifier l’application' : 'Nouvelle application';
$pageSub = 'Affichée dans la section Nos applications (iOS & Android)';
$activeNav = 'apps';

$error = '';
$flash = (string) ($_GET['flash'] ?? '');

$form = [
    'title' => (string) ($item['title'] ?? ''),
    'description' => (string) ($item['description'] ?? ''),
    'website_url' => (string) ($item['website_url'] ?? ''),
    'domain_label' => (string) ($item['domain_label'] ?? ''),
    'app_store_url' => (string) ($item['app_store_url'] ?? ''),
    'play_store_url' => (string) ($item['play_store_url'] ?? ''),
    'image_width' => (int) ($item['image_width'] ?? 743),
    'image_height' => (int) ($item['image_height'] ?? 1533),
    'theme' => (string) ($item['theme'] ?? 'navy'),
    'sort_order' => (int) ($item['sort_order'] ?? 100),
    'is_published' => (int) ($item['is_published'] ?? 1),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['_csrf'] ?? null)) {
        $error = 'Session expirée : rechargez la page.';
    } else {
        $form['title'] = trim((string) ($_POST['title'] ?? ''));
        $form['description'] = trim((string) ($_POST['description'] ?? ''));
        $form['website_url'] = trim((string) ($_POST['website_url'] ?? ''));
        $form['domain_label'] = trim((string) ($_POST['domain_label'] ?? ''));
        $form['app_store_url'] = trim((string) ($_POST['app_store_url'] ?? ''));
        $form['play_store_url'] = trim((string) ($_POST['play_store_url'] ?? ''));
        $form['image_width'] = max(200, min(1200, (int) ($_POST['image_width'] ?? 743)));
        $form['image_height'] = max(400, min(2500, (int) ($_POST['image_height'] ?? 1533)));
        $form['theme'] = in_array($_POST['theme'] ?? '', ['navy', 'gradient'], true) ? (string) $_POST['theme'] : 'navy';
        $form['sort_order'] = (int) ($_POST['sort_order'] ?? 100);
        $form['is_published'] = isset($_POST['is_published']) ? 1 : 0;

        if ($form['domain_label'] === '' && $form['website_url'] !== '') {
            $form['domain_label'] = showcase_domain_from_url($form['website_url']);
        }

        $urlOk = static function (string $u): bool {
            return $u === '' || filter_var($u, FILTER_VALIDATE_URL) !== false;
        };

        if ($form['title'] === '' || $form['description'] === '') {
            $error = 'Nom et description obligatoires.';
        } elseif (!$urlOk($form['website_url']) || !$urlOk($form['app_store_url']) || !$urlOk($form['play_store_url'])) {
            $error = 'Une des URL est invalide.';
        } else {
            try {
                if ($isEdit) {
                    $pdo->prepare(
                        'UPDATE showcase_apps SET
                           title = :title, description = :desc, website_url = :web, domain_label = :domain,
                           app_store_url = :ios, play_store_url = :play,
                           image_width = :w, image_height = :h, theme = :theme,
                           sort_order = :sort, is_published = :pub
                         WHERE id = :id LIMIT 1'
                    )->execute([
                        'title' => $form['title'],
                        'desc' => $form['description'],
                        'web' => $form['website_url'],
                        'domain' => $form['domain_label'],
                        'ios' => $form['app_store_url'],
                        'play' => $form['play_store_url'],
                        'w' => $form['image_width'],
                        'h' => $form['image_height'],
                        'theme' => $form['theme'],
                        'sort' => $form['sort_order'],
                        'pub' => $form['is_published'],
                        'id' => $id,
                    ]);
                    $uploadErr = showcase_apps_save_upload($pdo, $id, $_FILES['image'] ?? null);
                    if ($uploadErr !== null) {
                        $error = $uploadErr;
                    } else {
                        if (showcase_apps_has_upload($_FILES['image'] ?? null)) {
                            $pdo->prepare(
                                'UPDATE showcase_apps SET image_width = :w, image_height = :h WHERE id = :id LIMIT 1'
                            )->execute([
                                'w' => $form['image_width'],
                                'h' => $form['image_height'],
                                'id' => $id,
                            ]);
                        }
                        header('Location: ' . url('admin/apps.php?flash=updated'), true, 303);
                        exit;
                    }
                } else {
                    $pdo->prepare(
                        'INSERT INTO showcase_apps
                         (title, description, website_url, domain_label, app_store_url, play_store_url,
                          image_width, image_height, theme, sort_order, is_published)
                         VALUES (:title, :desc, :web, :domain, :ios, :play, :w, :h, :theme, :sort, :pub)'
                    )->execute([
                        'title' => $form['title'],
                        'desc' => $form['description'],
                        'web' => $form['website_url'],
                        'domain' => $form['domain_label'],
                        'ios' => $form['app_store_url'],
                        'play' => $form['play_store_url'],
                        'w' => $form['image_width'],
                        'h' => $form['image_height'],
                        'theme' => $form['theme'],
                        'sort' => $form['sort_order'],
                        'pub' => $form['is_published'],
                    ]);
                    $newId = (int) $pdo->lastInsertId();
                    $uploadErr = showcase_apps_save_upload($pdo, $newId, $_FILES['image'] ?? null);
                    if ($uploadErr !== null) {
                        header('Location: ' . url('admin/apps_edit.php?id=' . $newId . '&flash=img'), true, 303);
                        exit;
                    }
                    if (showcase_apps_has_upload($_FILES['image'] ?? null)) {
                        $pdo->prepare(
                            'UPDATE showcase_apps SET image_width = :w, image_height = :h WHERE id = :id LIMIT 1'
                        )->execute([
                            'w' => $form['image_width'],
                            'h' => $form['image_height'],
                            'id' => $newId,
                        ]);
                    }
                    header('Location: ' . url('admin/apps.php?flash=created'), true, 303);
                    exit;
                }
            } catch (Throwable $e) {
                $error = 'Erreur d’enregistrement. Réessayez.';
            }
        }
    }
}

$currentImg = $item ? showcase_apps_image_public_url($item['image_path'] ?? null) : null;

require __DIR__ . '/inc/header.php';
?>

<?php if ($flash === 'img'): ?>
  <p class="admin-alert admin-alert--error">App créée, mais l’image n’a pas pu être importée. Ajoutez-la ci-dessous.</p>
<?php endif; ?>
<?php if ($error !== ''): ?>
  <p class="admin-alert admin-alert--error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>

<div class="admin-card">
  <form method="post" enctype="multipart/form-data" class="admin-form" style="max-width:720px">
    <?= csrf_field() ?>
    <?php if ($isEdit): ?>
      <input type="hidden" name="id" value="<?= $id ?>">
    <?php endif; ?>

    <div class="admin-field">
      <label for="title">Nom *</label>
      <input type="text" id="title" name="title" required maxlength="160"
        value="<?= htmlspecialchars($form['title'], ENT_QUOTES, 'UTF-8') ?>">
    </div>

    <div class="admin-field">
      <label for="description">Description *</label>
      <textarea id="description" name="description" rows="3" required maxlength="1000"><?= htmlspecialchars($form['description'], ENT_QUOTES, 'UTF-8') ?></textarea>
    </div>

    <div class="admin-field">
      <label for="theme">Style du titre</label>
      <select id="theme" name="theme">
        <option value="navy" <?= $form['theme'] === 'navy' ? 'selected' : '' ?>>Navy (ARIA)</option>
        <option value="gradient" <?= $form['theme'] === 'gradient' ? 'selected' : '' ?>>Dégradé orange/bleu (Colobanes)</option>
      </select>
    </div>

    <div class="admin-field">
      <label for="website_url">Site web</label>
      <input type="url" id="website_url" name="website_url" maxlength="512" placeholder="https://exemple.com"
        value="<?= htmlspecialchars($form['website_url'], ENT_QUOTES, 'UTF-8') ?>">
    </div>

    <div class="admin-field">
      <label for="domain_label">Libellé du lien</label>
      <input type="text" id="domain_label" name="domain_label" maxlength="160" placeholder="exemple.com"
        value="<?= htmlspecialchars($form['domain_label'], ENT_QUOTES, 'UTF-8') ?>">
    </div>

    <div class="admin-field">
      <label for="app_store_url">Lien App Store</label>
      <input type="url" id="app_store_url" name="app_store_url" maxlength="512"
        value="<?= htmlspecialchars($form['app_store_url'], ENT_QUOTES, 'UTF-8') ?>">
    </div>

    <div class="admin-field">
      <label for="play_store_url">Lien Google Play</label>
      <input type="url" id="play_store_url" name="play_store_url" maxlength="512"
        value="<?= htmlspecialchars($form['play_store_url'], ENT_QUOTES, 'UTF-8') ?>">
    </div>

    <div class="admin-field" style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
      <div>
        <label for="image_width">Largeur image (px)</label>
        <input type="number" id="image_width" name="image_width" min="200" max="1200"
          value="<?= (int) $form['image_width'] ?>">
      </div>
      <div>
        <label for="image_height">Hauteur image (px)</label>
        <input type="number" id="image_height" name="image_height" min="400" max="2500"
          value="<?= (int) $form['image_height'] ?>">
      </div>
    </div>

    <div class="admin-field">
      <label for="sort_order">Ordre d’affichage</label>
      <input type="number" id="sort_order" name="sort_order" value="<?= (int) $form['sort_order'] ?>">
    </div>

    <div class="admin-field">
      <label for="image">Capture téléphone (JPG, PNG, WebP, GIF — max 5 Mo)</label>
      <?php if ($currentImg): ?>
        <div style="margin-bottom:10px">
          <img src="<?= htmlspecialchars($currentImg, ENT_QUOTES, 'UTF-8') ?>" alt="" width="80" height="160"
            style="object-fit:cover;border-radius:14px;background:#0f1d3d">
        </div>
      <?php endif; ?>
      <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp,image/gif">
    </div>

    <div class="admin-field">
      <label style="display:flex;gap:8px;align-items:center;font-weight:600">
        <input type="checkbox" name="is_published" value="1" <?= $form['is_published'] ? 'checked' : '' ?>>
        Publiée sur le site
      </label>
    </div>

    <div style="display:flex;gap:12px;flex-wrap:wrap">
      <button type="submit" class="btn-primary"><?= $isEdit ? 'Enregistrer' : 'Créer' ?></button>
      <a class="admin-btn admin-btn--ghost" href="<?= htmlspecialchars(url('admin/apps.php'), ENT_QUOTES, 'UTF-8') ?>">Annuler</a>
    </div>
  </form>
</div>

<?php require __DIR__ . '/inc/footer.php'; ?>
