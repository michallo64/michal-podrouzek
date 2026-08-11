# Project brief: personal TYPO3 promo/portfolio site (local build)

## Goal
Build a lean, self-showcasing personal website for Michal Podroužek, a TYPO3
developer. The site itself is part of the pitch: a technical visitor should
be able to look at the code (site package, one custom extension) and see
production-quality TYPO3 work. Low traffic expected — optimize for SEO
correctness and code cleanliness, not scale.

## Tech stack
- TYPO3 13 LTS, installed via Composer (not the classic installer)
- PHP 8.3
- DDEV for local development (matches existing workflow)
- MySQL/MariaDB via DDEV default
- No page builder distributions (Bootstrap Package etc.) — hand-roll a
  minimal custom site package so the codebase stays small and legible

## Repo/project structure
- Composer-based monorepo layout, PSR-4 autoloading for the custom
  extensions (consistent with existing professional conventions)
- `packages/sitepackage_promo/` — the site package (TypoScript, Fluid
  templates/layouts/partials, static assets pipeline)
- `packages/promo_showcase/` — the one custom extension that is the
  centerpiece (see below)
- `.ddev/` config committed for reproducible local setup
- README documenting local setup (`ddev start`, `composer install`,
  install-tool bootstrap) — should read like a real project README

## Site package requirements
- Root page + subpages: Home, Services, Case Studies, About/CV, Contact
- TypoScript setup split into sensible partials (setup.typoscript,
  constants.typoscript, page.typoscript) — no monolithic single file
- Fluid layout with header/nav/footer partials; content elements built as
  clean, reusable Fluid partials
- robots.txt, XML sitemap (TYPO3's built-in seo extension), and a
  Person + ProfessionalService JSON-LD schema block in the page template
- Per-page meta title/description fields wired through backend page
  properties, not hardcoded

## Custom extension: `promo_showcase`
Purpose: prove backend/TCA/Extbase skill live on the site, not just in text.
Pick ONE of these (recommend the dashboard widget — smallest surface, most
visibly "backend developer" to anyone who logs in to view the demo):
- A TYPO3 backend dashboard widget: live-calculated "years of TYPO3
  experience" / "extensions shipped" stat, pulling from a small TCA table
- OR a TCA-driven "Projects" content element: editable in the backend,
  rendered via Extbase/Fluid on the frontend (fields: title, tech stack,
  short description, hour range — no client names, no proprietary details)

Extension should be small, PSR-12, commented enough to read cleanly on
GitHub, and NOT contain any DEVSK client code, names, or specifics —
describe project types/scale only (see constraints below).

## Content model (fill with placeholder copy for now)
- Home: name, one-line positioning, CTA to Services/Contact
- Services: TYPO3 upgrades/migrations, custom extension development,
  Stripe/payment integrations, performance/PageSpeed work, WCAG/accessibility
- Case Studies: 2–4 anonymized write-ups (industry + tech stack + scale,
  no client names, no reused code)
- About/CV: background, certifications, tech stack
- Contact: form (Powermail or a lightweight custom form) + email

## SEO requirements
- Canonical URL per page, no duplicate-content traps
- Full name used consistently in title tags across key pages
- Fast Core Web Vitals — inline critical CSS, defer non-critical JS, lazy
  image loading
- XML sitemap auto-generated and linked from robots.txt

## Explicit constraints
- No DEVSK client names, logos, screenshots, or reused proprietary code —
  describe project types/scale only
- No page builder/distribution packages — hand-rolled site package only
- No unnecessary third-party extensions — keep the composer.json lean and
  legible

## Deliverable for this session
Scaffold the local DDEV + Composer + TYPO3 13 install, the empty site
package structure with TypoScript/Fluid skeleton wired to a placeholder
homepage, and the composer.json/package skeleton for `promo_showcase`
(implementation of the extension itself can be a follow-up session).