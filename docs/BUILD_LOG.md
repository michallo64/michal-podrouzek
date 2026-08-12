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

## Session 4: TYPO3_DEV_SPEC design (Stitch export) wired in

Goal: replace the Session 1 placeholder styling with the confirmed
"clinical spec-sheet" design (`stitch_typo3_engineering_systems/`),
without shipping the Tailwind CDN script or Google Fonts CDN the export
used for its own preview convenience. Source of truth was
`technical_specification/DESIGN.md` (full token frontmatter) plus the 5
screens' `*_code.html` files — **not** the `*_screen.png` files, two of
which (`contact_request_proposal`, `system_about_cv_updated`) are broken
stub files ("Image failed to fetch" saved as .png — `file` reports them
as ASCII text, not PNG). The other three screenshots are valid and were
used for visual cross-checking. If a future session needs the About/CV or
Contact screenshots, they'll need to be re-exported from Stitch — don't
assume the files in this folder are usable images without checking `file`
on them first.

### Self-hosted fonts

Pulled the actual `@font-face` rules from Google Fonts' `css2` endpoint
(with a desktop Chrome UA, so it returns woff2 not woff/ttf), kept only
the `latin` + `latin-ext` subsets (covers English and Slovak/Czech
diacritics — dropped cyrillic/greek/vietnamese), and downloaded the woff2
files directly into
`packages/sitepackage_promo/Resources/Public/Fonts/`. IBM Plex Sans and
Inter both turned out to be served as **variable fonts** by Google even
though we requested discrete weights (400/600/700 all resolve to the same
physical file per subset) — that's correct/intentional on Google's side:
a single `@font-face` per weight, all pointing at the same variable file,
still renders at the declared weight because browsers select that point
on the font's own `wght` axis. IBM Plex Mono is not variable — genuinely
separate files per weight (400/500/600/700 × 2 subsets = 8 files).

Material Symbols Outlined is a ~712KB variable icon font unsubset. Used
`fonttools`/`pyftsubset` (`pip3 install fonttools brotli`, not preinstalled)
with `--text="<curated icon list>" --layout-features='*'` to cut it to
~447KB (curated list: upgrade, api, history_edu, payments, speed,
accessibility_new, hub, integration_instructions, security, arrow_forward,
mail, psychology, data_object, history, fingerprint — union of every icon
used across Home/Services/Contact/About templates plus a few extra
Service Card select options for headroom). The reduction is smaller than
hoped (37%) because ligature-based icon fonts keep most of their GSUB
substitution chain even when subsetting to a handful of ligature strings —
this is a known limitation of subsetting ligature-driven icon fonts, not
a mistake in the subsetting command. Still strictly better than the
unsubset 712KB or a third-party CDN request.

All `@font-face` declarations live in
`packages/sitepackage_promo/Resources/Public/Css/fonts.css` (brand-owned,
since font choice is a brand decision, same reasoning as the original
`--font-sans` brand-contract property from Session 2) — loaded via
`page.includeCSS` in promo's `setup.typoscript`, before `brand.css` in
the same file so both concatenate in that order.

### Token system

`sitepackage_promo/Resources/Public/Css/brand.css` now carries the full
DESIGN.md frontmatter as CSS custom properties — every color role
(`--color-*`, ~40 of them, Material-3-style naming), the three-tier type
scale (`--text-*`, one set of size/weight/line-height/tracking per named
style), spacing (`--space-*`), and `--radius-sharp: 0px` (this design has
no rounded corners anywhere — DESIGN.md's "Shapes" section mentions tags
*could* optionally use a pill radius, but the shipped reference HTML
forces `border-radius: 0 !important` on every element, so sharp corners
everywhere is what was actually built, not the "optional pill" aside).

One extra token beyond DESIGN.md: `--color-border-hairline` (`#e3e6eb`).
DESIGN.md's own "Application Note" specifies this exact value for
structural dividers, but it's *different* from `colors.outline-variant`
(`#bfc9c0`) in the same frontmatter — the actual HTML confirms both are
real, distinct values used for different things (`outline-variant` for
real component borders, the hairline value only for the decorative grid
texture / form background grid). Don't merge these into one token if you
touch this later — they're intentionally different.

`sitepackage_base/Resources/Public/Css/base.css` was fully rewritten
(structure only, every value a `var(--x, neutral-fallback)`) rather than
duplicating a parallel neutral `:root` block for 40+ properties — literal
fallbacks live inline at each `var()` call instead. Also added generic
`h1`/`h2`/`h3`/`p` defaults so core content elements (e.g. the plain
"Header" CType used for page titles) pick up the type system without
needing a `.text-*` utility class by hand.

### Content Blocks extended (all in sitepackage_base, schema changes are additive)

- **Hero**: added `version_nodes` (Collection: label + active-checkbox,
  drives the PCB-trace upgrade-path graphic) and `stats` (Collection,
  max 4, rendered "//"-separated) fields; CTA now has an arrow icon.
- **Service Card**: `icon` Select values changed from the old abstract
  keys (upgrade/extension/payment/...) to real Material Symbols names;
  added `tech_specs` Collection (bulleted "> item" box).
- **Project Card**: added `status` Select (stable/critical/archived,
  colored per DESIGN.md's Status Indicators component) and `tech_tags`
  Collection (pill tags). **Dropped** the `project_type` field and the
  old free-text `tech_stack` field — the confirmed design's cards don't
  have a project-type/industry slot at all, and tags replace the comma
  list. Old columns (`sitepackage_projectcard_project_type`,
  `_tech_stack`) are still in the DB (Content Blocks schema migration is
  additive-only, same as core TYPO3) but nothing reads them anymore.
- **Tech Badge List**: reshaped from flat (`group_label` + one `badges`
  Collection) to nested (`groups` Collection, each with its own
  `group_label` + nested `badges` Collection) — needed for the About page
  design's 4 named groups (ENV.CORE/FRONTEND/TOOLS/INTEGRATION). Verified
  the nested-Collection schema generates correctly:
  `sitepackage_techbadgelist_groups.badges` is a count field, and
  `sitepackage_techbadgelist_badges.foreign_table_parent_uid` points at
  the *group* row's uid, not the parent tt_content row. Old flat-schema
  content (Session 3's Services/About tech badge lists) now renders empty
  (`data.groups` doesn't exist on old records) — expected, not a bug;
  those pages get rebuilt from the new schema when they're next touched.
- **Timeline Entry**: added `location` (Text) and `is_current` (Checkbox,
  drives filled-vs-outline node + bold-vs-secondary date color). Node
  shape confirmed square with a 2px border (not circular) per the actual
  export, contradicting nothing — original brief text didn't specify
  shape, this session's brief explicitly called it out. **Redesigned the
  connecting line**: each entry draws its own line segment
  (`.ce-timeline-entry::before`, full height of that entry) instead of a
  single line owned by a wrapping `.ce-timeline` container, because
  Timeline Entries are independent sibling `tt_content` records on a page
  (not Collection children of anything) — there's no shared DOM node to
  hang one continuous line off. Adjacent entries' segments sit flush and
  read as one continuous line; only the last entry's segment is shortened
  to stop at its own node instead of running past it.
- **Contact Sidebar Card** (new): generic small panel, `variant` Select
  (link/code) switches between a direct-contact link (icon + label +
  arrow that reveals on hover) and a mono code block (e.g. a GPG key).
- **Core Identity** (new): two-field panel for About's left sidebar
  (`what_i_do` bold sentence, `academic_lines` textarea).

New Select field values had to line up exactly with the Material Symbols
subset above — if you add a new icon option to Service Card or
Contact Sidebar Card's icon selects, add the icon name to the
`pyftsubset --text=` list and regenerate
`sitepackage_promo/Resources/Public/Fonts/material-symbols-outlined.woff2`,
or it'll just render as a blank box.

### Third container preset: 8/4 asymmetric split

`sitepackage-split84` (colPos 240 wide / 241 narrow), registered the same
way as the Session 2 presets. **Only the wide-first variant is built.**
The About page design needs a *mirrored* 4/8 (narrow first), but About
page assembly is out of scope for this session's required deliverable
(Home + Services), so a `sitepackage-split48` mirrored preset was not
added — add it the same way (swap colPos order in both the TCA
registration and a new `Split48.html` template) when About gets built.

### Nav / footer

Nav labels (INDEX/SERVICES/REPO/CONTACT) and the About page's exclusion
from nav are **not hardcoded** — they come from ordinary `nav_title` /
`nav_hide` page fields, set via SQL this session (Home→INDEX,
Services→SERVICES, Case Studies→REPO — "REPO" as in "repository of past
work", CaseStudies is the only page that plausibly maps to it since the
export's nav is identical on all 5 screens and never has more than these
4 items — About isn't linked from primary nav on *any* exported screen,
so `nav_hide=1` matches the source faithfully rather than being an
oversight). This keeps `sitepackage_base`'s Header partial fully generic
— a relaunch project just sets different `nav_title` values, no code
changes.

**Bug found and fixed**: a `directory` menu (used for
Services/Case-Studies/Contact) structurally can't include the page it's
rooted at, so Home never appeared in `mainnavigation`. Fixed generically
with a second `special = list` MenuProcessor call (`homenavigation`,
listing just `leveluid:0`, i.e. the root page) rendered before the main
loop — no hardcoded page uid anywhere, root's own `nav_title` (already
"INDEX") and active-state come through normally. **Second bug**, found
right after: that active-state used `{navPage.active}`, which TYPO3 sets
true for *every page in the current rootline*, not just the current page
— since the root page is an ancestor of every page on the site, "INDEX"
showed as active on every single page. Fixed by switching to
`{navPage.current}` (exact-page-match, a real MenuProcessor output
property, verified against `MenuProcessor.php`'s JSON template before
using it) for both nav loops. If you ever see a top-level nav item
staying highlighted on unrelated pages, check for this exact
`active`-vs-`current` mixup first.

### Deliberately deferred / simplified this session

- **Hairline-grid canvas texture** (subtle 1px/0.5-opacity background
  pattern DESIGN.md uses on Services and Case Studies, and a denser
  variant on About): skipped. The `.bg-hairline-grid` utility class
  exists in `base.css` but nothing applies it yet. It's page-specific
  (not every page has it), and doing that properly without hardcoding a
  page uid into the reusable `sitepackage_base` needs either a page-level
  TypoScript condition in the *brand* package or a new page-property
  toggle — not worth building for a barely-visible texture under this
  session's time budget. Low visual risk if it's added later.
- **Home bento teaser fidelity**: the "Core Upgrades" / "Extbase Dev"
  cards reuse the Service Card block (icon optional, `tech_specs`
  bulleted box) rather than a bespoke bento-card component. The actual
  export shows small inline tag pills there (not a bulleted TECH_SPECS
  box) and an "LTS" badge on Core Upgrades that Service Card has no field
  for. Accepted as a disclosed simplification — reusing an existing block
  beat adding a fourth near-duplicate card type for one homepage section.
  Visually close; not pixel-identical.
- **Case Studies, Contact, About page reassembly**: content blocks are
  ready (status/tags on Project Card, Contact Sidebar Card, Core Identity,
  multi-group Tech Badge List, Timeline location/current-state) but the
  pages themselves still hold Session 3's old-schema content and render
  with graceful degradation (missing fields just don't show — verified,
  nothing errors). Explicitly lower priority per this session's brief
  ("Case Studies and Contact can follow once the above is confirmed
  working"); About wasn't in the required deliverable list at all.
- **About/CV role-label text**: the brief flagged
  `SENIOR_SYSTEMS_ARCHITECT` (in the export's identity line, e.g.
  "IDENTITY: SENIOR_SYSTEMS_ARCHITECT // v_3.4.1 // 13+ MAJOR_UPGRADES")
  as pending confirmation and said to hold the field rather than
  hardcode it. Since About page assembly didn't happen this session
  anyway, this never came up in practice — flagging it here so the next
  session doesn't hardcode it from the export without checking back
  first.

### Verification performed

- `content-blocks:lint` — clean (caught two real schema errors along the
  way: `minitems: 0` isn't valid, must be omitted entirely to mean "no
  minimum"; `Select` fields don't support a `required` key, use
  `minitems: 1` instead — both are worth remembering for future blocks).
- Curled all 5 pages + sitemap.xml + robots.txt — all 200.
- Grepped every rendered page for `cdn.tailwindcss.com`,
  `fonts.googleapis.com`, `fonts.gstatic.com` — zero matches anywhere.
  Confirmed the merged/compressed CSS actually contains all 13
  `@font-face` rules and that a font file 200s when requested directly
  from its `_assets/` published path.
- Logged into the backend, confirmed no PHP warnings on Services' Page
  module (container/children render correctly there too, not just
  frontend).
- Screenshotted Home, Services, Case Studies, and About in-browser —
  Home and Services visually match the reference screenshots closely
  (fonts, colors, PCB trace with correct active v13 node, stat strip,
  TECH_SPECS boxes, 3-col and 8/4 grids all correct); Case Studies/About
  confirmed *not broken* with old-schema content, as expected.

## Session 5: Case Studies + Contact rebuilt

Goal: finish the two pages Session 4 deferred, using the same content
blocks and design tokens.

### Case Studies

Rebuilt with the real 6 case studies from
`logs_case_studies_updated_code.html` (not the 3 anonymized placeholders
from Session 1/3) in the existing `sitepackage-cardgrid3` container, each
a Project Card with `status`, `scale`, and `tech_tags` populated. Added
one new CSS pattern, `.eyebrow-log` (left-accent border + mono uppercase
label), for the page's `[SYS_LOG: CASE_STUDIES // ANONYMIZED]` header
treatment — implemented as a single `text` CType with hand-written HTML
(`<div class="eyebrow-log">...`), not a new content block, since it's a
one-off page-header pattern, not repeated content.

One deliberate normalization vs. the source: card 4 ("Backend Dashboard
Search Widget") is styled `STABLE` but rendered in *secondary/gray*
Tailwind classes in the exported HTML, not green — inconsistent with
every other `STABLE` card and with DESIGN.md's own Status Indicators spec
(which says status badges are always `primary_color`). Treated as a
one-off mistake in the Stitch export and normalized to the same green
`STABLE` styling as the other four, rather than reproducing the
inconsistency. If this was actually intentional in the source, flag it
and it can be reverted to a status-independent color.

### Contact

Rebuilt with the `sitepackage-split84` container: wide slot holds the
name/email/requirements form, narrow slot holds two Contact Sidebar Cards
(`DIRECT_LINK`, a `mailto:` link to the real project email — the source
mockup used a fake `admin@typo3devspec.local` address, swapped for
`podrouzekmichal@gmail.com` to match every other contact point on the
site; and `GPG_KEY_PUB`, a mono code block). The mockup's PCB-trace
divider graphic at the page bottom was skipped — decorative, not load-
bearing, same call as the hairline-grid texture deferral in Session 4.

**Real bug, not a design-fidelity issue**: the form was first built as
raw HTML inside a `text` CType's `bodytext` field (same pattern that
worked fine for the Case Studies eyebrow header). It rendered as visibly
escaped text (`&lt;input ...&gt;`) instead of an actual form. Root cause:
`bodytext` is RTE-oriented content and passes through TYPO3's parseFunc
HTML sanitizer, which only lets a curated tag allowlist through
unescaped (paragraphs, headings, basic inline markup, lists, links) —
`<form>`, `<input>`, `<textarea>`, `<label>`, `<button>` are not on it,
so they got HTML-entity-encoded instead of stripped (safer default, but
not what a raw-HTML field editor expects). **Fix**: moved the form into
its own Content Block (`sitepackage-contactform`, effectively zero
custom fields — reuses `header` for backend organization only, nothing
about the form itself is field-driven since the labels are stylized
system text, not editorial copy). Fluid templates aren't subject to this
sanitizer — they render exactly what the `.html` file says. **Rule of
thumb going forward**: raw HTML in a `text` CType's `bodytext` is fine
for basic prose-adjacent markup, but reach for a Content Block the moment
you need a genuinely non-prose tag (form elements, tables with custom
attributes, embeds, etc.) — don't debug why bodytext "ate" your tags,
just move it to a template.

The form itself is **presentational only** — no submit handler, no mail
sending. This matches what `README.md` already documented as a known
follow-up before this session, so it's not a new gap, just now visually
real instead of a placeholder mailto line. Documented directly in the
Content Block's own template as an `<f:comment>` (first attempt used
`{f:comment()}` — invalid Fluid syntax, rendered as literal visible text
in the page; `<f:comment>...</f:comment>` paired tags are correct). Next
step if/when this becomes a priority: either a real Extbase
form-processing controller (this project already has Mailpit configured
via DDEV for local mail testing, so the plumbing to test against is
there) or a lightweight vanilla PHP mail-send eID/middleware — worth a
deliberate choice next session rather than defaulting to the heavier
Extbase option out of habit.

### Verification performed

- `content-blocks:lint` — clean.
- Curled all 5 pages + sitemap.xml + robots.txt — all 200.
- Grepped both rebuilt pages for CDN references — none.
- Backend Page module on Contact — confirmed the Split 8/4 container
  shows Contact Form (wide) and both Contact Sidebar Cards (narrow)
  nested correctly, no PHP warnings.
- Screenshotted both pages in-browser — Case Studies matches the
  reference closely (status badge colors, tags, eyebrow header); Contact
  matches closely (grid-decor form panel, both sidebar card variants,
  arrow-reveal-on-hover on the direct-contact link).

## Session 6: Slovak (SK) language

Goal: use the header's existing (previously decorative) EN // SK toggle
for real — add Slovak as a second site language and translate the pages
rebuilt so far.

### Site config

Second language in `config/sites/main/config.yaml`: `languageId: 1`,
`base: /sk/`, `locale: sk_SK.UTF-8`, `fallbackType: fallback` with
`fallbacks: '0'` — any untranslated record automatically shows the
English version rather than a gap in the page. `sk_SK.UTF-8` isn't
actually installed as a system locale in the DDEV container
(`locale -a` doesn't list it) but TYPO3 didn't need it installed to
render `lang="sk-SK"` pages correctly — worth knowing if a future
session sees locale-dependent formatting (date/number) look off in
Slovak; that's the one thing that actually needs the real system locale,
everything else TYPO3 did here does not.

### Language toggle wired up

Was static `<span>EN</span> // <span>SK</span>` markup since Session 4.
Replaced with TYPO3 core's `LanguageMenuProcessor` (`as = languagemenu`,
added to the same `dataProcessing` block as the two nav menus in
`page.typoscript`), which gives each language item its own translated
`link` to the *current* page — switching language from `/services/`
correctly goes to `/sk/sluzby/`, not back to the Slovak homepage.

### Container children vs. Collection children — two different rules for translated records

Worked out (and confirmed by reading `FrontendContainerFactory::buildContainer`,
not guessed) that these are **not** the same pattern, despite both being
"child records with a parent-pointer column":

- **b13/container children** (`tx_container_parent`): the factory builds
  the container from `$cObj->data`, which — under TYPO3's default content
  overlay behavior — always carries the *original* record's `uid` even
  when rendering the Slovak version (fields are overlaid in place, the
  `uid` doesn't change). So it queries children with
  `WHERE tx_container_parent = <original container uid>`. A translated
  Service Card sitting in a translated container slot must therefore set
  `tx_container_parent` to the **same value as its English sibling**, not
  to a new "translated container" record — and the container wrapper row
  itself (`sitepackage-cardgrid3`, `sitepackage-split84`) never needs a
  translation at all, since it has no visible text.
- **Content Blocks Collection children** (`foreign_table_parent_uid`,
  e.g. `sitepackage_hero_stats`, `sitepackage_servicecard_tech_specs`):
  ordinary TCA `type=inline` IRRE, not language-overlaid at the child
  level. A translated parent record starts with **zero** children of its
  own — its Collection fields must get their own full set of child rows,
  with `foreign_table_parent_uid` pointing at the **translated** parent's
  own uid.

Getting this backwards in either direction produces silently-empty
sections (missing tech specs / stats / tags) rather than an error, so if
a future translated record's Collection field renders empty, check which
of these two rules applies before assuming the data is wrong.

One more schema detail worth recording: `tt_content`'s language-parent
column is `l18n_parent` (legacy naming), not `l10n_parent` — `pages` uses
`l10n_parent`. Both tables have `l10n_source` (correct modern naming) in
addition. Mixing these up is a silent no-op insert into a nonexistent
column, caught immediately by MySQL's "Unknown column" error, not a
runtime bug — but worth getting right the first time.

### What got translated, and what deliberately didn't

Translated: all page titles/slugs/meta descriptions/SEO titles for all 5
pages (Home→`/`, Services→`/sluzby/`, Case Studies→`/pripadove-studie/`,
About→`/o-mne/`, Contact→`/kontakt/`); Home (hero heading, stat strip,
both bento cards + their tech specs); Services (intro + all 3 cards +
tech specs); Case Studies (eyebrow header + all 6 cards + tech tags);
Contact (intro + both sidebar cards' body copy).

**Deliberately kept in English on both languages** — treated as stylized
"system chrome" that's part of the brand identity itself (matching how
`TYPO3_DEV_SPEC` the brandmark never translates either), not literal
content: nav labels (INDEX/SERVICES/REPO/CONTACT), HIRE_ME, footer
labels (v1.0.0/STK_STATUS/GPG_KEY/LEGAL/SYSTEM_STABLE), status badges
(STABLE/CRITICAL/ARCHIVED — also avoids needing language-specific CSS
class mapping, since `badge--{data.status}` keys directly off the stored
enum value), and the TECH_SPECS / SCALE // METRICS section labels inside
Service Card and Project Card. If this call turns out wrong, these are
all single hardcoded strings in their respective Fluid templates or
fixed TCA `Select` items — easy to find and change, not a structural
decision baked in anywhere else.

**Contact Form is the one exception that *did* need translating despite
being template-hardcoded**: unlike the "system chrome" labels above,
form field labels are functional instructions a real visitor has to
understand to use the form, so English-only wasn't acceptable there.
Converted the 4 hardcoded strings to `<f:translate key="{cb:languagePath()}:field.x" />`
and added `sk.labels.xlf` next to the block's `labels.xlf` — this is the
correct general pattern for localizing any future Content Block's
template-hardcoded (non-database) strings, worth reusing rather than
reinventing per-block.

**About page not translated**: still holds Session 3's old-schema
placeholder content (not yet rebuilt on the current design at all — see
Session 4/5 notes). Its page-level fields (title/slug/meta) are
translated so `/sk/o-mne/` has correct Slovak metadata and a working
Slovak URL, but the body content falls back to English via
`fallbackType: fallback` — confirmed this renders as a clean degradation
(translated `<title>`, English `<h1>`/body), not broken output. Translate
its content the same session About itself gets rebuilt on the new
design, not before.

### Verification performed

- Curled `/sk/`, `/sk/sluzby/`, `/sk/pripadove-studie/`, `/sk/o-mne/`,
  `/sk/kontakt/`, and `/sitemap.xml` — all 200.
- `content-blocks:lint` — clean.
- Grepped all 4 fully-translated SK pages for CDN references — none
  (font/token work from Session 4 is language-independent, as expected,
  but worth re-checking after adding a language since it's a plausible
  place for a regression).
- Confirmed the language toggle's `href` via curl on both `/` and
  `/services/` — points to `/sk/` and `/sk/sluzby/` respectively (not a
  flat "always go to Slovak homepage" link), and that `active`/`inactive`
  CSS classes swap correctly depending on which language is current.
- Screenshotted all 4 translated pages in-browser — Home, Services, Case
  Studies, Contact all render fully in Slovak with correct nav
  highlighting, correct status badge colors, correct XLIFF-driven form
  labels, no layout breakage from longer Slovak text strings (diacritics
  render correctly — confirms the `latin-ext` font subsetting from
  Session 4 was the right call, not just theoretical).
- Curled `/sk/o-mne/` specifically to confirm the fallback degrades
  cleanly (translated title, English body) rather than erroring or
  showing a blank page.
