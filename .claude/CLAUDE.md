# CLAUDE.md — olga-astro.com

Static marketing site for **Olga**, a professional astrologer offering personal
online astrology **consultations** (not mass-market horoscopes). Trilingual
(EN / FR / RU), currently a noindex GitHub Pages beta.

## ⚠️ BEFORE PRODUCTION (olga-astro.com) — must do
1. **Remove the noindex tag.** Every page's `<head>` contains
   `<meta name="robots" content="noindex, nofollow">` (clearly commented) so the
   GitHub Pages beta is never indexed. Replace all of them with
   `<meta name="robots" content="index, follow">` (or delete the tag) at go-live.
2. **Set the cPanel username** in `.cpanel.yml` — replace `CPANELUSER` in
   `DEPLOYPATH=/home/CPANELUSER/public_html/`.
3. Confirm canonical URLs all point to `https://olga-astro.com/...` (they do).

## Hosting & deploy
- VPS Hetzner + cPanel. Deploy path: `/home/CPANELUSER/public_html/`.
- Deployment via cPanel **Git Version Control** → it runs `.cpanel.yml`.
- Previewed on **GitHub Pages** during dev (served from a `/repo/` subfolder),
  which is why **all asset paths are relative** (`../assets/…`, `../css/…`) and
  never root-absolute. Do not introduce `/css/...` style paths.

## Stack & conventions
- Vanilla HTML5 + CSS + minimal JS. No build step, no framework.
- **Multi-page, clean URLs**: each page is a folder with `index.html`
  (e.g. `/natal-chart-reading/`). All interior pages live one level deep, so they
  reference shared assets with `../css/style.css`, `../js/main.js`, `../assets/…`
  and link home with `../`.
- Design system lives in `css/style.css` (tokens + components). Reuse it; don't
  add per-page stylesheets. Header/footer markup is duplicated per page (static
  site) — keep them in sync.
- **Mobile-first**, responsive, accessible (semantic landmarks, aria, visible
  focus rings, AA contrast).
- Images: WebP with a `<picture>` JPEG fallback, `loading="lazy"` + width/height
  to avoid CLS, descriptive natural `alt`. **Never hotlink** the old WordPress —
  assets are downloaded into `assets/img/`.
- Icons: inline SVG.
- **No em dashes** (—) or `&mdash;` anywhere in copy (universal owner rule) — use
  commas/colons. Grep `—`/`&mdash;` before every commit.
- **Big text blocks** are justified: `text-align:justify;-webkit-hyphens:auto;hyphens:auto`.
- **Hero action buttons** live in a flex container
  (`display:flex;flex-wrap:wrap;gap:.9rem`), never a bare `<p>` — inline flow makes
  them overlap when long FR/RU labels wrap.
- **`.reading` cards are whole-card clickable** via the title's stretched-link
  (`.reading h3 a::after{inset:0}`). Keep `.reading h3` UNpositioned, or the link
  shrinks back to just the heading (the watermark `.reading__num` sits at `z-index:-1`
  under `isolation:isolate`).

## Brand
- Palette: midnight navy `#0a112e`/`#101d49`, warm gold `#c9a24b`/`#e6c97f`,
  cream `#f7f2e8`. Celestial-refined aesthetic (subtle starfield, gold rules).
- Type: **Cormorant Garamond** (display) + **Inter** (body), via Google Fonts.
- The original `IG.png` "logo" was just an Instagram glyph, so the brand mark is a
  custom inline-SVG crescent wordmark ("OLGA ASTRO"). Photos of Olga:
  `assets/img/olga-portrait.*`, `assets/img/olga-banner.*`.
- Socials: IG `@astrolog_olga_`, FB `astrolog.olgam`, Telegram `astroolga2`.

## SEO model (validated strategy)
- Target **transactional / consultation** intent ONLY. **Never** target
  "horoscope / daily horoscope / [sign] 2026" — wrong intent + unwinnable.
- One consultation type = one landing page = one money keyword.
- Home money keyword: **online astrology consultation**.
- Per page: unique `<title>`, meta description, `<link rel="canonical">`,
  OG + Twitter tags, a single `<h1>`.
- JSON-LD on home: `WebSite` + `Person` (Olga) + `Service` list (9 consultations).
  FAQPage schema is live on `/natal-chart-reading/` (mirror it on other landings).
- `robots.txt` allows all (production-ready); `sitemap.xml` lists every page in
  all 3 languages with `xhtml:link` hreflang alternates.

## Internationalisation (EN / FR / RU)
The site is trilingual. **EN at the root**, **FR under `/fr/`**, **RU under `/ru/`**,
with **localised slugs** (e.g. `natal-chart-reading/` ↔ `/fr/etude-de-theme-natal/`
↔ `/ru/natalnaya-karta/`). Every page carries reciprocal `hreflang` alternates
(`en`/`fr`/`ru` + `x-default`→EN), the right `<html lang>` and `og:locale`, and a
header/mobile **language switcher** (`.lang-switch`).
- Asset paths stay **relative** and depth-aware: EN interior = `../`, FR/RU home =
  `../`, FR/RU interior = `../../`.
- **Don't hand-maintain the 45 pages.** The shells (header/footer/switcher/hreflang/
  paths) are generated deterministically; the LANGUAGE is produced by a 2-pass
  **transcreation** workflow (native copywriter + native editor, with a per-language
  astrology glossary that bans English calques — *not* literal translation) that
  writes content fragments. An assembler (`scratchpad/build_i18n.py`: slug map +
  FR/RU labels + path logic) then stitches them into the localised shells. FR reads
  native; RU is a strong AI draft pending Olga's review.
- **Flow to add/edit a localised page:** edit the EN page (or its hero image), update
  the matching `scratchpad/i18n/<lang>__<key>.main.html` + `.meta.json` fragments
  (or re-run the transcreation workflow for that page), then re-run `build_i18n.py`.
- Known TODO: JSON-LD **text** on FR/RU pages is still EN (URLs are localised);
  localise the schema strings in a later pass.

### Landing pages (folder → primary keyword)
- `natal-chart-reading/` → birth/natal chart reading
- `astrology-forecast-reading/` → astrology forecast reading
- `synastry-compatibility-reading/` → synastry / compatibility reading
- `relocation-astrology-reading/` → relocation astrology / astrocartography
- `career-astrology-reading/` → career astrology reading
- `children-astrology-reading/` → children's astrology reading
- `medical-astrology-reading/` → medical astrology reading
- `birth-time-rectification/` → birth time rectification
- `horary-astrology-reading/` → horary astrology reading

**Complete** (full EN + transcreated FR/RU): **home**, **natal-chart-reading**,
**astrology-forecast-reading**. The other **7 landings are still STUBS** (slug +
meta + breadcrumb + H1 + intro + CTA).

**To build a landing:** write the full EN page mirroring the natal/forecast template
(hero, what-it-is SEO body, 6 benefit cards, emotional cosmos band, 4-step process,
who-it's-for, Olga E-E-A-T split, FAQ accordion + FAQPage JSON-LD, related readings,
CTA — single H1, hreflang + switcher already wired, no em dashes). Give each landing
its **own thematic hero image** (downloaded, webp+jpg, portrait) — the home chart
wheel is reserved for the home page (e.g. natal = a glass natal-chart wheel,
forecast = a moon). Then localise FR + RU via the flow above.

## Cache-busting (important)
`.htaccess` caches CSS/JS for **1 month** and images for 1 year. Whenever you edit
`css/style.css` or `js/main.js`, **bump the version query string** `?v=AAAAMMJJx`
(date + letter, e.g. `?v=20260618a` → `20260618b`) on EVERY `<link>`/`<script>`
that references them, across **all** pages — otherwise returning visitors get
stale assets for up to a month. Current version stamp: **v=20260624b**.

## Theming gotcha (important)
Do NOT put `class="bg-dark"` on `<body>`. The `.bg-dark h2/h3/p/li` rules recolor
text white for dark sections; if `body` carries `.bg-dark`, those rules cascade into
nested light (`.bg-cream`) sections and make their text white-on-white (this broke
the card titles + headings once). `.bg-dark` belongs only on the specific dark
`<section>`s. The header is permanently dark-tinted so nav stays legible over both
dark (home hero) and light (interior) page tops.

## Git
- `main` = production, deployed on push. Per the owner's workflow, **commit and push
  straight to `main`** (no feature-branch ceremony, no review gate).
- Contact form is static: uses a Formspree placeholder + `mailto:` fallback —
  swap in the real Formspree form ID (or backend) before launch.
