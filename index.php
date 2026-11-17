<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/paths.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/portfolio.php';
require_once __DIR__ . '/includes/showcase_apps.php';
require __DIR__ . '/contact_handler.php';

$portfolioItems = [];
$showcaseApps = [];
try {
    $pdoHome = db();
    $portfolioItems = portfolio_list_published($pdoHome);
    $showcaseApps = showcase_apps_list_published($pdoHome);
} catch (Throwable $e) {
    $portfolioItems = [];
    $showcaseApps = [];
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Goo-Bridge – Agence Digitale | Sites, Apps &amp; Hébergement</title>
  <meta name="description"
    content="Goo-Bridge construit votre présence digitale : sites web, apps mobiles, scraping, IA pour analyses dynamiques, e-commerce et hébergement. Le pont entre la digitalisation et l'économie informelle." />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@400;500;600;700;800&display=swap"
    rel="stylesheet" />
  <link rel="icon" type="image/webp" href="images/favicon-goo-bridge.webp" />
  <link rel="stylesheet" href="style.css" />
</head>

<body>

  <!-- ===== NAVBAR ===== -->
  <?php $siteHeaderOnIndex = true; require __DIR__ . '/partials/site_header.php'; ?>

  <main>

    <!-- ===== HERO ===== -->
    <section id="accueil" class="lp-hero">
      <div class="lp-hero__inner">
        <div class="lp-hero__content reveal" data-delay="80">
          <p class="lp-overline">
            <span class="lp-overline__dot lp-overline__dot--blue"></span>
            DIGITALISATION
            <span class="lp-overline__sep">•</span>
            INFORMEL
            <span class="lp-overline__sep">•</span>
            IMPACT
            <span class="lp-overline__dot lp-overline__dot--purple"></span>
          </p>
          <h1 class="lp-hero__title">
            Nous construisons votre<br />
            <span class="lp-gradient-text">présence digitale</span>
          </h1>
          <p class="lp-hero__sub">
            De la conception à la mise en ligne, Goo-Bridge vous accompagne à chaque étape. Sites web modernes, applications mobiles performantes, hébergement sécurisé — tout ce qu'il faut pour que votre entreprise rayonne en ligne. Nous sommes le pont entre la digitalisation et l'économie informelle.
          </p>
          <div class="lp-hero__actions">
            <a href="#contact" class="lp-btn lp-btn--navy">
              Démarrer un projet
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                <line x1="5" y1="12" x2="19" y2="12" />
                <polyline points="12 5 19 12 12 19" />
              </svg>
            </a>
            <a href="#realisations" class="lp-btn lp-btn--play">
              Voir nos réalisations →
            </a>
          </div>
        </div>
        <div class="lp-hero__visual reveal" data-delay="120">
          <img src="images/landing/hero-rocket-laptop.webp" alt="Illustration — lancement digital Goo-Bridge" width="1536" height="1024" loading="eager" decoding="async" fetchpriority="high" />
        </div>
      </div>
    </section>

    <!-- ===== FEATURES ===== -->
    <section class="lp-features" aria-label="Nos engagements">
      <div class="lp-features__inner">
        <article class="lp-feature reveal" data-delay="0">
          <div class="lp-feature__icon lp-feature__icon--blue">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <circle cx="12" cy="12" r="10" />
              <polyline points="12 6 12 12 16 14" />
            </svg>
          </div>
          <div>
            <h3>Livraison dans les délais</h3>
            <p>Un planning clair dès le départ. Délai moyen : 2 à 8 semaines selon le projet.</p>
          </div>
        </article>
        <article class="lp-feature reveal" data-delay="80">
          <div class="lp-feature__icon lp-feature__icon--purple">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
              <polyline points="9 12 11 14 15 10" />
            </svg>
          </div>
          <div>
            <h3>Hébergement sécurisé SSL</h3>
            <p>HTTPS, sauvegardes et surveillance — votre site en ligne, rapide et protégé.</p>
          </div>
        </article>
        <article class="lp-feature reveal" data-delay="160">
          <div class="lp-feature__icon lp-feature__icon--orange">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
            </svg>
          </div>
          <div>
            <h3>Support inclus</h3>
            <p>Maintenance, mises à jour et évolutions — nous restons votre partenaire après la livraison.</p>
          </div>
        </article>
        <article class="lp-feature reveal" data-delay="240">
          <div class="lp-feature__icon lp-feature__icon--cyan">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path d="M3 11h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H3v-7z" />
              <path d="M21 11h-3a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h3v-7z" />
              <path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3z" />
            </svg>
          </div>
          <div>
            <h3>Pont digital ↔ informel</h3>
            <p>Nous digitalisons commerces, artisans et PME — outils simples pour le terrain réel.</p>
          </div>
        </article>
      </div>
    </section>

    <!-- ===== WHAT WE DO ===== -->
    <section id="services" class="lp-services">
      <div class="lp-section-head reveal">
        <div class="lp-section-head__line" aria-hidden="true"></div>
        <h2>Nos Services</h2>
        <div class="lp-section-head__line" aria-hidden="true"></div>
      </div>
      <p class="lp-section-sub reveal" data-delay="80">Ce que nous faisons concrètement pour vous — pas de jargon, juste des résultats.</p>
      <div class="lp-services__grid">
        <article class="lp-service-card lp-service-card--blue reveal" data-delay="0">
          <div class="lp-service-card__illus">
            <img src="images/landing/service-web-app.webp" alt="Développement web et e-commerce" width="1536" height="1024" loading="lazy" decoding="async" />
          </div>
          <h3>Développement Web</h3>
          <p>Sites vitrines, e-commerce et plateformes web qui attirent, convainquent et convertissent — responsive et optimisés SEO.</p>
          <a href="#contact" class="lp-service-card__arrow lp-service-card__arrow--blue" aria-label="Demander un devis web">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12" /><polyline points="12 5 19 12 12 19" /></svg>
          </a>
        </article>
        <article class="lp-service-card lp-service-card--purple reveal" data-delay="80">
          <div class="lp-service-card__illus">
            <img src="images/landing/service-mobile-app.webp" alt="Applications mobiles iOS et Android" width="1536" height="1024" loading="lazy" decoding="async" />
          </div>
          <h3>Applications Mobiles</h3>
          <p>Apps iOS, Android ou hybrides : fidéliser vos clients, rester présent dans leur poche, publier sur les stores.</p>
          <a href="#contact" class="lp-service-card__arrow lp-service-card__arrow--purple" aria-label="Discuter d'une app mobile">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12" /><polyline points="12 5 19 12 12 19" /></svg>
          </a>
        </article>
        <article class="lp-service-card lp-service-card--teal reveal" data-delay="120">
          <div class="lp-service-card__illus">
            <img src="images/landing/service-scraping.webp" alt="Scraping et collecte de données" width="1536" height="1024" loading="lazy" decoding="async" />
          </div>
          <h3>Scraping &amp; Collecte de données</h3>
          <p>Sites et outils de scraping pour extraire, structurer et synchroniser des données web — veille, catalogues, prix, contenus.</p>
          <a href="#contact" class="lp-service-card__arrow lp-service-card__arrow--teal" aria-label="Demander un projet scraping">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12" /><polyline points="12 5 19 12 12 19" /></svg>
          </a>
        </article>
        <article class="lp-service-card lp-service-card--cyan reveal" data-delay="160">
          <div class="lp-service-card__illus">
            <img src="images/landing/service-ai-analytics.webp" alt="IA et analyses dynamiques" width="1536" height="1024" loading="lazy" decoding="async" />
          </div>
          <h3>IA &amp; Analyses dynamiques</h3>
          <p>Plateformes avec intelligence artificielle pour analyser vos données en temps réel : tendances, alertes, scoring et tableaux de bord.</p>
          <a href="#contact" class="lp-service-card__arrow lp-service-card__arrow--cyan" aria-label="Demander une solution IA">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12" /><polyline points="12 5 19 12 12 19" /></svg>
          </a>
        </article>
        <article class="lp-service-card lp-service-card--orange reveal" data-delay="200">
          <div class="lp-service-card__illus">
            <img src="images/landing/service-desktop-app.webp" alt="Hébergement cloud sécurisé" width="1536" height="1024" loading="lazy" decoding="async" />
          </div>
          <h3>Hébergement &amp; Déploiement</h3>
          <p>Mise en ligne, domaine, SSL, sauvegardes et surveillance 24/7 — votre projet en production, fiable et scalable.</p>
          <a href="#contact" class="lp-service-card__arrow lp-service-card__arrow--orange" aria-label="Héberger mon projet">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12" /><polyline points="12 5 19 12 12 19" /></svg>
          </a>
        </article>
        <article class="lp-service-card lp-service-card--slate reveal" data-delay="240">
          <div class="lp-service-card__illus">
            <img src="images/landing/service-ux-ui.webp" alt="Digitalisation de l'informel" width="1536" height="1024" loading="lazy" decoding="async" />
          </div>
          <h3>Digitalisation de l'informel</h3>
          <p>Boutique en ligne, catalogue, paiement et outils métier adaptés aux commerçants, artisans et PME qui passent au digital.</p>
          <a href="#contact" class="lp-service-card__arrow lp-service-card__arrow--blue" aria-label="Digitaliser mon activité">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12" /><polyline points="12 5 19 12 12 19" /></svg>
          </a>
        </article>
      </div>
    </section>

    <!-- ===== PROCESSUS + SHOWCASE APP ===== -->
    <section class="lp-infographic reveal" aria-label="Notre processus et réalisations">
      <div class="lp-infographic__inner">
        <div class="lp-infographic__layout">

          <div class="lp-infographic__chart">
            <h2 class="lp-infographic__title">Notre Processus de Création</h2>
            <div class="lp-infographic__board">
              <svg class="lp-infographic__wiring" viewBox="0 0 800 460" preserveAspectRatio="xMidYMid meet" aria-hidden="true">
                <defs>
                  <filter id="lp-line-shadow" x="-30%" y="-30%" width="160%" height="160%">
                    <feDropShadow dx="2" dy="3" stdDeviation="2" flood-color="rgba(15,23,42,0.2)" />
                  </filter>
                </defs>
                <!-- barre supérieure segmentée -->
                <rect x="0" y="0" width="196" height="12" rx="2" fill="#3568a8" filter="url(#lp-line-shadow)" />
                <rect x="204" y="0" width="196" height="12" rx="2" fill="#f5a623" filter="url(#lp-line-shadow)" />
                <rect x="408" y="0" width="196" height="12" rx="2" fill="#689f38" filter="url(#lp-line-shadow)" />
                <rect x="612" y="0" width="188" height="12" rx="2" fill="#e53935" filter="url(#lp-line-shadow)" />
                <!-- connecteurs courts (étapes 01 & 03) -->
                <path d="M100 12 V62 H108" stroke="#3568a8" stroke-width="8" fill="none" stroke-linecap="round" stroke-linejoin="round" filter="url(#lp-line-shadow)" />
                <path d="M500 12 V62 H508" stroke="#689f38" stroke-width="8" fill="none" stroke-linecap="round" stroke-linejoin="round" filter="url(#lp-line-shadow)" />
                <!-- connecteurs longs (étapes 02 & 04 — orange & rouge, plus bas) -->
                <path d="M300 12 V218 H308" stroke="#f5a623" stroke-width="8" fill="none" stroke-linecap="round" stroke-linejoin="round" filter="url(#lp-line-shadow)" />
                <path d="M700 12 V218 H708" stroke="#e53935" stroke-width="8" fill="none" stroke-linecap="round" stroke-linejoin="round" filter="url(#lp-line-shadow)" />
              </svg>

              <div class="lp-infographic__steps">
                <article class="lp-step lp-step--01" style="--sc:#3568a8">
                  <div class="lp-step__ring">
                    <svg class="lp-step__arc" viewBox="0 0 200 200" aria-hidden="true">
                      <circle cx="100" cy="100" r="88" fill="none" stroke="currentColor" stroke-width="16" stroke-dasharray="488 66" stroke-linecap="round" transform="rotate(-90 100 100)" filter="url(#lp-line-shadow)" />
                    </svg>
                    <div class="lp-step__content">
                      <span class="lp-step__icon" aria-hidden="true">
                        <svg viewBox="0 0 64 64" fill="currentColor"><path d="M24 6h16v6h6a8 8 0 0 1 8 8v34a8 8 0 0 1-8 8H18a8 8 0 0 1-8-8V20a8 8 0 0 1 8-8h6V6zm2 10v2h12v-2H26zM18 28h28v4H18v-4zm0 10h28v4H18v-4zm0 10h18v4H18v-4z"/></svg>
                      </span>
                      <span class="lp-step__label">Étape 01 — Découverte</span>
                      <p>Comprendre votre activité, objectifs et clients. Cahier des charges clair avant toute ligne de code.</p>
                    </div>
                  </div>
                </article>

                <article class="lp-step lp-step--02" style="--sc:#f5a623">
                  <div class="lp-step__ring">
                    <svg class="lp-step__arc" viewBox="0 0 200 200" aria-hidden="true">
                      <circle cx="100" cy="100" r="88" fill="none" stroke="currentColor" stroke-width="16" stroke-dasharray="488 66" stroke-linecap="round" transform="rotate(-90 100 100)" filter="url(#lp-line-shadow)" />
                    </svg>
                    <div class="lp-step__content">
                      <span class="lp-step__icon" aria-hidden="true">
                        <svg viewBox="0 0 64 64" fill="currentColor"><path d="M32 10C20.9 10 12 18.9 12 30c0 9.4 7.6 17 17 17 1.7 0 3-1.3 3-3 0-.9-.4-1.7-1-2.2 1.2-.6 2.1-1.9 2.1-3.4 0-2.2-1.8-4-4-4H24V10h8z"/><circle cx="22" cy="24" r="3"/><circle cx="32" cy="18" r="3"/><circle cx="42" cy="24" r="3"/><circle cx="38" cy="34" r="3"/></svg>
                      </span>
                      <span class="lp-step__label">Étape 02 — Design</span>
                      <p>Maquettes validées avec vous avant le développement. Zéro surprise en fin de projet.</p>
                    </div>
                  </div>
                </article>

                <article class="lp-step lp-step--03" style="--sc:#689f38">
                  <div class="lp-step__ring">
                    <svg class="lp-step__arc" viewBox="0 0 200 200" aria-hidden="true">
                      <circle cx="100" cy="100" r="88" fill="none" stroke="currentColor" stroke-width="16" stroke-dasharray="488 66" stroke-linecap="round" transform="rotate(-90 100 100)" filter="url(#lp-line-shadow)" />
                    </svg>
                    <div class="lp-step__content">
                      <span class="lp-step__icon" aria-hidden="true">
                        <svg viewBox="0 0 64 64" fill="currentColor"><path d="M18 18 6 32l12 14v-8l-6-6 6-6V18zm28 0v8l6 6-6 6v8l12-14L46 18H46z"/><path d="M28 44h8L36 20h-8v24z" opacity=".9"/></svg>
                      </span>
                      <span class="lp-step__label">Étape 03 — Développement</span>
                      <p>Code propre et maintenable : PHP, APIs, bases de données — architectures claires côté serveur et interface.</p>
                    </div>
                  </div>
                </article>

                <article class="lp-step lp-step--04" style="--sc:#e53935">
                  <div class="lp-step__ring">
                    <svg class="lp-step__arc" viewBox="0 0 200 200" aria-hidden="true">
                      <circle cx="100" cy="100" r="88" fill="none" stroke="currentColor" stroke-width="16" stroke-dasharray="488 66" stroke-linecap="round" transform="rotate(-90 100 100)" filter="url(#lp-line-shadow)" />
                    </svg>
                    <div class="lp-step__content">
                      <span class="lp-step__icon" aria-hidden="true">
                        <svg viewBox="0 0 64 64" fill="currentColor"><path d="M32 6s-10 14-10 28c0 6 2 10 6 13l4 11 4-11c4-3 6-7 6-13 0-14-10-28-10-28zm0 42a5 5 0 1 0 0 10 5 5 0 0 0 0-10z"/><ellipse cx="32" cy="26" rx="6" ry="8" opacity=".35"/></svg>
                      </span>
                      <span class="lp-step__label">Étape 04 — Livraison</span>
                      <p>Tests, mise en ligne, formation, puis support &amp; évolution — notre relation continue après le lancement.</p>
                    </div>
                  </div>
                </article>
              </div>
            </div>
          </div>

          <figure class="lp-infographic__app">
            <img src="images/landing/aria-app-showcase.webp" alt="Application mobile Aria — formation Permis B et modules de conduite développés par Goo-Bridge" width="470" height="1003" loading="lazy" decoding="async" />
          </figure>

        </div>
      </div>
    </section>

    <section class="lp-techstack reveal" id="techstack" aria-label="Technologies et langages utilisés par Goo-Bridge">
      <div class="lp-techstack__rocket" id="techstackRocket" aria-hidden="true">
        <div class="lp-techstack__rocket-fly">
          <div class="lp-techstack__rocket-spin">
            <img class="lp-techstack__rocket-img" src="images/landing/techstack-rocket.webp" alt="" width="912" height="899" loading="lazy" decoding="async" />
            <span class="lp-techstack__rocket-glow"></span>
            <span class="lp-techstack__rocket-flame"></span>
          </div>
        </div>
      </div>
      <div class="lp-techstack__inner">
        <div class="lp-techstack__head">
          <span class="lp-techstack__line" aria-hidden="true"></span>
          <h2 class="lp-techstack__pill">Nos Technologies &amp; Langages</h2>
          <span class="lp-techstack__line" aria-hidden="true"></span>
        </div>

        <div class="lp-techstack__grid">
          <article class="lp-tech-card reveal" data-delay="0">
            <div class="lp-tech-card__icon">
              <img src="images/tech/python.svg" alt="Logo Python" width="64" height="64" loading="lazy" decoding="async" />
            </div>
            <h3>Python</h3>
            <p>Backend, automatisation, traitement de données et intégrations IA pour vos applications métier.</p>
          </article>

          <article class="lp-tech-card reveal" data-delay="60">
            <div class="lp-tech-card__icon">
              <img src="images/tech/javascript.svg" alt="Logo JavaScript" width="64" height="64" loading="lazy" decoding="async" />
            </div>
            <h3>JavaScript</h3>
            <p>React, Vue.js et Node.js pour des interfaces réactives, des SPA modernes et des APIs temps réel.</p>
          </article>

          <article class="lp-tech-card reveal" data-delay="120">
            <div class="lp-tech-card__icon">
              <img src="images/tech/php.svg" alt="Logo PHP" width="64" height="64" loading="lazy" decoding="async" />
            </div>
            <h3>PHP</h3>
            <p>Sites web dynamiques, e-commerce et intégrations sur mesure avec un écosystème mature et fiable.</p>
          </article>

          <article class="lp-tech-card reveal" data-delay="180">
            <div class="lp-tech-card__icon lp-tech-card__icon--duo">
              <img src="images/tech/mysql.svg" alt="Logo MySQL" width="64" height="64" loading="lazy" decoding="async" />
              <img src="images/tech/postgresql.svg" alt="Logo PostgreSQL" width="64" height="64" loading="lazy" decoding="async" />
            </div>
            <h3>SQL</h3>
            <p>MySQL et PostgreSQL pour des données structurées, des requêtes optimisées et une fiabilité en production.</p>
          </article>

          <article class="lp-tech-card reveal" data-delay="240">
            <div class="lp-tech-card__icon lp-tech-card__icon--duo">
              <img src="images/tech/mongodb.svg" alt="Logo MongoDB" width="64" height="64" loading="lazy" decoding="async" />
              <img src="images/tech/redis.svg" alt="Logo Redis" width="64" height="64" loading="lazy" decoding="async" />
            </div>
            <h3>NoSQL</h3>
            <p>MongoDB et Redis pour la flexibilité des schémas, le cache haute performance et les gros volumes.</p>
          </article>
        </div>
      </div>
    </section>

    <section id="realisations" class="section-realisations lp-realisations">
      <div class="section-header reveal">
        <span class="section-tag">Nos Réalisations</span>
        <h2>Des projets livrés, <span class="gradient-text">des clients satisfaits</span></h2>
        <p>Sites vitrines, apps mobiles et e-commerce en production — conçus, développés et hébergés par Goo-Bridge, du digital à l'informel.</p>
      </div>

      <div class="lp-realisations__layout reveal" data-delay="120">
        <div class="lp-realisations__stack-wrap">
          <?php if ($portfolioItems === []): ?>
            <p class="lp-section-sub" style="text-align:center;margin:2rem auto">Projets bientôt disponibles.</p>
          <?php else: ?>
            <?php
              $first = $portfolioItems[0];
              $firstTags = array_values(array_filter(array_map('trim', explode(',', (string) ($first['tags'] ?? '')))));
              $firstUrl = (string) ($first['website_url'] ?? '#');
              $firstDomain = (string) (($first['domain_label'] ?? '') !== '' ? $first['domain_label'] : portfolio_domain_from_url($firstUrl));
              $slotMap = ['center', 'right-1', 'right-2', 'hidden', 'left-2', 'left-1', 'hidden'];
            ?>
            <div class="lp-phone-carousel" id="phoneStack" aria-label="Projets réalisés">
              <?php foreach ($portfolioItems as $i => $pItem): ?>
                <?php
                  $pTitle = (string) ($pItem['title'] ?? '');
                  $pTags = (string) ($pItem['tags'] ?? '');
                  $pDesc = (string) ($pItem['description'] ?? '');
                  $pUrl = (string) ($pItem['website_url'] ?? '');
                  $pDomain = (string) (($pItem['domain_label'] ?? '') !== '' ? $pItem['domain_label'] : portfolio_domain_from_url($pUrl));
                  $pImg = portfolio_image_public_url($pItem['image_path'] ?? null) ?? '';
                  $pW = max(120, (int) ($pItem['image_width'] ?? 472));
                  $pH = max(200, (int) ($pItem['image_height'] ?? 1024));
                  $slot = $slotMap[$i] ?? 'hidden';
                ?>
                <article class="lp-phone-carousel__item<?= $i === 0 ? ' is-active' : '' ?>" data-index="<?= (int) $i ?>" data-slot="<?= htmlspecialchars($slot, ENT_QUOTES, 'UTF-8') ?>"
                  data-title="<?= htmlspecialchars($pTitle, ENT_QUOTES, 'UTF-8') ?>"
                  data-tags="<?= htmlspecialchars($pTags, ENT_QUOTES, 'UTF-8') ?>"
                  data-desc="<?= htmlspecialchars($pDesc, ENT_QUOTES, 'UTF-8') ?>"
                  data-url="<?= htmlspecialchars($pUrl !== '' ? $pUrl : '#', ENT_QUOTES, 'UTF-8') ?>"
                  data-domain="<?= htmlspecialchars($pDomain !== '' ? $pDomain : 'Voir le projet', ENT_QUOTES, 'UTF-8') ?>">
                  <?php if ($pImg !== ''): ?>
                    <img class="lp-phone-carousel__mockup" src="<?= htmlspecialchars($pImg, ENT_QUOTES, 'UTF-8') ?>"
                      alt="<?= htmlspecialchars($pTitle . ' — aperçu', ENT_QUOTES, 'UTF-8') ?>"
                      width="<?= $pW ?>" height="<?= $pH ?>"
                      loading="<?= $i === 0 ? 'eager' : 'lazy' ?>" decoding="async" />
                  <?php endif; ?>
                </article>
              <?php endforeach; ?>
            </div>

            <div class="lp-phone-carousel__panel">
              <div class="lp-phone-carousel__chips" id="stackTags">
                <?php foreach ($firstTags as $tag): ?>
                  <span class="lp-phone-carousel__chip"><?= htmlspecialchars($tag, ENT_QUOTES, 'UTF-8') ?></span>
                <?php endforeach; ?>
              </div>
              <h3 class="lp-phone-carousel__title" id="stackTitle"><?= htmlspecialchars((string) $first['title'], ENT_QUOTES, 'UTF-8') ?></h3>
              <p class="lp-phone-carousel__desc" id="stackDesc"><?= htmlspecialchars((string) $first['description'], ENT_QUOTES, 'UTF-8') ?></p>
              <a href="<?= htmlspecialchars($firstUrl !== '' ? $firstUrl : '#', ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="lp-phone-carousel__cta" id="stackLink">↗ <?= htmlspecialchars($firstDomain !== '' ? $firstDomain : 'Voir le projet', ENT_QUOTES, 'UTF-8') ?></a>
              <div class="lp-phone-carousel__dots" id="stackDots" role="tablist" aria-label="Choisir un projet">
                <?php foreach ($portfolioItems as $i => $pItem): ?>
                  <button type="button" class="<?= $i === 0 ? 'is-active' : '' ?>" data-index="<?= (int) $i ?>"
                    aria-label="<?= htmlspecialchars((string) $pItem['title'], ENT_QUOTES, 'UTF-8') ?>"
                    aria-selected="<?= $i === 0 ? 'true' : 'false' ?>"></button>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <!-- ===== POURQUOI GOO-BRIDGE ===== -->
    <section id="pourquoi" class="lp-why reveal" aria-label="Pourquoi Goo-Bridge">
      <div class="lp-why__bg" aria-hidden="true">
        <img src="images/landing/why-robot-ai.webp" alt="" width="1920" height="1280" loading="lazy" decoding="async" />
      </div>
      <div class="lp-why__inner">
        <div class="lp-why__head">
          <span class="lp-why__line lp-why__line--left" aria-hidden="true"></span>
          <h2>Pourquoi Goo-Bridge</h2>
          <span class="lp-why__line lp-why__line--right" aria-hidden="true"></span>
        </div>
        <p class="lp-why__sub">Un partenaire qui comprend vos enjeux — du digital au terrain.</p>

        <div class="lp-why__grid">
          <article class="lp-why-card">
            <span class="lp-why-card__icon" aria-hidden="true">
              <svg viewBox="0 0 64 64" width="56" height="56" fill="none">
                <circle cx="32" cy="32" r="30" fill="#eff6ff"/>
                <circle cx="32" cy="32" r="22" stroke="#2563eb" stroke-width="2" opacity="0.35"/>
                <path d="M32 16v16l10 6" stroke="#1e3a8a" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="32" cy="32" r="3" fill="#f97316"/>
                <path d="M32 10v3M32 51v3M10 32h3M51 32h3" stroke="#93c5fd" stroke-width="2" stroke-linecap="round"/>
              </svg>
            </span>
            <h3>Expertise reconnue</h3>
            <p>5 ans d'expérience web &amp; mobile, projets livrés en production.</p>
            <span class="lp-why-card__bar" aria-hidden="true"></span>
          </article>
          <article class="lp-why-card">
            <span class="lp-why-card__icon" aria-hidden="true">
              <svg viewBox="0 0 64 64" width="56" height="56" fill="none">
                <circle cx="32" cy="32" r="30" fill="#eff6ff"/>
                <path d="M20 38.5c0-7.5 5.4-13.5 12-13.5s12 6 12 13.5" stroke="#2563eb" stroke-width="2.5" stroke-linecap="round"/>
                <path d="M24 40h16l-1.6 7.2a2 2 0 0 1-2 1.6H27.6a2 2 0 0 1-2-1.6L24 40z" fill="#1e3a8a"/>
                <circle cx="26.5" cy="29" r="2.4" fill="#1e3a8a"/>
                <circle cx="37.5" cy="29" r="2.4" fill="#1e3a8a"/>
                <path d="M42 16l2.2 4.8L49 23l-4.8 2.2L42 30l-2.2-4.8L35 23l4.8-2.2L42 16z" fill="#f97316"/>
              </svg>
            </span>
            <h3>Réponse rapide</h3>
            <p>Échange sous 24 à 72&nbsp;h — suivi clair à chaque étape.</p>
            <span class="lp-why-card__bar" aria-hidden="true"></span>
          </article>
          <article class="lp-why-card">
            <span class="lp-why-card__icon" aria-hidden="true">
              <svg viewBox="0 0 64 64" width="56" height="56" fill="none">
                <circle cx="32" cy="32" r="30" fill="#eff6ff"/>
                <path d="M16 44V22" stroke="#93c5fd" stroke-width="2" stroke-linecap="round"/>
                <path d="M16 44h32" stroke="#93c5fd" stroke-width="2" stroke-linecap="round"/>
                <rect x="20" y="34" width="7" height="10" rx="1.5" fill="#60a5fa"/>
                <rect x="30" y="27" width="7" height="17" rx="1.5" fill="#2563eb"/>
                <rect x="40" y="20" width="7" height="24" rx="1.5" fill="#1e3a8a"/>
                <path d="M21 19l22 8" stroke="#f97316" stroke-width="2.25" stroke-linecap="round"/>
                <path d="M40.5 23.5l5-.8-.8 5" stroke="#f97316" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </span>
            <h3>Résultats concrets</h3>
            <p>Sites, apps et outils qui convertissent et font grandir votre activité.</p>
            <span class="lp-why-card__bar" aria-hidden="true"></span>
          </article>
          <article class="lp-why-card lp-why-card--accent">
            <span class="lp-why-card__icon" aria-hidden="true">
              <svg viewBox="0 0 64 64" width="56" height="56" fill="none">
                <circle cx="32" cy="32" r="30" fill="#fff7ed"/>
                <rect x="15" y="24" width="34" height="22" rx="4" fill="#fdba74" opacity="0.45"/>
                <rect x="17" y="22" width="30" height="20" rx="3.5" fill="#fff" stroke="#f97316" stroke-width="2"/>
                <path d="M17 29h30" stroke="#fed7aa" stroke-width="2"/>
                <circle cx="32" cy="34" r="5.5" fill="#f97316"/>
                <path d="M32 31.2v5.6M29.6 34h4.8" stroke="#fff" stroke-width="1.75" stroke-linecap="round"/>
                <rect x="21" y="33" width="4" height="3" rx="1" fill="#fb923c"/>
                <rect x="39" y="33" width="4" height="3" rx="1" fill="#fb923c"/>
              </svg>
            </span>
            <h3>Budget maîtrisé</h3>
            <p>Devis clair, pas de frais cachés — le juste investissement.</p>
            <span class="lp-why-card__bar" aria-hidden="true"></span>
          </article>
          <article class="lp-why-card">
            <span class="lp-why-card__icon" aria-hidden="true">
              <svg viewBox="0 0 64 64" width="56" height="56" fill="none">
                <circle cx="32" cy="32" r="30" fill="#eff6ff"/>
                <rect x="18" y="17" width="28" height="30" rx="4" fill="#fff" stroke="#2563eb" stroke-width="2"/>
                <path d="M18 24h28" stroke="#2563eb" stroke-width="2"/>
                <circle cx="25" cy="20.5" r="1.4" fill="#93c5fd"/>
                <circle cx="32" cy="20.5" r="1.4" fill="#93c5fd"/>
                <circle cx="39" cy="20.5" r="1.4" fill="#93c5fd"/>
                <rect x="23" y="29" width="6" height="6" rx="1.2" fill="#bfdbfe"/>
                <rect x="32" y="29" width="6" height="6" rx="1.2" fill="#bfdbfe"/>
                <rect x="41" y="29" width="5" height="6" rx="1.2" fill="#bfdbfe"/>
                <path d="M24 41.5l4 4 9-10" stroke="#1e3a8a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </span>
            <h3>Délais respectés</h3>
            <p>Planning dès le départ — livraison en 2 à 8 semaines.</p>
            <span class="lp-why-card__bar" aria-hidden="true"></span>
          </article>
          <article class="lp-why-card">
            <span class="lp-why-card__icon" aria-hidden="true">
              <svg viewBox="0 0 64 64" width="56" height="56" fill="none">
                <circle cx="32" cy="32" r="30" fill="#eff6ff"/>
                <path d="M32 14c-7.2 0-13 5.6-13 12.5 0 9.2 11.2 21.2 12.4 22.4a1 1 0 0 0 1.3 0C33.8 47.7 45 35.7 45 26.5 45 19.6 39.2 14 32 14z" fill="#fff" stroke="#2563eb" stroke-width="2"/>
                <circle cx="32" cy="26.5" r="5" fill="#1e3a8a"/>
                <path d="M18 48.5c2.8-3.2 7.4-5 14-5s11.2 1.8 14 5" stroke="#93c5fd" stroke-width="2.25" stroke-linecap="round"/>
                <circle cx="17" cy="44" r="3.2" fill="#60a5fa"/>
                <circle cx="47" cy="44" r="3.2" fill="#60a5fa"/>
              </svg>
            </span>
            <h3>Adapté au terrain</h3>
            <p>Solutions pour commerçants, artisans et PME — simples et utiles.</p>
            <span class="lp-why-card__bar" aria-hidden="true"></span>
          </article>
        </div>
      </div>
    </section>

    <!-- ===== INFRASTRUCTURE & RÉSEAUX ===== -->
    <section id="infrastructure" class="lp-infra reveal" aria-label="Infrastructure réseaux et systèmes">
      <div class="lp-infra__inner">
        <div class="lp-infra__head">
          <h2>Nos services infrastructure</h2>
          <p class="lp-infra__sub">Câblage, Wi‑Fi, vidéosurveillance, Starlink, baies et administration systèmes — du terrain à la baie.</p>
        </div>

        <div class="lp-infra__grid lp-infra__grid--services">
          <article class="lp-infra-svc">
            <div class="lp-infra-svc__art">
              <img src="images/landing/infra-network.webp" alt="Infrastructure réseaux" width="1536" height="1024" loading="lazy" decoding="async" />
            </div>
            <div class="lp-infra-svc__body">
              <h3>Infrastructure réseaux</h3>
              <p>Câblage RJ45, Wi‑Fi, switchs, routeurs &amp; ponts radio</p>
            </div>
          </article>

          <article class="lp-infra-svc">
            <div class="lp-infra-svc__art">
              <img src="images/landing/infra-cctv.webp" alt="Vidéosurveillance" width="1536" height="1024" loading="lazy" decoding="async" />
            </div>
            <div class="lp-infra-svc__body">
              <h3>Vidéosurveillance</h3>
              <p>Caméras IP, HD, PTZ, NVR/DVR &amp; accès mobile</p>
            </div>
          </article>

          <article class="lp-infra-svc">
            <div class="lp-infra-svc__art">
              <img src="images/landing/infra-starlink.webp" alt="Internet et Starlink" width="1536" height="1024" loading="lazy" decoding="async" />
            </div>
            <div class="lp-infra-svc__body">
              <h3>Internet &amp; Starlink</h3>
              <p>Installation Starlink + extension réseau</p>
            </div>
          </article>

          <article class="lp-infra-svc">
            <div class="lp-infra-svc__art">
              <img src="images/landing/infra-racks.webp" alt="Baies et datacenter" width="1536" height="1024" loading="lazy" decoding="async" />
            </div>
            <div class="lp-infra-svc__body">
              <h3>Baies &amp; datacenter</h3>
              <p>Montage, câblage, onduleurs &amp; maintenance</p>
            </div>
          </article>
        </div>

        <div class="lp-infra__block">
          <h3 class="lp-infra__block-title">Administration &amp; expertise</h3>
          <div class="lp-infra__grid lp-infra__grid--why">
            <article class="lp-infra-why">
              <span class="lp-infra-why__logo lp-infra-why__logo--cisco" aria-hidden="true">
                <img src="images/infra/cisco.svg" alt="" width="80" height="42" loading="lazy" decoding="async" />
              </span>
              <h4>Réseau Cisco</h4>
              <p>Switchs, VLANs, routage &amp; sécurité</p>
            </article>
            <article class="lp-infra-why">
              <span class="lp-infra-why__logo lp-infra-why__logo--photo lp-infra-why__logo--fill" aria-hidden="true">
                <img src="images/infra/linux.webp" alt="" width="72" height="72" loading="lazy" decoding="async" />
              </span>
              <h4>Linux</h4>
              <p>Serveurs, services &amp; durcissement</p>
            </article>
            <article class="lp-infra-why">
              <span class="lp-infra-why__logo" aria-hidden="true">
                <img src="images/infra/windows.svg" alt="" width="40" height="40" loading="lazy" decoding="async" />
              </span>
              <h4>Windows</h4>
              <p>Active Directory, postes &amp; serveurs</p>
            </article>
            <article class="lp-infra-why">
              <span class="lp-infra-why__logo lp-infra-why__logo--photo" aria-hidden="true">
                <img src="images/infra/support.webp" alt="" width="72" height="72" loading="lazy" decoding="async" />
              </span>
              <h4>Maintenance</h4>
              <p>Audit, contrats &amp; assistance technique</p>
            </article>
          </div>
        </div>

        <div class="lp-infra__strip" aria-label="Notre parcours intervention">
          <div class="lp-infra__strip-line" aria-hidden="true"></div>
          <div class="lp-infra__strip-item">
            <span class="lp-infra-why__logo lp-infra-why__logo--round" aria-hidden="true">
              <img src="images/infra/audit.svg" alt="" width="48" height="48" loading="lazy" decoding="async" />
            </span>
            <h4>Audit</h4>
            <p>Étude du site &amp; besoins</p>
          </div>
          <div class="lp-infra__strip-item">
            <span class="lp-infra-why__logo lp-infra-why__logo--round" aria-hidden="true">
              <img src="images/infra/install.svg" alt="" width="48" height="48" loading="lazy" decoding="async" />
            </span>
            <h4>Installation</h4>
            <p>Câblage &amp; équipements</p>
          </div>
          <div class="lp-infra__strip-item">
            <span class="lp-infra-why__logo lp-infra-why__logo--round" aria-hidden="true">
              <img src="images/infra/config.svg" alt="" width="48" height="48" loading="lazy" decoding="async" />
            </span>
            <h4>Configuration</h4>
            <p>Cisco, Linux, Windows</p>
          </div>
          <div class="lp-infra__strip-item">
            <span class="lp-infra-why__logo lp-infra-why__logo--round" aria-hidden="true">
              <img src="images/infra/suivi.svg" alt="" width="48" height="48" loading="lazy" decoding="async" />
            </span>
            <h4>Suivi</h4>
            <p>Maintenance &amp; formation</p>
          </div>
        </div>

        <div class="lp-infra__cta">
          <a href="#contact" class="lp-btn lp-btn--navy">Demander un devis infrastructure</a>
        </div>
      </div>
    </section>

    <!-- ===== NOS APPLICATIONS ===== -->
    <section id="nos-apps" class="lp-apps reveal" aria-label="Applications Goo-Bridge">
      <div class="lp-apps__inner">
        <div class="lp-apps__head">
          <h2>De l'idée aux stores</h2>
          <p class="lp-apps__sub">Nos applications iOS &amp; Android — conçues, développées et maintenues par Goo-Bridge</p>
        </div>

        <div class="lp-apps__stage">
          <div class="lp-apps__phones">
            <?php if ($showcaseApps === []): ?>
              <p class="lp-apps__sub" style="grid-column:1/-1;text-align:center">Applications bientôt disponibles.</p>
            <?php endif; ?>
            <?php foreach ($showcaseApps as $app): ?>
              <?php
                $aTitle = (string) ($app['title'] ?? '');
                $aDesc = (string) ($app['description'] ?? '');
                $aWeb = (string) ($app['website_url'] ?? '');
                $aDomain = (string) (($app['domain_label'] ?? '') !== '' ? $app['domain_label'] : showcase_domain_from_url($aWeb));
                $aIos = (string) ($app['app_store_url'] ?? '');
                $aPlay = (string) ($app['play_store_url'] ?? '');
                $aImg = showcase_apps_image_public_url($app['image_path'] ?? null) ?? '';
                $aW = max(200, (int) ($app['image_width'] ?? 743));
                $aH = max(400, (int) ($app['image_height'] ?? 1533));
                $aTheme = (string) ($app['theme'] ?? 'navy');
                $themeClass = $aTheme === 'gradient' ? 'lp-apps__phone--gradient' : 'lp-apps__phone--navy';
              ?>
              <article class="lp-apps__phone <?= htmlspecialchars($themeClass, ENT_QUOTES, 'UTF-8') ?>">
                <?php if ($aImg !== ''): ?>
                  <div class="lp-apps__phone-frame">
                    <img src="<?= htmlspecialchars($aImg, ENT_QUOTES, 'UTF-8') ?>"
                      alt="<?= htmlspecialchars($aTitle, ENT_QUOTES, 'UTF-8') ?>"
                      width="<?= $aW ?>" height="<?= $aH ?>" loading="lazy" decoding="async" />
                  </div>
                <?php endif; ?>
                <h3 class="lp-apps__phone-name"><?= htmlspecialchars($aTitle, ENT_QUOTES, 'UTF-8') ?></h3>
                <p class="lp-apps__phone-desc"><?= htmlspecialchars($aDesc, ENT_QUOTES, 'UTF-8') ?></p>
                <?php if ($aIos !== '' || $aPlay !== ''): ?>
                  <div class="lp-apps__stores">
                    <?php if ($aIos !== ''): ?>
                      <a class="lp-store-badge" href="<?= htmlspecialchars($aIos, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener"
                        aria-label="<?= htmlspecialchars('Télécharger ' . $aTitle . ' sur l’App Store', ENT_QUOTES, 'UTF-8') ?>">
                        <img src="images/badges/app-store.svg" alt="Download on the App Store" width="148" height="44" loading="lazy" decoding="async" />
                      </a>
                    <?php endif; ?>
                    <?php if ($aPlay !== ''): ?>
                      <a class="lp-store-badge" href="<?= htmlspecialchars($aPlay, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener"
                        aria-label="<?= htmlspecialchars('Télécharger ' . $aTitle . ' sur Google Play', ENT_QUOTES, 'UTF-8') ?>">
                        <img src="images/badges/google-play.svg" alt="Get it on Google Play" width="148" height="44" loading="lazy" decoding="async" />
                      </a>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>
                <?php if ($aWeb !== ''): ?>
                  <a class="lp-apps__web" href="<?= htmlspecialchars($aWeb, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener"><?= htmlspecialchars($aDomain !== '' ? $aDomain : 'Voir le site', ENT_QUOTES, 'UTF-8') ?> ↗</a>
                <?php endif; ?>
              </article>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== AVANT / APRÈS + AVANTAGES ===== -->
    <section id="digitalisation" class="lp-migrate reveal" aria-label="Avant et après Goo-Bridge">
      <div class="lp-migrate__inner">
        <div class="lp-migrate__head">
          <h2>Avant &amp; après <span>Goo-Bridge</span></h2>
          <p>Passez du papier et du chaos à un outil digital clair — et mesurez les gains dès le premier mois.</p>
        </div>

        <div class="lp-migrate__grid">
          <div class="lp-migrate__compare" aria-label="Comparaison avant et après">
            <figure class="lp-migrate__panel">
              <img src="images/landing/avant-apres-goobridge.webp" alt="Avant : papiers, carnets et chaos informel. Après Goo-Bridge : tableau de bord web et app mobile centralisés." width="1536" height="1024" loading="lazy" decoding="async" />
            </figure>
          </div>

          <aside class="lp-migrate__arcs" aria-label="Avantages de migrer vers le digital">
            <svg class="lp-migrate__infographic" viewBox="0 0 640 520" role="img" aria-labelledby="mig-infographic-title">
              <title id="mig-infographic-title">Avantages de migrer vers le digital avec Goo-Bridge</title>
              <defs>
                <filter id="migArcShadow" x="-35%" y="-35%" width="170%" height="170%">
                  <feDropShadow dx="3" dy="7" stdDeviation="5" flood-color="rgba(15,29,61,0.16)"/>
                </filter>
                <filter id="migHubShadow" x="-50%" y="-50%" width="200%" height="200%">
                  <feDropShadow dx="1" dy="5" stdDeviation="7" flood-color="rgba(15,29,61,0.14)"/>
                </filter>
                <linearGradient id="migHubGrad" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="0%" stop-color="#ffffff"/>
                  <stop offset="100%" stop-color="#eef3f9"/>
                </linearGradient>
                <!-- Chemins texte (arcs ouverts à gauche, centre 390,230) -->
                <path id="arcPath1" d="M390 318 A88 88 0 0 1 390 142"/>
                <path id="arcPath2" d="M390 348 A118 118 0 0 1 390 112"/>
                <path id="arcPath3" d="M390 378 A148 148 0 0 1 390 82"/>
                <path id="arcPath4" d="M390 408 A178 178 0 0 1 390 52"/>
              </defs>

              <!-- Fond cercles fantômes -->
              <g fill="none" stroke="#c5d0dc" stroke-width="1" opacity="0.45">
                <circle cx="390" cy="230" r="64"/>
                <circle cx="390" cy="230" r="94"/>
                <circle cx="390" cy="230" r="124"/>
                <circle cx="390" cy="230" r="154"/>
                <circle cx="390" cy="230" r="184"/>
                <circle cx="390" cy="230" r="214"/>
              </g>

              <!-- Arcs blancs (du plus grand au plus petit) — ouverture à gauche -->
              <path d="M390 408 A178 178 0 0 1 390 52" fill="none" stroke="#fff" stroke-width="28" stroke-linecap="butt" filter="url(#migArcShadow)"/>
              <path d="M390 378 A148 148 0 0 1 390 82" fill="none" stroke="#fff" stroke-width="28" stroke-linecap="butt" filter="url(#migArcShadow)"/>
              <path d="M390 348 A118 118 0 0 1 390 112" fill="none" stroke="#fff" stroke-width="28" stroke-linecap="butt" filter="url(#migArcShadow)"/>
              <path d="M390 318 A88 88 0 0 1 390 142" fill="none" stroke="#fff" stroke-width="28" stroke-linecap="butt" filter="url(#migArcShadow)"/>

              <!-- Textes courbes sur les arcs -->
              <g font-family="Poppins, system-ui, sans-serif" font-size="10" font-weight="700" fill="#a8b4c2" letter-spacing="1.8">
                <text><textPath href="#arcPath1" startOffset="22%">PRODUCTIVITÉ</textPath></text>
                <text><textPath href="#arcPath2" startOffset="20%">GAINS DE TEMPS</textPath></text>
                <text><textPath href="#arcPath3" startOffset="18%">ACCÈS PARTOUT</textPath></text>
                <text><textPath href="#arcPath4" startOffset="16%">CLARTÉ &amp; SUIVI</textPath></text>
              </g>

              <!-- Numéros à droite des arcs (près du hub) -->
              <g font-family="Poppins, system-ui, sans-serif" font-size="26" font-weight="800">
                <text x="455" y="198" fill="#e67e22">01</text>
                <text x="478" y="224" fill="#1abc9c">02</text>
                <text x="492" y="252" fill="#34495e">03</text>
                <text x="478" y="280" fill="#5dade2">04</text>
              </g>

              <!-- Traits de liaison + callouts gauche -->
              <g stroke="#9aa8b8" stroke-width="1.35" fill="none">
                <path d="M148 70 H205 L390 52"/>
                <path d="M148 150 H218 L390 112"/>
                <path d="M148 310 H218 L390 348"/>
                <path d="M148 400 H205 L390 408"/>
              </g>
              <g fill="#9aa8b8">
                <circle cx="390" cy="52" r="3.2"/>
                <circle cx="390" cy="112" r="3.2"/>
                <circle cx="390" cy="348" r="3.2"/>
                <circle cx="390" cy="408" r="3.2"/>
              </g>

              <!-- Callout 04 — +50% -->
              <g>
                <circle cx="56" cy="70" r="22" fill="#eaf6fc"/>
                <g fill="none" stroke="#5dade2" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" transform="translate(44 58)">
                  <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                </g>
                <text x="88" y="66" fill="#5dade2" font-size="22" font-weight="800" font-family="Poppins, system-ui, sans-serif">+50%</text>
                <text x="88" y="84" fill="#5dade2" font-size="11" font-weight="600" font-family="Poppins, system-ui, sans-serif">Clarté &amp; suivi</text>
              </g>

              <!-- Callout 03 — +55% -->
              <g>
                <circle cx="56" cy="150" r="22" fill="#eef1f4"/>
                <g fill="none" stroke="#34495e" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" transform="translate(44 138)">
                  <circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                </g>
                <text x="88" y="146" fill="#34495e" font-size="22" font-weight="800" font-family="Poppins, system-ui, sans-serif">+55%</text>
                <text x="88" y="164" fill="#34495e" font-size="11" font-weight="600" font-family="Poppins, system-ui, sans-serif">Accès partout</text>
              </g>

              <!-- Callout 02 — +65% -->
              <g>
                <circle cx="56" cy="310" r="22" fill="#e8faf6"/>
                <g fill="none" stroke="#1abc9c" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" transform="translate(44 298)">
                  <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>
                </g>
                <text x="88" y="306" fill="#1abc9c" font-size="22" font-weight="800" font-family="Poppins, system-ui, sans-serif">+65%</text>
                <text x="88" y="324" fill="#1abc9c" font-size="11" font-weight="600" font-family="Poppins, system-ui, sans-serif">Gains de temps</text>
              </g>

              <!-- Callout 01 — +75% -->
              <g>
                <circle cx="56" cy="400" r="22" fill="#fdf3e8"/>
                <g fill="none" stroke="#e67e22" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" transform="translate(44 388)">
                  <path d="M18 20V10M12 20V4M6 20v-6"/>
                </g>
                <text x="88" y="396" fill="#e67e22" font-size="22" font-weight="800" font-family="Poppins, system-ui, sans-serif">+75%</text>
                <text x="88" y="414" fill="#e67e22" font-size="11" font-weight="600" font-family="Poppins, system-ui, sans-serif">Productivité</text>
              </g>

              <!-- Hub central — logo -->
              <circle cx="390" cy="230" r="52" fill="#fff" filter="url(#migHubShadow)"/>
              <circle cx="390" cy="230" r="50" fill="#fff" stroke="#e4ebf3" stroke-width="1.2"/>
              <image
                href="images/logo-goo-bridge.webp"
                x="348"
                y="212"
                width="84"
                height="36"
                preserveAspectRatio="xMidYMid meet"
              />

              <!-- Légende bas -->
              <g font-family="Poppins, system-ui, sans-serif">
                <rect x="250" y="430" width="4" height="16" rx="1" fill="#e67e22"/>
                <text x="260" y="436" fill="#2c3e50" font-size="10" font-weight="800">01 · Organisation</text>
                <text x="260" y="448" fill="#7a8794" font-size="8.5" font-weight="500">Tout centralisé au même endroit</text>

                <rect x="250" y="456" width="4" height="16" rx="1" fill="#1abc9c"/>
                <text x="260" y="462" fill="#2c3e50" font-size="10" font-weight="800">02 · Temps gagné</text>
                <text x="260" y="474" fill="#7a8794" font-size="8.5" font-weight="500">Moins de saisie, plus d’actions</text>

                <rect x="430" y="430" width="4" height="16" rx="1" fill="#34495e"/>
                <text x="440" y="436" fill="#2c3e50" font-size="10" font-weight="800">03 · Visibilité</text>
                <text x="440" y="448" fill="#7a8794" font-size="8.5" font-weight="500">Indicateurs en temps réel</text>

                <rect x="430" y="456" width="4" height="16" rx="1" fill="#5dade2"/>
                <text x="440" y="462" fill="#2c3e50" font-size="10" font-weight="800">04 · Croissance</text>
                <text x="440" y="474" fill="#7a8794" font-size="8.5" font-weight="500">Un outil prêt à évoluer</text>
              </g>

              <!-- Accent décoratif bas-droit -->
              <path d="M560 390 A170 170 0 0 1 530 450" fill="none" stroke="#e67e22" stroke-width="3" stroke-linecap="round" opacity="0.7"/>
              <path d="M568 382 A182 182 0 0 1 536 448" fill="none" stroke="#1abc9c" stroke-width="3" stroke-linecap="round" opacity="0.55"/>
              <path d="M576 374 A194 194 0 0 1 542 446" fill="none" stroke="#5dade2" stroke-width="3" stroke-linecap="round" opacity="0.45"/>
            </svg>
          </aside>
        </div>
      </div>
    </section>

    <!-- ===== CONTACT ===== -->
    <section id="contact" class="section-contact">
      <div class="contact-layout reveal">
        <aside class="contact-aside">
          <div class="contact-info-card">
            <h2 class="contact-info-card__title">Restons en contact</h2>
            <p class="contact-info-card__text">Une question, un projet ? Contactez notre équipe — on planifie un échange sous 24 à 72&nbsp;h.</p>
            <ul class="contact-info-list">
              <li class="contact-info-item">
                <span class="contact-info-item__icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.81.36 1.6.7 2.34a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.74.34 1.53.57 2.34.7A2 2 0 0 1 22 16.92z"/></svg>
                </span>
                <span>
                  <span class="contact-info-item__label">Appelez-nous</span>
                  <a class="contact-info-item__value" href="tel:+221785303879">+221 78 530 38 79</a>
                </span>
              </li>
              <li class="contact-info-item">
                <span class="contact-info-item__icon contact-info-item__icon--whatsapp" aria-hidden="true">
                  <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/>
                  </svg>
                </span>
                <span>
                  <span class="contact-info-item__label">WhatsApp</span>
                  <a class="contact-info-item__value" href="https://wa.me/221711324152" target="_blank" rel="noopener noreferrer">+221 71 132 41 52</a>
                </span>
              </li>
              <li class="contact-info-item">
                <span class="contact-info-item__icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                </span>
                <span>
                  <span class="contact-info-item__label">Écrivez-nous</span>
                  <a class="contact-info-item__value" href="mailto:contact@goo-bridge.com">contact@goo-bridge.com</a>
                </span>
              </li>
              <li class="contact-info-item">
                <span class="contact-info-item__icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                </span>
                <span>
                  <span class="contact-info-item__label">Adresse</span>
                  <span class="contact-info-item__value">Dakar, Sénégal</span>
                </span>
              </li>
            </ul>
          </div>
          <div class="contact-map">
            <iframe
              title="Localisation Goo-Bridge — Dakar, Sénégal"
              src="https://www.google.com/maps?q=Dakar,+Sénégal&amp;hl=fr&amp;z=13&amp;output=embed"
              loading="lazy"
              referrerpolicy="no-referrer-when-downgrade"
              allowfullscreen></iframe>
          </div>
        </aside>

        <div class="contact-form-panel">
          <div class="contact-form-panel__head">
            <span class="contact-form-panel__tag">Contact</span>
            <h3 class="contact-form-panel__title">Envoyez-nous un message</h3>
            <p class="contact-form-panel__lead">Décrivez votre besoin — nous vous répondons sous 24 à 72&nbsp;h (jours ouvrés) avec la suite des étapes.</p>
          </div>
          <form class="contact-form" method="post" action="" accept-charset="UTF-8">
            <?= csrf_field() ?>
            <?php require_once __DIR__ . '/includes/honeypot.php'; echo honeypot_field(); ?>
            <?php if ($form_error !== ''): ?>
            <p class="form-error"><?= htmlspecialchars($form_error, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
            <?php if ($form_success): ?>
            <p class="form-success" id="contact-form-success" role="status">✓ Message bien envoyé. Vérifiez votre boîte mail : nous vous avons envoyé une confirmation (vérifiez aussi les courriers indésirables).</p>
            <?php endif; ?>
            <div class="form-field">
              <label for="name">Votre nom</label>
              <input type="text" id="name" name="name" placeholder="Entrez votre nom complet" autocomplete="name" required maxlength="120" />
            </div>
            <div class="form-field">
              <label for="email">Adresse email</label>
              <input type="email" id="email" name="email" placeholder="Entrez votre email" autocomplete="email" required maxlength="255" />
            </div>
            <div class="form-row">
              <div class="form-field">
                <label for="phone">Téléphone <span class="form-field__optional">(optionnel)</span></label>
                <input type="tel" id="phone" name="phone" placeholder="+221 77 000 00 00" autocomplete="tel" inputmode="tel" maxlength="32" />
              </div>
              <div class="form-field">
                <label for="service">Type de projet</label>
                <select id="service" name="service">
                  <option value="">Sélectionnez une option…</option>
                  <option>Site vitrine</option>
                  <option>E-commerce</option>
                  <option>Application mobile</option>
                  <option>Plateforme web</option>
                  <option>Scraping / collecte de données</option>
                  <option>IA &amp; analyses dynamiques</option>
                  <option>Infrastructure réseaux</option>
                  <option>Vidéosurveillance</option>
                  <option>Starlink / Internet</option>
                  <option>Cisco / Linux / Windows</option>
                  <option>Hébergement</option>
                  <option>Hébergement Android</option>
                  <option>Hébergement Apple</option>
                  <option>ERP (gestionnaire de stock)</option>
                  <option>Autre</option>
                </select>
              </div>
            </div>
            <div class="form-field">
              <label for="message">Votre message</label>
              <textarea id="message" name="message" placeholder="Écrivez votre question ou décrivez votre projet ici…" required maxlength="5000"></textarea>
            </div>
            <button type="submit" class="btn-primary btn-submit contact-submit-btn">
              <span>Envoyer le message</span>
            </button>
          </form>
        </div>
      </div>
    </section>

  </main>

  <footer class="site-footer">
    <div class="footer-inner">
      <div class="footer-brand">
        <a href="#accueil" class="nav-logo" aria-label="GOO-BRIDGE accueil">
          <img src="images/logo-goo-bridge.webp" alt="GOO-BRIDGE" width="150" height="61" loading="lazy" decoding="async" />
        </a>
        <p>Connecter vos idées au monde digital. Sites, apps et hébergement pour faire grandir votre activité.</p>
        <div class="footer-social" aria-label="Réseaux sociaux">
          <a href="mailto:contact@goo-bridge.com" class="footer-social__link" aria-label="Email">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          </a>
          <a href="tel:+221785303879" class="footer-social__link" aria-label="Téléphone">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.81.36 1.6.7 2.34a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.74.34 1.53.57 2.34.7A2 2 0 0 1 22 16.92z"/></svg>
          </a>
          <a href="https://aria-edu.com/" target="_blank" rel="noopener" class="footer-social__link" aria-label="ARIA">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
          </a>
        </div>
      </div>
      <nav class="footer-cols" aria-label="Navigation pied de page">
        <div class="footer-col">
          <h4>Services</h4>
          <a href="#services">Sites web</a>
          <a href="#services">Applications mobiles</a>
          <a href="#services">Scraping &amp; IA</a>
          <a href="#infrastructure">Infrastructure</a>
          <a href="#services">Hébergement</a>
        </div>
        <div class="footer-col">
          <h4>Entreprise</h4>
          <a href="#accueil">Accueil</a>
          <a href="#pourquoi">Pourquoi nous</a>
          <a href="#realisations">Réalisations</a>
          <a href="#nos-apps">Nos apps</a>
        </div>
        <div class="footer-col">
          <h4>Ressources</h4>
          <a href="#contact">Contact</a>
          <a href="<?= htmlspecialchars(url('support.php'), ENT_QUOTES, 'UTF-8') ?>">Support</a>
          <a href="https://aria-edu.com/" target="_blank" rel="noopener">ARIA</a>
          <a href="https://colobanes.com/" target="_blank" rel="noopener">Colobanes</a>
        </div>
      </nav>
    </div>
    <div class="footer-bottom">
      <p>&copy; 2026 Goo-Bridge. Tous droits réservés. · <a href="mailto:contact@goo-bridge.com">contact@goo-bridge.com</a></p>
    </div>
  </footer>

  <script>
  (function () {
    var rocketRoot = document.getElementById('techstackRocket');
    var techSection = document.getElementById('techstack');
    if (rocketRoot && techSection) {
      var rocketDone = false;
      var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

      function launchRocket() {
        if (rocketDone) return;
        rocketDone = true;
        if (reduceMotion) {
          rocketRoot.classList.add('is-ignite', 'is-fly');
          return;
        }
        rocketRoot.classList.add('is-ignite');
        window.setTimeout(function () {
          rocketRoot.classList.add('is-spin');
        }, 700);
        window.setTimeout(function () {
          rocketRoot.classList.add('is-fly');
        }, 3400);
      }

      if ('IntersectionObserver' in window) {
        var rocketObs = new IntersectionObserver(function (entries) {
          entries.forEach(function (entry) {
            if (entry.isIntersecting) {
              launchRocket();
              rocketObs.disconnect();
            }
          });
        }, { threshold: 0.35, rootMargin: '0px 0px -8% 0px' });
        rocketObs.observe(techSection);
      } else {
        launchRocket();
      }
    }

    var stack = document.getElementById('phoneStack');
    if (stack) {
      var items = stack.querySelectorAll('.lp-phone-carousel__item');
      var dots = document.querySelectorAll('#stackDots button');
      var titleEl = document.getElementById('stackTitle');
      var tagsEl = document.getElementById('stackTags');
      var descEl = document.getElementById('stackDesc');
      var linkEl = document.getElementById('stackLink');
      var active = 0;
      var timer;
      var len = items.length;

      function wrapOffset(i, center) {
        var off = i - center;
        var half = Math.floor(len / 2);
        if (off > half) off -= len;
        if (off < -half) off += len;
        return off;
      }

      function slotFromOffset(off) {
        if (off === 0) return 'center';
        if (off === -1) return 'left-1';
        if (off === 1) return 'right-1';
        if (off === -2) return 'left-2';
        if (off === 2) return 'right-2';
        return 'hidden';
      }

      function renderTags(tagStr) {
        if (!tagsEl) return;
        tagsEl.innerHTML = '';
        (tagStr || '').split(',').forEach(function (tag) {
          tag = tag.trim();
          if (!tag) return;
          var chip = document.createElement('span');
          chip.className = 'lp-phone-carousel__chip';
          chip.textContent = tag;
          tagsEl.appendChild(chip);
        });
      }

      function setActive(index) {
        active = index;
        items.forEach(function (item, i) {
          var off = wrapOffset(i, index);
          var slot = slotFromOffset(off);
          item.dataset.slot = slot;
          item.classList.toggle('is-active', off === 0);
          item.setAttribute('aria-hidden', Math.abs(off) > 2 ? 'true' : 'false');
        });
        dots.forEach(function (dot, i) {
          dot.classList.toggle('is-active', i === index);
          dot.setAttribute('aria-selected', i === index ? 'true' : 'false');
        });
        var cur = items[index];
        if (!cur || !titleEl) return;
        titleEl.textContent = cur.dataset.title || '';
        renderTags(cur.dataset.tags || '');
        descEl.textContent = cur.dataset.desc || '';
        linkEl.href = cur.dataset.url || '#';
        linkEl.textContent = '↗ ' + (cur.dataset.domain || 'Visiter le site');
      }

      function next() {
        setActive((active + 1) % items.length);
      }

      items.forEach(function (item, i) {
        item.addEventListener('click', function () {
          setActive(i);
          resetTimer();
        });
      });
      dots.forEach(function (dot) {
        dot.addEventListener('click', function () {
          setActive(parseInt(dot.dataset.index, 10));
          resetTimer();
        });
      });

      function resetTimer() {
        clearInterval(timer);
        timer = setInterval(next, 5000);
      }

      setActive(0);
      resetTimer();
    }

    var el = document.getElementById('contact-form-success');
    if (!el) return;

    var hideDelayMs = 30000;
    var fadeMs = 380;

    window.setTimeout(function () {
      el.style.transition = 'opacity ' + (fadeMs / 1000) + 's ease';
      el.style.opacity = '0';
      window.setTimeout(function () {
        el.setAttribute('hidden', '');
        el.style.display = 'none';
        el.removeAttribute('role');
        try {
          var url = new URL(window.location.href);
          url.searchParams.delete('sent');
          var next = url.pathname + url.search + (url.hash || '#contact');
          window.history.replaceState(null, '', next);
        } catch (e) {}
      }, fadeMs);
    }, hideDelayMs);
  })();
  </script>
</body>

</html>