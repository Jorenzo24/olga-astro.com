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
   The 301s from the old WordPress URLs are **already written** in `.htaccess`; they do
   nothing on GitHub Pages and take effect the moment the domain switches over.
3. Confirm canonical URLs all point to `https://olga-astro.com/...` (they do).

## ⚠️ The OLD site is still live on the production domain
`https://olga-astro.com/` currently serves Olga's **old WordPress**: Yoast sitemap, 17
indexed URLs, latin slugs, content **100% Russian** on a `fr-FR` locale. Going live
overwrites it, so `.htaccess` now carries **301s from every old URL to the matching
`/ru/` page** (added 2026-09-16). Notes:
- `/consultation/` is deliberately **not** redirected: the new site already owns that
  path as the EN "Consultations" page. Its hreflang points at `/ru/konsultatsii/`.
- The 6 old blog posts (percent-encoded Cyrillic slugs) all 301 to `/ru/stati/`;
  there are no per-article pages on the new site.
- The old site shows a flat **200 €** on all 10 consultations, but Olga's 2026-08 copy
  raised natal to 300 € and forecast to 250 €. **The 200 € is stale, never reuse it.**
- Two copy-paste bugs on the old site, worth knowing: the block titled
  «Гороскоп личной жизни» actually contains the **Профориентация** text verbatim, and
  «Ситуация, требующая разрешения» opens with the natal-chart text. That is why the
  personal-life consultation effectively never existed.
- `assets/img/olga-banner.*` **is** the old home banner (`bandeau-Olga-astro.png`,
  1920x700, byte-for-byte the same picture). It is now the RU home hero.

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
- **No em dashes** (—) or `&mdash;` anywhere in **EN/FR** copy (owner rule): use
  commas/colons. **Exception: Russian.** On RU pages the dash тире (—) IS allowed and
  expected where grammar needs it (predicate dash in `X — это Y`, appositions): a bare
  comma there reads as an error to natives. So grep `—`/`&mdash;` before commit on EN/FR
  only; RU is exempt.
- **Big text blocks** are justified: `text-align:justify;-webkit-hyphens:auto;hyphens:auto`.
- `.facts` (duration/price band) and `.note` (caveat aside) are components in
  `css/style.css` §18/§19. Use them; do not restyle inline.
- **Hero action buttons** live in a flex container
  (`display:flex;flex-wrap:wrap;gap:.9rem`), never a bare `<p>` — inline flow makes
  them overlap when long FR/RU labels wrap.
- **`.reading` cards are whole-card clickable** via the title's stretched-link
  (`.reading h3 a::after{inset:0}`). Keep `.reading h3` UNpositioned, or the link
  shrinks back to just the heading (the watermark `.reading__num` sits at `z-index:-1`
  under `isolation:isolate`).

## Brand
- Her full name is **Ольга Матюшкина / Olga Matyushkina** and she has been consulting
  for **more than 9 years** (from her own bio, 2026-08-10). Both facts are now live in
  the about pages and in the `Person` / `provider` JSON-LD across the site.
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
- JSON-LD on home: `WebSite` + `Person` (Olga) + `Service` list (RU: 10 consultations,
  fully localised; EN/FR: still 9 and still in English, see open items).
  **FAQPage schema is live on every landing in all 3 languages** (10 in RU, 9 in EN/FR), and every
  `name`/`text` string is byte-identical to the visible FAQ. Keep it that way: Google
  treats drifted FAQ schema as a violation. Verify with the audit snippet below.
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
- The shell of every one of the 46 pages (header/footer/switcher/hreflang/breadcrumb/
  depth-aware paths) **already exists and is correct in the page itself.** Editing is
  surgical: change only the `<head>` meta/JSON-LD and the body, never the shell.
- ⚠️ The old assembler `scratchpad/build_i18n.py` + `scratchpad/i18n/*` fragments were
  **transient scratchpad artifacts and are GONE** (scratchpad is wiped between
  sessions; they were never committed). Do not look for them. There is no build step.
- **Flow to add/edit a localised page (current, no tooling):**
  1. Edit the EN page directly.
  2. For each of FR/RU, open the matching localised file (its shell is already wired)
     and replace the body by **transcreating** the EN body, mirroring a *completed*
     same-language page for conventions (e.g. `fr/etude-de-theme-natal/`,
     `ru/natalnaya-karta/`): native copy, banned English calques, depth-aware paths
     (FR/RU interior = `../../`), localised sibling slugs, and localise the Service +
     FAQPage + Breadcrumb JSON-LD strings to match the visible copy.
  Parallel subagents (one per page/lang) work well here; keep the FAQ JSON-LD text
  byte-identical to the visible FAQ. FR reads native; RU is a strong AI draft pending
  Olga's review (the 7 newest RU landings had a native-editor polish pass).
- JSON-LD text is now localised on **every** landing, about and contact page in all 3
  languages (the old "schema still in EN" TODO is closed). See the open items below for
  what is left on the home pages.

### Landing pages (EN folder → primary keyword)
- `natal-chart-reading/` → birth/natal chart reading
- `astrology-forecast-reading/` → astrology forecast reading
- `love-astrology-reading/` → love astrology reading (FR `fr/astrologie-amour/`)
- `synastry-compatibility-reading/` → synastry / compatibility reading
- `relocation-astrology-reading/` → relocation astrology / astrocartography
- `career-astrology-reading/` → career astrology reading
- `children-astrology-reading/` → children's astrology reading
- `medical-astrology-reading/` → medical astrology reading
- `birth-time-rectification/` → birth time rectification
- `horary-astrology-reading/` → **electional** astrology (choosing a date) **+ horary**

### ⚠️ RU vocabulary diverges from EN/FR, deliberately (2026-09-16)
Olga's corrections were written against the `/ru/` URLs. **Her "consultation only" rule
applies to Russian ONLY.** Joseph's arbitration: EN and FR keep `reading` / « étude »,
because the entire English keyword set (`*-reading` slugs) is built on it and her rule
would cost the rankings. So expect, permanently until she says otherwise:
- RU: «консультация» everywhere, her own service names, her slugs.
- EN/FR: "reading" / « étude », keyword-rich labels, existing slugs.
- **All three share her ORDER and her ten consultations.** Order and inventory are
  product decisions and stay in sync; only the wording differs.

The ten, in her order:

| # | Her name (RU, use it verbatim) | RU slug | EN sibling |
|---|---|---|---|
| 1 | Личный гороскоп или натальная карта | `ru/natalnaya-karta/` | natal-chart-reading |
| 2 | Астрологический прогноз | `ru/astrologicheskiy-prognoz/` | astrology-forecast-reading |
| 3 | Гороскоп личной жизни | `ru/goroskop-lichnoy-zhizni/` | love-astrology-reading |
| 4 | Синастрия — гороскоп совместимости | `ru/sinastriya-sovmestimost/` | synastry-compatibility-reading |
| 5 | Профориентация | `ru/proforientatsiya/` | career-astrology-reading |
| 6 | Детский гороскоп | `ru/detskiy-goroskop/` | children-astrology-reading |
| 7 | Релокация — астрология переезда | `ru/relokatsiya/` | relocation-astrology-reading |
| 8 | Гороскоп здоровья | `ru/goroskop-zdorovya/` | medical-astrology-reading |
| 9 | Ректификация | `ru/rektifikatsiya/` | birth-time-rectification |
| 10 | Ситуации, требующие разрешения | `ru/razreshenie-situatsii/` | horary-astrology-reading |

**Her vocabulary rule (RU only, absolute there).** In Russian the service is *always* «консультация», never
«разбор» and never «анализ». Both nouns are now at **zero** across the 16 RU pages, and
the audit for it is `grep -r "разбор\|анализ" ru/`. Mind the gender flip when editing:
разбор is masculine, консультация feminine, so adjectives, participles and anaphoric
pronouns all move (`этот разбор остаётся содержательным` → `эта консультация остаётся
содержательной`). The **verb** разобраться («разобраться в себе») is fine and stays.
EN/FR keep "reading" / « étude » on purpose, see above: do not "fix" them.

Other RU-only changes from the same round: the FAQ section is renamed **«Вопрос-ответ»**
(her old site's own wording), `ru/voprosy-otvety/` was a **stub** and is now the full
4-question Q&A ported from the old site plus FAQPage schema, `ru/ob-olge/` publishes her
bio **unsplit** (one continuous text, Socrates epigraph restored, no invented H2s), and
the RU home uses the **full-bleed banner hero** (`.hero--banner`, css §20).
  ⚠️ This page pivoted (2026-08-10). Olga's copy for this URL is about *Электив*,
  choosing the date of an event. The slug, hreflang, sitemap and internal links were
  deliberately KEPT; the page now leads on electional and keeps the whole horary body
  below, bridged by her heading "Ситуация, требующая разрешения" / "A situation that
  needs resolving" / « Une situation à trancher ». Nav and card labels read
  "Электив и хорарная астрология" / "Electional & Horary Astrology" /
  « Astrologie électionnelle et horaire ».

**Complete** (full EN + transcreated FR/RU): **9 landings** plus home, about and
contact. Each landing has its own thematic hero (`<key>-hero.*`) and cosmos band
(`<key>-cosmos.*`) in `assets/img/` (Unsplash/Pexels, free licence). No STUBS remain
except the Formspree placeholder on the contact pages.

## ⚠️ Page anatomy since the client rewrite (2026-08-10)

Olga rewrote her own copy (doc "сайт новый правки"). **Her copy comes first, in her
exact order, uninterrupted by ours**; the earlier SEO copy is kept but sits **below**.
Every landing now follows this order, and no two adjacent sections share a background:

1. HERO `.section--tight` (plain) — her opening paragraphs
2. "… is for you if…" `bg-cream` — `ul.qlist`
3. What you get + price `bg-dark` + `.starfield` — `ul.qgrid`, `.note`, `.facts`, `.qcta`
4. How it works `id="how-it-works"` `bg-cream` — her 4 steps as `.reading` cards 01-04
   `<!-- CLIENT COPY ends here. Below: complementary SEO sections. -->`
5. SEO prose (plain) · 6. 6 benefit cards `bg-cream` · 7. cosmos band `bg-dark` ·
   8. Olga E-E-A-T `bg-cream` · 9. FAQ (plain) · 10. related `bg-cream` · 11. CTA `bg-dark`

Sections she gave no copy for are **omitted, never invented**. Rules that follow from this:
- `.facts` (duration/price) exists ONLY where she gave the figure. **Never write
  "price on request".** Live prices: natal 2 h / 300 €, forecast 1 h / 250 €. Duration
  only (1 h): relocation, career, children, medical. Nothing at all: synastry,
  rectification, electional. A lone `.facts` cell gets `style="max-width:420px"`.
- Her shared 4-step "Как это проходит" block is on the **7 chart-based** landings only.
  Rectification and electional keep their own process (her steps would be circular there).
- Where she gave a price, the Service JSON-LD carries a matching `offers` block.
- Her H1s lead with her own wording (Профориентация, Гороскоп здоровья, Релокация,
  Электив…) while the money keyword stays in the H1 tail, the `<title>` and the SEO H2.
  The nav still uses the keyword-rich labels; only the horary→electional label changed.

**To build a landing:** mirror `ru/natalnaya-karta/index.html`, the canonical reference.
Give each landing its **own thematic hero image** (downloaded, webp+jpg, portrait) — the
home chart wheel is reserved for the home page. Then localise FR + RU via the flow above.

## 📋 Open items (as of 2026-09-16, after Olga's corrections round)

**#1 — Waiting on Olga: prices and durations.** Still only 2 of 10 priced. Nothing
invented, those landings show no `.facts` band. The old site's flat 200 € is stale.

Ask, then add (+ a matching `offers` block in the Service JSON-LD, each language):

| Consultation | Has | Missing |
|---|---|---|
| Личной жизни, relocation, career, children, medical | duration 1 h | **price** |
| Synastry, rectification, "situations requiring resolution" | — | **price + duration** |

**#2 — Confirm with Olga: "Ситуации, требующие разрешения".** Her September list names
this consultation that way and does not mention «Электив» at all, yet in August she
pivoted this very page *to* electional (choosing a date). The page now leads with her
name and keeps **both** the electional and horary bodies. Worth one question to her.

**Known gaps, pre-existing:**
- The 3 **home** pages have a visible 4-item accordion with **no FAQPage schema**.
  RU's home JSON-LD is now fully Russian with all 10 services; **EN and FR home JSON-LD
  are still English-only, but now list all 10.** Note the home accordion headings are topic labels,
  not questions, so FAQPage there would need genuine Q&A first.
- Meta descriptions over ~158 chars on: `fr/articles`, `fr/consultations`, `fr/contact`,
  `fr/faq`, `fr/index`, `ru/stati`. (`ru/konsultatsii`, `ru/kontakty` and
  `ru/voprosy-otvety` were fixed in this round.)
- Contact form still on the Formspree placeholder (see Git section).

**Resolved:** the nav/footer label decision (RU = her names, EN/FR = keyword labels, by
Joseph's arbitration), the `ru/voprosy-otvety` stub, the RU home JSON-LD, the love
consultation in all three languages, and the home grid (see below).

## Home consultations section: three tiers (2026-09-16)
Ten consultations broke the old "featured + two rows of four" (it left an orphan row), so
the section on all three home pages now reads: the **featured** natal card, then **three
illustrated `.showcase` cards** (forecast, love, synastry) using the landings' own hero
photos, then a `.eyebrow.tier-label` separator and the **six specialised ones** in
`.readings-grid--trio` (3 across = two full rows). 1 + 3 + 6 = 10, and no tier leaves a
half-empty row at any breakpoint. Components in `css/style.css` §22. `.showcase h3` must
stay UNpositioned, same stretched-link rule as `.reading`.

Photos for the love landing: `assets/img/love-hero.*` (900x1020) and `love-cosmos.*`
(1600x900), both cropped from the source Joseph supplied; shared by the three languages.

## Audit snippet (run before any commit that touches page bodies)

```bash
python3 - << 'PY'
import io,glob,json,re,html
from collections import Counter
def txt(x): return re.sub(r'\s+',' ',html.unescape(re.sub(r'<[^>]+>',' ',x))).replace(' ',' ').strip()
bad=[]
for p in sorted(glob.glob('**/index.html',recursive=True)):
    s=io.open(p,encoding='utf-8').read(); iss=[]
    lang='ru' if p.startswith('ru/') else ('fr' if p.startswith('fr/') else 'en')
    if s.count('<h1')!=1: iss.append('h1')
    if s.count('<section')!=s.count('</section>'): iss.append('sec')
    if [k for k,v in Counter(re.findall(r'\sid="([^"]+)"',s)).items() if v>1]: iss.append('dup-id')
    if lang!='ru' and (s.count('—')+s.count('&mdash;')): iss.append('EM-DASH')
    for m in re.finditer(r'<script type="application/ld\+json">(.*?)</script>',s,re.S):
        try:
            j=json.loads(m.group(1))
            if isinstance(j,dict) and j.get('@type')=='FAQPage':
                vis=[txt(x.group(1)) for x in re.finditer(r'<button class="accordion__trigger"[^>]*>(.*?)</button>',s,re.S)]
                if [txt(q['name']) for q in j['mainEntity']]!=vis: iss.append('FAQ-DRIFT')
        except Exception: iss.append('BAD-JSON-LD')
    seq=[('d' if 'bg-dark' in c else 'c' if 'bg-cream' in c else 'p') for c in re.findall(r'<section class="([^"]*)"',s)]
    if any(seq[i]==seq[i-1] for i in range(1,len(seq))): iss.append('bg-repeat')
    if iss: bad.append((p,iss))
print('ALL CLEAN' if not bad else bad)
PY
```
Also check internal links resolve and no `?v=` stamp is stale.

## Cache-busting (important)
`.htaccess` caches CSS/JS for **1 month** and images for 1 year. Whenever you edit
`css/style.css` or `js/main.js`, **bump the version query string** `?v=AAAAMMJJx`
(date + letter, e.g. `?v=20260618a` → `20260618b`) on EVERY `<link>`/`<script>`
that references them, across **all** pages — otherwise returning visitors get
stale assets for up to a month. Current version stamp: **v=20260916b**.

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
