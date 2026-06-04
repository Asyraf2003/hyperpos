# 0012 - Cashier Dark Light Theme Readiness Blueprint

## Metadata

- Date: 2026-06-04
- Area: Cashier UI
- Status: Audit recorded, implementation not started
- Related blueprint: `docs/03_blueprints/ui/0011_cashier_stepper_mobile_ui_redesign.md`
- Active concern: Cashier UI supports the global light theme visually, but is not ready for consistent dark/light theme switching.

## FACT

Global theme infrastructure already exists.

- `resources/views/layouts/app.blade.php` loads both `assets/compiled/css/app.css` and `assets/compiled/css/app-dark.css`.
- `resources/views/layouts/app.blade.php` loads `assets/static/js/initTheme.js` before the app shell and `assets/static/js/components/dark.js` after the app shell.
- `public/assets/static/js/components/dark.js` writes `data-bs-theme` to the document element and persists the selected theme in local storage.
- `resources/views/layouts/partials/sidebar-cashier.blade.php` includes the dark/light toggle with id `toggle-dark`.

The cashier problem is not absence of global dark mode. The proven problem is local cashier UI overriding the global theme with hardcoded light colors and light-only Bootstrap utility classes.

## Scope In

- Cashier dashboard.
- Cashier product search.
- Cashier account preferences.
- Cashier note index/history.
- Cashier note detail.
- Cashier create/edit workspace.
- Cashier-visible payment and refund surfaces.
- Shared note partials that render on cashier detail surfaces.
- Cashier JavaScript that renders markup or toggles light-only classes dynamically.

## Scope Out

- Admin UI theme readiness.
- Backend transaction behavior.
- Payment, refund, correction, and note lifecycle domain changes.
- Database schema changes.
- Report/export surfaces.
- Visual implementation patch before the audit map is accepted.

## Current Proven Hotspots

### Global Shell

Global shell is theme-capable, but still contains light-flavored buttons in fallback/back actions.

- `resources/views/layouts/app.blade.php`
- `resources/views/layouts/partials/sidebar-cashier.blade.php`
- `public/assets/static/js/components/dark.js`

### Dashboard Area

The dashboard area has hardcoded light card backgrounds, light text colors, and light shadows.

- `resources/views/cashier/dashboard/index.blade.php`
- `resources/views/cashier/dashboard/product-search.blade.php`
- `resources/views/cashier/dashboard/account-preferences.blade.php`
- `public/assets/static/js/pages/cashier-dashboard.js`

Observed patterns:

- `background: #fff`
- `color: #0f172a`
- `color: #64748b`
- light-only card border/shadow values

### Cashier Note Index

The note index defines local CSS variables, but the variable values are light-only and do not switch under `data-bs-theme=dark`.

- `resources/views/cashier/notes/index.blade.php`
- `resources/views/cashier/notes/partials/filter-drawer.blade.php`
- `public/assets/static/js/pages/cashier-note-index.js`

Observed patterns:

- `--note-card: #ffffff`
- `--note-muted: #64748b`
- `--note-text: #0f172a`
- `btn-light-secondary`
- AJAX table rows rendered with `text-muted`

### Cashier Note Detail

The detail page is the largest light-theme override. It forces page background, card background, table background, badges, and buttons to light colors.

- `resources/views/cashier/notes/show.blade.php`
- `resources/views/shared/notes/show.blade.php`
- `resources/views/cashier/notes/partials/note-overview.blade.php`
- `resources/views/cashier/notes/partials/note-rows-table.blade.php`
- `resources/views/cashier/notes/partials/billing-table.blade.php`
- `resources/views/cashier/notes/partials/payment-actions.blade.php`
- `resources/views/cashier/notes/partials/add-rows-form.blade.php`
- `resources/views/cashier/notes/partials/payment-form.blade.php`
- `resources/views/cashier/notes/partials/refund-form.blade.php`
- `resources/views/cashier/notes/partials/payment-modal.blade.php`
- `resources/views/cashier/notes/partials/refund-modal.blade.php`
- `resources/views/cashier/notes/partials/note-revision-timeline.blade.php`
- `resources/views/cashier/notes/partials/correction-actions.blade.php`
- `resources/views/cashier/notes/partials/correction-history.blade.php`
- `resources/views/shared/notes/partials/header-summary.blade.php`
- `resources/views/shared/notes/partials/versioning-compact.blade.php`
- `resources/views/shared/notes/partials/payment-summary-actions.blade.php`

Observed patterns:

- `background: #f0ebf8`
- `--detail-card: #ffffff`
- `--detail-muted: #5f6368`
- `--detail-text: #202124`
- `background: #fff !important`
- `--bs-table-bg: #fff`
- `--bs-table-striped-bg: #fff`
- `bg-light`
- `bg-light-subtle`
- `bg-light-secondary`
- `bg-light-info`
- `text-dark`
- `btn-light-secondary`
- `btn-light-primary`

### Cashier Workspace

The workspace already has local tokens, but the token values are light-only and many subcomponents still use hardcoded light backgrounds and light button classes.

- `resources/views/cashier/notes/workspace/create.blade.php`
- `resources/views/cashier/notes/workspace/partials/info-card.blade.php`
- `resources/views/cashier/notes/workspace/partials/rincian-card.blade.php`
- `resources/views/cashier/notes/workspace/partials/note-description-card.blade.php`
- `resources/views/cashier/notes/workspace/partials/review-payment-card.blade.php`
- `resources/views/cashier/notes/workspace/partials/payment-modal.blade.php`
- `resources/views/cashier/notes/workspace/partials/payment-modal-left.blade.php`
- `resources/views/cashier/notes/workspace/partials/payment-modal-right.blade.php`
- `resources/views/cashier/notes/workspace/partials/payment-modal-cash.blade.php`
- `resources/views/cashier/notes/workspace/partials/payment-modal-footer.blade.php`
- `resources/views/cashier/notes/workspace/partials/payment-modal-summary.blade.php`
- `resources/views/cashier/notes/workspace/partials/refund-modal.blade.php`
- `resources/views/cashier/notes/workspace/partials/templates/product.blade.php`
- `resources/views/cashier/notes/workspace/partials/templates/service.blade.php`
- `resources/views/cashier/notes/workspace/partials/templates/service-store-stock.blade.php`
- `resources/views/cashier/notes/workspace/partials/templates/service-external.blade.php`

Observed patterns:

- `background: #f0ebf8`
- `--workspace-card: #ffffff`
- `--workspace-border: #dadce0`
- `--workspace-muted: #5f6368`
- `--workspace-text: #202124`
- `--workspace-accent: #673ab7`
- `--workspace-accent-soft: #ede7f6`
- `background: #fff`
- `color: #3c4043`
- `border-bottom: 1px solid #eceff1`
- `btn-light`
- `btn-light-primary`
- `btn-light-secondary`
- `btn-light-danger`
- `bg-light text-dark border`

### Cashier JavaScript

Some cashier JS renders light-only classes or toggles light-only button states at runtime.

- `public/assets/static/js/pages/cashier-note-workspace/boot.js`
- `public/assets/static/js/pages/cashier-note-workspace/payment-flow.js`
- `public/assets/static/js/pages/cashier-note-workspace/rows.js`
- `public/assets/static/js/pages/cashier-note-workspace/search.js`
- `public/assets/static/js/pages/cashier-note-index.js`
- `public/assets/static/js/pages/cashier-note-payment.js`
- `public/assets/static/js/pages/cashier-note-refund.js`
- `public/assets/static/js/pages/cashier-note-add-rows.js`
- `public/assets/static/js/pages/cashier-note-create.js`
- `public/assets/static/js/pages/cashier-note-workspace.js`
- `public/assets/static/js/pages/cashier-dashboard.js`

Observed patterns:

- `button.classList.add("btn-light", "text-dark")`
- `button.classList.toggle("btn-light", !active)`
- `button.classList.toggle("text-dark", !active)`
- rendered empty/loading rows with `text-muted`
- rendered action buttons with `btn-light-danger`

## GAP

The audit has identified the main dark/light blockers, but implementation still needs a final file-by-file patch map before changing UI.

Known remaining checks:

- Browser proof for light and dark theme has not been captured.
- Mobile viewport proof has not been captured.
- Exact contrast values have not been measured.
- Some shared note partials may also be rendered by admin pages, so patching those files must preserve admin behavior or be scoped under cashier-specific wrappers.
- Old JS files such as `cashier-note-create.js`, `cashier-note-add-rows.js`, and `cashier-note-workspace.js` may be legacy or still reachable. Their route usage must be confirmed before removal or deep patching.

## Decision

Create a cashier theme readiness track separate from the stepper redesign blueprint.

Reason:

- `0011_cashier_stepper_mobile_ui_redesign.md` decides interaction direction.
- This blueprint decides dark/light readiness and migration boundaries.
- Mixing both would make implementation harder to verify because visual structure and theme compatibility are different concerns.

## Recommended Theme Contract

Introduce cashier-local semantic tokens and make them switch under `html[data-bs-theme="dark"]`.

Recommended token families:

- `--cashier-page-bg`
- `--cashier-surface`
- `--cashier-surface-subtle`
- `--cashier-border`
- `--cashier-border-strong`
- `--cashier-text`
- `--cashier-muted`
- `--cashier-accent`
- `--cashier-accent-soft`
- `--cashier-accent-border`
- `--cashier-danger-soft`
- `--cashier-warning-soft`
- `--cashier-success-soft`
- `--cashier-shadow`

The implementation should prefer these tokens over page-specific hardcoded variables where practical.

## Migration Rules

- Do not change backend behavior.
- Do not change route names or form field names.
- Do not change payment/refund/correction eligibility.
- Do not remove JS files until route usage proves they are unreachable.
- Replace hardcoded colors with semantic tokens.
- Replace `bg-light text-dark` badge patterns with cashier semantic badge classes.
- Replace JS `btn-light` and `text-dark` toggles with semantic inactive classes or theme-safe Bootstrap classes.
- Keep the stepper/mobile structure from `0011_cashier_stepper_mobile_ui_redesign.md`.
- Shared note partials must be patched carefully because they may be reused outside cashier.

## Proposed Implementation Order

1. Add shared cashier theme token CSS.
2. Patch dashboard area.
3. Patch cashier note index and filter drawer.
4. Patch cashier workspace shell and workspace partials.
5. Patch cashier note detail shell and detail partials.
6. Patch cashier JS runtime class toggles.
7. Patch shared note partials under a safe wrapper or semantic utility.
8. Run targeted grep proof for remaining light-only tokens.
9. Run browser visual proof for light and dark on mobile and desktop.

## Audit Commands Used

Run from repo root:

```bash
rg -n "data-bs-theme|theme-toggle|app-dark|dark|light" resources/views/layouts resources/views/shared resources/views/cashier app/Adapters/In/Http/Controllers/Cashier public/assets/static/js --glob '!vendor'
```

```bash
rg -n "bg-light|text-dark|btn-light|#fff|#ffffff|#f0ebf8|#202124|#0f172a|#64748b|#5f6368|background:\s*#fff|color:\s*#fff" resources/views/cashier resources/views/shared/notes public/assets/static/js/pages/cashier-dashboard.js public/assets/static/js/pages/cashier-note-* public/assets/static/js/pages/cashier-note-workspace --glob '!vendor'
```

```bash
wc -l resources/views/cashier/dashboard/*.blade.php resources/views/cashier/notes/*.blade.php resources/views/cashier/notes/partials/*.blade.php resources/views/cashier/notes/workspace/*.blade.php resources/views/cashier/notes/workspace/partials/*.blade.php resources/views/cashier/notes/workspace/partials/templates/*.blade.php resources/views/shared/notes/*.blade.php resources/views/shared/notes/partials/*.blade.php resources/views/layouts/partials/sidebar-cashier.blade.php
```

## Current Progress

- Audit recorded in this blueprint.
- Implementation not started.
- No theme patch has been applied yet.
- No test or browser proof has been run yet.

## Next Active Step

Build the final implementation map from this blueprint:

- file path
- theme issue
- whether patch is CSS-only, Blade class replacement, JS class replacement, or shared-partial scoping
- risk level
- verification command

After that, start implementation with the lowest-risk shared cashier theme token layer.
