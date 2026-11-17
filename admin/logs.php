<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/paths.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/app_log.php';
require_once __DIR__ . '/../includes/maintenance.php';
require_once __DIR__ . '/../includes/rate_limit.php';

require_admin();

$pageTitle = 'Logs & système — Goo-Bridge Admin';
$pageHeading = 'Logs & système';
$pageSub = 'Erreurs et maintenance';
$activeNav = 'logs';

$flash = '';
$flashType = 'ok';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['_csrf'] ?? null)) {
        $flash = 'Session expirée : réessayez.';
        $flashType = 'error';
    } else {
        $action = (string) ($_POST['action'] ?? '');
        if ($action === 'clear_logs') {
            if (app_log_clear()) {
                $flash = 'Journal vidé.';
            } else {
                $flash = 'Impossible de vider le journal.';
                $flashType = 'error';
            }
        } elseif ($action === 'maintenance_on') {
            if (maintenance_enable()) {
                app_log_write('info', 'Mode maintenance activé', ['admin' => current_admin_email()]);
                $flash = 'Mode maintenance activé.';
            } else {
                $flash = 'Échec d’activation du mode maintenance.';
                $flashType = 'error';
            }
        } elseif ($action === 'maintenance_off') {
            if (maintenance_disable()) {
                app_log_write('info', 'Mode maintenance désactivé', ['admin' => current_admin_email()]);
                $flash = 'Mode maintenance désactivé.';
            } else {
                $flash = 'Échec de désactivation.';
                $flashType = 'error';
            }
        }
    }
}

$logs = app_log_read(250);
$logCount = app_log_count();
$maintOn = maintenance_is_enabled();

$countSecurity = 0;
$countErrors = 0;
$countInfo = 0;
foreach ($logs as $row) {
    $level = (string) ($row['level'] ?? '');
    if ($level === 'security' || $level === 'warning') {
        $countSecurity++;
    } elseif ($level === 'error' || $level === 'critical') {
        $countErrors++;
    } else {
        $countInfo++;
    }
}

$levelMeta = static function (string $level): array {
    return match ($level) {
        'critical' => ['label' => 'critique', 'class' => 'admin-log-badge--error'],
        'error' => ['label' => 'erreur', 'class' => 'admin-log-badge--error'],
        'warning' => ['label' => 'avertissement', 'class' => 'admin-log-badge--warn'],
        'security' => ['label' => 'sécurité', 'class' => 'admin-log-badge--warn'],
        'info' => ['label' => 'info', 'class' => 'admin-log-badge--info'],
        'debug' => ['label' => 'debug', 'class' => 'admin-log-badge--info'],
        default => ['label' => $level !== '' ? $level : 'log', 'class' => 'admin-log-badge--info'],
    };
};

$formatTime = static function (string $iso): string {
    $ts = strtotime($iso);
    if ($ts === false) {
        return $iso;
    }

    return date('d/m H:i', $ts);
};

$formatTimeFull = static function (string $iso): string {
    $ts = strtotime($iso);
    if ($ts === false) {
        return $iso;
    }

    return date('d/m/Y H:i:s', $ts);
};

require __DIR__ . '/inc/header.php';
?>

<div class="admin-logs-page">
  <?php if ($flash !== ''): ?>
    <p class="admin-alert admin-alert--<?= $flashType === 'error' ? 'error' : 'ok' ?>" role="status"><?= htmlspecialchars($flash, ENT_QUOTES, 'UTF-8') ?></p>
  <?php endif; ?>

  <div class="admin-logs-kpis" role="list">
    <article class="admin-logs-kpi" role="listitem">
      <header class="admin-logs-kpi__head">
        <h3>Erreurs</h3>
        <?php if ($countErrors > 0): ?>
          <span class="admin-logs-kpi__tag admin-logs-kpi__tag--warn">À voir</span>
        <?php else: ?>
          <span class="admin-logs-kpi__tag">OK</span>
        <?php endif; ?>
      </header>
      <dl class="admin-logs-kpi__rows">
        <div><dt>Affichées</dt><dd><?= (int) $countErrors ?></dd></div>
        <div><dt>Total journal</dt><dd><?= (int) $logCount ?></dd></div>
      </dl>
    </article>

    <article class="admin-logs-kpi" role="listitem">
      <header class="admin-logs-kpi__head">
        <h3>Sécurité</h3>
        <?php if ($countSecurity > 0): ?>
          <span class="admin-logs-kpi__tag admin-logs-kpi__tag--warn">Actif</span>
        <?php else: ?>
          <span class="admin-logs-kpi__tag">Calme</span>
        <?php endif; ?>
      </header>
      <dl class="admin-logs-kpi__rows">
        <div><dt>Événements</dt><dd><?= (int) $countSecurity ?></dd></div>
        <div><dt>Votre IP</dt><dd class="admin-logs-kpi__ip"><?= htmlspecialchars(rate_limit_client_ip(), ENT_QUOTES, 'UTF-8') ?></dd></div>
      </dl>
    </article>

    <article class="admin-logs-kpi" role="listitem">
      <header class="admin-logs-kpi__head">
        <h3>Infos</h3>
        <span class="admin-logs-kpi__tag admin-logs-kpi__tag--info">Journal</span>
      </header>
      <dl class="admin-logs-kpi__rows">
        <div><dt>Entrées info</dt><dd><?= (int) $countInfo ?></dd></div>
        <div><dt>Affichées</dt><dd><?= count($logs) ?> / <?= (int) $logCount ?></dd></div>
      </dl>
    </article>

    <article class="admin-logs-kpi" role="listitem">
      <header class="admin-logs-kpi__head">
        <h3>Site</h3>
        <?php if ($maintOn): ?>
          <span class="admin-logs-kpi__tag admin-logs-kpi__tag--warn">503</span>
        <?php else: ?>
          <span class="admin-logs-kpi__tag admin-logs-kpi__tag--ok">En ligne</span>
        <?php endif; ?>
      </header>
      <dl class="admin-logs-kpi__rows">
        <div><dt>État</dt><dd><?= $maintOn ? 'Maintenance' : 'Public' ?></dd></div>
        <div><dt>Admin</dt><dd>Accessible</dd></div>
      </dl>
    </article>
  </div>

  <section class="admin-logs-panel">
    <header class="admin-logs-panel__head">
      <div class="admin-logs-panel__titles">
        <h2>Journal</h2>
        <span class="admin-logs-panel__count"><?= (int) $logCount ?></span>
      </div>
      <div class="admin-logs-panel__actions">
        <form method="post" class="admin-logs-inline-form">
          <?= csrf_field() ?>
          <?php if ($maintOn): ?>
            <input type="hidden" name="action" value="maintenance_off">
            <button type="submit" class="btn-primary admin-submit">Désactiver maintenance</button>
          <?php else: ?>
            <input type="hidden" name="action" value="maintenance_on">
            <button
              type="submit"
              class="admin-btn admin-btn--ghost"
              onclick="return confirm('Activer le mode maintenance pour les visiteurs ?');"
            >Maintenance</button>
          <?php endif; ?>
        </form>
        <form method="post" class="admin-logs-inline-form" onsubmit="return confirm('Vider tout le journal ?');">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="clear_logs">
          <button type="submit" class="admin-btn admin-btn--danger">Vider</button>
        </form>
      </div>
    </header>

    <?php if ($logs === []): ?>
      <div class="admin-logs-empty">
        <p>Aucune entrée.</p>
      </div>
    <?php else: ?>
      <div class="admin-table-wrap admin-logs-table-wrap">
        <table class="admin-table admin-logs-table">
          <thead>
            <tr>
              <th scope="col">Dernière fois</th>
              <th scope="col">Type</th>
              <th scope="col">Message</th>
              <th scope="col">IP</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($logs as $row): ?>
              <?php
                $level = (string) ($row['level'] ?? 'error');
                $meta = $levelMeta($level);
                $ctx = $row['context'] ?? [];
                $ctxParts = [];
                if (is_array($ctx)) {
                    if (isset($ctx['file'])) {
                        $ctxParts[] = (string) $ctx['file'] . (isset($ctx['line']) ? ':' . $ctx['line'] : '');
                    }
                    foreach ($ctx as $ck => $cv) {
                        if (in_array($ck, ['file', 'line', 'errno'], true)) {
                            continue;
                        }
                        if (is_scalar($cv) || $cv === null) {
                            $ctxParts[] = $ck . '=' . (string) $cv;
                        }
                    }
                }
                $uri = (string) ($row['uri'] ?? '');
                $method = (string) ($row['method'] ?? '');
                $sub = trim(($method !== '' ? $method . ' ' : '') . $uri);
                if ($ctxParts !== []) {
                    $sub = ($sub !== '' ? $sub . ' · ' : '') . implode(' · ', $ctxParts);
                }
                $t = (string) ($row['t'] ?? '');
              ?>
              <tr>
                <td class="admin-logs-table__time">
                  <time datetime="<?= htmlspecialchars($t, ENT_QUOTES, 'UTF-8') ?>" title="<?= htmlspecialchars($formatTimeFull($t), ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars($formatTime($t), ENT_QUOTES, 'UTF-8') ?>
                  </time>
                </td>
                <td>
                  <span class="admin-log-badge <?= $meta['class'] ?>"><?= htmlspecialchars($meta['label'], ENT_QUOTES, 'UTF-8') ?></span>
                </td>
                <td class="admin-logs-table__msg">
                  <strong><?= htmlspecialchars((string) ($row['message'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                  <?php if ($sub !== ''): ?>
                    <span><?= htmlspecialchars($sub, ENT_QUOTES, 'UTF-8') ?></span>
                  <?php endif; ?>
                </td>
                <td class="admin-logs-table__ip"><?= htmlspecialchars((string) ($row['ip'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </section>
</div>

<?php require __DIR__ . '/inc/footer.php'; ?>
