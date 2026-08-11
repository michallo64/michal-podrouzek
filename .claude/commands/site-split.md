# Session brief: split into sitepackage_base + sitepackage_promo

Refactor before building content elements (see promo-site-content-blocks-brief.md,
not yet executed — do this split first, then build the blocks directly
inside the new structure).

## Goal
Turn the current single site package into two composer packages so the
reusable skeleton can be lifted out for future client relaunch projects
without disentangling brand-specific content later.

## New structure
- `packages/sitepackage_base/` — the reusable starter kit
    - TypoScript skeleton (setup/constants/page, split into partials)
    - Fluid layout + partials (header/nav/footer structure, no brand colors
      hardcoded — use CSS custom properties instead)
    - All Content Blocks will live here once built (see content-blocks brief)
    - Both b13/container grid presets
    - SEO scaffolding: schema.org JSON-LD template (fields populated by
      brand layer), XML sitemap config, meta field wiring
    - README.md: document what's reusable vs. what a new project must
      supply (see "Brand contract" below)

- `packages/sitepackage_promo/` — Michal's brand layer
    - CSS custom properties file: colors, font stack, spacing scale, logo
      asset reference — this is the ONE file a future relaunch swaps
    - Actual page content / copy (handled via backend records, not code,
      but any hardcoded brand text belongs here, not in base)
    - Any promo-only TypoScript overrides (should be minimal — if you find
      yourself overriding a lot, it's a sign something belongs in base
      instead as a configurable option)

## Renaming while splitting
- Rename the "Case Study Card" content block concept to "Project Card" /
  "Reference Card" in the base package — keep it generic (project type,
  tech stack, scale, description), not tied to the case-study framing
- Any other block names that read as promo-specific should be
  genericized now, before backend records/TCA reference them

## Brand contract (document this in sitepackage_base/README.md)
Define what sitepackage_base expects sitepackage_promo (or any future
brand package) to provide:
- A defined set of CSS custom properties (list them explicitly)
- A logo asset at a defined path
- Site config values: site name, default meta description, schema.org
  Person/Organization type + name

## Composer/config changes
- Update composer.json: two packages, sitepackage_promo depends on
  sitepackage_base
- Update site configuration (config.yaml under config/sites/) to load
  both packages' TypoScript, base first then promo overrides
- Verify DDEV config still resolves both packages correctly after the
  split — run a full `ddev composer install` and confirm the site still
  loads before moving on

## Deliverable for this session
- Two packages in place, existing scaffolded pages still rendering
  correctly after the split
- README in sitepackage_base documenting the brand contract
- Confirmation (screenshot or describe) that the site loads locally
  post-refactor before starting content element work