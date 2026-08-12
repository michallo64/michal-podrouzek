# Session brief: wire the Stitch design into sitepackage_base / sitepackage_promo

Confirmed final design direction: clinical/spec-sheet aesthetic ("TYPO3_DEV_SPEC"),
as exported from Google Stitch (DESIGN.md + HTML screens you can find /Volumes/www/promo_showcase/stitch_typo3_engineering_systems).
This supersedes the earlier warm/friendly design brief — do not use that version.

Builds on: promo-site-package-split-brief.md (sitepackage_base +
sitepackage_promo split) and promo-site-content-blocks-brief.md (Content
Blocks + b13/container). This session adapts those to match the confirmed
design instead of the original placeholder styling.

## Critical: do not ship the Tailwind CDN script

The exported HTML includes `<script src="https://cdn.tailwindcss.com">`.
This is a Stitch preview convenience only — do NOT include it in the
TYPO3 build. It compiles CSS client-side on every request, which
directly conflicts with the project's PageSpeed/SEO goals. Instead:

- Extract the full color, typography, spacing, and border-radius token
  set from DESIGN.md's frontmatter into CSS custom properties in
  sitepackage_promo (this is the brand layer per the package split)
- Hand-write the actual CSS rules the exported HTML implies (borders,
  hover states, grid layouts) as real stylesheet rules, not Tailwind
  utility classes at runtime
- Self-host the three typefaces (IBM Plex Sans, IBM Plex Mono, Inter)
  and Material Symbols Outlined rather than loading from Google Fonts
  CDN — same performance rationale

## Design tokens (from DESIGN.md, confirmed direction)

Colors — map directly to CSS custom properties in sitepackage_promo:
- --color-background / --color-surface: #f7f9fe
- --color-surface-container-lowest: #ffffff
- --color-on-surface (ink/text): #181c1f
- --color-on-surface-variant (secondary text): #3f4942
- --color-primary (signal green): #005535
- --color-primary-fixed (light green accent, e.g. PCB nodes): #a6f3c6
- --color-tertiary (signal orange, primary CTA): #882700
- --color-outline-variant (hairline borders): #bfc9c0 (border rule
  color #E3E6EB as used in hairline-grid background)
- Full palette is in DESIGN.md frontmatter — port all of it, not just
  the subset used in these five screens, so later pages have the full
  system available

Typography — three-tier system, self-hosted:
- Display/headings: IBM Plex Sans, 600/700 weight
- Body: Inter, 400 weight
- Labels/data/mono: IBM Plex Mono, 500/700 weight, uppercase for
  eyebrow labels, slightly increased letter-spacing at small sizes

Shape: sharp corners everywhere (0px radius) except small tag/badge
pills which may use full pill radius — per DESIGN.md "Shapes" section.
No shadows for elevation — depth comes from hairline borders (1px,
#E3E6EB) and background tonal shifts only.

## Content Blocks to build/extend (in sitepackage_base)

Some blocks from the earlier content-blocks brief need extending based
on what the actual design shows:

1. **Hero** — extend with: the PCB-trace "upgrade path" graphic (v9
   through v13, nodes on a horizontal line, per index_home_updated),
   stat strip (3 stats, "//"-separated), CTA button with arrow icon
2. **Service Card** — extend with: Material Symbols icon field, and a
   "TECH_SPECS" bullet list (not just a plain description) — see
   services_capabilities screen for the exact structure (icon,
   headline, description, divider, uppercase label, bulleted list in a
   light-gray inset box)
3. **Case Study / Project Card** — extend with: a status badge field
   (enum: STABLE / CRITICAL / ARCHIVED, styled per DESIGN.md's Status
   Indicators component), hour-range field, and a tech-tag list
   (multiple small tag pills, not a single field) — see
   logs_case_studies_updated screen
4. **CTA Banner** — style per the orange "tertiary" button treatment,
   sharp corners, arrow icon on hover-shift

New component needed (not in original brief):
5. **Contact Sidebar Card** — small reusable card with a headline and
   either a direct-contact link (icon + email, arrow reveals on hover)
   or a code/mono content block (see the GPG_KEY_PUB block on the
   Contact screen) — build as a generic "small info card" content
   block that can hold either pattern, since the contact page uses two
   of them side by side

## Container grids (b13/container) — extend

The earlier brief specified a uniform 3-column grid and a stacked
single column. The actual design also needs:
- **Asymmetric 8/4 split** — used on the Home bento section (Core
  Upgrades spans 8 cols, Extbase Dev spans 4) and the Contact page
  (form spans 8, sidebar cards span 4). Add this as a third container
  preset.

## Navigation / shared components
- Top nav: logo/wordmark left, INDEX / SERVICES / REPO / CONTACT
  center-right, EN // SK language toggle, HIRE_ME button (tertiary
  orange, links to mailto or contact page)
- Footer: wordmark + copyright left, link columns right (v1.0.0,
  STK_STATUS, GPG_KEY, LEGAL) — build as a Fluid partial, not a
  content block, since it's structural/repeated on every page rather
  than editor-editable content
- These are shared across all pages — implement once in the site
  package's main layout, not per-page

## Pages confirmed in this export
- Home (index) — hero + bento services teaser
- Services (SYSTEM_SERVICES) — 3-col grid, hairline-grid background
  texture on the page canvas
- Case Studies (LOGS) — 3-col grid, redaction notice copy already
  present ("Client identifying data has been redacted")
- About/CV (SYSTEM_ABOUT) — not shown in this upload's code samples;
  confirm structure against the uploaded screenshot before building
- Contact (REQUEST_PROPOSAL) — 8/4 split, form + sidebar cards

## About/CV page (system_about_cv_updated)

Two-column layout: 4-col left sidebar, 8-col right column (use the 8/4
container preset, mirrored).

Left column, two stacked cards:
- **Core Identity card**: icon + "CORE_IDENTITY" header, then two
  labeled fields — "WHAT_I_DO" (one bold sentence) and "ACADEMIC_SYS"
  (education, two lines). Build as a simple two-field content block,
  not part of the CV timeline structure.
- **Tech Registry card**: icon + header, then four labeled groups
  (ENV.CORE, ENV.FRONTEND, ENV.TOOLS, ENV.INTEGRATION), each a wrapped
  row of small bordered tag pills. Extend the earlier "Tech Badge List"
  block to support multiple named groups rather than one flat list.

Right column: **Timeline** content block/list — vertical hairline line
down the left edge, each entry a small square node (filled/bordered
depending on current vs past), a bold mono date range, role title,
company/location in small mono, and an optional description line. This
matches the "Timeline Entry" block already scoped — just confirm the
node styling (square with 2px border, not circular) and the vertical
connecting line are implemented per this export, not the original
brief's description.

Header above both columns: small locale indicator (EN/SK), circular-
crop photo (grayscale, color on hover) at modest size — not a large
hero image, consistent with the earlier "keep photo modest" direction
— page title "CURRICULUM_VITAE", and an identity line with a fingerprint
icon showing role label + version-style tag + upgrade count. NOTE: the
role label text ("SENIOR_SYSTEMS_ARCHITECT") is pending confirmation —
see flag above; hold this specific field until confirmed rather than
hardcoding it.

## Deliverable for this session
- CSS custom properties file in sitepackage_promo with the full design
  token set (no Tailwind CDN dependency)
- Self-hosted font setup (IBM Plex Sans, IBM Plex Mono, Inter, Material
  Symbols Outlined)
- Content Blocks extended/built per the field lists above
- Third container grid preset (8/4 asymmetric split) registered
- Shared nav + footer partials matching the exported structure
- Home and Services pages assembled and visually matching the export;
  Case Studies and Contact can follow once the above is confirmed
  working