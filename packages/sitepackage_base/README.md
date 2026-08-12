# sitepackage_base

Reusable TYPO3 13 site package starter kit. This package carries **no
brand identity** — no hardcoded colors, fonts, logo, or copy — so it can be
lifted into a future client relaunch project without disentangling
brand-specific content first. If you find yourself adding a color, a font
name, or promo-specific copy here, it belongs in a brand package instead
(see the contract below).

## What lives here

- TypoScript skeleton, split into `constants` / `setup` / `page` /
  `container` partials (`Configuration/TypoScript/`), delivered as a
  TYPO3 13 **Site Set** (`Configuration/Sets/SitepackageBase/`)
- Fluid layout + partials (`Resources/Private/{Layouts,Templates,Partials}`)
  — header/nav/footer structure only, styled entirely through CSS custom
  properties
- `Resources/Public/Css/base.css` — structural CSS; every color/font/
  spacing value is a `var(--...)` with a neutral inline fallback (no
  literals). Also carries generic `h1`/`h2`/`h3`/`p` defaults so core
  content elements pick up the type system without a `.text-*` class.
- `b13/container` grid presets (`Configuration/TCA/Overrides/tt_content.php`):
  **One Column**, **Two Columns**, **Card Grid (3 columns)**, and
  **Split 8/4** (asymmetric, wide-first — see `docs/BUILD_LOG.md` Session
  4 for why there's no mirrored 4/8 variant yet). Add new presets here,
  not in a brand package, so they stay reusable.
- Content Blocks (`ContentBlocks/ContentElements/`): Hero, Service Card,
  Project Card, Stat Strip, Timeline Entry, Tech Badge List, CTA Banner,
  Contact Sidebar Card, Core Identity — generically named (e.g. "Project
  Card", not "Case Study Card")
- SEO scaffolding: Person/ProfessionalService JSON-LD skeleton
  (`page.headerData` in `page.typoscript`), XML sitemap wiring (via the
  `typo3/seo-sitemap` Set dependency), meta title/description bound to
  backend page fields

## Brand contract

A brand package must declare a Site Set that lists
`podrouzek/sitepackage-base` as a dependency, then supply the following.
Site Sets load dependencies first, so anything the brand package sets
here overrides this package's neutral defaults automatically — no other
wiring is needed.

### 1. CSS custom properties

Define these on `:root` in a stylesheet included **after** `base.css`
(add it via `page.includeCSS` in the brand package's own TypoScript —
see `sitepackage_promo/Configuration/TypoScript/setup.typoscript` for a
working example). The full, current token list — colors, typography,
spacing, shape — is documented where it's actually defined, not
duplicated here to avoid drift:

- **`sitepackage_base/Resources/Public/Css/base.css`** — every property
  name this package consumes, each with its neutral fallback inline at
  the `var(--x, fallback)` call site. Grep for `var(--` if you need the
  full list at a glance.
- **`sitepackage_promo/Resources/Public/Css/brand.css`** — a complete,
  real-value example of every property filled in (currently the
  "TYPO3_DEV_SPEC" clinical/spec-sheet design — full color roles,
  three-tier type scale, spacing, `--radius-sharp: 0px`). Copy this
  file's structure for a new brand package rather than reinventing the
  property names.

Two properties are **not** part of the swap-per-brand contract, despite
living in `:root` — leave them alone unless you're deliberately changing
layout mechanics, not just reskinning:
- `--content-max-width` / `--content-gutter` — page width, not a brand
  color/font/spacing choice
- `--radius-sharp` — exists for documentation of the *current* brand's
  shape language (sharp corners); a future brand with rounded corners
  would need actual CSS changes in `base.css`'s component rules, not
  just a token override, since corner radius isn't parameterized per
  component today

### 2. Self-hosted fonts

Font **choice** and the actual `@font-face`/woff2 files are a brand
decision — see `sitepackage_promo/Resources/Public/Css/fonts.css` and
`Resources/Public/Fonts/` for the pattern (subset to `latin` +
`latin-ext`, downloaded directly from Google Fonts rather than loaded
from a CDN at runtime — see `docs/BUILD_LOG.md` Session 4 for exactly
how). A new brand package supplies its own `fonts.css` and font files;
`base.css` only ever references font *roles* (`--font-display`,
`--font-body`, `--font-mono`), never a specific typeface name.

### 3. Logo asset (optional)

Set the `sitepackage.logoPath` TypoScript constant to an `EXT:` path
(e.g. `EXT:sitepackage_promo/Resources/Public/Images/logo.svg`). The
header partial renders an `<img>` if set, otherwise falls back to the
`sitepackage.brandmark` constant as text — so this is optional, not
required. The current brand (promo) uses the text fallback; no logo
image exists yet.

### 4. Site identity constants

Override these in the brand package's `constants.typoscript` (see
`sitepackage.*` in `Configuration/TypoScript/constants.typoscript` here
for the full list with descriptions): `sitepackage.siteName`,
`sitepackage.siteTagline`, `sitepackage.siteUrl`,
`sitepackage.contactEmail`, `sitepackage.brandmark` (short codename for
the header/footer wordmark — distinct from `siteName`, which is used in
`<title>`/meta/JSON-LD instead), `sitepackage.schemaPersonType` (`Person`
or `Organization`, controls the JSON-LD `@type`).

### 5. Nav labels and page visibility

Not a TypoScript constant — ordinary backend page fields. Set each
page's **Nav title** to override what the header nav shows (falls back
to the page title), and **Hide in menu** to exclude a page from primary
nav entirely. `sitepackage_base`'s Header partial never hardcodes a page
uid or label.

## Extending the JSON-LD

The base JSON-LD skeleton is intentionally minimal (Person +
ProfessionalService, no extra fields). A brand package that wants richer
structured data (e.g. `knowsAbout`, `serviceType`) should add its own
`page.headerData.20` TEXT cObject with an additional `<script
type="application/ld+json">` block rather than editing this package's
`page.headerData.10`.
