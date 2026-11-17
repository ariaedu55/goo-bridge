<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/paths.php';
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/honeypot.php';
require_once __DIR__ . '/includes/rate_limit.php';
require_once __DIR__ . '/includes/app_log.php';
require_once __DIR__ . '/includes/country_dial.php';
require_once __DIR__ . '/includes/auth.php';

header('Content-Type: text/html; charset=UTF-8');

$pageTitle = 'Connexion — Support Goo-Bridge';
$error = '';
$notRecognized = false;

$allowedCc = country_allowed_dials();
$phoneCc = in_array(trim((string) ($_POST['phone_cc'] ?? '221')), $allowedCc, true)
    ? trim((string) ($_POST['phone_cc'] ?? '221'))
    : '221';
$phoneLocal = trim((string) ($_POST['phone_local'] ?? ''));
$emailVal = trim((string) ($_POST['email'] ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['_csrf'] ?? null)) {
        $error = 'Session expirée : rechargez la page et réessayez.';
    } elseif (honeypot_triggered()) {
        app_log_write('security', 'Honeypot support déclenché', ['form' => 'support']);
        $error = 'Aucune entreprise ne correspond à ces identifiants. Vérifiez votre email et votre numéro de téléphone.';
        $notRecognized = true;
    } elseif (!($rlSupport = rate_limit_hit('support_login', 20, 3600))['allowed']) {
        app_log_write('security', 'Rate limit support', ['retry_after' => $rlSupport['retry_after']]);
        $error = 'Trop de tentatives. Réessayez dans ' . $rlSupport['retry_after'] . ' seconde(s).';
    } elseif (!isset($_POST['accept_terms']) || (string) $_POST['accept_terms'] !== '1') {
        $error = 'Vous devez accepter la politique de confidentialité pour continuer.';
    } elseif ($emailVal === '' || !filter_var($emailVal, FILTER_VALIDATE_EMAIL)) {
        $error = 'Indiquez une adresse email valide.';
    } elseif ($phoneLocal === '' || strlen($phoneLocal) > 40
        || !preg_match('/^[\d\s().+-]{4,40}$/', $phoneLocal)) {
        $error = 'Indiquez votre numéro de téléphone (sans répéter l’indicatif).';
    } else {
        $digitsIn = preg_replace('/\D+/', '', $phoneLocal);
        if (strlen($digitsIn) < 5) {
            $error = 'Indiquez un numéro de téléphone valide.';
        } else {
            try {
                $pdo = db();
                $emailNorm = mb_strtolower($emailVal, 'UTF-8');
                $stmt = $pdo->prepare(
                    'SELECT id, ticket_portal_token, phone FROM clients
                     WHERE email IS NOT NULL AND TRIM(LOWER(email)) = :em'
                );
                $stmt->execute(['em' => $emailNorm]);
                $rows = $stmt->fetchAll();

                $token = null;
                foreach ($rows as $row) {
                    if (country_phone_matches($row['phone'] ?? null, $phoneCc, $phoneLocal)) {
                        $tok = trim((string) ($row['ticket_portal_token'] ?? ''));
                        if ($tok === '') {
                            $up = $pdo->prepare(
                                'UPDATE clients SET ticket_portal_token = :tok WHERE id = :id LIMIT 1'
                            );
                            $tok = bin2hex(random_bytes(32));
                            $up->execute(['tok' => $tok, 'id' => (int) $row['id']]);
                        }
                        $token = $tok;
                        break;
                    }
                }

                if ($token !== null) {
                    $_SESSION['support_client_token'] = $token;
                    $_SESSION['support_client_auth_at'] = time();
                    header(
                        'Location: ' . url('client_support.php?t=' . rawurlencode($token)),
                        true,
                        303
                    );
                    exit;
                }

                $error = 'Aucune entreprise ne correspond à ces identifiants. Vérifiez votre email et votre numéro de téléphone.';
                $notRecognized = true;
            } catch (Throwable $e) {
                $error = 'Une erreur technique est survenue. Réessayez dans quelques instants.';
            }
        }
    }
}

$contactUrl = url('index.php#contact');
$isHighlight = $notRecognized;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@600;700&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <link rel="icon" type="image/webp" href="<?= htmlspecialchars(url('images/favicon-goo-bridge.webp'), ENT_QUOTES, 'UTF-8') ?>" />
  <link rel="stylesheet" href="<?= htmlspecialchars(url('style.css'), ENT_QUOTES, 'UTF-8') ?>">
  <link rel="stylesheet" href="<?= htmlspecialchars(url('admin/admin.css'), ENT_QUOTES, 'UTF-8') ?>">
  <style>
    body.lg-page {
      margin: 0;
      background: #f6f1ea;
      font-family: 'Poppins', system-ui, sans-serif;
    }
    .lg-shell {
      min-height: calc(100vh - var(--nav-h, 64px));
      box-sizing: border-box;
      padding: 0;
      background: #f6f1ea;
    }
    body.lg-page main.lg-shell {
      padding-top: var(--nav-h, 64px);
    }
    .lg-shell .auth-layout {
      min-height: calc(100vh - var(--nav-h, 64px));
    }
    .lg-card {
      width: 100%;
      max-width: 440px;
      background: #fff;
      border-radius: 22px;
      padding: clamp(28px, 4vw, 36px);
      border: 1px solid rgba(15, 29, 61, 0.06);
      box-shadow: 0 18px 48px rgba(15, 29, 61, 0.12);
      justify-self: end;
    }
    .lg-brand {
      display: inline-flex;
      align-items: center;
      margin-bottom: 14px;
      line-height: 0;
      text-decoration: none;
    }
    .lg-brand img {
      display: block;
      height: 52px;
      width: auto;
    }
    .lg-h1 {
      margin: 0 0 6px;
      font-size: 1.35rem;
      font-weight: 800;
      color: #0f1d3d;
      text-align: left;
    }
    .lg-sub {
      margin: 0 0 22px;
      color: #64748b;
      font-size: 0.9rem;
      line-height: 1.5;
      text-align: left;
    }
    .lg-alert {
      padding: 12px 14px;
      border-radius: 12px;
      margin-bottom: 16px;
      font-size: 0.88rem;
      line-height: 1.45;
      background: rgba(185, 28, 28, 0.07);
      border: 1px solid rgba(185, 28, 28, 0.22);
      color: #991b1b;
      display: flex;
      gap: 10px;
      align-items: flex-start;
    }
    .lg-alert svg { flex-shrink: 0; margin-top: 2px; }
    .lg-field { margin-bottom: 16px; }
    .lg-field > label,
    .lg-field-cell > label {
      display: block;
      font-size: 0.82rem;
      font-weight: 700;
      margin-bottom: 7px;
      color: #0f1d3d;
    }
    .lg-input {
      width: 100%;
      box-sizing: border-box;
      padding: 13px 14px;
      border-radius: 12px;
      border: 1px solid #e2e8f0;
      background: #fff;
      font-size: 0.95rem;
      font-family: inherit;
      color: #0f1d3d;
      transition: border-color 0.2s, box-shadow 0.2s;
    }
    .lg-input::placeholder { color: #94a3b8; }
    .lg-input:focus {
      outline: none;
      border-color: #f97316;
      box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.16);
    }
    .lg-phone-grid {
      display: grid;
      grid-template-columns: minmax(118px, 34%) 1fr;
      gap: 12px;
    }
    .lg-phone-grid > .lg-field-cell { min-width: 0; }
    .lg-phone-cc {
      cursor: pointer;
      appearance: none;
      font-weight: 600;
      font-size: 0.9rem;
      padding: 13px 34px 13px 12px;
      background-color: #fff;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' fill='none' stroke='%2394a3b8' stroke-width='2.2' stroke-linecap='round'%3E%3Cpath d='M3.5 5.5L7 9l3.5-3.5'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 12px center;
      text-overflow: ellipsis;
      white-space: nowrap;
      overflow: hidden;
    }
    .lg-check {
      display: flex;
      align-items: flex-start;
      gap: 10px;
      margin: 16px 0 20px;
      font-size: 0.86rem;
      color: #0f1d3d;
      line-height: 1.45;
      cursor: pointer;
    }
    .lg-check input[type="checkbox"] {
      flex-shrink: 0;
      width: 18px;
      height: 18px;
      margin-top: 1px;
      accent-color: #f97316;
      cursor: pointer;
    }
    .lg-check a {
      color: #f97316;
      font-weight: 700;
      text-decoration: underline;
      text-underline-offset: 2px;
    }
    .lg-submit {
      width: 100%;
      padding: 14px 20px;
      border-radius: 12px;
      border: 0;
      background: #0f1d3d;
      color: #fff;
      font-size: 0.98rem;
      font-weight: 700;
      cursor: pointer;
      box-shadow: 0 10px 24px rgba(15, 29, 61, 0.22);
      transition: background 0.2s, transform 0.15s;
    }
    .lg-submit:hover { background: #1e3a8a; transform: translateY(-1px); }
    .lg-foot {
      margin-top: 18px;
      padding-top: 16px;
      border-top: 1px solid #e2e8f0;
      text-align: center;
      font-size: 0.85rem;
      color: #64748b;
    }
    .lg-foot a { color: #f97316; font-weight: 600; text-decoration: none; }
    .lg-foot a:hover { text-decoration: underline; }
    .lg-contact {
      margin-top: 18px;
      padding: 18px;
      border-radius: 16px;
      background: rgba(249, 115, 22, 0.08);
      border: 1px solid rgba(249, 115, 22, 0.22);
      display: flex;
      gap: 12px;
      align-items: flex-start;
    }
    .lg-contact--highlight {
      background: rgba(249, 115, 22, 0.12);
      border-color: rgba(249, 115, 22, 0.4);
      box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.1);
    }
    .lg-contact__icon {
      flex-shrink: 0;
      width: 38px;
      height: 38px;
      border-radius: 12px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: #fff;
      color: #f97316;
      border: 1px solid rgba(15, 29, 61, 0.06);
    }
    .lg-contact__body { min-width: 0; flex: 1; }
    .lg-contact__title {
      font-size: 0.92rem;
      font-weight: 700;
      color: #0f1d3d;
      margin: 0 0 4px;
    }
    .lg-contact__text {
      margin: 0 0 12px;
      font-size: 0.82rem;
      line-height: 1.5;
      color: #64748b;
    }
    .lg-contact__btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 10px 16px;
      border-radius: 999px;
      background: #f97316;
      color: #fff;
      font-weight: 700;
      font-size: 0.84rem;
      text-decoration: none;
      box-shadow: 0 8px 20px rgba(249, 115, 22, 0.3);
      transition: background 0.2s, transform 0.15s;
    }
    .lg-contact__btn:hover { background: #ea580c; transform: translateY(-1px); }
    @media (max-width: 960px) {
      .lg-card {
        justify-self: stretch;
        max-width: none;
        order: -1;
      }
    }
    @media (max-width: 480px) {
      .lg-phone-grid { grid-template-columns: 1fr; }
      .lg-shell .auth-layout {
        padding-left: max(12px, env(safe-area-inset-left, 0px));
        padding-right: max(12px, env(safe-area-inset-right, 0px));
      }
    }
  </style>
</head>
<body class="bridge-body lg-page">
  <?php require __DIR__ . '/partials/site_header.php'; ?>

  <main class="lg-shell">
    <div class="auth-layout">
      <?php require __DIR__ . '/partials/auth_steps.php'; ?>

      <section class="lg-card" aria-labelledby="lg-title">
        <a href="<?= htmlspecialchars(url('index.php'), ENT_QUOTES, 'UTF-8') ?>" class="lg-brand" aria-label="GOO-BRIDGE accueil">
          <img src="<?= htmlspecialchars(url('images/logo-goo-bridge.webp'), ENT_QUOTES, 'UTF-8') ?>" alt="GOO-BRIDGE" width="180" height="74" decoding="async" />
        </a>
        <h1 class="lg-h1" id="lg-title">Espace support</h1>
        <p class="lg-sub">Accédez à votre espace pour suivre, déclarer et gérer vos tickets.</p>

        <?php if ($error !== ''): ?>
          <div class="lg-alert" role="alert">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <circle cx="12" cy="12" r="10"/>
              <line x1="12" y1="8" x2="12" y2="12"/>
              <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <span><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></span>
          </div>
        <?php endif; ?>

        <form method="post" action="<?= htmlspecialchars(url('support.php'), ENT_QUOTES, 'UTF-8') ?>" autocomplete="on" novalidate>
          <?= csrf_field() ?>
          <?= honeypot_field() ?>

          <div class="lg-field">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required maxlength="255" inputmode="email"
              class="lg-input" autocomplete="email"
              value="<?= htmlspecialchars($emailVal, ENT_QUOTES, 'UTF-8') ?>"
              placeholder="vous@entreprise.com">
          </div>

          <div class="lg-field">
            <div class="lg-phone-grid">
              <div class="lg-field-cell">
                <label for="phone_cc">Indicatif</label>
                <?= render_country_dial_select('phone_cc', $phoneCc, [
                    'id' => 'phone_cc',
                    'class' => 'lg-input lg-phone-cc',
                    'autocomplete' => 'country',
                    'aria-label' => 'Indicatif téléphonique pays',
                ]) ?>
              </div>
              <div class="lg-field-cell">
                <label for="phone_local">Téléphone</label>
                <input type="tel" id="phone_local" name="phone_local" required maxlength="40" inputmode="tel"
                  class="lg-input" autocomplete="tel-national"
                  value="<?= htmlspecialchars($phoneLocal, ENT_QUOTES, 'UTF-8') ?>"
                  placeholder="77 123 45 67">
              </div>
            </div>
          </div>

          <label class="lg-check">
            <input type="checkbox" name="accept_terms" value="1" required<?= isset($_POST['accept_terms']) ? ' checked' : '' ?>>
            <span>J’ai lu et j’accepte la <a href="<?= htmlspecialchars(url('index.php#contact'), ENT_QUOTES, 'UTF-8') ?>">politique de confidentialité</a>.</span>
          </label>

          <button type="submit" class="lg-submit">Connexion</button>

          <div class="lg-foot">
            <a href="<?= htmlspecialchars(url('index.php'), ENT_QUOTES, 'UTF-8') ?>">← Retour au site</a>
          </div>
        </form>

        <aside class="lg-contact <?= $isHighlight ? 'lg-contact--highlight' : '' ?>" aria-live="polite">
          <div class="lg-contact__icon" aria-hidden="true">
            <?php if ($isHighlight): ?>
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9.1 9a3 3 0 0 1 5.8 1c0 2-3 3-3 3"/>
                <line x1="12" y1="17" x2="12.01" y2="17"/>
                <circle cx="12" cy="12" r="10"/>
              </svg>
            <?php else: ?>
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                <polyline points="22,6 12,13 2,6"/>
              </svg>
            <?php endif; ?>
          </div>
          <div class="lg-contact__body">
            <?php if ($isHighlight): ?>
              <p class="lg-contact__title">Pas encore client Goo-Bridge ?</p>
              <p class="lg-contact__text">
                Vos identifiants ne sont pas reconnus. Envoyez-nous votre demande via le formulaire de contact — réponse sous 24 à 72&nbsp;h ouvrées.
              </p>
            <?php else: ?>
              <p class="lg-contact__title">Une question avant de vous connecter&nbsp;?</p>
              <p class="lg-contact__text">
                Pas encore client ou nouveau projet ? Passez par notre formulaire de contact.
              </p>
            <?php endif; ?>
            <a href="<?= htmlspecialchars($contactUrl, ENT_QUOTES, 'UTF-8') ?>" class="lg-contact__btn">
              <?= $isHighlight ? 'Envoyer ma demande' : 'Nous contacter' ?>
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <line x1="5" y1="12" x2="19" y2="12"/>
                <polyline points="12 5 19 12 12 19"/>
              </svg>
            </a>
          </div>
        </aside>
      </section>
    </div>
  </main>
</body>
</html>
