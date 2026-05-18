# Handoff 0030 - Create Transaction Package Pricing UI Contract

Status: Fixed, focused verified, `make verify` PASS  
Date: 2026-05-18  
Repo: HyperPOS Laravel  
Root: `/home/asyraf/Code/laravel/bengkel2/app`

## Scope

This handoff closes the UI/input contract slice for create transaction service + store-stock package pricing.

Scope included:

- create transaction only
- service + store-stock only
- UI contract for `pricing_mode`
- UI contract for `package_total_rupiah`
- JS old input/draft hydration support
- JS summary support for package total
- preservation of store-stock product total for payment partial default behavior

Scope excluded:

- external purchase package pricing
- edit/revision package recalculation
- refund package recalculation
- mixed payment
- pecahan/cash denomination
- explicit allocation audit event/table
- browser/manual QA

## Locked business/backend facts

Backend package pricing was already implemented before this UI slice.

Locked split rule:

- `sparepart_total = product.harga_jual * qty`
- `service_price = package_total - sparepart_total`

Locked minimum rule:

- package total must be greater than or equal to sparepart total
- package total may equal sparepart total
- service price may be `0`
- package total below sparepart total must be rejected without side effect

Locked scope rule:

- package pricing belongs to create transaction item/pricing composition
- payment seam must remain untouched
- external purchase package pricing remains out of scope because current external purchase field is `unit_cost_rupiah`, not customer-facing charge

## Starting gap

Backend accepted package payload:

- `pricing_mode=package_auto_split`
- `package_total_rupiah`

But the create workspace UI did not render those fields for service + store-stock rows.

RED proof:

Command:

    php artisan test tests/Feature/Note/CreateTransactionWorkspaceTemplateContractFeatureTest.php

Output:

    FAIL  Tests\Feature\Note\CreateTransactionWorkspaceTemplateContractFeatureTest
    ⨯ workspace create page embeds service store stock package pricing contract

Failure anchor:

    Expected rendered create page to contain:
    name="items[__INDEX__][pricing_mode]"

Conclusion:

The backend package pricing contract existed, but the create transaction UI did not expose it.

## Files changed

Blade:

- `resources/views/cashier/notes/workspace/partials/templates/service-store-stock.blade.php`

JavaScript:

- `public/assets/static/js/pages/cashier-note-workspace/rows.js`
- `public/assets/static/js/pages/cashier-note-workspace/summary.js`

Test:

- `tests/Feature/Note/CreateTransactionWorkspaceTemplateContractFeatureTest.php`

## Implementation summary

### Blade contract

The service + store-stock template now renders:

- `items[__INDEX__][pricing_mode]`
- default option `manual_split`
- option `package_auto_split`
- `items[__INDEX__][package_total_rupiah]`

Important safety decision:

- UI default remains `manual_split`
- UI must not default every row to `package_auto_split`
- This preserves existing manual split behavior

### Rows JS

`rows.js` now:

- includes package total in keyboard field sequence
- restores `pricing_mode` from old input/draft
- restores `package_total_rupiah` from old input/draft
- defaults pricing mode to `manual_split`

### Summary JS

`summary.js` now:

- reads `pricing_mode`
- reads `package_total_rupiah`
- when mode is `package_auto_split`, row total follows package total
- preserves store-stock product total as `qty * unit_price_rupiah`
- computes UI service residual as `max(package_total - store_stock_total, 0)`

Safety detail:

- preserving `productTotal` avoids breaking payment-flow partial default behavior
- payment seam implementation was not touched

## Proof - UI contract RED

Command:

    php artisan test tests/Feature/Note/CreateTransactionWorkspaceTemplateContractFeatureTest.php

Output:

    FAIL  Tests\Feature\Note\CreateTransactionWorkspaceTemplateContractFeatureTest
    ✓ workspace create page embeds explicit service part source values
    ⨯ workspace create page embeds service store stock package pricing contract

    To contain: name="items[__INDEX__][pricing_mode]"

    Tests: 1 failed, 1 passed (6 assertions)

## Proof - UI contract GREEN

Command:

    php -l resources/views/cashier/notes/workspace/partials/templates/service-store-stock.blade.php
    php -l tests/Feature/Note/CreateTransactionWorkspaceTemplateContractFeatureTest.php

    php artisan test tests/Feature/Note/CreateTransactionWorkspaceTemplateContractFeatureTest.php

Output:

    No syntax errors detected in resources/views/cashier/notes/workspace/partials/templates/service-store-stock.blade.php
    No syntax errors detected in tests/Feature/Note/CreateTransactionWorkspaceTemplateContractFeatureTest.php

    PASS  Tests\Feature\Note\CreateTransactionWorkspaceTemplateContractFeatureTest
    ✓ workspace create page embeds explicit service part source values
    ✓ workspace create page embeds service store stock package pricing contract

    Tests: 2 passed (9 assertions)

## Proof - focused UI/backend package compatibility

Command:

    php artisan test \
      tests/Feature/Note/CreateTransactionWorkspaceTemplateContractFeatureTest.php \
      tests/Feature/Note/CreateTransactionWorkspaceServiceStoreStockFeatureTest.php

Output:

    PASS  Tests\Feature\Note\CreateTransactionWorkspaceTemplateContractFeatureTest
    ✓ workspace create page embeds explicit service part source values
    ✓ workspace create page embeds service store stock package pricing contract

    PASS  Tests\Feature\Note\CreateTransactionWorkspaceServiceStoreStockFeatureTest
    ✓ cashier can store workspace service with store stock payload and redirect to history
    ✓ cashier can store workspace service store stock with package total auto split
    ✓ cashier can store workspace service store stock package total equal sparepart minimum
    ✓ cashier cannot store workspace service store stock package total below sparepart minimum

    Tests: 6 passed (40 assertions)

## Proof - JS hydration and summary patch

Changed anchors:

- `rows.js` restores package mode and package total
- `summary.js` computes package residual while preserving product total

Final summary anchors:

    public/assets/static/js/pages/cashier-note-workspace/summary.js
    18:    const storeStockTotal = qty * product;
    20:    if (type === "product") return { service: 0, product: storeStockTotal };
    22:      return { service: Math.max(packageTotal - storeStockTotal, 0), product: storeStockTotal };
    24:    if (type === "service_store_stock") return { service, product: storeStockTotal };

## Proof - focused blast radius

Command:

    node --check public/assets/static/js/pages/cashier-note-workspace/rows.js
    node --check public/assets/static/js/pages/cashier-note-workspace/summary.js

    php -l resources/views/cashier/notes/workspace/partials/templates/service-store-stock.blade.php
    php -l tests/Feature/Note/CreateTransactionWorkspaceTemplateContractFeatureTest.php
    php -l tests/Feature/Note/CreateTransactionWorkspaceServiceStoreStockFeatureTest.php
    php -l tests/Feature/Note/CreateTransactionWorkspaceServiceExternalPurchaseFeatureTest.php
    php -l tests/Feature/Note/CreateTransactionWorkspaceFullCashFeatureTest.php
    php -l tests/Feature/Note/CreateTransactionWorkspaceFullTransferFeatureTest.php
    php -l tests/Feature/Note/CreateTransactionWorkspaceSkipFeatureTest.php
    php -l tests/Feature/Note/CreateTransactionWorkspacePartialTransferFeatureTest.php
    php -l tests/Feature/Note/CreateTransactionWorkspacePartialCashFeatureTest.php

    php artisan test \
      tests/Feature/Note/CreateTransactionWorkspaceTemplateContractFeatureTest.php \
      tests/Feature/Note/CreateTransactionWorkspaceServiceStoreStockFeatureTest.php \
      tests/Feature/Note/CreateTransactionWorkspaceServiceExternalPurchaseFeatureTest.php \
      tests/Feature/Note/CreateTransactionWorkspaceFullCashFeatureTest.php \
      tests/Feature/Note/CreateTransactionWorkspaceFullTransferFeatureTest.php \
      tests/Feature/Note/CreateTransactionWorkspaceSkipFeatureTest.php \
      tests/Feature/Note/CreateTransactionWorkspacePartialTransferFeatureTest.php \
      tests/Feature/Note/CreateTransactionWorkspacePartialCashFeatureTest.php

Output:

    PASS  Tests\Feature\Note\CreateTransactionWorkspaceTemplateContractFeatureTest
    ✓ workspace create page embeds explicit service part source values
    ✓ workspace create page embeds service store stock package pricing contract

    PASS  Tests\Feature\Note\CreateTransactionWorkspaceServiceStoreStockFeatureTest
    ✓ cashier can store workspace service with store stock payload and redirect to history
    ✓ cashier can store workspace service store stock with package total auto split
    ✓ cashier can store workspace service store stock package total equal sparepart minimum
    ✓ cashier cannot store workspace service store stock package total below sparepart minimum

    PASS  Tests\Feature\Note\CreateTransactionWorkspaceServiceExternalPurchaseFeatureTest
    ✓ cashier can store workspace service with external purchase payload and redirect to history

    PASS  Tests\Feature\Note\CreateTransactionWorkspaceFullCashFeatureTest
    ✓ cashier can store workspace with full cash payment and redirect to history

    PASS  Tests\Feature\Note\CreateTransactionWorkspaceFullTransferFeatureTest
    ✓ cashier can store workspace with full transfer payment and redirect to history

    PASS  Tests\Feature\Note\CreateTransactionWorkspaceSkipFeatureTest
    ✓ cashier can store workspace and redirect to history when skipping payment

    PASS  Tests\Feature\Note\CreateTransactionWorkspacePartialTransferFeatureTest
    ✓ cashier can store workspace with selected partial transfer payment and redirect to history

    PASS  Tests\Feature\Note\CreateTransactionWorkspacePartialCashFeatureTest
    ✓ cashier can store workspace with partial cash payment and cash detail

    Tests: 12 passed (81 assertions)

## Proof - make verify

Command:

    make verify

Owner-provided final output:

    Tests:    1062 passed (5764 assertions)
    Duration: 52.23s

Interpretation:

- `make verify` completed successfully.
- Final Pest summary was `1062 passed / 5764 assertions`.
- Detailed intermediate PHPStan/audit output was not pasted in this handoff, but there was no reported make failure.

## Current status

The create transaction service + store-stock package pricing slice is now closed for backend + UI contract.

Closed facts:

- backend package pricing works
- UI renders package pricing contract
- default pricing mode is manual split
- old input/draft hydration restores package fields
- summary uses package total in package mode
- product total is preserved for payment-flow partial default behavior
- focused blast-radius passed
- full `make verify` passed

## Remaining gaps

These are intentionally not closed in this slice:

- no browser/manual QA
- no explicit package allocation audit event/table
- no external purchase cost-vs-charge design
- no external purchase package pricing
- no edit/revision/refund package recalculation
- no pecahan/cash denomination work

## Progress interpretation

Final Goal Progress: 84%

Meaning:

- create transaction maturity for the current create-only foundation slice
- not global HyperPOS maturity
- not refund/edit/reporting maturity

Main Process Progress: 100%

Meaning:

- UI package total input contract for service + store-stock is complete and verified

Sub-step Progress: 100%

Meaning:

- final full verification step passed with `make verify`

## Suggested next targets

Pick one explicitly. Do not start all.

Recommended order:

1. explicit package allocation audit metadata/event/table
2. external purchase cost-vs-charge design note only
3. browser/manual QA checklist for create transaction package pricing
4. future edit/revision/refund package recalculation blueprint

Do not implement external purchase package pricing until cost-vs-charge is designed.

Do not touch edit/revision/refund package behavior without a blueprint first.

## Opening prompt for next session

Kita sedang di repo HyperPOS Laravel:

/home/asyraf/Code/laravel/bengkel2/app

Baca handoff terbaru dulu:

docs/99_archive/handoff/v2/edit_refund_sniper/0030_create_transaction_package_pricing_ui_contract_handoff.md

Mode kerja wajib:

- Blueprint/proof-first.
- One active target per step.
- Jangan broad audit repo.
- Jangan mulai dari git status/log/push/remote sync.
- Owner handles commit/push/manual sync.
- Jangan klaim fixed/full green tanpa local proof.
- Local command output owner adalah source of truth tertinggi untuk test, make verify, runtime, dirty/local-only files, migration proof, dan working tree state.
- Source read-only inspection wajib pakai GitHub/repo connector dulu jika file sudah committed/pushed.
- Jangan minta saya paste sed panjang untuk source file committed yang bisa dibaca dari repo.
- GitHub remote/source read tidak boleh dipakai untuk mengklaim local test/make verify proof.
- Gunakan rg/fd/sed untuk command lokal; jangan grep -R/find.
- Jangan implement external purchase package pricing.
- Jangan lompat ke edit/revision/refund/mixed payment/pecahan.
- Jangan reopen payment seam kecuali ada regression proof.
- Jangan broad audit dari handoff lama kecuali ada konflik/proof baru.

Locked facts:

- Backend package pricing for service + store-stock is implemented.
- UI package pricing contract is implemented.
- UI default pricing mode is manual split.
- `package_auto_split` is available as explicit option.
- `package_total_rupiah` is rendered in service + store-stock template.
- `rows.js` restores `pricing_mode` and `package_total_rupiah`.
- `summary.js` uses package total for package mode while preserving store-stock product total.
- Payment seam was not touched.
- External purchase package pricing remains out of scope.

Latest owner-provided proof:

- UI contract targeted: PASS, 2 tests / 9 assertions.
- Focused UI/backend package proof: PASS, 6 tests / 40 assertions.
- Focused create transaction blast-radius: PASS, 12 tests / 81 assertions.
- Full `make verify`: PASS, 1062 tests / 5764 assertions, duration 52.23s.

Files changed in the closed slice:

- resources/views/cashier/notes/workspace/partials/templates/service-store-stock.blade.php
- public/assets/static/js/pages/cashier-note-workspace/rows.js
- public/assets/static/js/pages/cashier-note-workspace/summary.js
- tests/Feature/Note/CreateTransactionWorkspaceTemplateContractFeatureTest.php

Current remaining gaps:

- no browser/manual QA
- no explicit package allocation audit metadata/event/table
- no external purchase cost-vs-charge design
- no edit/revision/refund package recalculation blueprint
- no pecahan/cash denomination work

Next target must be selected explicitly.

Recommended next target:

1. explicit package allocation audit metadata/event/table

Alternative targets:

2. external purchase cost-vs-charge design note only
3. browser/manual QA checklist for create transaction package pricing
4. future edit/revision/refund package recalculation blueprint

Before implementing anything, inspect only the selected target’s current source/docs. Do not broad audit the repo.
