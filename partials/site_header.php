<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/paths.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/session.php';

/**
 * Indique si on est en train d'afficher la page d'accueil (index.php).
 * Si oui : les liens internes utilisent des ancres "#section".
 * Sinon : les liens pointent vers "index.php#section" pour fonctionner depuis n'importe où.
 *
 * La variable `$siteHeaderOnIndex` peut être définie AVANT l'inclusion pour forcer une valeur.
 */
$siteHeaderOnIndex = isset($siteHeaderOnIndex)
    ? (bool) $siteHeaderOnIndex
    : (bool) (($_SERVER['SCRIPT_NAME'] ?? '') !== ''
        && preg_match('#/index\.php$#', (string) $_SERVER['SCRIPT_NAME']));

$siteHeaderLink = static function (string $hash) use ($siteHeaderOnIndex): string {
    $hash = ltrim($hash, '#');
    return $siteHeaderOnIndex
        ? '#' . $hash
        : htmlspecialchars(url('index.php#' . $hash), ENT_QUOTES, 'UTF-8');
};

$siteLogoUrl = htmlspecialchars(url('images/logo-goo-bridge.webp'), ENT_QUOTES, 'UTF-8');
?>
<header id="navbar">
  <input type="checkbox" id="nav-menu-toggle" class="nav-menu-checkbox">
  <nav class="nav-container">
    <a href="<?= $siteHeaderLink('accueil') ?>" class="nav-logo" aria-label="GOO-BRIDGE accueil">
      <img src="<?= $siteLogoUrl ?>" alt="GOO-BRIDGE" width="160" height="66" decoding="async" />
    </a>
    <ul class="nav-links" role="list">
      <li><a href="<?= $siteHeaderLink('accueil') ?>">Accueil</a></li>
      <li><a href="<?= $siteHeaderLink('services') ?>">Services</a></li>
      <li><a href="<?= $siteHeaderLink('realisations') ?>">Réalisations</a></li>
    </ul>
    <?php if (admin_logged_in()): ?>
      <a href="<?= htmlspecialchars(url('admin/dashboard.php'), ENT_QUOTES, 'UTF-8') ?>" class="btn-nav-login">Login</a>
    <?php else: ?>
      <a href="<?= htmlspecialchars(url('login.php'), ENT_QUOTES, 'UTF-8') ?>" class="btn-nav-login">Connexion</a>
    <?php endif; ?>
    <a href="<?= htmlspecialchars(url('support.php'), ENT_QUOTES, 'UTF-8') ?>" class="btn-nav-support">Support</a>
    <a href="<?= $siteHeaderLink('contact') ?>" class="btn-cta" id="nav-cta">Consultation gratuite</a>
    <label for="nav-menu-toggle" class="mobile-menu-btn" aria-label="Ouvrir ou fermer le menu">
      <span></span><span></span><span></span>
    </label>
  </nav>
  <label for="nav-menu-toggle" class="mobile-nav-backdrop" aria-hidden="true"></label>
  <div class="mobile-nav" id="mobileNav">
    <div class="mobile-nav__head">
      <a href="<?= $siteHeaderLink('accueil') ?>" class="mobile-nav__brand" aria-label="GOO-BRIDGE accueil">
        <img src="<?= $siteLogoUrl ?>" alt="GOO-BRIDGE" width="140" height="57" decoding="async" />
      </a>
      <label for="nav-menu-toggle" class="mobile-nav__close" aria-label="Fermer le menu">×</label>
    </div>
    <p class="mobile-nav__lead">Navigation rapide</p>
    <a href="<?= $siteHeaderLink('accueil') ?>">Accueil</a>
    <a href="<?= $siteHeaderLink('services') ?>">Services</a>
    <a href="<?= $siteHeaderLink('realisations') ?>">Réalisations</a>
    <a href="<?= htmlspecialchars(url('support.php'), ENT_QUOTES, 'UTF-8') ?>">Support</a>
    <a href="<?= $siteHeaderLink('contact') ?>">Contact</a>
    <?php if (admin_logged_in()): ?>
      <a href="<?= htmlspecialchars(url('admin/dashboard.php'), ENT_QUOTES, 'UTF-8') ?>" class="mobile-nav__action">Tableau de bord</a>
      <a href="<?= htmlspecialchars(url('logout.php'), ENT_QUOTES, 'UTF-8') ?>" class="mobile-nav__ghost">Déconnexion</a>
    <?php else: ?>
      <a href="<?= htmlspecialchars(url('login.php'), ENT_QUOTES, 'UTF-8') ?>" class="mobile-nav__action">Connexion</a>
    <?php endif; ?>
  </div>
</header>
