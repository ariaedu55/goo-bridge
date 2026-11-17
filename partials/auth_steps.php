<?php
declare(strict_types=1);

$appSteps = [
    ['n' => '01', 't' => 'Premier contact', 'd' => 'Le client nous contacte', 'i' => 'chat'],
    ['n' => '02', 't' => 'Découverte & brief', 'd' => 'Comprendre le besoin', 'i' => 'search'],
    ['n' => '03', 't' => 'Proposition & devis', 'd' => 'Offre claire et chiffrée', 'i' => 'file'],
    ['n' => '04', 't' => 'Design UI / UX', 'd' => 'Maquettes & parcours', 'i' => 'pen'],
    ['n' => '05', 't' => 'Développement', 'd' => 'Code, API & intégrations', 'i' => 'code'],
    ['n' => '06', 't' => 'Tests & validation', 'd' => 'Qualité avant livraison', 'i' => 'check'],
    ['n' => '07', 't' => 'Livraison & stores', 'd' => 'Mise en ligne iOS / Android', 'i' => 'rocket'],
];
?>
<section class="auth-steps" aria-label="7 étapes de création d'application">
  <p class="auth-steps__eyebrow">— Votre partenaire digital —</p>
  <h1 class="auth-steps__title">
    <span class="auth-steps__title-main">7 étapes pour</span>
    <span class="auth-steps__title-script">créer votre app</span>
  </h1>
  <p class="auth-steps__lead">Du premier contact à la mise en ligne sur les stores — un parcours clair, simple et suivi.</p>

  <ol class="auth-stairs">
    <?php foreach ($appSteps as $i => $step): ?>
    <li class="auth-stair <?= $i % 2 === 0 ? 'auth-stair--navy' : 'auth-stair--white' ?>" style="--i:<?= (int) $i ?>">
      <span class="auth-stair__num"><?= htmlspecialchars($step['n'], ENT_QUOTES, 'UTF-8') ?></span>
      <span class="auth-stair__body">
        <strong><?= htmlspecialchars($step['t'], ENT_QUOTES, 'UTF-8') ?></strong>
        <small><?= htmlspecialchars($step['d'], ENT_QUOTES, 'UTF-8') ?></small>
      </span>
      <span class="auth-stair__icon" aria-hidden="true">
        <?php if ($step['i'] === 'chat'): ?>
        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        <?php elseif ($step['i'] === 'search'): ?>
        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.3-4.3"/></svg>
        <?php elseif ($step['i'] === 'file'): ?>
        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M9 13h6M9 17h4"/></svg>
        <?php elseif ($step['i'] === 'pen'): ?>
        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
        <?php elseif ($step['i'] === 'code'): ?>
        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
        <?php elseif ($step['i'] === 'check'): ?>
        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        <?php else: ?>
        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/><path d="M12 15l-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/></svg>
        <?php endif; ?>
      </span>
    </li>
    <?php endforeach; ?>
    <li class="auth-stair auth-stair--done" style="--i:7">
      <span class="auth-stair__body">
        <strong>C'est lancé</strong>
        <small>App en ligne &amp; suivie</small>
      </span>
      <span class="auth-stair__icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
      </span>
    </li>
  </ol>

  <div class="auth-steps__foot">
    <span class="auth-steps__arrow" aria-hidden="true"></span>
    <p>Votre projet démarre avec <strong>les bonnes étapes</strong>.</p>
  </div>
</section>
