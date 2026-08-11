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
  spacing value is a `var(--...)` with a neutral fallback
- `b13/container` grid presets (`Configuration/TCA/Overrides/tt_content.php`):
  **One Column** and **Two Columns**. Add new presets here, not in a brand
  package, so they stay reusable
- Content Blocks (once built — see the content-blocks work) will also live
  here, generically named (e.g. "Project Card", not "Case Study Card")
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
(add it via `page.includeCSS` in the brand package's own TypoScript, see
`sitepackage_promo/Configuration/TypoScript/setup.typoscript` for the
one-line example):

| Property | Purpose | Neutral default (base.css) |
| --- | --- | --- |
| `--color-bg` | Page background | `#ffffff` |
| `--color-text` | Body text | `#101820` |
| `--color-muted` | Secondary text (taglines, footer) | `#5a6472` |
| `--color-accent` | Links, active nav state | `#1c5cff` |
| `--color-border` | Hairline borders (header/footer) | `#e4e7eb` |
| `--font-sans` | Font stack | system font stack |
| `--space-xs` / `--space-sm` / `--space-md` / `--space-lg` | Spacing scale | `0.5rem` / `0.75rem` / `1.25rem` / `1.5rem` |

`--layout-content-width` is a base-internal layout constant, not part of
the brand contract — leave it alone unless you're deliberately changing
the page's max width.

### 2. Logo asset (optional)

Set the `sitepackage.logoPath` TypoScript constant to an `EXT:` path
(e.g. `EXT:sitepackage_promo/Resources/Public/Images/logo.svg`). The
header partial renders an `<img>` if set, otherwise falls back to
`siteName` as text — so this is optional, not required.

### 3. Site identity constants

Override these in the brand package's `constants.typoscript` (see
`sitepackage.*` in `Configuration/TypoScript/constants.typoscript` here
for the full list with descriptions): `sitepackage.siteName`,
`sitepackage.siteTagline`, `sitepackage.siteUrl`,
`sitepackage.contactEmail`, `sitepackage.schemaPersonType` (`Person` or
`Organization`, controls the JSON-LD `@type`).

## Extending the JSON-LD

The base JSON-LD skeleton is intentionally minimal (Person +
ProfessionalService, no extra fields). A brand package that wants richer
structured data (e.g. `knowsAbout`, `serviceType`) should add its own
`page.headerData.20` TEXT cObject with an additional `<script
type="application/ld+json">` block rather than editing this package's
`page.headerData.10`.
