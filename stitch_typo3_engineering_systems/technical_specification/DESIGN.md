---
name: Technical Specification
colors:
  surface: '#f7f9fe'
  surface-dim: '#d7dade'
  surface-bright: '#f7f9fe'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f1f4f8'
  surface-container: '#ebeef2'
  surface-container-high: '#e5e8ec'
  surface-container-highest: '#e0e3e7'
  on-surface: '#181c1f'
  on-surface-variant: '#3f4942'
  inverse-surface: '#2d3134'
  inverse-on-surface: '#eef1f5'
  outline: '#6f7a72'
  outline-variant: '#bfc9c0'
  surface-tint: '#1b6b47'
  primary: '#005535'
  on-primary: '#ffffff'
  primary-container: '#1f6e4a'
  on-primary-container: '#a0edc0'
  inverse-primary: '#8ad7ab'
  secondary: '#555f6d'
  on-secondary: '#ffffff'
  secondary-container: '#d6e0f1'
  on-secondary-container: '#596372'
  tertiary: '#882700'
  on-tertiary: '#ffffff'
  tertiary-container: '#b13500'
  on-tertiary-container: '#ffd3c6'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#a6f3c6'
  primary-fixed-dim: '#8ad7ab'
  on-primary-fixed: '#002112'
  on-primary-fixed-variant: '#005233'
  secondary-fixed: '#d9e3f4'
  secondary-fixed-dim: '#bdc7d8'
  on-secondary-fixed: '#121c28'
  on-secondary-fixed-variant: '#3e4755'
  tertiary-fixed: '#ffdbd0'
  tertiary-fixed-dim: '#ffb59e'
  on-tertiary-fixed: '#390b00'
  on-tertiary-fixed-variant: '#842500'
  background: '#f7f9fe'
  on-background: '#181c1f'
  surface-variant: '#e0e3e7'
typography:
  display-hero:
    fontFamily: IBM Plex Sans
    fontSize: 36px
    fontWeight: '700'
    lineHeight: '1.2'
    letterSpacing: -0.02em
  headline-section:
    fontFamily: IBM Plex Sans
    fontSize: 24px
    fontWeight: '700'
    lineHeight: '1.3'
    letterSpacing: -0.01em
  headline-card:
    fontFamily: IBM Plex Sans
    fontSize: 18px
    fontWeight: '600'
    lineHeight: '1.4'
  body-base:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: '1.5'
  label-mono:
    fontFamily: IBM Plex Mono
    fontSize: 14px
    fontWeight: '500'
    lineHeight: '1.4'
    letterSpacing: 0.02em
  label-tiny:
    fontFamily: IBM Plex Mono
    fontSize: 13px
    fontWeight: '500'
    lineHeight: '1.0'
    letterSpacing: 0.03em
  display-hero-mobile:
    fontFamily: IBM Plex Sans
    fontSize: 28px
    fontWeight: '700'
    lineHeight: '1.2'
spacing:
  unit: 4px
  xs: 0.5rem
  sm: 0.75rem
  md: 1.5rem
  lg: 2rem
  xl: 4rem
  max-width: 72rem
  gutter: 2rem
---

## Brand & Style

This design system is built on the philosophy of **Engineering Precision**. It treats the personal site of a TYPO3 developer not as a marketing brochure, but as a technical spec sheet—disciplined, structured, and authoritative. 

The aesthetic is a hybrid of **Minimalism** and **Brutalism**, stripped of decorative "fluff" like gradients, soft shadows, or organic blobs. It uses high-contrast "Ink on Paper" visuals and a strict 1px hairline grid to communicate deep system competence and production-grade quality. Every element exists for a functional reason, emphasizing architectural clarity and structural integrity.

- **Atmosphere:** Clinical, professional, reliable, and precise.
- **Visual Language:** Sharp edges, dense metadata, clear eyebrow labels, and deliberate alignment.
- **Audience:** Fellow engineers, technical project managers, and enterprise clients seeking structured expertise.

## Colors

The palette is derived from technical documentation and architectural blueprints. 

- **Primary (Signal Green):** Used for technical indicators, active state badges, and verified metrics. It signals system health and engineering precision.
- **Secondary (Slate):** Reserved for metadata, subtitles, and secondary information to provide a visual hierarchy that doesn't distract from core content.
- **Tertiary (Signal Orange):** A high-priority trigger used for primary calls to action and critical interactive highlights.
- **Neutral (Ink):** The primary text color, providing maximum contrast against the paper-like background.
- **Background (Paper):** A slightly off-white, tactile canvas that reduces eye strain and reinforces the "spec sheet" aesthetic.

**Application Note:** Use the `border_hairline` (#E3E6EB) for all structural dividers. Avoid any color fills that aren't strictly functional.

## Typography

The system employs a three-tier typographic hierarchy to differentiate between headers, content, and data.

1.  **IBM Plex Sans (Headings):** Technical and geometric. Used for display titles and section headers to establish a "tech-first" impression.
2.  **Inter (Body):** A neutral, highly legible sans-serif for continuous reading of project descriptions and experience.
3.  **IBM Plex Mono (Utility):** Monospaced fonts are used for all "data" elements—dates, tags, technical specs, and eyebrow labels.

**Rules:**
- Maintain tight line heights on headings to keep the design compact.
- Use uppercase for `label-mono` when used as eyebrows to create a clear "Field Label" appearance.
- Tracking should be slightly increased for monospaced elements to ensure legibility at small sizes.

## Layout & Spacing

The layout is governed by a **fixed grid** with a hard max-width of 72rem (1152px), ensuring content remains readable and focused.

- **Grid Model:** A strict 12-column system is used for desktop. 
  - **Cards:** Usually span 4 columns (3-up) or 6 columns (2-up).
  - **Sidebar Content:** Spans 4 columns against 8 columns of main content.
- **Rhythm:** A 4px baseline unit drives all spacing. Use `md` (24px) for internal padding of cards and `lg` (32px) for gaps between major sections.
- **Responsive Reflow:**
  - **Desktop (>1024px):** Full 12-column grid.
  - **Tablet (768px - 1024px):** 2-column card layouts, reduced side margins (24px).
  - **Mobile (<768px):** Single column stack, side margins (16px).

Whitespace is "purposeful"—never empty for the sake of it, but used to separate logical technical blocks.

## Elevation & Depth

This design system rejects the use of shadows to create depth. Instead, it uses **Tonal Layers** and **Bold Borders**.

- **Surface Tiers:** The base layer is `background_paper`. Elements that require focus (like cards or inputs) use a pure white background.
- **Borders:** Depth is defined by 1px solid hairline borders (`#E3E6EB`). There is no "elevation" in the traditional sense; the UI is intentionally flat and architectural.
- **Interaction:** On hover, depth is signaled not by a shadow, but by a subtle shift in border color or background tint (e.g., changing a border from hairline gray to `primary_color`).

## Shapes

The shape language is strictly **Sharp (0px)**. 

- All containers, buttons, and input fields must have square corners. 
- The only exception is the `label-tiny` badges (tags), which can optionally use a `pill` radius (999px) to differentiate "tags" from "structural blocks," though a sharp 2px radius is preferred for consistency with the technical spec aesthetic.
- Do not use rounded corners on images or cards.

## Components

### Buttons
- **Primary:** Solid `tertiary_color` (Signal Orange) with white `label-mono` text. Sharp corners.
- **Secondary:** 1px `neutral_color` border, no fill, sharp corners.
- **State:** Hover effect involves a solid fill of `neutral_color`.

### Cards (Service/Project)
- **Structure:** 1px hairline border, white background.
- **Content:** Header in `headline-card`, description in `body-base`. 
- **Footer:** Technical stack tags rendered in `label-tiny` with a light gray background (#F0F3F6).

### Inputs
- **Field:** 1px border (#E3E6EB), pure white background, sharp corners.
- **Label:** `label-mono` placed strictly above the input field in `secondary_color`.
- **Focus:** 1px solid `primary_color` (Signal Green) border.

### Status Indicators / Badges
- Used for "LTS", "Composer", or "PHP version". 
- Styling: `label-tiny` font, `primary_color` text, and a 1px `primary_color` border.

### Timeline
- A vertical hairline border on the left.
- Each entry is a block with a `label-mono` date range at the top, emphasizing chronological precision.