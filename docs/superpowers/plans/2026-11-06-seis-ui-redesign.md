# SEIS Cohesive UI Redesign Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Apply one cohesive, accessible warm-academic visual system to every SEIS login and admin surface.

**Architecture:** Keep the existing PHP templates and layered CSS structure. Establish authoritative tokens in `variables.css`, make core CSS consume those tokens, then normalize page modules and make only the dashboard markup changes needed for hierarchy and empty-state improvements.

**Tech Stack:** PHP 8.2, HTML5, Bootstrap 5/Bootstrap Icons, custom CSS, Playwright browser inspection.

**Spec:** `docs/superpowers/specs/2026-11-06-seis-ui-design.md`

## Global Constraints

- Preserve routes, data access, roles, form behavior, and application copy unless the copy is an empty-state instruction.
- Use Playfair Display only for page/section headings and Source Sans 3 for interface text.
- Semantic colors appear only for semantic states; ordinary metrics use the shared brown/amber treatment.
- All body text and interactive controls must meet WCAG AA contrast and expose visible keyboard focus.
- Do not add a dependency or restructure the PHP application.
- The project directory is currently untracked, so do not create Git commits that could capture unrelated files.

## Review Focus

- Narrow screens: navigation and content must remain usable without horizontal page overflow.
- Dense tables: headers, badges, and action buttons must remain legible and aligned.
- Empty datasets: dashboard and list pages must show a clear, useful empty state.
- Keyboard use: links, fields, buttons, and pagination must display a visible focus ring.
- Semantic states: success, warning, error, and info styling must remain distinguishable without relying on color alone.

---

### Task 1: Establish the shared design system

**Files:**
- Modify: `assets/css/core/variables.css`
- Modify: `assets/css/core/base.css`
- Modify: `assets/css/core/layout.css`
- Modify: `assets/css/core/components.css`

**Interfaces:**
- Produces: shared `--color-*`, spacing, radius, shadow, and focus tokens consumed by every module.

- [ ] Replace the legacy palette with the approved primary, secondary, accent, neutral, and semantic token pairs while retaining temporary `--clr-*` aliases for compatibility.
- [ ] Normalize global typography, links, alerts, badges, focus-visible states, selection, and reduced-motion behavior in `base.css`.
- [ ] Apply accessible topbar/sidebar/main-content treatments and consistent icon slots and responsive behavior in `layout.css`.
- [ ] Normalize buttons, cards, tables, fields, pagination, stat cards, fill bars, quick actions, and empty states in `components.css` using shared tokens only.
- [ ] Run token and hard-coded-color searches; expected result is that core component colors resolve through tokens except intentional SVG data and transparent overlays.

### Task 2: Align login and page modules

**Files:**
- Modify: `assets/css/module/auth.css`
- Modify: `assets/css/module/dashboard.css`
- Modify: `assets/css/module/courses.css`
- Modify: `assets/css/module/enrollment.css`
- Modify: `assets/css/module/reports.css`
- Modify: `assets/css/module/settings.css`
- Modify: `assets/css/module/students.css`
- Modify: `assets/css/module/users.css`

**Interfaces:**
- Consumes: Task 1 design tokens and component primitives.
- Produces: page-specific layouts with no private palette or inconsistent radius/shadow rules.

- [ ] Rework the login composition with the shared surfaces, accessible form controls, primary action, and restrained decorative shapes.
- [ ] Convert all module-local colors, chips, progress states, and panels to shared brand or semantic tokens.
- [ ] Standardize local spacing, radii, and shadows; remove decorative one-off gradients and pill values where they do not communicate status.
- [ ] Search module CSS for raw hex colors and legacy opacity colors; expected result is only token definitions or intentional asset literals.

### Task 3: Improve dashboard hierarchy and empty states

**Files:**
- Modify: `admin/dashboard.php`
- Verify: `includes/sidebar.php`

**Interfaces:**
- Consumes: normalized stat cards, buttons, tables, and empty-state classes.
- Produces: four visually consistent metrics, title-level actions, a 2:1 content grid, and actionable empty states.

- [ ] Remove the redundant quick-link strip because the same destinations already exist in the sidebar and page-header actions.
- [ ] Keep four equal stat cards with the single shared icon treatment.
- [ ] Replace inline header-button and empty-state styling with reusable classes.
- [ ] Add an “Add the first student” action to the student empty state and a consistent course-data empty state.
- [ ] Verify every sidebar navigation link includes one Bootstrap Icon in the fixed icon slot.

### Task 4: Validate all pages and responsive states

**Files:**
- Verify: all `*.php`
- Verify: all `assets/css/**/*.css`

**Interfaces:**
- Consumes: completed UI redesign.
- Produces: evidence that syntax, runtime behavior, accessibility mechanics, and visual layout are intact.

- [ ] Run PHP lint over every PHP file; expect zero syntax errors.
- [ ] Run local authenticated smoke checks for login and representative dashboard, table, form, report, and settings routes; expect HTTP 200 responses.
- [ ] Run the Impeccable detector once over all changed CSS/PHP targets and resolve material findings in one batch.
- [ ] Capture desktop and mobile views of login, dashboard, a dense table, and a form in one inspection pass; fix all visible hierarchy, overflow, contrast, focus, and spacing defects together.
- [ ] Perform one final desktop/mobile confirmation pass and stop polishing once the requested system is visibly consistent.
