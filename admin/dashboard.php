<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/paths.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_admin();

$pageTitle = 'Dashboard — Goo-Bridge Admin';
$pageHeading = 'Dashboard';
$pageSub = 'Pilotez votre portefeuille et vos dossiers';
$activeNav = 'dashboard';

$pdo = db();

$totalClients = (int) $pdo->query('SELECT COUNT(*) FROM clients')->fetchColumn();

$statusCounts = ['active' => 0, 'paused' => 0, 'completed' => 0];
$stmt = $pdo->query('SELECT status, COUNT(*) AS c FROM clients GROUP BY status');
while ($row = $stmt->fetch()) {
    $statusCounts[$row['status']] = (int) $row['c'];
}

$recent = $pdo->query(
    'SELECT id, company_name, project_type, status, website_url, updated_at FROM clients ORDER BY updated_at DESC LIMIT 8'
)->fetchAll();

function admin_status_badge(string $status): string
{
    return match ($status) {
        'active' => '<span class="admin-badge admin-badge--active">Actif</span>',
        'paused' => '<span class="admin-badge admin-badge--paused">En pause</span>',
        'completed' => '<span class="admin-badge admin-badge--completed">Terminé</span>',
        default => htmlspecialchars($status, ENT_QUOTES, 'UTF-8'),
    };
}

function admin_dashboard_initials(string $name): string
{
    $name = trim(preg_replace('/\s+/', ' ', $name) ?? '');
    if ($name === '') {
        return '?';
    }
    $parts = explode(' ', $name);
    if (count($parts) === 1) {
        return mb_strtoupper(mb_substr($parts[0], 0, 2, 'UTF-8'), 'UTF-8');
    }
    $first = mb_substr($parts[0], 0, 1, 'UTF-8');
    $last = mb_substr($parts[count($parts) - 1], 0, 1, 'UTF-8');

    return mb_strtoupper($first . $last, 'UTF-8');
}

/** @return array{0: string, 1: string} */
function admin_dashboard_avatar_palette(string $name): array
{
    $palettes = [
        ['#2563eb', '#3b82f6'],
        ['#0ea5e9', '#38bdf8'],
        ['#8b5cf6', '#a78bfa'],
        ['#f97316', '#fb923c'],
        ['#ec4899', '#f472b6'],
        ['#14b8a6', '#2dd4bf'],
        ['#f59e0b', '#fbbf24'],
        ['#6366f1', '#818cf8'],
    ];
    $hash = abs(crc32($name));

    return $palettes[$hash % count($palettes)];
}

function admin_dashboard_format_updated(?string $sqlTs): string
{
    if ($sqlTs === null || $sqlTs === '') {
        return '—';
    }
    $ts = strtotime($sqlTs);

    return $ts !== false ? date('d/m/Y · H:i', $ts) : '—';
}

$hrefClients = url('admin/clients.php');
$hrefNew = url('admin/clients_new.php');
$hrefTickets = url('admin/support_tickets.php');
$hrefExpenses = url('admin/expenses.php');

$ticketsTotal = 0;
$ticketsOpen = 0;
try {
    $ticketsTotal = (int) $pdo->query('SELECT COUNT(*) FROM client_support_tickets')->fetchColumn();
    $ticketsOpen = (int) $pdo->query("SELECT COUNT(*) FROM client_support_tickets WHERE status = 'open'")->fetchColumn();
} catch (Throwable $e) {
    $ticketsTotal = 0;
    $ticketsOpen = 0;
}

$welcomeName = current_admin_email() ?? 'Admin';
try {
    $stName = $pdo->prepare('SELECT first_name FROM admins WHERE id = :id LIMIT 1');
    $stName->execute(['id' => (int) (current_admin_id() ?? 0)]);
    $fn = trim((string) ($stName->fetchColumn() ?: ''));
    if ($fn !== '') {
        $welcomeName = $fn;
    } else {
        $welcomeName = explode('@', (string) current_admin_email())[0] ?: 'Admin';
    }
} catch (Throwable $e) {
    $welcomeName = explode('@', (string) current_admin_email())[0] ?: 'Admin';
}

$todayLabel = date('l j F Y');
$frDays = [
    'Monday' => 'Lundi', 'Tuesday' => 'Mardi', 'Wednesday' => 'Mercredi', 'Thursday' => 'Jeudi',
    'Friday' => 'Vendredi', 'Saturday' => 'Samedi', 'Sunday' => 'Dimanche',
];
$frMonths = [
    'January' => 'janvier', 'February' => 'février', 'March' => 'mars', 'April' => 'avril',
    'May' => 'mai', 'June' => 'juin', 'July' => 'juillet', 'August' => 'août',
    'September' => 'septembre', 'October' => 'octobre', 'November' => 'novembre', 'December' => 'décembre',
];
$todayLabel = strtr(date('l j F Y'), $frDays + $frMonths);

$pct = static function (int $part, int $total): int {
    if ($total <= 0) {
        return 0;
    }

    return (int) round(($part / $total) * 100);
};

require __DIR__ . '/inc/header.php';
?>

<div class="admin-dashboard">
  <section class="admin-welcome" aria-label="Bienvenue">
    <div class="admin-welcome__text">
      <p class="admin-welcome__hello">Bonjour, <?= htmlspecialchars($welcomeName, ENT_QUOTES, 'UTF-8') ?></p>
      <p class="admin-welcome__date"><?= htmlspecialchars($todayLabel, ENT_QUOTES, 'UTF-8') ?></p>
    </div>
    <div class="admin-welcome__actions">
      <a class="admin-welcome__btn admin-welcome__btn--primary" href="<?= htmlspecialchars($hrefNew, ENT_QUOTES, 'UTF-8') ?>">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
        Nouvelle entreprise
      </a>
      <a class="admin-welcome__btn admin-welcome__btn--ghost" href="<?= htmlspecialchars($hrefTickets, ENT_QUOTES, 'UTF-8') ?>">
        Tickets
        <?php if ($ticketsOpen > 0): ?>
          <span class="admin-welcome__badge"><?= (int) $ticketsOpen ?></span>
        <?php endif; ?>
      </a>
    </div>
    <div class="admin-welcome__mini" aria-label="Aperçu rapide">
      <article class="admin-welcome__stat">
        <span class="admin-welcome__stat-label">Clients</span>
        <strong><?= $totalClients ?></strong>
      </article>
      <article class="admin-welcome__stat">
        <span class="admin-welcome__stat-label">Actifs</span>
        <strong><?= $statusCounts['active'] ?></strong>
      </article>
      <article class="admin-welcome__stat">
        <span class="admin-welcome__stat-label">Tickets ouverts</span>
        <strong><?= $ticketsOpen ?></strong>
      </article>
      <article class="admin-welcome__stat">
        <span class="admin-welcome__stat-label">Terminés</span>
        <strong><?= $statusCounts['completed'] ?></strong>
      </article>
    </div>
  </section>

  <section class="admin-kpi-grid" aria-label="Indicateurs clés">
    <article class="admin-kpi">
      <div class="admin-kpi__head">
        <span class="admin-kpi__icon admin-kpi__icon--blue" aria-hidden="true">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </span>
        <span class="admin-kpi__label">Total clients</span>
      </div>
      <p class="admin-kpi__value"><?= $totalClients ?></p>
      <div class="admin-kpi__bar" aria-hidden="true"><span style="width:100%"></span></div>
      <p class="admin-kpi__hint">Portefeuille enregistré</p>
    </article>
    <article class="admin-kpi">
      <div class="admin-kpi__head">
        <span class="admin-kpi__icon admin-kpi__icon--green" aria-hidden="true">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </span>
        <span class="admin-kpi__label">Projets actifs</span>
      </div>
      <p class="admin-kpi__value"><?= $statusCounts['active'] ?></p>
      <div class="admin-kpi__bar admin-kpi__bar--green" aria-hidden="true"><span style="width:<?= $pct($statusCounts['active'], $totalClients) ?>%"></span></div>
      <p class="admin-kpi__hint"><?= $pct($statusCounts['active'], $totalClients) ?>% du portefeuille</p>
    </article>
    <article class="admin-kpi">
      <div class="admin-kpi__head">
        <span class="admin-kpi__icon admin-kpi__icon--amber" aria-hidden="true">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="10" y1="15" x2="10" y2="9"/><line x1="14" y1="15" x2="14" y2="9"/></svg>
        </span>
        <span class="admin-kpi__label">En pause</span>
      </div>
      <p class="admin-kpi__value"><?= $statusCounts['paused'] ?></p>
      <div class="admin-kpi__bar admin-kpi__bar--amber" aria-hidden="true"><span style="width:<?= $pct($statusCounts['paused'], $totalClients) ?>%"></span></div>
      <p class="admin-kpi__hint"><?= $pct($statusCounts['paused'], $totalClients) ?>% du portefeuille</p>
    </article>
    <article class="admin-kpi">
      <div class="admin-kpi__head">
        <span class="admin-kpi__icon admin-kpi__icon--orange" aria-hidden="true">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 11a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v2a2 2 0 0 0 0 4v2a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-2a2 2 0 0 0 0-4v-2z"/><path d="M9 5v14"/></svg>
        </span>
        <span class="admin-kpi__label">Tickets ouverts</span>
      </div>
      <p class="admin-kpi__value"><?= $ticketsOpen ?></p>
      <div class="admin-kpi__bar admin-kpi__bar--orange" aria-hidden="true"><span style="width:<?= $pct($ticketsOpen, max($ticketsTotal, 1)) ?>%"></span></div>
      <p class="admin-kpi__hint"><?= $ticketsTotal ?> ticket<?= $ticketsTotal !== 1 ? 's' : '' ?> au total</p>
    </article>
  </section>

  <div class="admin-dash-split">
    <section class="admin-panel admin-dash-feed">
      <header class="admin-dash-feed__head">
        <div class="admin-dash-feed__head-text">
          <span class="admin-dash-feed__eyebrow">Activité</span>
          <h2 class="admin-dash-feed__title">Derniers dossiers mis à jour</h2>
        </div>
        <a class="admin-dash-feed__all" href="<?= htmlspecialchars($hrefClients, ENT_QUOTES, 'UTF-8') ?>">
          Tout l’annuaire
          <span aria-hidden="true">→</span>
        </a>
      </header>

      <?php if ($recent === []): ?>
        <div class="admin-dash-empty">
          <div class="admin-dash-empty__icon" aria-hidden="true">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /><line x1="19" y1="8" x2="19" y2="14" /><line x1="22" y1="11" x2="16" y2="11" /></svg>
          </div>
          <h3 class="admin-dash-empty__title">Aucune entreprise encore</h3>
          <p class="admin-dash-empty__text">Créez votre première fiche pour voir les indicateurs et l’historique ici.</p>
          <a class="btn-primary admin-dash-empty__btn" href="<?= htmlspecialchars($hrefNew, ENT_QUOTES, 'UTF-8') ?>">Ajouter une entreprise</a>
        </div>
      <?php else: ?>
        <ul class="admin-dash-feed__list" role="list">
          <?php foreach ($recent as $r): ?>
            <?php
              $cid = (int) $r['id'];
              $cname = (string) $r['company_name'];
              $initials = admin_dashboard_initials($cname);
              $palette = admin_dashboard_avatar_palette($cname);
              $ptype = trim((string) ($r['project_type'] ?? ''));
              $detailUrl = url('admin/client_detail.php?id=' . $cid);
              $tasksUrl = url('admin/project_tasks.php?id=' . $cid);
              $st = (string) $r['status'];
            ?>
            <li>
              <article class="admin-dash-feed-card admin-dash-feed-card--<?= htmlspecialchars($st, ENT_QUOTES, 'UTF-8') ?>">
                <span class="admin-dash-feed-card__avatar" aria-hidden="true" style="--dash-avatar-from: <?= htmlspecialchars($palette[0], ENT_QUOTES, 'UTF-8') ?>; --dash-avatar-to: <?= htmlspecialchars($palette[1], ENT_QUOTES, 'UTF-8') ?>;"><?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') ?></span>
                <div class="admin-dash-feed-card__main">
                  <h3 class="admin-dash-feed-card__title">
                    <a href="<?= htmlspecialchars($detailUrl, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($cname, ENT_QUOTES, 'UTF-8') ?></a>
                  </h3>
                  <p class="admin-dash-feed-card__type"><?= $ptype !== '' ? htmlspecialchars($ptype, ENT_QUOTES, 'UTF-8') : 'Type non renseigné' ?></p>
                  <div class="admin-dash-feed-card__meta">
                    <?= admin_status_badge($st) ?>
                    <span class="admin-dash-feed-card__updated" title="Dernière mise à jour sur la fiche">
                      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10" /><path d="M12 6v6l4 2" /></svg>
                      <?= htmlspecialchars(admin_dashboard_format_updated((string) ($r['updated_at'] ?? '')), ENT_QUOTES, 'UTF-8') ?>
                    </span>
                    <?php if (!empty($r['website_url'])): ?>
                      <a class="admin-dash-feed-card__site" href="<?= htmlspecialchars($r['website_url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Site ↗</a>
                    <?php endif; ?>
                  </div>
                </div>
                <div class="admin-dash-feed-card__actions">
                  <a class="admin-dash-feed-card__btn admin-dash-feed-card__btn--ghost" href="<?= htmlspecialchars($tasksUrl, ENT_QUOTES, 'UTF-8') ?>">Tâches</a>
                  <a class="btn-primary admin-dash-feed-card__btn admin-dash-feed-card__btn--pri" href="<?= htmlspecialchars($detailUrl, ENT_QUOTES, 'UTF-8') ?>">Fiche</a>
                </div>
              </article>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </section>

    <aside class="admin-quick" aria-label="Actions rapides">
      <h2 class="admin-quick__title">Actions rapides</h2>
      <div class="admin-quick__grid">
        <a class="admin-quick__card admin-quick__card--blue" href="<?= htmlspecialchars($hrefNew, ENT_QUOTES, 'UTF-8') ?>">
          <span class="admin-quick__ico" aria-hidden="true">+</span>
          <span>Ajouter une entreprise</span>
        </a>
        <a class="admin-quick__card admin-quick__card--green" href="<?= htmlspecialchars($hrefClients, ENT_QUOTES, 'UTF-8') ?>">
          <span class="admin-quick__ico" aria-hidden="true">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
          </span>
          <span>Voir les clients</span>
        </a>
        <a class="admin-quick__card admin-quick__card--orange" href="<?= htmlspecialchars($hrefTickets, ENT_QUOTES, 'UTF-8') ?>">
          <span class="admin-quick__ico" aria-hidden="true">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 11a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v2a2 2 0 0 0 0 4v2a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-2a2 2 0 0 0 0-4v-2z"/></svg>
          </span>
          <span>Tickets support</span>
        </a>
        <a class="admin-quick__card admin-quick__card--purple" href="<?= htmlspecialchars($hrefExpenses, ENT_QUOTES, 'UTF-8') ?>">
          <span class="admin-quick__ico" aria-hidden="true">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 1v22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
          </span>
          <span>Dépenses</span>
        </a>
      </div>

      <div class="admin-quick__pending">
        <h3 class="admin-quick__pending-title">À surveiller</h3>
        <a class="admin-quick__pending-item" href="<?= htmlspecialchars($hrefTickets, ENT_QUOTES, 'UTF-8') ?>">
          <span>
            <strong>Tickets ouverts</strong>
            <em>Support client</em>
          </span>
          <span class="admin-quick__pending-count"><?= $ticketsOpen ?></span>
        </a>
        <a class="admin-quick__pending-item" href="<?= htmlspecialchars($hrefClients, ENT_QUOTES, 'UTF-8') ?>">
          <span>
            <strong>Projets en pause</strong>
            <em>Portefeuille</em>
          </span>
          <span class="admin-quick__pending-count admin-quick__pending-count--amber"><?= $statusCounts['paused'] ?></span>
        </a>
        <a class="admin-quick__pending-item" href="<?= htmlspecialchars($hrefClients, ENT_QUOTES, 'UTF-8') ?>">
          <span>
            <strong>Projets terminés</strong>
            <em>Livrés</em>
          </span>
          <span class="admin-quick__pending-count admin-quick__pending-count--green"><?= $statusCounts['completed'] ?></span>
        </a>
      </div>
    </aside>
  </div>
</div>

<?php require __DIR__ . '/inc/footer.php'; ?>
