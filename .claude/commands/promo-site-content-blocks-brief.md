# Session brief: Content Blocks + Container grids

Continuing the promo site build (see promo-site-claude-code-brief.md for
project context: TYPO3 13 LTS, composer-based, sitepackage_promo package).

## Install
- `composer require friendsoftypo3/content-blocks` (current stable, 1.0+,
  FriendsOfTYPO3 namespace — extension key `content_blocks`)
- `composer require b13/container`
- Activate both in the Extension Manager
- If running classic (non-Composer) mode, apply the .htaccess/nginx rule
  from the Content Blocks docs to block direct access to the
  ContentBlocks resource folders — skip if fully Composer-mode with
  public/ web root already locked down

## Content Blocks to create
Each as its own content-block folder (config.yaml + assets/templates/
language) inside sitepackage_promo, following the standard Content
Blocks folder convention:

1. **Hero** — fields: heading (text), subheading (text), CTA label (text),
   CTA link (link). Used once, on Home.
2. **Service Card** — fields: icon (select/asset), title (text),
   description (textarea). Repeated inside a container grid on Services.
3. **Case Study Card** — fields: project type (text), tech stack (comma
   list or category relation), scale/hour range (text), description
   (textarea). NO client name field — deliberately omitted per project
   constraints. Repeated inside a container grid on Case Studies.
4. **Stat Strip** — fields: repeatable collection of {number, label}
   pairs (3–4 items). Used once, Home or About.
5. **Timeline Entry** — fields: year/date range (text), title (text),
   description (textarea). Repeated (not in a container grid — stacked
   list) on About/CV.
6. **Tech Badge List** — fields: group label (text), badges (repeatable
   text collection). Used on Services and/or About.
7. **CTA Banner** — fields: heading (text), body (textarea), button
   label (text), button link (link). Reusable across page bottoms.

## Container grids (b13/container)
Define container configurations for:
- **3-column card grid** — desktop 3 cols, tablet 2, mobile 1. Used to
  wrap Service Cards and Case Study Cards.
- **Single column / stacked** — for Timeline Entries and CTA Banner,
  where no grid is needed but consistent spacing/max-width is.

Register allowed content types per container slot so editors can only
drop the intended Content Block into each grid (e.g. only Service Card
inside the Services grid container).

## Fluid/templating conventions
- Each Content Block's template.html lives in its own block folder, kept
  small — no logic beyond simple f:if/f:for, matching the site package's
  existing clean-code intent
- Reuse existing typography/spacing variables from the site package's
  CSS rather than introducing new ad hoc styles per block
- Tag each block's root element with a stable CSS class
  (`ce-hero`, `ce-service-card`, etc.) so the design pass (Claude Design
  output) can be mapped onto them predictably

## Deliverable for this session
- Content Blocks + b13/container installed and configured
- All 7 blocks scaffolded with fields and minimal placeholder Fluid
  output (unstyled is fine — design pass comes later)
- Two container grid presets registered and usable in the backend
- Backend layout / allowed content types wired so Home, Services, Case
  Studies, and About pages can be assembled from these blocks