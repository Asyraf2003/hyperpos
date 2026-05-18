# Handoff 0032 - Create Transaction Package Pricing Browser Manual QA

Status: Checklist prepared, manual browser proof pending
Date: 2026-05-18
Repo: HyperPOS Laravel
Root: `/home/asyraf/Code/laravel/bengkel2/app`

## Scope

This handoff prepares browser/manual QA for create transaction service + store-stock package pricing.

Scope included:

- create transaction only
- cashier create workspace page only
- service + store-stock package pricing UI behavior
- manual split mode
- package auto split mode
- package total equal sparepart minimum
- package total below sparepart minimum rejection path
- summary display behavior
- old input / validation return behavior
- audit metadata visibility through database/local inspection after submit

Scope excluded:

- production source patch
- payment seam changes
- external purchase package pricing
- edit/revision/refund package recalculation
- mixed payment
- pecahan/cash denomination
- dedicated package allocation audit table/read path

## Locked facts from 0030 and 0031

- Backend package pricing for service + store-stock is implemented.
- UI package pricing contract is implemented.
- UI default pricing mode is `manual_split`.
- `package_auto_split` is available as explicit option.
- `package_total_rupiah` is rendered in service + store-stock template.
- `rows.js` restores `pricing_mode` and `package_total_rupiah`.
- `summary.js` uses package total for package mode while preserving store-stock product total.
- Payment seam was not touched.
- External purchase package pricing remains out of scope.
- Create transaction package auto split records explicit package allocation metadata in `audit_logs.context.package_allocations`.
- Update/revision call-site compatibility was preserved.
- Full `make verify` passed after the package allocation audit metadata patch.

## Source anchors inspected before checklist

Current source anchors expected:

- `resources/views/cashier/notes/workspace/partials/templates/service-store-stock.blade.php`
  - renders `items[__INDEX__][pricing_mode]`
  - renders `manual_split`
  - renders `package_auto_split`
  - renders `items[__INDEX__][package_total_rupiah]`
- `public/assets/static/js/pages/cashier-note-workspace/rows.js`
  - includes package pricing fields in keyboard sequence
  - restores `pricing_mode`
  - restores `package_total_rupiah`
- `public/assets/static/js/pages/cashier-note-workspace/summary.js`
  - computes package mode total from `package_total_rupiah`
  - preserves store-stock product total as `qty * unit_price_rupiah`
- `public/assets/static/js/pages/cashier-note-workspace/search.js`
  - fills selected product id
  - fills default unit price when empty
  - sets minimum unit price dataset
  - refreshes summary after product selection

## Manual QA setup

Preconditions:

- Local app can be opened in browser.
- A cashier user can access create workspace.
- At least one store-stock product exists with:
  - available stock greater than or equal to 2
  - default selling price known
  - minimum unit price known

Suggested test product:

- product name: record locally during QA
- product id: record locally if visible from database/debug output
- stock before: record locally
- selling price: record locally

## Browser QA checklist

### Case 1 - Create page renders package pricing controls

Steps:

1. Login as cashier.
2. Open create transaction workspace.
3. Add row: service + sparepart toko.
4. Confirm the row shows:
   - Mode Harga
   - option `Input servis dan sparepart terpisah`
   - option `Total Paket (auto split)`
   - Total Paket (Rupiah)
   - Harga Servis (Rupiah)
   - Sparepart Toko
   - Qty Sparepart
   - Harga Sparepart (Rupiah)

Expected:

- Default selected mode is manual split.
- Total Paket input is visible.
- No console error appears.

Result:

- PENDING

### Case 2 - Manual split remains default and summary uses service + sparepart

Steps:

1. Keep mode as manual split.
2. Fill service name.
3. Fill service price, for example `50.000`.
4. Select store-stock product with selling price, for example `40.000`.
5. Set qty `1`.
6. Confirm summary total.

Expected:

- Summary total equals service price plus sparepart total.
- With example values: `50.000 + 40.000 = 90.000`.
- Product minimum price warning does not appear when unit price equals catalog/minimum price.

Result:

- PENDING

### Case 3 - Package auto split above sparepart minimum

Steps:

1. Add or reset service + sparepart toko row.
2. Set mode to `Total Paket (auto split)`.
3. Select product with selling price `40.000`.
4. Set qty `1`.
5. Fill package total `150.000`.
6. Observe summary total before submit.
7. Submit note without payment or with a safe payment path only if needed for local flow.

Expected UI:

- Summary total equals `150.000`.
- Product total basis remains `40.000`.
- Service residual is implicitly `110.000`.
- No client-side stock/minimum warning appears for valid stock and price.

Expected persistence:

- note total is `150.000`.
- store-stock line total is `40.000`.
- service detail price is `110.000`.
- audit log context includes `package_allocations`.
- `package_allocations[0].pricing_mode` is `package_auto_split`.
- `package_allocations[0].package_total_rupiah` is `150000`.
- `package_allocations[0].sparepart_total_rupiah` is `40000`.
- `package_allocations[0].service_price_rupiah` is `110000`.

Result:

- PENDING

### Case 4 - Package auto split equal sparepart minimum

Steps:

1. Add service + sparepart toko row.
2. Set mode to `Total Paket (auto split)`.
3. Select product with selling price `40.000`.
4. Set qty `1`.
5. Fill package total `40.000`.
6. Submit note.

Expected:

- Summary total equals `40.000`.
- Service price residual is `0`.
- Store-stock line remains `40.000`.
- Submit succeeds.
- Audit package allocation records service_price_rupiah `0`.

Result:

- PENDING

### Case 5 - Package total below sparepart minimum is rejected

Steps:

1. Add service + sparepart toko row.
2. Set mode to `Total Paket (auto split)`.
3. Select product with selling price `40.000`.
4. Set qty `1`.
5. Fill package total `30.000`.
6. Submit note.

Expected:

- Submit is rejected.
- User is returned to workspace with validation/workspace failure.
- Old input restores:
  - pricing mode `package_auto_split`
  - package total `30.000`
  - selected row data where applicable
- No note/work item/store-stock line/inventory/payment side effect is created for this rejected submit.

Result:

- PENDING

### Case 6 - Old input and draft hydration restore package fields

Steps:

1. Trigger rejected package submit from Case 5.
2. On returned page, inspect restored row.

Expected:

- Mode remains `Total Paket (auto split)`.
- Package total input still shows attempted value.
- Manual split default does not overwrite the returned package mode.

Result:

- PENDING

### Case 7 - External purchase remains out of package pricing scope

Steps:

1. Add service + pembelian luar row.
2. Inspect visible inputs.

Expected:

- No package pricing mode is presented for external purchase row.
- No package total field is presented for external purchase row.
- Existing external purchase cost flow remains unchanged.

Result:

- PENDING

## Suggested local proof commands after browser QA

Use local database inspection only after creating a known package-auto-split note.

Minimum source anchor check:

    rg -n "pricing_mode|package_total_rupiah|package_auto_split" resources/views/cashier/notes/workspace/partials/templates/service-store-stock.blade.php public/assets/static/js/pages/cashier-note-workspace/rows.js public/assets/static/js/pages/cashier-note-workspace/summary.js

Minimum syntax/smoke check:

    node --check public/assets/static/js/pages/cashier-note-workspace/rows.js
    node --check public/assets/static/js/pages/cashier-note-workspace/summary.js
    php -l resources/views/cashier/notes/workspace/partials/templates/service-store-stock.blade.php

Optional focused regression after manual QA:

    php artisan test \
      tests/Feature/Note/CreateTransactionWorkspaceTemplateContractFeatureTest.php \
      tests/Feature/Note/CreateTransactionWorkspaceServiceStoreStockFeatureTest.php \
      tests/Feature/Note/CreateTransactionWorkspacePackageAllocationAuditFeatureTest.php

## Closure rule

This checklist can be marked browser/manual QA complete only after owner provides:

- browser case results
- created note identifier or equivalent local proof for valid package submit
- rejected-submit proof for below-minimum package total
- audit package allocation proof for at least one valid package submit
- confirmation that no console error appeared during the tested browser paths

Do not claim full safe state from this checklist alone unless owner also provides final `make verify` or explicitly states the current final verified baseline still applies after no source changes.

## Remaining gaps after this checklist

These remain out of scope:

- dedicated package allocation audit table/read path
- external purchase cost-vs-charge design
- external purchase package pricing
- edit/revision/refund package recalculation blueprint
- pecahan/cash denomination work
