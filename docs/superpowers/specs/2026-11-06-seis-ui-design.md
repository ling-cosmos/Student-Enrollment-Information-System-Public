# SEIS Cohesive UI Redesign

## Intent

Unify the existing warm academic identity across login, dashboard, navigation, tables, forms, reports, and empty states. Preserve the brown-and-cream character while eliminating unrelated decorative colors and inconsistent component treatments.

## Direction

- Keep the editorial pairing of Playfair Display headings and Source Sans 3 interface text.
- Use espresso and brown for structure, clay for secondary controls, and amber only for emphasis and primary calls to action.
- Reserve green, amber-brown, red, and slate-blue exclusively for semantic success, warning, error, and informational states.
- Standardize all surfaces around shared tokens for color, spacing, radius, elevation, focus, and typography.

## Component Rules

- Sidebar uses dark espresso, fixed-width Bootstrap Icon slots, subdued cream labels, and one consistent active treatment.
- Topbar uses primary brown with accessible cream text and a compact amber identity mark.
- Cards and tables use near-white surfaces, quiet warm borders, restrained shadows, and 14px outer radii.
- Controls are 40–44px high with 10px radii and visible amber focus rings.
- Stat cards are neutral and use the same amber-tinted icon treatment; semantic colors appear only when the statistic itself communicates state.
- Buttons follow primary, secondary, outline, and semantic variants without page-specific tones.
- Badges use paired semantic foreground/background tokens.
- Empty states use one muted icon, a clear sentence, and a relevant action when one exists.

## Layout

- Use a consistent 4/8/12/16/24/32px spacing scale.
- Keep four equal dashboard metrics, place page actions beside the title, and use a 2:1 desktop split for recent students and course enrollment.
- Collapse navigation into the existing responsive pattern and stack dashboard regions on narrow screens.

## Accessibility

- Maintain at least WCAG AA contrast for all body text and controls.
- Avoid opacity-based text colors for essential labels.
- Provide visible `:focus-visible` states and minimum 40px interactive targets.
- Do not use color as the only status indicator; retain labels and icons.

## Scope

The redesign changes visual styling and minor presentation markup only. Application behavior, routes, data, permissions, and business logic remain unchanged.
