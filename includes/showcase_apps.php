<?php

declare(strict_types=1);

require_once __DIR__ . '/paths.php';

function showcase_apps_image_max_bytes(): int
{
    return 5 * 1024 * 1024;
}

function showcase_apps_upload_dir(): string
{
    return dirname(__DIR__) . '/uploads/showcase_apps';
}

function showcase_apps_ensure_schema(PDO $pdo): void
{
    $pdo->exec(<<<SQL
CREATE TABLE IF NOT EXISTS showcase_apps (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(160) NOT NULL,
  description TEXT NOT NULL,
  website_url VARCHAR(512) NOT NULL DEFAULT '',
  domain_label VARCHAR(160) NOT NULL DEFAULT '',
  app_store_url VARCHAR(512) NOT NULL DEFAULT '',
  play_store_url VARCHAR(512) NOT NULL DEFAULT '',
  image_path VARCHAR(512) NULL DEFAULT NULL,
  image_width SMALLINT UNSIGNED NOT NULL DEFAULT 743,
  image_height SMALLINT UNSIGNED NOT NULL DEFAULT 1533,
  theme ENUM('navy','gradient') NOT NULL DEFAULT 'navy',
  sort_order INT NOT NULL DEFAULT 0,
  is_published TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_showcase_apps_pub (is_published, sort_order, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

    $pdo->exec(<<<SQL
CREATE TABLE IF NOT EXISTS showcase_app_floats (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  label VARCHAR(80) NOT NULL,
  color_hex VARCHAR(7) NOT NULL DEFAULT '#2563eb',
  position_slot ENUM('tl','bl','tr','mr','br') NOT NULL DEFAULT 'tl',
  sort_order INT NOT NULL DEFAULT 0,
  is_published TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_showcase_floats_pub (is_published, sort_order, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

    $appCount = (int) $pdo->query('SELECT COUNT(*) FROM showcase_apps')->fetchColumn();
    if ($appCount === 0) {
        $ins = $pdo->prepare(
            'INSERT INTO showcase_apps
             (title, description, website_url, domain_label, app_store_url, play_store_url,
              image_path, image_width, image_height, theme, sort_order, is_published)
             VALUES (:title, :desc, :web, :domain, :ios, :play, :img, :w, :h, :theme, :sort, 1)'
        );
        $ins->execute([
            'title' => 'ARIA',
            'desc' => 'Parcours scolaire : notes, absences, bulletins, inscriptions.',
            'web' => 'https://aria-edu.com/',
            'domain' => 'aria-edu.com',
            'ios' => 'https://apps.apple.com/us/app/aria-edu/id6770082530',
            'play' => 'https://play.google.com/store/apps/details?id=com.ariaedu.app',
            'img' => 'images/apps/aria-showcase.webp',
            'w' => 743,
            'h' => 1533,
            'theme' => 'navy',
            'sort' => 10,
        ]);
        $ins->execute([
            'title' => 'Colobanes',
            'desc' => 'Marketplace : produits, promos, panier et compte client.',
            'web' => 'https://colobanes.com/',
            'domain' => 'colobanes.com',
            'ios' => 'https://apps.apple.com/pt/app/colobanes/id6771130832',
            'play' => 'https://play.google.com/store/apps/details?id=com.colobanes.app&hl=fr',
            'img' => 'images/apps/colobanes-showcase.webp',
            'w' => 695,
            'h' => 1458,
            'theme' => 'gradient',
            'sort' => 20,
        ]);
    }

    $floatCount = (int) $pdo->query('SELECT COUNT(*) FROM showcase_app_floats')->fetchColumn();
    if ($floatCount === 0) {
        $fins = $pdo->prepare(
            'INSERT INTO showcase_app_floats (label, color_hex, position_slot, sort_order, is_published)
             VALUES (:label, :color, :pos, :sort, 1)'
        );
        foreach ([
            ['Suivi scolaire', '#7c3aed', 'tl', 10],
            ['Gestion élèves', '#2563eb', 'bl', 20],
            ['Marché en ligne', '#ea580c', 'tr', 30],
            ['E-commerce', '#0ea5e9', 'mr', 40],
            ['Marketplace', '#16a34a', 'br', 50],
        ] as $f) {
            $fins->execute([
                'label' => $f[0],
                'color' => $f[1],
                'pos' => $f[2],
                'sort' => $f[3],
            ]);
        }
    }
}

function showcase_apps_image_public_url(?string $stored): ?string
{
    if ($stored === null || $stored === '') {
        return null;
    }
    if (!preg_match(
        '#^(uploads/showcase_apps/[1-9][0-9]*|images/apps/[a-z0-9_-]+)\.(jpg|jpeg|png|gif|webp)$#i',
        $stored
    )) {
        return null;
    }

    return url($stored);
}

function showcase_apps_delete_upload_files(int $id): void
{
    $dir = showcase_apps_upload_dir();
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
function showcase_apps_has_upload(?array $file): bool
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
 */
function showcase_apps_save_upload(PDO $pdo, int $id, ?array $file): ?string
{
    if (!showcase_apps_has_upload($file)) {
        return null;
    }

    $tmp = (string) ($file['tmp_name'] ?? '');
    $size = (int) ($file['size'] ?? 0);
    if ($size > showcase_apps_image_max_bytes()) {
        return 'L’image dépasse 5 Mo.';
    }
    if (!is_uploaded_file($tmp)) {
        return 'Fichier image invalide.';
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmp);
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

    $dir = showcase_apps_upload_dir();
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        return 'Impossible de créer le dossier d’upload.';
    }

    showcase_apps_delete_upload_files($id);

    $relative = 'uploads/showcase_apps/' . $id . '.' . $ext;
    $dest = dirname(__DIR__) . '/' . $relative;
    if (!move_uploaded_file($tmp, $dest)) {
        return 'Échec de l’enregistrement du fichier.';
    }

    if ($ext !== 'webp' && function_exists('imagecreatefromstring') && function_exists('imagewebp')) {
        $raw = @file_get_contents($dest);
        $im = $raw !== false ? @imagecreatefromstring($raw) : false;
        if ($im !== false) {
            $webpRel = 'uploads/showcase_apps/' . $id . '.webp';
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
    $params = ['path' => $relative, 'id' => $id];
    $sql = 'UPDATE showcase_apps SET image_path = :path';
    if (is_array($dims) && $dims[0] > 0 && $dims[1] > 0) {
        $sql .= ', image_width = :w, image_height = :h';
        $params['w'] = (int) $dims[0];
        $params['h'] = (int) $dims[1];
    }
    $sql .= ' WHERE id = :id LIMIT 1';
    $pdo->prepare($sql)->execute($params);

    return null;
}

/** @return list<array<string,mixed>> */
function showcase_apps_list_published(PDO $pdo): array
{
    showcase_apps_ensure_schema($pdo);
    $stmt = $pdo->query(
        'SELECT * FROM showcase_apps WHERE is_published = 1 ORDER BY sort_order ASC, id ASC'
    );

    return $stmt ? $stmt->fetchAll() : [];
}

/** @return list<array<string,mixed>> */
function showcase_apps_list_all(PDO $pdo): array
{
    showcase_apps_ensure_schema($pdo);
    $stmt = $pdo->query('SELECT * FROM showcase_apps ORDER BY sort_order ASC, id ASC');

    return $stmt ? $stmt->fetchAll() : [];
}

function showcase_apps_find(PDO $pdo, int $id): ?array
{
    showcase_apps_ensure_schema($pdo);
    $stmt = $pdo->prepare('SELECT * FROM showcase_apps WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();

    return $row !== false ? $row : null;
}

/** @return list<array<string,mixed>> */
function showcase_floats_list_published(PDO $pdo): array
{
    showcase_apps_ensure_schema($pdo);
    $stmt = $pdo->query(
        'SELECT * FROM showcase_app_floats WHERE is_published = 1 ORDER BY sort_order ASC, id ASC'
    );

    return $stmt ? $stmt->fetchAll() : [];
}

/** @return list<array<string,mixed>> */
function showcase_floats_list_all(PDO $pdo): array
{
    showcase_apps_ensure_schema($pdo);
    $stmt = $pdo->query('SELECT * FROM showcase_app_floats ORDER BY sort_order ASC, id ASC');

    return $stmt ? $stmt->fetchAll() : [];
}

function showcase_floats_find(PDO $pdo, int $id): ?array
{
    showcase_apps_ensure_schema($pdo);
    $stmt = $pdo->prepare('SELECT * FROM showcase_app_floats WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();

    return $row !== false ? $row : null;
}

function showcase_domain_from_url(string $url): string
{
    $host = parse_url($url, PHP_URL_HOST);
    if (!is_string($host) || $host === '') {
        return '';
    }

    return preg_replace('/^www\./i', '', $host) ?? $host;
}

function showcase_normalize_hex(string $hex): string
{
    $hex = trim($hex);
    if (preg_match('/^#[0-9a-fA-F]{6}$/', $hex) === 1) {
        return strtolower($hex);
    }
    if (preg_match('/^[0-9a-fA-F]{6}$/', $hex) === 1) {
        return '#' . strtolower($hex);
    }

    return '#2563eb';
}
