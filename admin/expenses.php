<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/paths.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

require_admin();

$pageTitle = 'Comptabilité — Goo-Bridge Admin';
$pageHeading = 'Comptabilité';
$pageSub = 'Entrées, dépenses et solde';
$activeNav = 'expenses';

$pdo = db();
$error = '';

// Schéma : client_id + entry_type (expense|income)
try {
    $col = $pdo->query("SHOW COLUMNS FROM expenses LIKE 'client_id'")->fetch();
    if (!$col) {
        $pdo->exec('ALTER TABLE expenses ADD COLUMN client_id INT UNSIGNED NULL, ADD KEY idx_exp_client (client_id)');
    }
} catch (Throwable $e) {
}

try {
    $col = $pdo->query("SHOW COLUMNS FROM expenses LIKE 'entry_type'")->fetch();
    if (!$col) {
        $pdo->exec(
            "ALTER TABLE expenses ADD COLUMN entry_type ENUM('expense','income') NOT NULL DEFAULT 'expense' AFTER amount, ADD KEY idx_exp_type (entry_type)"
        );
    }
} catch (Throwable $e) {
}

try {
    $col = $pdo->query("SHOW COLUMNS FROM clients LIKE 'owner_type'")->fetch();
    if ($col) {
        $clientsStmt = $pdo->prepare(
            'SELECT id, company_name, website_url FROM clients WHERE owner_type = :ot ORDER BY company_name ASC'
        );
        $clientsStmt->execute([':ot' => 'goo_owner']);
        $clients = $clientsStmt->fetchAll();
    } else {
        $clients = [];
    }
} catch (Throwable $e) {
    $clients = [];
}

try {
    $categoriesStmt = $pdo->query(
        "SELECT DISTINCT TRIM(category) AS category FROM expenses
         WHERE category IS NOT NULL AND category != '' ORDER BY category ASC"
    );
    $categories = array_column($categoriesStmt->fetchAll(), 'category');
} catch (Throwable $e) {
    $categories = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['_csrf'] ?? null)) {
        $error = 'Session invalide — rechargez la page.';
    } else {
        $occurred = trim((string) ($_POST['occurred_at'] ?? ''));
        $amount = trim((string) ($_POST['amount'] ?? ''));
        $category = trim((string) ($_POST['category'] ?? '')) ?: null;
        $description = trim((string) ($_POST['description'] ?? '')) ?: null;
        $entryType = (string) ($_POST['entry_type'] ?? 'expense');
        if (!in_array($entryType, ['expense', 'income'], true)) {
            $entryType = 'expense';
        }
        $clientId = null;
        if (isset($_POST['client_id']) && $_POST['client_id'] !== '') {
            $clientId = ctype_digit((string) $_POST['client_id']) ? (int) $_POST['client_id'] : null;
        }

        if ($occurred === '' || $amount === '') {
            $error = 'La date et le montant sont requis.';
        } elseif (!is_numeric(str_replace(',', '.', $amount))) {
            $error = 'Montant invalide.';
        } else {
            $amountVal = abs((float) str_replace(',', '.', $amount));
            try {
                $ins = $pdo->prepare(
                    'INSERT INTO expenses (occurred_at, amount, entry_type, category, description, client_id)
                     VALUES (:occurred, :amount, :entry_type, :category, :description, :client_id)'
                );
                $ins->execute([
                    'occurred' => $occurred,
                    'amount' => $amountVal,
                    'entry_type' => $entryType,
                    'category' => $category,
                    'description' => $description,
                    'client_id' => $clientId,
                ]);
                $flashKey = $entryType === 'income' ? 'income' : 'expense';
                header('Location: ' . url('admin/expenses.php?flash=' . $flashKey), true, 303);
                exit;
            } catch (Throwable $e) {
                $error = database_user_error_message($e);
            }
        }
    }
}

$flash = (string) ($_GET['flash'] ?? '');

$from = trim((string) ($_GET['from'] ?? ''));
$to = trim((string) ($_GET['to'] ?? ''));
$filterCategory = trim((string) ($_GET['category'] ?? ''));
$filterClient = trim((string) ($_GET['client_id'] ?? ''));
$filterType = trim((string) ($_GET['entry_type'] ?? ''));
$sort = (string) ($_GET['sort'] ?? 'date_desc');

$where = [];
$params = [];
if ($from !== '') {
    $where[] = 'e.occurred_at >= :from';
    $params[':from'] = $from;
}
if ($to !== '') {
    $where[] = 'e.occurred_at <= :to';
    $params[':to'] = $to;
}
if ($filterCategory !== '') {
    $where[] = 'LOWER(TRIM(e.category)) LIKE :cat';
    $params[':cat'] = '%' . mb_strtolower($filterCategory, 'UTF-8') . '%';
}
if ($filterClient !== '') {
    $where[] = 'e.client_id = :client_id';
    $params[':client_id'] = (int) $filterClient;
}
if (in_array($filterType, ['expense', 'income'], true)) {
    $where[] = 'e.entry_type = :entry_type';
    $params[':entry_type'] = $filterType;
}

$order = 'e.occurred_at DESC, e.id DESC';
switch ($sort) {
    case 'date_asc':
        $order = 'e.occurred_at ASC, e.id DESC';
        break;
    case 'amount_asc':
        $order = 'e.amount ASC, e.id DESC';
        break;
    case 'amount_desc':
        $order = 'e.amount DESC, e.id DESC';
        break;
}

$totalIncome = 0.0;
$totalExpense = 0.0;
$balance = 0.0;
$entriesCount = 0;
$entries = [];

try {
    $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
    $stmt = $pdo->prepare(
        'SELECT e.id, e.occurred_at, e.amount, e.entry_type, e.category, e.description, e.created_at, e.client_id, c.company_name
         FROM expenses e
         LEFT JOIN clients c ON e.client_id = c.id
         ' . $whereSql . '
         ORDER BY ' . $order . '
         LIMIT 200'
    );
    $stmt->execute($params);
    $entries = $stmt->fetchAll();

    $totStmt = $pdo->prepare(
        "SELECT
            COALESCE(SUM(CASE WHEN entry_type = 'income' THEN amount ELSE 0 END), 0) AS income_sum,
            COALESCE(SUM(CASE WHEN entry_type = 'expense' OR entry_type IS NULL THEN amount ELSE 0 END), 0) AS expense_sum,
            COUNT(*) AS c
         FROM expenses e
         " . $whereSql
    );
    $totStmt->execute($params);
    $tot = $totStmt->fetch() ?: [];
    $totalIncome = (float) ($tot['income_sum'] ?? 0);
    $totalExpense = (float) ($tot['expense_sum'] ?? 0);
    $balance = $totalIncome - $totalExpense;
    $entriesCount = (int) ($tot['c'] ?? count($entries));
} catch (Throwable $e) {
    $entries = [];
}

$formType = (string) ($_POST['entry_type'] ?? 'expense');
if (!in_array($formType, ['expense', 'income'], true)) {
    $formType = 'expense';
}

require __DIR__ . '/inc/header.php';
?>

<div class="admin-expenses-page">
  <div class="admin-page-toolbar">
    <div class="admin-page-toolbar__stats">
      <span>Entrées <strong style="color:#166534">€ <?= number_format($totalIncome, 2, ',', ' ') ?></strong></span>
      <span class="admin-page-toolbar__sep" aria-hidden="true">·</span>
      <span>Dépenses <strong style="color:#b91c1c">€ <?= number_format($totalExpense, 2, ',', ' ') ?></strong></span>
      <span class="admin-page-toolbar__sep" aria-hidden="true">·</span>
      <span>Solde <strong style="color:<?= $balance >= 0 ? '#166534' : '#b91c1c' ?>">€ <?= number_format($balance, 2, ',', ' ') ?></strong></span>
    </div>
  </div>

  <?php if ($error !== ''): ?>
    <p class="admin-alert admin-alert--error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
  <?php elseif ($flash === 'expense'): ?>
    <p class="admin-alert admin-alert--ok">Dépense enregistrée — déduite du solde.</p>
  <?php elseif ($flash === 'income'): ?>
    <p class="admin-alert admin-alert--ok">Entrée d’argent enregistrée.</p>
  <?php endif; ?>

  <section class="admin-panel admin-board admin-expenses-board">
    <header class="admin-board__head">
      <div class="admin-board__head-text">
        <span class="admin-board__eyebrow">Comptabilité</span>
        <h2>Mouvements <span class="admin-heading-count"><?= $entriesCount ?></span></h2>
      </div>
      <ul class="admin-board__stats" role="list">
        <li class="admin-board__stat admin-board__stat--active">
          <span class="admin-board__stat-dot" aria-hidden="true"></span>
          <span class="admin-board__stat-num" style="font-size:1rem;color:#166534">€&nbsp;<?= number_format($totalIncome, 2, ',', ' ') ?></span>
          <span class="admin-board__stat-label">Entrées</span>
        </li>
        <li class="admin-board__stat">
          <span class="admin-board__stat-dot" aria-hidden="true"></span>
          <span class="admin-board__stat-num" style="font-size:1rem;color:#b91c1c">€&nbsp;<?= number_format($totalExpense, 2, ',', ' ') ?></span>
          <span class="admin-board__stat-label">Dépenses</span>
        </li>
        <li class="admin-board__stat admin-board__stat--billing">
          <span class="admin-board__stat-dot" aria-hidden="true"></span>
          <span class="admin-board__stat-num" style="font-size:1rem;color:<?= $balance >= 0 ? '#166534' : '#b91c1c' ?>">€&nbsp;<?= number_format($balance, 2, ',', ' ') ?></span>
          <span class="admin-board__stat-label">Solde</span>
        </li>
      </ul>
    </header>

    <form method="get" class="admin-expenses-filters" role="search">
      <div class="admin-expenses-filters__group">
        <label>
          <span class="admin-expenses-filters__lab">Du</span>
          <input type="date" name="from" value="<?= htmlspecialchars($from, ENT_QUOTES, 'UTF-8') ?>">
        </label>
        <label>
          <span class="admin-expenses-filters__lab">Au</span>
          <input type="date" name="to" value="<?= htmlspecialchars($to, ENT_QUOTES, 'UTF-8') ?>">
        </label>
        <label>
          <span class="admin-expenses-filters__lab">Type</span>
          <select name="entry_type">
            <option value="">Tous</option>
            <option value="income" <?= $filterType === 'income' ? 'selected' : '' ?>>Entrées</option>
            <option value="expense" <?= $filterType === 'expense' ? 'selected' : '' ?>>Dépenses</option>
          </select>
        </label>
        <label>
          <span class="admin-expenses-filters__lab">Motif</span>
          <select name="category">
            <option value="">Tous</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?>" <?= $filterCategory === $cat ? 'selected' : '' ?>><?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label>
          <span class="admin-expenses-filters__lab">Entreprise</span>
          <select name="client_id">
            <option value="">Toutes</option>
            <?php foreach ($clients as $client): ?>
              <option value="<?= (int) $client['id'] ?>" <?= $filterClient === (string) $client['id'] ? 'selected' : '' ?>><?= htmlspecialchars($client['company_name'], ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <button type="submit" class="btn-primary admin-expenses-filters__submit">Filtrer</button>
        <?php if ($from !== '' || $to !== '' || $filterCategory !== '' || $filterClient !== '' || $filterType !== ''): ?>
          <a class="admin-expenses-filters__reset" href="<?= htmlspecialchars(url('admin/expenses.php'), ENT_QUOTES, 'UTF-8') ?>">Réinitialiser</a>
        <?php endif; ?>
      </div>
    </form>

    <div class="admin-expenses-split">
      <section class="admin-expenses-card" aria-labelledby="exp-form-title">
        <header class="admin-expenses-card__head">
          <h3 id="exp-form-title">Nouveau mouvement</h3>
          <p>Enregistrez une entrée d’argent ou une dépense. Le solde se met à jour automatiquement.</p>
        </header>
        <form method="post" class="admin-form admin-form--expenses">
          <?= csrf_field() ?>
          <div class="admin-field">
            <label for="entry_type">Type *</label>
            <select id="entry_type" name="entry_type" required>
              <option value="income" <?= $formType === 'income' ? 'selected' : '' ?>>Entrée d’argent (+)</option>
              <option value="expense" <?= $formType === 'expense' ? 'selected' : '' ?>>Dépense (−)</option>
            </select>
          </div>
          <div class="admin-grid-2">
            <div class="admin-field">
              <label for="occurred_at">Date</label>
              <input type="date" id="occurred_at" name="occurred_at" required value="<?= htmlspecialchars((string) ($_POST['occurred_at'] ?? date('Y-m-d')), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="admin-field">
              <label for="amount">Montant (€)</label>
              <input type="text" id="amount" name="amount" required inputmode="decimal" value="<?= htmlspecialchars((string) ($_POST['amount'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
          </div>
          <div class="admin-field">
            <label for="category">Motif</label>
            <input type="text" id="category" name="category" maxlength="128" placeholder="Ex. Vente, Abonnement, Salaire, Hébergement…"
              value="<?= htmlspecialchars((string) ($_POST['category'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
          </div>
          <div class="admin-field">
            <label for="client_id">Affilier à l'entreprise</label>
            <select id="client_id" name="client_id">
              <option value="">— Aucune —</option>
              <?php foreach ($clients as $c): ?>
                <option value="<?= (int) $c['id'] ?>" <?= (isset($_POST['client_id']) && (string) $_POST['client_id'] === (string) $c['id']) ? 'selected' : '' ?>><?= htmlspecialchars((string) $c['company_name'] . ($c['website_url'] ? ' — ' . $c['website_url'] : ''), ENT_QUOTES, 'UTF-8') ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="admin-field">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4"><?= htmlspecialchars((string) ($_POST['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
          </div>
          <button type="submit" class="btn-primary admin-submit">Enregistrer</button>
        </form>
      </section>

      <section class="admin-expenses-card" aria-labelledby="exp-list-title">
        <header class="admin-expenses-card__head">
          <h3 id="exp-list-title">Historique</h3>
          <p>Derniers 200 mouvements<?= ($from !== '' || $to !== '' || $filterCategory !== '' || $filterClient !== '' || $filterType !== '') ? ' (filtrés)' : '' ?>.</p>
        </header>
        <?php if ($entries === []): ?>
          <div class="admin-board__empty">
            <div class="admin-board__empty-icon" aria-hidden="true">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 1v22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
            <h3>Aucun mouvement</h3>
            <p>Aucune entrée ou dépense ne correspond aux filtres actifs.</p>
          </div>
        <?php else: ?>
          <ul class="admin-expenses-list" role="list">
            <?php foreach ($entries as $e): ?>
              <?php
                $isIncome = (($e['entry_type'] ?? 'expense') === 'income');
                $sign = $isIncome ? '+' : '−';
                $amtColor = $isIncome ? '#166534' : '#b91c1c';
              ?>
              <li class="admin-expense-item">
                <div class="admin-expense-item__row">
                  <div class="admin-expense-item__main">
                    <strong>
                      <span style="display:inline-block;padding:2px 8px;border-radius:999px;font-size:.68rem;margin-right:6px;background:<?= $isIncome ? '#dcfce7' : '#fee2e2' ?>;color:<?= $amtColor ?>">
                        <?= $isIncome ? 'Entrée' : 'Dépense' ?>
                      </span>
                      <?= htmlspecialchars((string) ($e['category'] ?? '—'), ENT_QUOTES, 'UTF-8') ?>
                    </strong>
                    <?php if (!empty($e['company_name'])): ?>
                      <span class="admin-expense-item__company"><?= htmlspecialchars((string) $e['company_name'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                    <span class="admin-expense-item__date"><?= htmlspecialchars((string) $e['occurred_at'], ENT_QUOTES, 'UTF-8') ?></span>
                  </div>
                  <div class="admin-expense-item__amount" style="color:<?= $amtColor ?>">
                    <?= $sign ?> € <?= number_format((float) $e['amount'], 2, ',', ' ') ?>
                  </div>
                  <button type="button" class="admin-expense-item__toggle view-more" data-id="<?= (int) $e['id'] ?>">Détails</button>
                </div>
                <div class="admin-expense-item__details admin-list__details" hidden>
                  <p><?= htmlspecialchars((string) ($e['description'] ?? ''), ENT_QUOTES, 'UTF-8') !== '' ? htmlspecialchars((string) $e['description'], ENT_QUOTES, 'UTF-8') : 'Aucune description.' ?></p>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </section>
    </div>
  </section>
</div>

<?php require __DIR__ . '/inc/footer.php'; ?>

<script>
document.addEventListener('click', function (e) {
  if (!e.target.matches || !e.target.matches('.view-more')) return;
  var btn = e.target;
  var li = btn.closest('li');
  if (!li) return;
  var details = li.querySelector('.admin-list__details, .admin-expense-item__details');
  if (!details) return;
  var isHidden = details.hasAttribute('hidden');
  if (isHidden) {
    details.removeAttribute('hidden');
    btn.textContent = 'Masquer';
  } else {
    details.setAttribute('hidden', '');
    btn.textContent = 'Détails';
  }
});
</script>
