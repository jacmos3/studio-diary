#!/usr/bin/env python3
from __future__ import annotations

import argparse
import shutil
from pathlib import Path


def replace_once(text: str, old: str, new: str, label: str) -> str:
    if old not in text:
        raise RuntimeError(f"Patch marker not found for {label}")
    return text.replace(old, new, 1)


def patch_renderer_app(app: str) -> str:
    app = replace_once(
        app,
        """let currentLang = 'it';
const SUPPORTED_LANGS = new Set(['it', 'en', 'es', 'fr']);
""",
        """let currentLang = 'it';
const BOOTSTRAP_PREVIEW_DATA = (typeof window !== 'undefined' && window.__CAMMINO_BOOTSTRAP_DATA__ && typeof window.__CAMMINO_BOOTSTRAP_DATA__ === 'object')
  ? window.__CAMMINO_BOOTSTRAP_DATA__
  : null;
const IS_BOOTSTRAP_PREVIEW = !!(BOOTSTRAP_PREVIEW_DATA && BOOTSTRAP_PREVIEW_DATA.previewMode);
const buildBootstrapPreviewUrl = (lang = currentLang, anchor = '') => {
  if (!IS_BOOTSTRAP_PREVIEW) return '';
  const configured = String(BOOTSTRAP_PREVIEW_DATA.previewBaseUrl || '/preview/').trim() || '/preview/';
  const url = new URL(configured, window.location.origin);
  const normalizedLang = normalizeLang(lang) || 'it';
  const projectSlug = String(BOOTSTRAP_PREVIEW_DATA.projectSlug || '').trim();
  if (projectSlug) url.searchParams.set('project', projectSlug);
  url.searchParams.set('lang', normalizedLang);
  url.hash = anchor ? (String(anchor).startsWith('#') ? String(anchor) : `#${anchor}`) : '';
  return `${url.pathname}${url.search}${url.hash}`;
};
const SUPPORTED_LANGS = new Set(['it', 'en', 'es', 'fr']);
""",
        'bootstrap prelude',
    )
    app = replace_once(
        app,
        """const buildLocalizedPath = (lang, pathnameValue = '') => {
  const targetLang = normalizeLang(lang) || 'it';
  let pathValue = String(pathnameValue || '/');
""",
        """const buildLocalizedPath = (lang, pathnameValue = '') => {
  const targetLang = normalizeLang(lang) || 'it';
  if (IS_BOOTSTRAP_PREVIEW) return buildBootstrapPreviewUrl(targetLang);
  let pathValue = String(pathnameValue || '/');
""",
        'localized path',
    )
    app = replace_once(
        app,
        """const buildLocalizedMapPath = (lang, params = null) => {
  const targetLang = normalizeLang(lang) || 'it';
  const base = `/${targetLang}/map/`;
""",
        """const buildLocalizedMapPath = (lang, params = null) => {
  const targetLang = normalizeLang(lang) || 'it';
  if (IS_BOOTSTRAP_PREVIEW) {
    const dayKey = params && params.day ? normalizeDayUiKey(String(params.day || '')) : '';
    return buildBootstrapPreviewUrl(targetLang, dayKey ? `day-${dayKey}` : 'mini-map');
  }
  const base = `/${targetLang}/map/`;
""",
        'localized map path',
    )
    app = replace_once(
        app,
        """const buildLocalizedContactPath = (lang) => {
  const targetLang = normalizeLang(lang) || 'it';
  return `/${targetLang}/contatti/`;
};
""",
        """const buildLocalizedContactPath = (lang) => {
  const targetLang = normalizeLang(lang) || 'it';
  if (IS_BOOTSTRAP_PREVIEW) return '/';
  return `/${targetLang}/contatti/`;
};
""",
        'localized contact path',
    )
    app = replace_once(
        app,
        """const buildLocalizedOfferPath = (lang) => {
  const targetLang = normalizeLang(lang) || 'it';
  return `/${targetLang}/crea-il-tuo-diario/`;
};
""",
        """const buildLocalizedOfferPath = (lang) => {
  const targetLang = normalizeLang(lang) || 'it';
  if (IS_BOOTSTRAP_PREVIEW) return '/';
  return `/${targetLang}/crea-il-tuo-diario/`;
};
""",
        'localized offer path',
    )
    app = replace_once(
        app,
        """const buildLocalizedPeoplePath = (lang) => {
  const targetLang = normalizeLang(lang) || 'it';
  return `/${targetLang}/people/`;
};
""",
        """const buildLocalizedPeoplePath = (lang) => {
  const targetLang = normalizeLang(lang) || 'it';
  if (IS_BOOTSTRAP_PREVIEW) return buildBootstrapPreviewUrl(targetLang, 'timeline-people');
  return `/${targetLang}/people/`;
};
""",
        'localized people path',
    )
    app = replace_once(
        app,
        """const buildLocalizedProloguePath = (lang) => {
  const targetLang = normalizeLang(lang) || 'it';
  return `/${targetLang}/prologue/`;
};
""",
        """const buildLocalizedProloguePath = (lang) => {
  const targetLang = normalizeLang(lang) || 'it';
  if (IS_BOOTSTRAP_PREVIEW) return buildBootstrapPreviewUrl(targetLang, 'hero');
  return `/${targetLang}/prologue/`;
};
""",
        'localized prologue path',
    )
    app = replace_once(
        app,
        """const syncLanguagePath = (lang) => {
  if (typeof window === 'undefined' || !window.history || typeof window.history.replaceState !== 'function') return;
  const nextPath = buildLocalizedPath(lang, window.location.pathname);
  if (!nextPath || nextPath === window.location.pathname) return;
  const nextUrl = `${nextPath}${window.location.search}${window.location.hash}`;
  window.history.replaceState(null, '', nextUrl);
};
""",
        """const syncLanguagePath = (lang) => {
  if (typeof window === 'undefined' || !window.history || typeof window.history.replaceState !== 'function') return;
  if (IS_BOOTSTRAP_PREVIEW) {
    const nextUrl = buildBootstrapPreviewUrl(lang, String(window.location.hash || '').replace(/^#/, ''));
    const currentUrl = `${window.location.pathname}${window.location.search}${window.location.hash}`;
    if (!nextUrl || nextUrl === currentUrl) return;
    window.history.replaceState(null, '', nextUrl);
    return;
  }
  const nextPath = buildLocalizedPath(lang, window.location.pathname);
  if (!nextPath || nextPath === window.location.pathname) return;
  const nextUrl = `${nextPath}${window.location.search}${window.location.hash}`;
  window.history.replaceState(null, '', nextUrl);
};
""",
        'sync language path',
    )
    app = replace_once(
        app,
        """const updateSeoForLang = (lang) => {
  const normalized = normalizeLang(lang) || 'it';
  const seo = SEO_META[normalized] || SEO_META.it;
  const origin = window.location.origin || '';
  const isProloguePage = isProloguePathname(window.location.pathname);
  const canonicalPath = isProloguePage ? `/${normalized}/prologue/` : `/${normalized}/`;
  const canonical = `${origin}${canonicalPath}`;
  const altIt = `${origin}${isProloguePage ? '/it/prologue/' : '/it/'}`;
  const altEn = `${origin}${isProloguePage ? '/en/prologue/' : '/en/'}`;
  const altEs = `${origin}${isProloguePage ? '/es/prologue/' : '/es/'}`;
  const altFr = `${origin}${isProloguePage ? '/fr/prologue/' : '/fr/'}`;
  const robotsContent = 'noindex,follow,max-image-preview:large';
""",
        """const updateSeoForLang = (lang) => {
  const normalized = normalizeLang(lang) || 'it';
  const seo = SEO_META[normalized] || SEO_META.it;
  const origin = window.location.origin || '';
  const isProloguePage = !IS_BOOTSTRAP_PREVIEW && isProloguePathname(window.location.pathname);
  const canonicalPath = IS_BOOTSTRAP_PREVIEW
    ? buildBootstrapPreviewUrl(normalized)
    : (isProloguePage ? `/${normalized}/prologue/` : `/${normalized}/`);
  const canonical = `${origin}${canonicalPath}`;
  const altIt = `${origin}${IS_BOOTSTRAP_PREVIEW ? buildBootstrapPreviewUrl('it') : (isProloguePage ? '/it/prologue/' : '/it/')}`;
  const altEn = `${origin}${IS_BOOTSTRAP_PREVIEW ? buildBootstrapPreviewUrl('en') : (isProloguePage ? '/en/prologue/' : '/en/')}`;
  const altEs = `${origin}${IS_BOOTSTRAP_PREVIEW ? buildBootstrapPreviewUrl('es') : (isProloguePage ? '/es/prologue/' : '/es/')}`;
  const altFr = `${origin}${IS_BOOTSTRAP_PREVIEW ? buildBootstrapPreviewUrl('fr') : (isProloguePage ? '/fr/prologue/' : '/fr/')}`;
  const robotsContent = 'noindex,follow,max-image-preview:large';
""",
        'seo',
    )
    app = replace_once(
        app,
        """  document.querySelectorAll('.map-link').forEach((el) => {
    if (el && el.tagName === 'A') el.href = buildLocalizedMapPath(normalized);
  });
""",
        """  document.querySelectorAll('.map-link').forEach((el) => {
    if (!el || el.tagName !== 'A' || el.dataset.staticLink === '1') return;
    el.href = buildLocalizedMapPath(normalized);
  });
""",
        'map links',
    )
    app = replace_once(
        app,
        """const loadPublicUiSettings = async () => {
  try {
""",
        """const loadPublicUiSettings = async () => {
  if (IS_BOOTSTRAP_PREVIEW) {
    applyFooterTemplateCtaVisibility();
    return;
  }
  try {
""",
        'public ui settings',
    )
    app = replace_once(app, "const TRACKING_ENABLED = true;\n", "const TRACKING_ENABLED = !IS_BOOTSTRAP_PREVIEW;\n", 'tracking flag')
    app = replace_once(
        app,
        """        setLang(targetLang, { renderExisting: false });
""",
        """        setLang(targetLang, { renderExisting: false, syncPath: !IS_BOOTSTRAP_PREVIEW });
""",
        'language switcher',
    )
    app = replace_once(
        app,
        """    setLang(getInitialLanguage());
""",
        """    setLang(getInitialLanguage(), { syncPath: !IS_BOOTSTRAP_PREVIEW });
""",
        'initial language',
    )
    return app


def sync_renderer(source_root: Path, studio_root: Path) -> None:
    renderer_root = studio_root / 'renderer'
    (renderer_root / 'assets' / 'img').mkdir(parents=True, exist_ok=True)

    source_app = source_root / 'app.js'
    source_css = source_root / 'styles.css'
    source_hero = source_root / 'assets' / 'img' / 'img_f98bce6cb159.jpg'

    for path in [source_app, source_css, source_hero]:
        if not path.is_file():
            raise FileNotFoundError(f'Missing source file: {path}')

    app_text = source_app.read_text(encoding='utf-8')
    app_text = patch_renderer_app(app_text)
    (renderer_root / 'app.js').write_text(app_text, encoding='utf-8')
    shutil.copy2(source_css, renderer_root / 'styles.css')
    shutil.copy2(source_hero, renderer_root / 'assets' / 'img' / 'img_f98bce6cb159.jpg')


def main() -> int:
    parser = argparse.ArgumentParser(description='Sync renderer snapshot into Diary Studio.')
    parser.add_argument('source', help='Path to the renderer repo site root (the folder containing app.js and styles.css).')
    parser.add_argument('--studio-root', default=str(Path(__file__).resolve().parents[1]), help='Diary Studio repo root.')
    args = parser.parse_args()

    source_root = Path(args.source).expanduser().resolve()
    studio_root = Path(args.studio_root).expanduser().resolve()
    sync_renderer(source_root, studio_root)
    print(f'Synced renderer from {source_root} to {studio_root / "renderer"}')
    return 0


if __name__ == '__main__':
    raise SystemExit(main())
