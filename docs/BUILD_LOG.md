# Build log

Written during the initial scaffolding session so work here is resumable
without re-deriving context. Read this before making structural changes.

## What exists

- **DDEV**: project type `typo3`, docroot `public`, PHP 8.3, MariaDB 10.11.
- **Composer root** (`composer.json`): TYPO3 13.4 core packages (trimmed —
  `cms-form`, `cms-impexp`, `cms-reactions`, `cms-recycler`, `cms-sys-note`,
  `cms-webhooks` deliberately left out as unneeded for a lean promo site)
  plus the two local path-repository packages below.
- **`packages/sitepackage_base`**: the reusable, brand-neutral starter kit
  (split out from `sitepackage_promo` in a later session — see "Session 2"
  below). Owns the TYPO3 13 **Site Set** (`Configuration/Sets/SitepackageBase/`),
  all structural TypoScript (`Configuration/TypoScript/{constants,setup,page,container}.typoscript`),
  all Fluid (`Resources/Private/{Layouts,Templates,Partials}`), structural
  CSS driven by custom properties (`Resources/Public/Css/base.css`),
  `b13/container` grid presets (`Configuration/TCA/Overrides/tt_content.php`),
  and 7 Content Blocks (`ContentBlocks/ContentElements/` — see "Session 3"
  below). Full brand contract documented in
  `packages/sitepackage_base/README.md`.
- **`packages/sitepackage_promo`**: the brand layer only. Its Site Set
  (`Configuration/Sets/SitepackagePromo/`) depends on
  `podrouzek/sitepackage-base` (which itself depends on
  `typo3/fluid-styled-content` + `typo3/seo-sitemap` — no need to list
  those again here; Site Set dependencies are transitive and load in
  order, base before promo). Owns only
  `Configuration/TypoScript/constants.typoscript` (site identity values —
  the brand contract's constant side) and a one-line
  `Configuration/TypoScript/setup.typoscript` that adds
  `Resources/Public/Css/brand.css` via `page.includeCSS`. No Fluid of its
  own — everything renders through base's templates.
- **`packages/promo_showcase`**: one Extbase-backed table
  (`tx_promoshowcase_domain_model_milestone`, TCA at
  `Configuration/TCA/`) feeding two backend dashboard widgets
  (`Classes/Dashboard/{ExperienceYearsDataProvider,ExtensionsShippedDataProvider}.php`,
  wired in `Configuration/Services.yaml`, grouped via
  `Configuration/Backend/DashboardWidgetGroups.php`). Both reuse TYPO3
  core's `NumberWithIconWidget` rather than shipping a custom Fluid widget
  template.
- **Site config**: `config/sites/main/config.yaml`, `rootPageId: 1`,
  `dependencies: [podrouzek/sitepackage-promo]`.
- **Page tree**: uid 1 = Home (site root, slug `/`), uid 2–5 = Services,
  Case Studies, About, Contact (pid 1, slugs `/services/` etc). Created
  directly via SQL during scaffolding, not through the backend UI — see
  the `pages` / `tt_content` INSERT statements in shell history if you
  need to recreate this from a bare `typo3 setup --create-site` (which
  only creates the single root page).
- **Milestone data**: one `career_start` row (2016-01-01) and three
  `extension_shipped` rows — placeholder numbers for the dashboard
  widgets to calculate from.
- Backend admin: username `admin`, password `PromoShowcase2026!` (local
  DDEV only — rotate before this ever touches a public deployment).

## Non-obvious bugs hit during scaffolding (and why the fix looks like it does)

1. **`ddev composer create-project` refuses to install into the mounted
   project root as a subdirectory.** Worked around by installing into
   `/tmp/typo3install` inside the web container, then `cp -a` into
   `/var/www/html`.

2. **`typo3 setup --create-site` inserts a *classic* `sys_template` row**
   (root page, `clear=3`) containing a hardcoded "Welcome to TYPO3" splash
   plus a raw `page.100 = CONTENT` dump of colPos 0. `clear=3` resets the
   TypoScript stack for that branch, so it fully shadowed our Site Set —
   the homepage rendered the stock welcome page instead of our template.
   **Fix**: deleted that `sys_template` row; Site Sets are the only
   TypoScript source now. If you ever re-run `typo3 setup --create-site`,
   delete `sys_template` again.

3. **Fluid Layout containing its own `<!DOCTYPE html><html><head>…`
   double-wraps the page.** With `config.disableAllHeaderCode` unset
   (default — needed to keep `includeCSS`/`page.meta.*` TypoScript-driven),
   TSFE's `RequestHandler::generatePageContent()` always hands the PAGE
   object's rendered content to `PageRenderer`, which supplies its own
   `<html><head>…</head><body>` wrapper and inserts the content *inside*
   it. A Fluid Layout that also opens `<html><head>` produces two nested
   documents. **Fix**: the Layout (`Resources/Private/Layouts/Default.html`)
   renders only the body fragment (header/main/footer partials); the
   `<head>` is entirely TypoScript-driven (`page.includeCSS`,
   `page.headerData`, core SEO meta tags).

4. **`lib.dynamicContent` built with `LOAD_REGISTER` +
   `f:cObject typoscriptObjectPath="lib.dynamicContent"><br>{colPos: 0}`**
   (the classic "sitepackage builder" recipe for parameterizing colPos)
   produced a broken SQL query (`... {#colPos} = {register:colPos}` didn't
   resolve as expected, corrupting the WHERE clause). Since every page
   template here only ever uses one content column, the register
   indirection wasn't needed. **Fix**: `lib.dynamicContent` is now a
   direct `CONTENT` cObject hardcoded to `colPos = 0`; the Fluid call is
   `<f:cObject typoscriptObjectPath="lib.dynamicContent" />` with no data
   argument. If a second column is ever needed, don't resurrect the
   register trick — define a second named `lib.*` cObject instead.

5. **Fluid 4 (shipped with TYPO3 13.4, `typo3fluid/fluid ^4.6`) does not
   implicitly forward variables into `<f:render partial="..." />`.**
   Older Fluid passed all current variables into a partial by default;
   this version requires `arguments="{_all}"` (or an explicit list)
   or the partial sees nothing. Every `f:render partial=` call in
   `Layouts/Default.html` now sets `arguments="{_all}"` explicitly. If you
   add a new partial call and its variables show up empty, this is almost
   certainly why.

6. **Raw JSON inside a Fluid template breaks Fluid's own parser.** The
   Person/ProfessionalService JSON-LD block has nested `{ }` (e.g.
   `"founder": {"@id": "{siteUrl}#person"}`); Fluid's tokenizer treats bare
   `{` as the start of its own expression/array syntax and the whole
   template's variable interpolation silently no-ops (renders every
   `{variable}` as literal text, no error). **Fix**: the JSON-LD is no
   longer Fluid at all — it's a plain TypoScript `TEXT` cObject
   (`page.headerData.10` in `page.typoscript`) using `{$sitepackage.*}`
   constant substitution, which happens at TypoScript parse-time and is
   immune to bare `{`/`}` in the surrounding text. Don't move this back
   into Fluid without escaping every structural brace.

7. **The `XmlSitemap` `PageType` route enhancer 404'd every subpage.**
   Following the pattern from `EXT:seo`'s own docs
   (`map: { /: 0, sitemap.xml: ... }`) or adding `default: /` / `index: /`
   both caused `PageTypeDecorator` to build a regex matching *any* request
   ending in `/`, which then got misidentified as a page-type suffix and
   stripped — so `/services/` was matched against `/services` internally
   and failed to resolve against the stored slug. **Fix**: the enhancer
   only maps `sitemap.xml`, nothing else:
   ```yaml
   routeEnhancers:
     XmlSitemap:
       type: PageType
       map:
         sitemap.xml: 1533906435
   ```
   Do not add a `/` (bare or as `default`/`index`) to this enhancer.

8. **`MilestoneRepository::initializeObject()` (the standard
   `setRespectStoragePage(false)` pattern) worked from a CLI command but
   silently failed from the dashboard widget**, returning 0/null for both
   widgets even though the data was correct (verified via a throwaway CLI
   command). Root cause: the dashboard widget's data providers are
   constructed via the core Symfony DI container (`Configuration/Services.yaml`
   autowiring), which does not reliably run Extbase's `initializeObject()`
   lifecycle hook the way Extbase's own CLI/controller bootstrap does.
   **Fix**: `respectStoragePage(false)` is now set explicitly inside each
   query method (`createUnrestrictedQuery()` in `MilestoneRepository`)
   rather than once in `initializeObject()`. If you add a new repository
   method, use `createUnrestrictedQuery()` too — don't add a fresh
   `createQuery()` call and assume the old settings apply.

## Explicitly deferred / not done

- Contact page is a static mailto link, not a real form.
- No automated tests.
- `config/sites/main/config.yaml` `base` still points at the DDEV URL.
- Case studies / About / Services copy is placeholder per the project
  brief (see root `CLAUDE.md` — no real client data, ever).

## Session 2: split into sitepackage_base + sitepackage_promo

Goal: pull the reusable skeleton (TypoScript, Fluid, CSS structure,
container grids) out of `sitepackage_promo` into a brand-neutral
`sitepackage_base`, so a future relaunch project only needs to swap the
brand package. Full brand contract is in
`packages/sitepackage_base/README.md` — don't duplicate it here, just the
mechanics and the one new bug.

**What moved where**: see the updated `sitepackage_base` /
`sitepackage_promo` entries under "What exists" above. Short version —
everything structural went to base; promo kept only
`constants.typoscript` (site identity values) and a one-line
`setup.typoscript` (`page.includeCSS.brand = EXT:sitepackage_promo/...`).
CSS split the same way: `base.css` defines every custom property with a
neutral default, `brand.css` (loaded after, via TypoScript array-key
ordering following Site Set dependency order) overrides them — verified
by checking the compiled/merged CSS output contains both
`--color-accent` declarations in the right order, brand's last.

Also added `b13/container` (^4.0, confirmed TYPO3 13.4/14.3 compatible on
Packagist before adding) to `sitepackage_base`, with two starter grid
presets ("One Column", "Two Columns") registered via
`Configuration/TCA/Overrides/tt_content.php` using
`\B13\Container\Tca\Registry::configureContainer()` — not a Site Set
dependency (it doesn't ship one; it's TCA/PHP-only, no static template
needed). **Don't add `b13/container` to a Set's `dependencies:` list** —
there is no such Set and TYPO3 will fail to resolve it.

### New bug: missing `tx_container_parent` DB column after adding an extension

After `ddev composer update` pulled in `b13/container`, the backend
Page/Layout module (`Web > Page`) showed two "PHP Warning: Undefined
array key tx_container_parent" banners
(`vendor/b13/container/Classes/Listener/ContentUsedOnPage.php:31`). Root
cause: b13/container's TCA adds a `tx_container_parent` column to
`tt_content`, but composer-installing an extension does **not** run a
database schema migration — that's a separate step, and it was skipped
here. **Fix**: `ddev exec /var/www/html/vendor/bin/typo3 extension:setup`
(the CLI equivalent of activating an extension in the Extension Manager,
which runs pending schema migrations for all active extensions). Verified
via `SHOW COLUMNS FROM tt_content LIKE 'tx_container%'` that
`tx_container_parent` exists afterward, and that the warnings were gone
on reload. **Takeaway**: any time a new extension is added to composer.json
and it ships TCA/`ext_tables.sql` changes, run `extension:setup` (or the
install tool's "Analyze Database Structure") before assuming it's live —
`cache:flush` alone does not do this.

### Verification performed

- `typo3 site:sets:list` — confirmed both new Sets resolve with the
  correct dependency chain (`sitepackage-base` → fluid-styled-content +
  seo-sitemap; `sitepackage-promo` → `sitepackage-base`).
- Curled `/`, all four subpages, `/sitemap.xml`, `/robots.txt` — all 200.
- Diffed the compiled CSS to confirm `base.css` then `brand.css` both
  concatenated in the right order (brand's `--color-accent` wins).
- Logged into the backend, opened the "New Content Element" wizard,
  confirmed a "Container" group with both presets appears and is
  selectable.
- Confirmed the pre-existing dashboard widgets (Session 1, item 8 above)
  still work after the extension list changed — `extension:list` shows
  `container`, `sitepackage_base`, `sitepackage_promo`, `promo_showcase`
  all active.

## Session 3: Content Blocks + a 3-column card grid

Goal: build the 7 content blocks from the content-blocks brief, directly
into `sitepackage_base` (not the old single-package layout, which no
longer exists after Session 2).

**Composer**: `friendsoftypo3/content-blocks` — the 2.x line requires
TYPO3 14.3, incompatible with our 13.4 install; pinned to `^1.6` (resolved
to 1.6.3) instead. Checked this on Packagist *before* attempting install,
same as `b13/container` in Session 2 — always verify a new TYPO3 extension's
core version constraint before requiring it.

**Blocks** (all in `packages/sitepackage_base/ContentBlocks/ContentElements/`,
vendor `sitepackage`): `hero`, `service-card`, `project-card`, `stat-strip`,
`timeline-entry`, `tech-badge-list`, `cta-banner`. Scaffolded via
`typo3 content-blocks:create --no-interaction` per block (reliable
boilerplate — folder structure, config.yaml skeleton, both Fluid
templates, labels.xlf), then hand-edited fields/templates/labels.

- **Renamed** "Case Study Card" → **Project Card** per the site-split
  brief's instruction to genericize block names before any backend
  records reference them (this was the first thing built after the
  rename requirement, so there was no migration to do — just named it
  right from the start).
- **No client name field** on Project Card, by design — matches the
  project's anonymization constraint (see root `CLAUDE.md`).
- Fields reuse the core `header` field via `useExistingField: true` where
  it plays the role of a title (Hero, Service Card, Project Card, Timeline
  Entry, CTA Banner) — new dedicated fields only where there wasn't a
  suitable existing one. Tech stack on Project Card is a plain
  comma-separated `Text` field, not a `Category` relation — avoids needing
  to pre-seed a `sys_category` taxonomy for a handful of tags on a small
  site.
- Stat Strip and Tech Badge List use `Collection` fields (IRRE). Content
  Blocks auto-generates a child table per collection
  (`sitepackage_statstrip_stats`, `sitepackage_techbadgelist_badges`) with
  a `foreign_table_parent_uid` column linking back to the parent
  `tt_content` row, and the parent's own field
  (`sitepackage_statstrip_stats` etc.) stores the **count** of children —
  `DESCRIBE`d the generated tables before writing any SQL rather than
  guessing the schema.
- After every `config.yaml` change: `rm -rf var/cache/*` + `cache:flush` +
  `extension:setup --extension=sitepackage_base` — same schema-migration
  gotcha as the `b13/container` bug in Session 2, and it bit again here
  (new `tt_content` columns + two new tables) until schema setup was run.
- `typo3 content-blocks:lint` caught nothing wrong on the first pass, but
  ran it before every schema-setup step regardless — cheap and catches
  YAML mistakes before they become a confusing runtime error.

**Container grid**: added a third preset, `sitepackage-cardgrid3`
("Card Grid (3 columns)"), alongside the two from Session 2 — CSS grid,
1 column mobile / 2 tablet (`min-width: 40rem`) / 3 desktop
(`min-width: 64rem`), in `base.css` as `.ce-card-grid`. Registered with an
`allowed: {CType: 'sitepackage_service_card, sitepackage_project_card'}`
restriction on its column, **but this is not actually enforced**: per
`b13/container`'s own README, `allowed`/`disallowed` only take effect with
`ichhabrecht/content-defender` installed, and that extension's latest
release (3.5.3) caps out at TYPO3 12.1 — it does not support 13.4. Checked
on Packagist before deciding; not worth pulling in an unsupported/
unmaintained dependency just for this. The `allowed` key is left in place
as declarative intent (self-documenting, forward-compatible if
content_defender ever adds v13 support) and callers are trusted to pick
the right card type for now. If this ever needs real enforcement, look
for a content_defender v13 release first before reaching for a custom
solution.

**Content assembly**: pages rebuilt directly via SQL (`tt_content` +
the two collection child tables), the same approach as Session 1's
placeholder content. Container parent/child linking is fully mechanical —
just `pid`, `colPos` (`0` for the container itself, `230` for this card
grid's single column, per the `Configuration/TCA/Overrides/tt_content.php`
registration), and `tx_container_parent` = the container row's `uid` — no
DataHandler needed, this isn't IRRE. IRRE (Collections) *does* need the
child rows written to the generated child table with the correct
`foreign_table_parent_uid`, and the parent's count field set to match, as
above.

- **Home**: Hero → Stat Strip (10+ years / 3 extensions / 100% Composer)
  → CTA Banner.
- **Services**: header → Card Grid (3 col) with 5 Service Cards → Tech
  Badge List → CTA Banner.
- **Case Studies**: header → Card Grid (3 col) with the 3 Project Cards
  (content carried over from Session 1's placeholder text elements,
  reshaped into the new fields) → CTA Banner.
- **About**: header → intro text (unchanged from Session 1) → Tech Badge
  List → three Timeline Entries stacked directly at colPos 0 (deliberately
  *not* wrapped in the "One Column" container — the brief calls for a
  plain stacked list here, and the existing spacing/border-bottom on
  `.ce-timeline-entry` already gives consistent rhythm without one) → CTA
  Banner.
- **Contact**: left untouched — out of scope for this session's block set.

### Verification performed

- `content-blocks:lint` — clean.
- Curled `/`, all four rebuilt pages, `sitemap.xml` — all 200.
- Grepped rendered HTML for every block's CSS-class-scoped content
  (`ce-hero__heading`, `ce-stat-strip__number`, `ce-service-card__title`
  ×5, `ce-project-card__*` ×3 with no client names, `ce-timeline-entry__*`
  ×3) to confirm real data reached the frontend, not just that the page
  returned 200.
- Backend Page module on Services: confirmed the "Card Grid (3 columns)"
  container shows its 5 Service Card children nested inside it, no PHP
  warnings.
- Screenshotted the rendered Home and Services pages — card grid is
  visibly 3 columns at desktop width, brand orange accent applied
  correctly throughout (proof the base/brand split from Session 2 still
  works end to end with real content on top of it).
