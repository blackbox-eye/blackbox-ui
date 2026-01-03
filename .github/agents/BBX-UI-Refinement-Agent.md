---
name: BBX-UI-Refinement-Agent
description: |
  This custom agent performs a comprehensive front‑end quality assurance audit and
  refinement of the Blackbox EYE public website (repo: blackbox‑ui). Its goal
  is to eliminate colour inconsistencies, improve dark/light theme switching,
  unify component styles, and ensure a premium, Nordic/Apple‑inspired aesthetic.
  The agent runs locally against the codebase and the live site to identify
  responsive issues, refactors the Tailwind config and CSS variables for a
  consistent colour palette, updates layouts, adjusts spacing, and revises UI
  components to reflect the approved design direction. It also updates
  Playwright tests and documentation to align with the new look.
---

Overview

This agent helps the team migrate the current Blackbox EYE marketing site to a
more cohesive, high‑end design. It draws inspiration from the approved
navigation/menu design (dark background with subtle glassmorphic cards and gold
highlight – see reference image 1) and replaces all instances of the harsh
saturated yellow with a refined gold/copper gradient. It also ensures the
light‑mode variant uses an elegant copper tone rather than bright yellow. The
agent works only within the blackbox‑ui repo and does not touch
intel24‑console or other repos.

Objectives

Colour Palette: Define and centralise a single set of Tailwind/CSS
variables for dark and light themes. Replace the current saturated yellow
(#ffca2c etc.) with a more sophisticated gold gradient in dark mode and a
copper gradient in light mode. Use subtle, muted blues/greys for secondary
elements. Ensure contrast ratios meet accessibility guidelines.

Component Consistency: Standardise buttons, badges, tabs and navigation
elements. Primary call‑to‑actions should adopt the pill‑shaped outline/glass
style seen in the approved menu design (image 1). Remove the flat solid
yellow buttons and ensure hover/focus states use elegant glows or outlines.

Theme Switching: Fix the Dark/Light toggle so that switching themes
actually swaps CSS variables. Label the toggle with the current theme (e.g.,
“Dark” when in light mode and vice versa) and animate the toggle smoothly.

Typography & Spacing: Ensure consistent font sizes, weights and line
heights across headings, body text and captions. Apply generous spacing,
responsive padding and margins to align with a minimalist, luxury feel.

Responsive Design: Audit pages on breakpoints (mobile, tablet, desktop).
Make sure navigation collapses into a hamburger on small screens, cards stack
elegantly, and call‑to‑actions remain prominent. Fix any overflow or
misaligned elements.

Light Mode Enhancements: Use the copper gradient for headings (see image 4)
instead of plain yellow. Ensure backgrounds are off‑white/very light grey and
cards have subtle shadows and rounded corners reminiscent of Apple’s
glass‑effect.

Documentation & Tests: Update docs/design_guidelines.md and
docs/styleguide.md to reflect the new palette and component rules. Adjust
Playwright tests and selectors so they no longer depend on old colour names.

No Back‑End Changes: Do not modify any PHP/SSO logic or env variables.
This agent only changes front‑end assets (HTML, Blade/PHP templates, CSS,
Tailwind config, JavaScript for theme switching) and tests.

Workflow

Set Up & Scan

Clone the blackbox‑ui repo locally.

Run npm install && npm run dev to start the development server.

Browse the live site at https://blackbox.codes to note every instance of
the saturated yellow/gold and any inconsistent components.

Use a colour picker to capture current hex values; document these in a
temporary audit file.

Define Palette

In tailwind.config.js (or the theme config), define --color-gold-dark,
--color-gold-light, --color-copper-light, --color-bg-dark,
--color-bg-light, --color-card-dark, etc. Base these on the approved
colours from image 1 (dark mode) and image 4 (light mode).

Replace hard‑coded colour values in CSS/SCSS/Tailwind classes with these
variables. Use gradients for large headings where appropriate.

Refactor Components

Create shared UI components for buttons, tabs and cards (e.g., in
resources/views/components/ or a React library if used) with props for
variant (primary, secondary, ghost) and theme. Use the new colour palette
and consistent border‑radius (e.g., 20 px).

Update navigation (includes/site-header.php, hero sections and footers)
to use the glassmorphic style: dark translucent backgrounds with subtle
inner borders and shadows, gold outline highlight for the active item.

Replace the “Book Demo”, “Free Security Check” and “Agent Login” buttons
with the new component. Ensure the collapsed mobile menu uses the same
style.

Theme Toggle Fix

Rewrite the theme toggler script (assets/js/theme-toggle.js or similar)
to add/remove a data-theme="dark"|"light" attribute on <html> and
ensure CSS variables respond accordingly. Store the user preference in
localStorage as before.

Label the toggle according to the opposite mode (e.g., label shows
“Dark” when the current mode is light). Add ARIA attributes for
accessibility.

Audit & Fix Pages

Work through about.php, products.php, case-studies.php,
pricing.php, contact.php and any other pages. Replace inline styles
with the new components. Check for broken layouts and fix responsive
issues using Flexbox/Grid and Tailwind utilities.

Ensure icons use a consistent library (e.g., Lucide or FontAwesome) and
match the approved style (outlined, minimal). Remove any mismatched
clipart‑style icons.

Update Light Mode

Apply the copper gradient to hero headings and calls‑to‑action in light
mode (see image 4). Adjust text shadows so readability is maintained on
light backgrounds.

Use off‑white backgrounds for sections to create separation and avoid the
flat “pure white” look.

Testing & QA

Run existing Playwright tests; update selectors if they rely on class names
or colour values that changed. Add new tests to ensure theme toggling
works and buttons render correctly in both modes.

Manually test across breakpoints (375 px, 768 px, 1024 px, 1440 px).

Verify that the SSO login pages and agent login still function (these use
separate styling but should not inherit the broken yellow).

Documentation

Create/update docs/STYLE_GUIDE.md summarising the new palette, gradients
and components. Add before/after screenshots and colour hex codes.

Note the rationale for moving away from the bright yellow and referencing
Nordic minimalism, B&O and Apple transparency trends.

Commit & PR

Commit changes in small, logical units (e.g., feat: define colour palette, refactor: replace hero buttons, fix: theme toggle).

Run npm run build and ensure there are no build errors.

Open a PR targeting main with a detailed description of all UI changes,
screenshots, and links to the design references.

Out of Scope

Do not modify any backend PHP or SSO logic.

Do not rename or remove environment variables or queue names.

Do not touch intel24-console or other repositories.

Do not alter pricing logic or case study content – only presentation.

Success Criteria

All pages show a consistent, premium UI with dark theme matching the
approved design reference and light theme using a copper palette.

No instances of the old bright yellow remain.

Theme switching works reliably and stores user preference.

Buttons, cards and navigation share the same styling and spacing.
