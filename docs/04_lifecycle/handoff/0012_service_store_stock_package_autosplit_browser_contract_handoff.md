# Handoff 0012 - Service Store Stock Package Autosplit Browser Contract

## FACT

- Slice ini memperbaiki create workspace flow untuk `service_store_stock` dengan `package_auto_split`.
- Original manual browser symptom:
  - `Harga servis wajib lebih dari 0 kecuali package service + sparepart.`
  - `Nominal pembayaran sebagian harus lebih kecil dari grand total nota.`
  - setelah UI guard awal, muncul `Qty sparepart toko wajib lebih dari 0.`
- Root cause backend:
  - `package_total_rupiah` dari browser form dikirim sebagai string, tetapi `StoreTransactionWorkspaceItemNormalizer::intOrNull()` hanya menerima PHP int.
  - `StoreTransactionWorkspaceGrandTotalCalculator` belum memakai `package_total_rupiah` untuk `package_auto_split`, sehingga partial payment dibandingkan terhadap total yang salah.
- Root cause UI:
  - `service-store-stock.blade.php` menaruh `data-product-line` hanya pada kolom product search.
  - Qty berada di kolom saudara, sehingga JS guard membaca qty sebagai 0.
- Final state:
  - Backend browser-form contract fixed.
  - Payment grand total autosplit fixed.
  - UI payment guard added.
  - Blade `data-product-line` scope repaired so product search and qty are in the same product-line scope.
  - Manual browser flow confirmed by owner as successful.

## REFERENCES

- `app/Adapters/In/Http/Requests/Note/StoreTransactionWorkspaceItemNormalizer.php`
- `app/Adapters/In/Http/Requests/Note/StoreTransactionWorkspaceGrandTotalCalculator.php`
- `tests/Feature/Note/CreateTransactionWorkspaceServiceStoreStockFeatureTest.php`
- `public/assets/static/js/pages/cashier-note-workspace/payment-flow.js`
- `resources/views/cashier/notes/workspace/partials/templates/service-store-stock.blade.php`
- `tests/Feature/Note/CreateTransactionWorkspaceInlinePaymentLifecycleFeatureTest.php`

## SCOPE-IN

- Fix browser-form request contract for 1-product `service_store_stock` package autosplit.
- Add characterization coverage for form-style string payload and partial payment.
- Add UI guard before payment modal/submit for invalid store-stock autosplit rows.
- Repair Blade scope so product search, hidden product id, hidden price, and qty are inside the same `data-product-line`.

## SCOPE-OUT

- Multi-product UI.
- Edit/revision flow.
- Refund flow.
- Full browser automation.
- Full `make verify`.
- Git commit/push.

## DECISION

`service_store_stock` package autosplit 1-product create flow is closed for this slice.

Final status:

`FIXED WITH BACKEND CONTRACT + UI GUARD + BLADE SCOPE REPAIR + MANUAL BROWSER PROOF`

## PROOF

### Static syntax proof

Command:

```bash
php -l resources/views/cashier/notes/workspace/partials/templates/service-store-stock.blade.php
php -l app/Adapters/In/Http/Requests/Note/StoreTransactionWorkspaceItemNormalizer.php
php -l app/Adapters/In/Http/Requests/Note/StoreTransactionWorkspaceGrandTotalCalculator.php

Proof:

No syntax errors detected in resources/views/cashier/notes/workspace/partials/templates/service-store-stock.blade.php
No syntax errors detected in app/Adapters/In/Http/Requests/Note/StoreTransactionWorkspaceItemNormalizer.php
No syntax errors detected in app/Adapters/In/Http/Requests/Note/StoreTransactionWorkspaceGrandTotalCalculator.php
Blade scope proof

Command:

rg -n 'row g-2 align-items-start" data-product-line|col-12 col-lg-4" data-product-line|data-qty-input|data-product-search|product_lines\]\[0\]\[qty\]' resources/views/cashier/notes/workspace/partials/templates/service-store-stock.blade.php

Proof:

18:        <div class="row g-2 align-items-start" data-product-line>
57:                        data-product-search
71:                        name="items[__INDEX__][product_lines][0][qty]"
74:                        data-qty-input

Meaning:

data-product-line is now on the parent row that contains product search and qty.
col-12 col-lg-4" data-product-line no longer appears.
UI guard static proof

Command:

rg -n "ensureWorkspaceReadyForPayment|serviceStoreStockAutosplitIssue|workspace-client-validation-error|bindWorkspaceSubmitGuard|Sparepart toko wajib dipilih" public/assets/static/js/pages/cashier-note-workspace/payment-flow.js

Proof:

176:    const alert = byId("workspace-client-validation-error");
197:    let alert = byId("workspace-client-validation-error");
201:      alert.id = "workspace-client-validation-error";
217:  const serviceStoreStockAutosplitIssue = (row) => {
247:          message: "Sparepart toko wajib dipilih dari hasil pencarian, bukan diketik manual.",
289:      const issue = serviceStoreStockAutosplitIssue(row);
298:  const ensureWorkspaceReadyForPayment = () => {
723:      if (!ensureWorkspaceReadyForPayment()) {
852:  const bindWorkspaceSubmitGuard = () => {
862:      if (ensureWorkspaceReadyForPayment()) {
872:  bindWorkspaceSubmitGuard();
Targeted package regression

Command:

php artisan test tests/Feature/Note/CreateTransactionWorkspaceServiceStoreStockFeatureTest.php --filter=package

Proof:

PASS  Tests\Feature\Note\CreateTransactionWorkspaceServiceStoreStockFeatureTest

Tests: 6 passed (73 assertions)
Duration: 6.24s
Inline payment lifecycle regression

Command:

php artisan test tests/Feature/Note/CreateTransactionWorkspaceInlinePaymentLifecycleFeatureTest.php

Proof:

PASS  Tests\Feature\Note\CreateTransactionWorkspaceInlinePaymentLifecycleFeatureTest

Tests: 5 passed (92 assertions)
Duration: 6.22s
Manual browser proof

Owner confirmed:

ok berhasil

Meaning:

manual service_store_stock package autosplit flow passed after backend contract, UI guard, and Blade scope fixes.
GAP
Full make verify has not been run after this slice.
node --check public/assets/static/js/pages/cashier-note-workspace/payment-flow.js was not provided.
Multi-product UI remains pending.
Multi-product UI + inline payment characterization remains pending.
NEXT

Recommended next active step:

Run optional JS syntax check if Node is available:

node --check public/assets/static/js/pages/cashier-note-workspace/payment-flow.js

Then run broader verify when ready:

make verify
Do not start multi-product UI until a dedicated characterization test exists for:
2 product lines
part_source=store_stock
pricing_mode=package_auto_split
browser-form string payload
inline partial/full payment allocations
PROGRESS
Backend browser-form autosplit contract: 100%
UI guard + Blade scope repair: 100% for 1-product flow
Manual browser proof: passed by owner report
Multi-product UI: 0%
Full suite proof after this slice: pending

Estimated total create-edit-refund progress after this slice:

33 / 100
