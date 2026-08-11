# Michal Podroužek — Promo Showcase

Personal TYPO3 promo/portfolio site. The site is itself part of the pitch:
a hand-rolled site package (no page builder distribution) plus one small
custom extension, built to read as production-quality TYPO3 13 LTS work.

## Stack

- TYPO3 13.4 LTS, Composer-based install (no classic installer)
- PHP 8.3, MariaDB 10.11 via DDEV
- `packages/sitepackage_base` — the reusable, brand-neutral starter kit:
  TypoScript (split into constants/setup/page/container partials), a
  TYPO3 13 **Site Set**, Fluid layout + partials, structural CSS driven
  entirely by CSS custom properties, `b13/container` grid presets. Meant
  to be lifted into future relaunch projects unmodified — see
  `packages/sitepackage_base/README.md` for the brand contract it expects
  a brand package to fulfill.
- `packages/sitepackage_promo` — the brand layer for this specific site:
  `brand.css` (the CSS custom property values), site identity constants,
  and a Site Set that depends on `sitepackage_base`. This is the one
  package a relaunch would replace.
- `packages/promo_showcase` — one custom extension: a TCA/Extbase-backed
  backend dashboard widget group ("years of TYPO3 experience" and
  "extensions shipped", both live-calculated from a small milestone table)

## Local setup

Requires [DDEV](https://ddev.com/) and Composer.

```bash
ddev start
ddev composer install
```

TYPO3 is already installed in this repo's history (site config lives in
`config/sites/main/`). To bootstrap a **fresh** database from scratch
instead (e.g. after `ddev delete` or on a new machine), run the
install-tool bootstrap non-interactively:

```bash
TYPO3_DB_DRIVER=mysqli \
TYPO3_DB_USERNAME=db \
TYPO3_DB_PASSWORD=db \
TYPO3_DB_PORT=3306 \
TYPO3_DB_HOST=db \
TYPO3_DB_DBNAME=db \
TYPO3_SETUP_ADMIN_EMAIL=you@example.com \
TYPO3_SETUP_ADMIN_USERNAME=admin \
TYPO3_SETUP_ADMIN_PASSWORD="change-me" \
TYPO3_SETUP_CREATE_SITE="https://promo-showcase.ddev.site/" \
TYPO3_PROJECT_NAME="Michal Podroužek — Promo Showcase" \
ddev exec /var/www/html/vendor/bin/typo3 setup --force --no-interaction
```

Then add the site set dependency to `config/sites/main/config.yaml`
(`dependencies: [podrouzek/sitepackage-promo]`) and recreate the page tree
— see `docs/BUILD_LOG.md` for the exact page/content SQL used during
scaffolding, since a fresh `setup` only creates a single root page.

Visit `https://promo-showcase.ddev.site/` for the frontend and
`https://promo-showcase.ddev.site/typo3/` for the backend.

## Documentation

`docs/BUILD_LOG.md` is a running log of what was built, why, and every
non-obvious bug hit along the way (routing, Fluid variable scoping, Extbase
DI quirks) — read it before continuing work on this repo.

## Known placeholders / follow-ups

- Contact page has a plain mailto link; a real form (Powermail or a
  lightweight custom one) is a follow-up.
- Case studies, About, and Services copy is placeholder text per the
  project brief — no real client data is or will be used (see
  `CLAUDE.md` constraints).
- `config/sites/main/config.yaml` still points at the DDEV local URL; swap
  `base` for the real domain before deploying.

## License

GPL-2.0-or-later
