<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/paths.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/portfolio.php';

require_admin();

$pdo = db();
portfolio_ensure_schema($pdo);

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$isEdit = $id > 0;
$item = $isEdit ? portfolio_find($pdo, $id) : null;

if ($isEdit && $item === null) {
    header('Location: ' . url('admin/portfolio.php?flash=error'), true, 302);
    exit;
}

$pageTitle = ($isEdit ? 'Modifier' : 'Ajouter') . ' une réalisation — Goo-Bridge Admin';
$pageHeading = $isEdit ? 'Modifier la réalisation' : 'Nouvelle réalisation';
$pageSub = 'Sites web et applications affichés dans le carrousel';
$activeNav = 'portfolio';

$error = '';
$flash = (string) ($_GET['flash'] ?? '');

$form = [
    'title' => (string) ($item['title'] ?? ''),
    'project_kind' => (string) ($item['project_kind'] ?? 'site'),
    'tags' => (string) ($item['tags'] ?? ''),
    'description' => (string) ($item['description'] ?? ''),
    'website_url' => (string) ($item['website_url'] ?? ''),
    'domain_label' => (string) ($item['domain_label'] ?? ''),
    'image_width' => (int) ($item['image_width'] ?? 472),
    'image_height' => (int) ($item['image_height'] ?? 1024),
    'sort_order' => (int) ($item['sort_order'] ?? 100),
    'is_published' => (int) ($item['is_published'] ?? 1),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['_csrf'] ?? null)) {
        $error = 'Session expirée : rechargez la page.';
    } else {
        $form['title'] = trim((string) ($_POST['title'] ?? ''));
        $form['project_kind'] = in_array($_POST['project_kind'] ?? '', ['site', 'app'], true)
            ? (string) $_POST['project_kind']
            : 'site';
        $form['tags'] = portfolio_normalize_tags((string) ($_POST['tags'] ?? ''));
        $form['description'] = trim((string) ($_POST['description'] ?? ''));
        $form['website_url'] = trim((string) ($_POST['website_url'] ?? ''));
        $form['domain_label'] = trim((string) ($_POST['domain_label'] ?? ''));
        $form['image_width'] = max(120, min(900, (int) ($_POST['image_width'] ?? 472)));
        $form['image_height'] = max(200, min(2000, (int) ($_POST['image_height'] ?? 1024)));
        $form['sort_order'] = (int) ($_POST['sort_order'] ?? 100);
        $form['is_published'] = isset($_POST['is_published']) ? 1 : 0;

        if ($form['domain_label'] === '' && $form['website_url'] !== '') {
            $form['domain_label'] = portfolio_domain_from_url($form['website_url']);
        }

        if ($form['title'] === '' || $form['description'] === '') {
            $error = 'Titre et description sont obligatoires.';
        } elseif ($form['website_url'] !== '' && !filter_var($form['website_url'], FILTER_VALIDATE_URL)) {
            $error = 'URL du site invalide (ex. https://exemple.com).';
        } else {
            try {
                if ($isEdit) {
                    $stmt = $pdo->prepare(
                        'UPDATE portfolio_items SET
                           title = :title, project_kind = :kind, tags = :tags, description = :desc,
                           website_url = :url, domain_label = :domain,
                           image_width = :w, image_height = :h, sort_order = :sort, is_published = :pub
                         WHERE id = :id LIMIT 1'
                    );
                    $stmt->execute([
                        'title' => $form['title'],
                        'kind' => $form['project_kind'],
                        'tags' => $form['tags'],
                        'desc' => $form['description'],
                        'url' => $form['website_url'],
                        'domain' => $form['domain_label'],
                        'w' => $form['image_width'],
                        'h' => $form['image_height'],
                        'sort' => $form['sort_order'],
                        'pub' => $form['is_published'],
                        'id' => $id,
                    ]);

                    $uploadErr = portfolio_save_upload($pdo, $id, $_FILES['image'] ?? null);
                    if ($uploadErr !== null) {
                        $error = $uploadErr;
                    } else {
                        // Si upload a recalculé w/h, réappliquer les valeurs manuelles si fournies
                        if (!portfolio_has_upload($_FILES['image'] ?? null)) {
                            // ok
                        } else {
                            // Réappliquer les dimensions choisies dans le formulaire (priorité admin)
                            $pdo->prepare(
                                'UPDATE portfolio_items SET image_width = :w, image_height = :h WHERE id = :id LIMIT 1'
                            )->execute([
                                'w' => $form['image_width'],
                                'h' => $form['image_height'],
                                'id' => $id,
                            ]);
                        }
                        header('Location: ' . url('admin/portfolio.php?flash=updated'), true, 303);
                        exit;
                    }
                } else {
                    $stmt = $pdo->prepare(
                        'INSERT INTO portfolio_items
                         (title, project_kind, tags, description, website_url, domain_label,
                          image_width, image_height, sort_order, is_published)
                         VALUES (:title, :kind, :tags, :desc, :url, :domain, :w, :h, :sort, :pub)'
                    );
                    $stmt->execute([
                        'title' => $form['title'],
                        'kind' => $form['project_kind'],
                        'tags' => $form['tags'],
                        'desc' => $form['description'],
                        'url' => $form['website_url'],
                        'domain' => $form['domain_label'],
                        'w' => $form['image_width'],
                        'h' => $form['image_height'],
                        'sort' => $form['sort_order'],
                        'pub' => $form['is_published'],
                    ]);
                    $newId = (int) $pdo->lastInsertId();
                    $uploadErr = portfolio_save_upload($pdo, $newId, $_FILES['image'] ?? null);
                    if ($uploadErr !== null) {
                        // garder l’entrée mais signaler
                        header('Location: ' . url('admin/portfolio_edit.php?id=' . $newId . '&flash=img'), true, 303);
                        exit;
                    }
                    if (portfolio_has_upload($_FILES['image'] ?? null)) {
                        $pdo->prepare(
                            'UPDATE portfolio_items SET image_width = :w, image_height = :h WHERE id = :id LIMIT 1'
                        )->execute([
                            'w' => $form['image_width'],
                            'h' => $form['image_height'],
                            'id' => $newId,
                        ]);
                    }
                    header('Location: ' . url('admin/portfolio.php?flash=created'), true, 303);
                    exit;
                }
            } catch (Throwable $e) {
                $error = 'Erreur d’enregistrement. Réessayez.';
            }
        }
    }
}

$currentImg = $item ? portfolio_image_public_url($item['image_path'] ?? null) : null;

require __DIR__ . '/inc/header.php';
?>

<?php if ($flash === 'img'): ?>
  <p class="admin-alert admin-alert--error">Projet créé, mais l’image n’a pas pu être importée. Ajoutez-la ci-dessous.</p>
<?php endif; ?>
<?php if ($error !== ''): ?>
  <p class="admin-alert admin-alert--error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>

<div class="admin-card">
  <form method="post" action="" enctype="multipart/form-data" class="admin-form" style="max-width:720px">
    <?= csrf_field() ?>
    <?php if ($isEdit): ?>
      <input type="hidden" name="id" value="<?= $id ?>">
    <?php endif; ?>

    <div class="admin-field">
      <label for="title">Titre *</label>
      <input type="text" id="title" name="title" required maxlength="160"
        value="<?= htmlspecialchars($form['title'], ENT_QUOTES, 'UTF-8') ?>">
    </div>

    <div class="admin-field">
      <label for="project_kind">Type</label>
      <select id="project_kind" name="project_kind">
        <option value="site" <?= $form['project_kind'] === 'site' ? 'selected' : '' ?>>Site web</option>
        <option value="app" <?= $form['project_kind'] === 'app' ? 'selected' : '' ?>>Application</option>
      </select>
    </div>

    <div class="admin-field">
      <label for="tags">Tags (séparés par des virgules)</label>
      <input type="text" id="tags" name="tags" maxlength="255" placeholder="E-commerce, Mobile, Éducation"
        value="<?= htmlspecialchars($form['tags'], ENT_QUOTES, 'UTF-8') ?>">
    </div>

    <div class="admin-field">
      <label for="description">Description *</label>
      <textarea id="description" name="description" rows="4" required maxlength="2000"><?= htmlspecialchars($form['description'], ENT_QUOTES, 'UTF-8') ?></textarea>
    </div>

    <div class="admin-field">
      <label for="website_url">URL du projet</label>
      <input type="url" id="website_url" name="website_url" maxlength="512" placeholder="https://exemple.com"
        value="<?= htmlspecialchars($form['website_url'], ENT_QUOTES, 'UTF-8') ?>">
    </div>

    <div class="admin-field">
      <label for="domain_label">Libellé du lien (domaine affiché)</label>
      <input type="text" id="domain_label" name="domain_label" maxlength="160" placeholder="exemple.com"
        value="<?= htmlspecialchars($form['domain_label'], ENT_QUOTES, 'UTF-8') ?>">
    </div>

    <div class="admin-field" style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
      <div>
        <label for="image_width">Largeur image (px)</label>
        <input type="number" id="image_width" name="image_width" min="120" max="900"
          value="<?= (int) $form['image_width'] ?>">
      </div>
      <div>
        <label for="image_height">Hauteur image (px)</label>
        <input type="number" id="image_height" name="image_height" min="200" max="2000"
          value="<?= (int) $form['image_height'] ?>">
      </div>
    </div>
    <p class="admin-muted" style="margin-top:-8px">Ex. 472×1024 pour un mockup téléphone. Ces valeurs sont utilisées sur le site public.</p>

    <div class="admin-field">
      <label for="sort_order">Ordre d’affichage</label>
      <input type="number" id="sort_order" name="sort_order" value="<?= (int) $form['sort_order'] ?>">
    </div>

    <div class="admin-field">
      <label for="image">Capture / mockup (JPG, PNG, WebP, GIF — max 5 Mo)</label>
      <?php if ($currentImg): ?>
        <div style="margin-bottom:10px">
          <img src="<?= htmlspecialchars($currentImg, ENT_QUOTES, 'UTF-8') ?>" alt="" width="90" height="160"
            style="object-fit:cover;border-radius:12px;background:#0f1d3d;box-shadow:0 8px 20px rgba(0,0,0,.15)">
        </div>
      <?php endif; ?>
      <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp,image/gif">
    </div>

    <div class="admin-field">
      <label style="display:flex;align-items:center;gap:8px;font-weight:600">
        <input type="checkbox" name="is_published" value="1" <?= $form['is_published'] ? 'checked' : '' ?>>
        Publié sur le site
      </label>
    </div>

    <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:8px">
      <button type="submit" class="btn-primary"><?= $isEdit ? 'Enregistrer' : 'Créer' ?></button>
      <a class="admin-btn admin-btn--ghost" href="<?= htmlspecialchars(url('admin/portfolio.php'), ENT_QUOTES, 'UTF-8') ?>">Annuler</a>
    </div>
  </form>
</div>

<?php require __DIR__ . '/inc/footer.php'; ?>
