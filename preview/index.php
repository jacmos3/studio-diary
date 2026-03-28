<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/bootstrap.php';
require_once dirname(__DIR__) . '/lib/store.php';

$slug = trim((string)($_GET['project'] ?? ''));
$project = $slug !== '' ? studio_get_project_by_slug($slug) : null;
if (!$project) {
  http_response_code(404);
  echo 'Project not found.';
  exit;
}

$studioRenderMode = (($studioRenderMode ?? 'preview') === 'published') ? 'published' : 'preview';
$isPreviewShell = $studioRenderMode === 'preview';
$projectLang = studio_validate_lang((string)($project['primary_lang'] ?? 'it'));
$package = studio_build_export_package((int)$project['id']);
$payloadRaw = file_get_contents((string)$package['entries_json_path']);
$payload = json_decode((string)$payloadRaw, true);
if (!is_array($payload)) {
  throw new RuntimeException('Entries export non valido');
}
$rendererVersion = (string)max((int)(@filemtime(dirname(__DIR__) . '/renderer/renderer-env.js') ?: 0), (int)(@filemtime(dirname(__DIR__) . '/renderer/renderer-day-utils.js') ?: 0), (int)(@filemtime(dirname(__DIR__) . '/renderer/renderer-core.js') ?: 0), (int)(@filemtime(dirname(__DIR__) . '/renderer/styles.css') ?: 0));
$modeBaseUrl = $isPreviewShell ? '/preview/' : '/published/';
$bootstrap = [
  'entriesData' => $payload,
  'previewMode' => true,
  'previewBaseUrl' => $modeBaseUrl,
  'projectSlug' => $project['slug'],
  'renderMode' => $studioRenderMode,
];
$builderI18nOverrides = [
  'it' => [
    'prologue_title' => 'Prologo',
    'footer_note' => 'Questo testo finale è un placeholder. Puoi usarlo per tirare le somme del viaggio, raccontare cosa ti è rimasto addosso oppure aggiungere un epilogo personale.',
    'after_camino_title' => 'Dopo il viaggio',
    'after_camino_text' => 'Questa sezione è un placeholder. Puoi usarla per raccontare il ritorno, ciò che è cambiato dopo il viaggio o quello che è rimasto aperto una volta tornato.',
    'open_map_hero' => 'Apri la mappa del percorso',
    'offer_link_hero' => 'Vuoi creare anche tu un diario simile? Scopri come funziona',
    'open_short' => 'Apri Mappa',
  ],
  'en' => [
    'prologue_title' => 'Prologue',
    'footer_note' => 'This closing text is a placeholder. You can use it to sum up the trip, describe what stayed with you, or add a personal epilogue.',
    'after_camino_title' => 'After the journey',
    'after_camino_text' => 'This section is a placeholder. You can use it to describe the return, what changed after the trip, or what remained unresolved once you were back home.',
    'open_map_hero' => 'Open route map',
    'offer_link_hero' => 'Do you want to build a diary like this one? See how it works',
    'open_short' => 'Open Map',
  ],
  'es' => [
    'prologue_title' => 'Prólogo',
    'footer_note' => 'Este texto final es un placeholder. Puedes usarlo para resumir el viaje, contar qué te dejó dentro o añadir un epílogo personal.',
    'after_camino_title' => 'Después del viaje',
    'after_camino_text' => 'Esta sección es un placeholder. Puedes usarla para contar el regreso, lo que cambió después del viaje o lo que quedó abierto una vez que volviste.',
    'open_map_hero' => 'Abrir el mapa del recorrido',
    'offer_link_hero' => '¿Quieres crear un diario parecido? Mira cómo funciona',
    'open_short' => 'Abrir mapa',
  ],
  'fr' => [
    'prologue_title' => 'Prologue',
    'footer_note' => 'Ce texte de fin est un placeholder. Tu peux l’utiliser pour faire le bilan du voyage, raconter ce qu’il t’a laissé ou ajouter un épilogue personnel.',
    'after_camino_title' => 'Après le voyage',
    'after_camino_text' => 'Cette section est un placeholder. Tu peux l’utiliser pour raconter le retour, ce qui a changé après le voyage ou ce qui est resté en suspens une fois rentré.',
    'open_map_hero' => 'Ouvrir la carte du parcours',
    'offer_link_hero' => 'Tu veux créer un journal comme celui-ci ? Regarde comment ça marche',
    'open_short' => 'Ouvrir carte',
  ],
];
$builderPrologueNarratives = [
  'it' => implode("\n", [
    '**Titolo**',
    'Prologo placeholder.',
    '',
    '**Dove ero / tappa**',
    'Usa questa sezione per raccontare cosa è successo prima del primo vero giorno di cammino.',
    '',
    '**Scena chiave**',
    'Aggiungi una scena breve che faccia capire come è iniziato davvero il viaggio.',
    '',
    '**Una cosa che ho capito**',
    'Usa questo paragrafo per un’intuizione iniziale, un dubbio o un piccolo cambiamento di prospettiva prima della partenza.',
    '',
    '**Nota pratica**',
    'Aggiungi qui i dettagli pratici che vale la pena conservare.'
  ]),
  'en' => implode("\n", [
    '**Title**',
    'Placeholder prologue.',
    '',
    '**Where I was / stage**',
    'Use this section to describe what happened before the first real day on the road.',
    '',
    '**Key scene**',
    'Add a short scene that helps explain how the journey really started.',
    '',
    '**What I understood**',
    'Use this paragraph for an early realization, a doubt, or a turning point before departure.',
    '',
    '**Practical note**',
    'Add any practical details worth preserving.'
  ]),
  'es' => implode("\n", [
    '**Título**',
    'Prólogo placeholder.',
    '',
    '**Dónde estaba / etapa**',
    'Usa esta sección para contar lo que ocurrió antes del primer día real de camino.',
    '',
    '**Escena clave**',
    'Añade una escena breve que explique cómo empezó de verdad el viaje.',
    '',
    '**Una cosa que entendí**',
    'Usa este párrafo para una intuición inicial, una duda o un cambio de perspectiva antes de salir.',
    '',
    '**Nota práctica**',
    'Añade aquí los detalles prácticos que merezca la pena conservar.'
  ]),
  'fr' => implode("\n", [
    '**Titre**',
    'Prologue placeholder.',
    '',
    '**Où j’étais / étape**',
    'Utilise cette section pour raconter ce qui s’est passé avant le premier vrai jour de marche.',
    '',
    '**Scène clé**',
    'Ajoute une scène courte qui explique comment le voyage a réellement commencé.',
    '',
    '**Ce que j’ai compris**',
    'Utilise ce paragraphe pour une intuition de départ, un doute ou un basculement avant le départ.',
    '',
    '**Note pratique**',
    'Ajoute ici les détails pratiques qui méritent d’être conservés.'
  ]),
];
$rendererEnv = [
  'mode' => $studioRenderMode,
  'themeClass' => 'renderer-theme--builder',
  'dayNumbering' => 'explicit-or-chronological',
  'legacyPeopleCatalog' => [],
  'gpsInferenceExcludedOrig' => [],
  'features' => [
    'tracking' => false,
    'comments' => false,
    'footerTemplateCta' => false,
  ],
  'i18nOverrides' => $builderI18nOverrides,
  'contentOverrides' => [
    'prologueNarrative' => $builderPrologueNarratives,
  ],
];
$metaDescription = (string)($project['subtitle'] ?: ($isPreviewShell ? 'Anteprima del diario in lavorazione.' : 'Diario di viaggio pubblicato.'));
$pageTitle = (string)$project['title'] . ($isPreviewShell ? ' — Anteprima' : '');
$eyebrowText = $isPreviewShell
  ? 'Anteprima'
  : [
      'it' => 'Diario di viaggio',
      'en' => 'Travel diary',
      'es' => 'Diario de viaje',
      'fr' => 'Journal de voyage',
    ][$projectLang];
$leadText = (string)($project['subtitle'] ?: ($isPreviewShell ? 'Versione di anteprima del diario in lavorazione.' : 'Diario di viaggio pubblicato.'));
$selfHref = './?project=' . rawurlencode($project['slug']);
$footerNote = $isPreviewShell ? 'Anteprima del diario generata nello Studio.' : '';
function h(string $value): string {
  return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?><!doctype html>
<html lang="<?= h($projectLang) ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= h($pageTitle) ?></title>
  <meta id="meta-description" name="description" content="<?= h($metaDescription) ?>" />
  <link id="seo-canonical" rel="canonical" href="<?= h($selfHref) ?>" />
  <link id="seo-alt-it" rel="alternate" hreflang="it" href="<?= h($selfHref) ?>" />
  <link id="seo-alt-en" rel="alternate" hreflang="en" href="<?= h($selfHref) ?>" />
  <link id="seo-alt-es" rel="alternate" hreflang="es" href="<?= h($selfHref) ?>" />
  <link id="seo-alt-fr" rel="alternate" hreflang="fr" href="<?= h($selfHref) ?>" />
  <link id="seo-alt-default" rel="alternate" hreflang="x-default" href="<?= h($selfHref) ?>" />
  <link rel="stylesheet" href="/renderer/styles.css?v=<?= h($rendererVersion) ?>" />
  <link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
    crossorigin=""
  />
</head>
<body>
  <header class="hero" id="hero">
    <div class="hero__inner">
      <div class="hero__top">
        <div class="hero__title">
          <?php if ($isPreviewShell): ?>
            <span class="eyebrow"><?= h($eyebrowText) ?></span>
          <?php else: ?>
            <span class="eyebrow" data-i18n="eyebrow"><?= h($eyebrowText) ?></span>
          <?php endif; ?>
          <h1><?= h($project['title']) ?></h1>
          <p class="lead"><?= h($leadText) ?></p>
          <?php if ($project['intro_text'] !== ''): ?>
            <p class="hero__intro"><?= nl2br(h($project['intro_text'])) ?></p>
          <?php endif; ?>
        </div>
        <div class="controls">
          <div class="lang-select">
            <label for="lang-select" class="lang-select__label">Lingua</label>
            <select id="lang-select" class="lang-select__input" aria-label="Seleziona lingua">
              <option value="it">🇮🇹 Italiano</option>
              <option value="en">🇬🇧 English</option>
              <option value="es">🇪🇸 Español</option>
              <option value="fr">🇫🇷 Français</option>
            </select>
          </div>
          <?php if ($isPreviewShell): ?>
            <div class="manage-tools">
              <a class="map-link" data-static-link="1" href="/"><?= h('Torna allo Studio') ?></a>
            </div>
          <?php endif; ?>
        </div>
      </div>
      <div class="hero__stats">
        <div class="stat"><div class="stat__value" id="stat-days">–</div><div class="stat__label" data-i18n="days">Giorni</div></div>
        <div class="stat"><div class="stat__value" id="stat-photos">–</div><div class="stat__label" data-i18n="photos">Foto</div></div>
        <div class="stat"><div class="stat__value" id="stat-videos">–</div><div class="stat__label" data-i18n="videos">Video</div></div>
      </div>
    </div>
  </header>

  <section class="timeline-nav" aria-label="timeline">
    <div class="timeline-nav__wrap">
      <div class="timeline-nav__inner" id="timeline-nav"></div>
      <div class="timeline-nav__actions" id="timeline-nav-actions"></div>
    </div>
    <div class="timeline-people" id="timeline-people">
      <a class="timeline-people__link" id="timeline-people-link" href="#">Mappa delle persone</a>
      <div class="timeline-people__rail" id="timeline-people-rail"></div>
    </div>
  </section>

  <main class="content" id="content">
    <div class="loading">Sto preparando il diario…</div>
  </main>

  <button class="day-picker-fab" id="day-picker-toggle" type="button">Vai al giorno</button>
  <div class="day-picker-sheet" id="day-picker-sheet" aria-hidden="true">
    <div class="day-picker-sheet__backdrop" id="day-picker-backdrop"></div>
    <div class="day-picker-sheet__panel">
      <div class="day-picker-sheet__head">
        <strong id="day-picker-title">Vai al giorno</strong>
        <button type="button" class="day-picker-sheet__close" id="day-picker-close" aria-label="Chiudi">×</button>
      </div>
      <div class="day-picker-sheet__list" id="day-picker-list"></div>
    </div>
  </div>

  <div class="mini-map" id="mini-map">
    <div class="mini-map__title">
      <span class="mini-map__date" id="mini-map-date"></span>
      <div class="mini-map__actions">
        <a class="mini-map__open" href="#">Apri Mappa</a>
        <button class="mini-map__toggle" id="mini-map-toggle" type="button" aria-label="Minimizza mappa">–</button>
      </div>
    </div>
    <div class="mini-map__body" id="mini-map-body"></div>
  </div>

  <?php if ($footerNote !== ''): ?>
    <footer class="footer" id="footer">
      <div class="footer__inner">
        <div class="footer__note"><?= h($footerNote) ?></div>
      </div>
    </footer>
  <?php endif; ?>

  <div class="modal" id="live-modal" aria-hidden="true">
    <div class="modal__backdrop" id="live-modal-backdrop"></div>
    <div class="modal__content">
      <button class="modal__close" id="live-modal-close" aria-label="Close">×</button>
      <div id="live-modal-body"></div>
    </div>
  </div>

  <div class="modal comments-modal" id="comments-modal" aria-hidden="true">
    <div class="modal__backdrop" id="comments-modal-backdrop"></div>
    <div class="modal__content comments-modal__content">
      <button class="modal__close" id="comments-modal-close" aria-label="Close">×</button>
      <h3 class="comments-modal__title" id="comments-modal-title">Commenti</h3>
      <div class="comments__list" id="comments-list"></div>
      <form class="comments__form" id="comments-form">
        <input id="comments-author" type="text" maxlength="80" placeholder="Nome" required />
        <textarea id="comments-text" maxlength="1200" placeholder="Scrivi un commento" required></textarea>
        <button type="submit">Invia</button>
      </form>
    </div>
  </div>

  <script>
    const rendererEnv = <?= json_encode($rendererEnv, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    rendererEnv.buildUrl = function(kind, ctx = {}) {
      const configured = <?= json_encode($modeBaseUrl, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
      const projectSlug = <?= json_encode((string)$project['slug'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
      const fallbackLang = <?= json_encode($projectLang, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
      const normalizedLang = String((ctx && ctx.lang) || fallbackLang || 'it').trim() || 'it';
      const url = new URL(configured, window.location.origin);
      if (projectSlug) url.searchParams.set('project', projectSlug);
      url.searchParams.set('lang', normalizedLang);
      const params = ctx && ctx.params && typeof ctx.params === 'object' ? ctx.params : null;
      const extra = ctx && ctx.extra && typeof ctx.extra === 'object' ? ctx.extra : null;
      const anchorValue = extra && typeof extra.anchor === 'string' ? extra.anchor.replace(/^#/, '') : '';
      if (kind === 'localized-path') {
        url.hash = anchorValue ? `#${anchorValue}` : '';
        return `${url.pathname}${url.search}${url.hash}`;
      }
      if (kind === 'map') {
        const dayKey = params && params.day ? String(params.day || '').trim() : '';
        const uptoKey = params && params.upto ? String(params.upto || '').trim() : '';
        const nextAnchor = dayKey ? `day-${dayKey}` : (uptoKey ? `day-${uptoKey}` : 'mini-map');
        url.hash = `#${nextAnchor}`;
        return `${url.pathname}${url.search}${url.hash}`;
      }
      if (kind === 'people') {
        url.hash = '#timeline-people';
        return `${url.pathname}${url.search}${url.hash}`;
      }
      if (kind === 'prologue') {
        url.hash = '#hero';
        return `${url.pathname}${url.search}${url.hash}`;
      }
      if (kind === 'track-index') {
        return `/exported/${encodeURIComponent(projectSlug)}/data/tracks/index.json`;
      }
      if (kind === 'track-day') {
        const dayKey = params && params.day ? String(params.day || '').trim().slice(0, 10) : '';
        if (!dayKey) return '';
        return `/exported/${encodeURIComponent(projectSlug)}/data/tracks/day/${encodeURIComponent(dayKey)}.json`;
      }
      if (kind === 'contact' || kind === 'offer' || kind === 'builder') {
        return '/';
      }
      return '';
    };
    window.__CAMMINO_RENDERER_ENV__ = rendererEnv;
    window.__CAMMINO_BOOTSTRAP_DATA__ = <?= json_encode($bootstrap, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
  </script>
  <script
    src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
    crossorigin=""
  ></script>
  <script src="/renderer/renderer-env.js?v=<?= h($rendererVersion) ?>"></script>
  <script src="/renderer/renderer-day-utils.js?v=<?= h($rendererVersion) ?>"></script>
  <script src="/renderer/renderer-core.js?v=<?= h($rendererVersion) ?>"></script>
</body>
</html>
