<?php

declare(strict_types=1);

/** @var string $pageTitle */
/** @var string $activeNav */
/** @var string|null $pageHeading */
/** @var string|null $pageSub */

require_once __DIR__ . '/../../includes/paths.php';

if (!function_exists('current_admin_email')) {
    require_once __DIR__ . '/../../includes/auth.php';
}

$adminEmail = current_admin_email() ?? 'admin';
$adminName = '';
if (isset($_SESSION['admin_id'])) {
    try {
        require_once __DIR__ . '/../../includes/db.php';
        $st = db()->prepare('SELECT first_name, last_name FROM admins WHERE id = :id LIMIT 1');
        $st->execute(['id' => (int) $_SESSION['admin_id']]);
        $nm = $st->fetch(PDO::FETCH_ASSOC);
        if ($nm) {
            $adminName = trim(((string) ($nm['first_name'] ?? '')) . ' ' . ((string) ($nm['last_name'] ?? '')));
        }
    } catch (Throwable $e) {
        $adminName = '';
    }
}
if ($adminName === '') {
    $adminName = explode('@', $adminEmail)[0] ?: 'Admin';
}
$adminInitial = mb_strtoupper(mb_substr($adminName, 0, 1, 'UTF-8'), 'UTF-8');

$pageHeading = isset($pageHeading) && is_string($pageHeading) && $pageHeading !== ''
    ? $pageHeading
    : (string) preg_replace('/\s*[—–-]\s*Goo-Bridge.*$/iu', '', $pageTitle);
$pageSub = isset($pageSub) && is_string($pageSub) ? $pageSub : '';

$navUsersActive = isset($activeNav) && in_array($activeNav, ['admins', 'admins_create', 'admins_edit'], true);
$isSuper = function_exists('current_admin_is_super') && current_admin_is_super();

$navIcon = static function (string $name): string {
    return match ($name) {
        'dashboard' => '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/></svg>',
        'clients' => '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
        'tickets' => '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 11a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v2a2 2 0 0 0 0 4v2a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-2a2 2 0 0 0 0-4v-2z"/><path d="M9 5v14"/></svg>',
        'expenses' => '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 1v22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>',
        'portfolio' => '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="2" width="14" height="20" rx="3"/><path d="M12 18h.01"/></svg>',
        'apps' => '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="7" y="2" width="10" height="20" rx="2"/><path d="M11 18h2"/></svg>',
        'admins' => '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
        'logs' => '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>',
        'site' => '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>',
        'logout' => '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>',
        default => '',
    };
};

?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="icon" type="image/webp" href="<?= htmlspecialchars(url('images/favicon-goo-bridge.webp'), ENT_QUOTES, 'UTF-8') ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <?php
    $styleCssPath = dirname(__DIR__, 2) . '/style.css';
    $adminCssPath = dirname(__DIR__) . '/admin.css';
    $styleCssVer = is_file($styleCssPath) ? (string) filemtime($styleCssPath) : '1';
    $adminCssVer = is_file($adminCssPath) ? (string) filemtime($adminCssPath) : '1';
  ?>
  <link rel="stylesheet" href="<?= htmlspecialchars(url('style.css') . '?v=' . $styleCssVer, ENT_QUOTES, 'UTF-8') ?>">
  <link rel="stylesheet" href="<?= htmlspecialchars(url('admin/admin.css') . '?v=' . $adminCssVer, ENT_QUOTES, 'UTF-8') ?>">
</head>
<body class="admin-body admin-body--app">
  <input type="checkbox" id="admin-shell-nav-toggle" class="admin-shell-nav-toggle" aria-hidden="true">
  <label for="admin-shell-nav-toggle" class="admin-sidebar-backdrop" aria-hidden="true"></label>

  <div class="admin-app">
    <aside class="admin-sidebar" aria-label="Navigation admin">
      <div class="admin-sidebar__brand">
        <a href="<?= htmlspecialchars(url('admin/dashboard.php'), ENT_QUOTES, 'UTF-8') ?>" class="admin-sidebar__logo" aria-label="GOO-BRIDGE Admin">
          <img src="<?= htmlspecialchars(url('images/logo-goo-bridge.webp'), ENT_QUOTES, 'UTF-8') ?>" alt="GOO-BRIDGE" width="148" height="60" decoding="async" />
        </a>
        <p class="admin-sidebar__panel-label">Admin Panel</p>
        <label for="admin-shell-nav-toggle" class="admin-sidebar__close" aria-label="Fermer le menu">×</label>
      </div>

      <nav class="admin-sidebar__nav">
        <a href="<?= htmlspecialchars(url('admin/dashboard.php'), ENT_QUOTES, 'UTF-8') ?>" class="admin-sidebar__link<?= $activeNav === 'dashboard' ? ' is-active' : '' ?>">
          <span class="admin-sidebar__ico"><?= $navIcon('dashboard') ?></span>
          <span>Dashboard</span>
        </a>
        <a href="<?= htmlspecialchars(url('admin/clients.php'), ENT_QUOTES, 'UTF-8') ?>" class="admin-sidebar__link<?= $activeNav === 'clients' ? ' is-active' : '' ?>">
          <span class="admin-sidebar__ico"><?= $navIcon('clients') ?></span>
          <span>Clients</span>
        </a>
        <a href="<?= htmlspecialchars(url('admin/support_tickets.php'), ENT_QUOTES, 'UTF-8') ?>" class="admin-sidebar__link<?= $activeNav === 'tickets' ? ' is-active' : '' ?>">
          <span class="admin-sidebar__ico"><?= $navIcon('tickets') ?></span>
          <span>Tickets</span>
        </a>
        <a href="<?= htmlspecialchars(url('admin/expenses.php'), ENT_QUOTES, 'UTF-8') ?>" class="admin-sidebar__link<?= $activeNav === 'expenses' ? ' is-active' : '' ?>">
          <span class="admin-sidebar__ico"><?= $navIcon('expenses') ?></span>
          <span>Compta</span>
        </a>
        <a href="<?= htmlspecialchars(url('admin/portfolio.php'), ENT_QUOTES, 'UTF-8') ?>" class="admin-sidebar__link<?= $activeNav === 'portfolio' ? ' is-active' : '' ?>">
          <span class="admin-sidebar__ico"><?= $navIcon('portfolio') ?></span>
          <span>Réalisations</span>
        </a>
        <a href="<?= htmlspecialchars(url('admin/apps.php'), ENT_QUOTES, 'UTF-8') ?>" class="admin-sidebar__link<?= $activeNav === 'apps' ? ' is-active' : '' ?>">
          <span class="admin-sidebar__ico"><?= $navIcon('apps') ?></span>
          <span>Applications</span>
        </a>
        <a href="<?= htmlspecialchars(url('admin/logs.php'), ENT_QUOTES, 'UTF-8') ?>" class="admin-sidebar__link<?= $activeNav === 'logs' ? ' is-active' : '' ?>">
          <span class="admin-sidebar__ico"><?= $navIcon('logs') ?></span>
          <span>Logs &amp; système</span>
        </a>
        <?php if ($isSuper): ?>
          <a href="<?= htmlspecialchars(url('admin/admins.php'), ENT_QUOTES, 'UTF-8') ?>" class="admin-sidebar__link<?= $navUsersActive ? ' is-active' : '' ?>">
            <span class="admin-sidebar__ico"><?= $navIcon('admins') ?></span>
            <span>Utilisateurs</span>
          </a>
        <?php endif; ?>
        <a href="<?= htmlspecialchars(url('index.php'), ENT_QUOTES, 'UTF-8') ?>" class="admin-sidebar__link">
          <span class="admin-sidebar__ico"><?= $navIcon('site') ?></span>
          <span>Site public</span>
        </a>
      </nav>

      <div class="admin-sidebar__foot">
        <div class="admin-sidebar__user">
          <span class="admin-sidebar__avatar" aria-hidden="true"><?= htmlspecialchars($adminInitial, ENT_QUOTES, 'UTF-8') ?></span>
          <span class="admin-sidebar__user-meta">
            <strong><?= htmlspecialchars($adminName, ENT_QUOTES, 'UTF-8') ?></strong>
            <em><?= htmlspecialchars($adminEmail, ENT_QUOTES, 'UTF-8') ?></em>
          </span>
        </div>
        <a href="<?= htmlspecialchars(url('logout.php'), ENT_QUOTES, 'UTF-8') ?>" class="admin-sidebar__logout">
          <?= $navIcon('logout') ?>
          <span>Déconnexion</span>
        </a>
      </div>
    </aside>

    <div class="admin-content">
      <header class="admin-topbar">
        <div class="admin-topbar__left">
          <label for="admin-shell-nav-toggle" class="admin-topbar__burger" aria-label="Ouvrir ou fermer le menu">
            <span></span><span></span><span></span>
          </label>
          <div class="admin-topbar__titles">
            <h1 class="admin-topbar__title"><?= htmlspecialchars($pageHeading, ENT_QUOTES, 'UTF-8') ?></h1>
            <?php if ($pageSub !== ''): ?>
              <p class="admin-topbar__sub"><?= htmlspecialchars($pageSub, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
          </div>
        </div>
        <div class="admin-topbar__right">
          <span class="admin-topbar__chip" aria-hidden="true"><?= htmlspecialchars($adminInitial, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
      </header>
      <main class="admin-main">
