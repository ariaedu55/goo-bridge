<?php

declare(strict_types=1);

require_once __DIR__ . '/paths.php';

function portfolio_image_max_bytes(): int
{
    return 5 * 1024 * 1024;
}

function portfolio_upload_abs_dir(): string
{
    return dirname(__DIR__) . '/uploads/portfolio';
}

/**
 * Crée la table portfolio_items si besoin et seed les projets initiaux une seule fois.
 */
function portfolio_ensure_schema(PDO $pdo): void
{
    $pdo->exec(<<<SQL
CREATE TABLE IF NOT EXISTS portfolio_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(160) NOT NULL,
  project_kind ENUM('site','app') NOT NULL DEFAULT 'site',
  tags VARCHAR(255) NOT NULL DEFAULT '',
  description TEXT NOT NULL,
  website_url VARCHAR(512) NOT NULL DEFAULT '',
  domain_label VARCHAR(160) NOT NULL DEFAULT '',
  image_path VARCHAR(512) NULL DEFAULT NULL,
  image_width SMALLINT UNSIGNED NOT NULL DEFAULT 472,
  image_height SMALLINT UNSIGNED NOT NULL DEFAULT 1024,
  sort_order INT NOT NULL DEFAULT 0,
  is_published TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_portfolio_pub_sort (is_published, sort_order, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

    $count = (int) $pdo->query('SELECT COUNT(*) FROM portfolio_items')->fetchColumn();
    if ($count > 0) {
        return;
    }

    $seed = [
        ['Sugar Paper', 'site', 'E-commerce,Pâtisserie', 'Boutique en ligne spécialisée en décoration de gâteaux et fournitures pâtissières.', 'https://sugar-paper.com/', 'sugar-paper.com', 'images/realisations/mobile-screens/sugar-paper.webp', 472, 1024, 10],
        ['Aria Édu', 'app', 'App Web,Éducation', 'Plateforme éducative avec QR Code, espace personnel élève, parent et enseignant.', 'https://aria-edu.com/', 'aria-edu.com', 'images/realisations/mobile-screens/aria-edu.webp', 472, 1024, 20],
        ['Fouta Poids Lourds', 'site', 'E-commerce,Poids Lourds', 'Boutique de pièces détachées pour véhicules poids lourds avec catalogue complet.', 'https://e.foutapoidslourds.com/', 'foutapoidslourds.com', 'images/realisations/mobile-screens/fouta-poids.webp', 472, 1024, 30],
        ['Aria Education', 'app', 'Plateforme,Gestion Scolaire', 'Gestion scolaire complète : présences, bulletins, tableau de bord, notifications.', 'https://aria-education.com/', 'aria-education.com', 'images/realisations/mobile-screens/aria-education.webp', 440, 956, 40],
        ['Colobanes', 'app', 'E-commerce,Marketplace', 'Marché en ligne multi-catégories : recherche, vitrines produits et parcours d\'achat.', 'https://colobanes.com/', 'colobanes.com', 'images/realisations/mobile-screens/colobanes.webp', 472, 1024, 50],
        ['Trésor Africain', 'site', 'E-commerce,Épicerie africaine', 'Boutique en ligne : catalogue produits, recherche, compte client et parcours d\'achat.', 'https://tresorafricain.com/', 'tresorafricain.com', 'images/realisations/mobile-screens/tresor-africain.webp', 440, 956, 60],
        ['Analyse Yayematy', 'app', 'Analyse marché,Intelligence commerciale', 'Application d\'analyse du marché sénégalais : tendances, produits les plus recherchés et opportunités d\'investissement.', 'https://analyse.yayematy.com/', 'analyse.yayematy.com', 'images/realisations/mobile-screens/yayematy.webp', 472, 1024, 70],
    ];

    $ins = $pdo->prepare(
        'INSERT INTO portfolio_items
         (title, project_kind, tags, description, website_url, domain_label, image_path, image_width, image_height, sort_order, is_published)
         VALUES (:title, :kind, :tags, :desc, :url, :domain, :img, :w, :h, :sort, 1)'
    );

    foreach ($seed as $row) {
        $ins->execute([
            'title' => $row[0],
            'kind' => $row[1],
            'tags' => $row[2],
            'desc' => $row[3],
            'url' => $row[4],
            'domain' => $row[5],
            'img' => $row[6],
            'w' => $row[7],
            'h' => $row[8],
            'sort' => $row[9],
        ]);
    }
}

function portfolio_image_public_url(?string $stored): ?string
{
    if ($stored === null || $stored === '') {
        return null;
    }
    if (!preg_match(
        '#^(uploads/portfolio/[1-9][0-9]*|images/realisations/mobile-screens/[a-z0-9_-]+)\.(jpg|jpeg|png|gif|webp)$#i',
        $stored
    )) {
        return null;
    }

    return url($stored);
}

function portfolio_delete_upload_files(int $id): void
{
    $dir = portfolio_upload_abs_dir();
    if (!is_dir($dir)) {
        return;
    }
    foreach (glob($dir . '/' . $id . '.*') ?: [] as $path) {
        if (is_file($path)) {
            @unlink($path);
        }
    }
}

/** @param array{name?: string, type?: string, tmp_name?: string, error?: int, size?: int}|null $file */
function portfolio_has_upload(?array $file): bool
{
    return $file !== null
        && isset($file['error'])
        && $file['error'] === UPLOAD_ERR_OK
        && isset($file['size'])
        && (int) $file['size'] > 0
        && isset($file['tmp_name'])
        && is_uploaded_file((string) $file['tmp_name']);
}

/**
 * @param array{name?: string, type?: string, tmp_name?: string, error?: int, size?: int}|null $file
 * @return string|null message d’erreur ou null si OK / rien à uploader
 */
function portfolio_save_upload(PDO $pdo, int $id, ?array $file): ?string
{
    if (!portfolio_has_upload($file)) {
        return null;
    }

    $tmp = (string) ($file['tmp_name'] ?? '');
    $size = (int) ($file['size'] ?? 0);
    if ($size > portfolio_image_max_bytes()) {
        return 'L’image dépasse 5 Mo.';
    }
    if (!is_uploaded_file($tmp)) {
        return 'Fichier image invalide.';
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmp);
    if ($mime === false) {
        return 'Impossible de détecter le type de l’image.';
    }

    $ext = match ($mime) {
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
        default => null,
    };
    if ($ext === null) {
        return 'Formats acceptés : JPG, PNG, WebP ou GIF.';
    }

    $dir = portfolio_upload_abs_dir();
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        return 'Impossible de créer le dossier d’upload.';
    }

    // Supprimer anciens uploads de cet id (pas les chemins seed images/)
    portfolio_delete_upload_files($id);

    $relative = 'uploads/portfolio/' . $id . '.' . $ext;
    $dest = dirname(__DIR__) . '/' . $relative;
    if (!move_uploaded_file($tmp, $dest)) {
        return 'Échec de l’enregistrement du fichier.';
    }

    // Convertir en WebP si possible pour alléger
    if ($ext !== 'webp' && function_exists('imagecreatefromstring') && function_exists('imagewebp')) {
        $raw = @file_get_contents($dest);
        $im = $raw !== false ? @imagecreatefromstring($raw) : false;
        if ($im !== false) {
            $webpRel = 'uploads/portfolio/' . $id . '.webp';
            $webpAbs = dirname(__DIR__) . '/' . $webpRel;
            if (@imagewebp($im, $webpAbs, 82)) {
                imagedestroy($im);
                if (is_file($dest) && $dest !== $webpAbs) {
                    @unlink($dest);
                }
                $relative = $webpRel;
            } else {
                imagedestroy($im);
            }
        }
    }

    $dims = @getimagesize(dirname(__DIR__) . '/' . $relative);
    $w = is_array($dims) ? (int) $dims[0] : 0;
    $h = is_array($dims) ? (int) $dims[1] : 0;

    $stmt = $pdo->prepare(
        'UPDATE portfolio_items SET image_path = :path'
        . ($w > 0 && $h > 0 ? ', image_width = :w, image_height = :h' : '')
        . ' WHERE id = :id LIMIT 1'
    );
    $params = ['path' => $relative, 'id' => $id];
    if ($w > 0 && $h > 0) {
        // Conserver proportions type téléphone : plafonner largeur affichage ~472
        $displayW = min(472, $w);
        $displayH = (int) round($h * ($displayW / max(1, $w)));
        $params['w'] = $displayW;
        $params['h'] = max(1, $displayH);
    }
    $stmt->execute($params);

    return null;
}

/** @return list<array<string,mixed>> */
function portfolio_list_published(PDO $pdo): array
{
    portfolio_ensure_schema($pdo);
    $stmt = $pdo->query(
        'SELECT * FROM portfolio_items
         WHERE is_published = 1
         ORDER BY sort_order ASC, id ASC'
    );

    return $stmt ? $stmt->fetchAll() : [];
}

/** @return list<array<string,mixed>> */
function portfolio_list_all(PDO $pdo): array
{
    portfolio_ensure_schema($pdo);
    $stmt = $pdo->query('SELECT * FROM portfolio_items ORDER BY sort_order ASC, id ASC');

    return $stmt ? $stmt->fetchAll() : [];
}

function portfolio_find(PDO $pdo, int $id): ?array
{
    portfolio_ensure_schema($pdo);
    $stmt = $pdo->prepare('SELECT * FROM portfolio_items WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();

    return $row !== false ? $row : null;
}

function portfolio_normalize_tags(string $tags): string
{
    $parts = array_filter(array_map('trim', explode(',', $tags)), static fn ($t) => $t !== '');

    return implode(',', array_values($parts));
}

function portfolio_domain_from_url(string $url): string
{
    $host = parse_url($url, PHP_URL_HOST);
    if (!is_string($host) || $host === '') {
        return '';
    }

    return preg_replace('/^www\./i', '', $host) ?? $host;
}
