# Handoff 0029 - Create Transaction Service Store-Stock Package Pricing

Status: Focused implementation GREEN, owner-reported `make verify` PASS  
Date: 2026-05-17  
Repo: HyperPOS Laravel  
Root: `/home/asyraf/Code/laravel/bengkel2/app`

## Critical workflow rule for next sessions

Source read-only inspection should use GitHub/repo connector first when files are already committed/pushed.

Do not ask owner to paste long `sed` output for source files that can be read from repo.

Local command output from owner remains the highest source of truth for:

- tests
- `make verify`
- runtime state
- dirty/local-only files
- migration proof
- files not committed/pushed yet
- current working tree status

Do not claim local proof from GitHub remote.

Owner handles commit/push/manual sync. Do not manage git unless explicitly asked.

## Mode rules

- Blueprint/proof-first.
- One active target per step.
- Do not broad audit repo.
- Do not start from git status/log/push/remote sync.
- Do not claim fixed/full green without local proof.
- Use repo connector for committed source inspection.
- Use local command only for proof/test/runtime/dirty state.
- Use `rg/fd/sed`; do not use `grep -R/find` in local commands.
- Do not reopen external purchase package pricing unless explicitly selected.
- Do not touch refund/edit/mixed payment/pecahan unless explicitly selected.
- Do not implement UI polish before backend/domain proof is stable.

## Locked business decisions

### Service + store-stock package pricing

UI may allow one fast package total input.

Backend must split explicitly:

- `sparepart_total = product.harga_jual * qty`
- `service_price = package_total - sparepart_total`

Minimum:

- package total must be greater than or equal to sparepart total
- package total may equal sparepart total
- service price may be zero
- package total below sparepart total must be rejected

Reason:

- valid business cases exist where owner/family/customer only pays sparepart while service fee is waived

### External purchase

External purchase is intentionally out of scope.

Current external purchase field is `unit_cost_rupiah`.

Do not treat `unit_cost_rupiah` as customer-facing sale price.

Future external purchase package pricing needs a separate domain split:

- external purchase cost
- external purchase customer charge
- margin/profit calculation

### Payment

Payment seam is untouched.

Package pricing belongs to create transaction item/pricing composition, not cash/transfer/payment allocation.

## Files changed in this slice

### Request / validation

- `app/Adapters/In/Http/Requests/Note/StoreTransactionWorkspaceRules.php`
- `app/Adapters/In/Http/Requests/Note/StoreTransactionWorkspaceItemNormalizer.php`
- `app/Adapters/In/Http/Requests/Note/StoreTransactionWorkspaceMeaningfulItemDetector.php`
- `app/Adapters/In/Http/Requests/Note/StoreTransactionWorkspaceServiceItemValidator.php`
- `app/Adapters/In/Http/Requests/Note/StoreTransactionWorkspaceServicePriceValidator.php`

### Application / composer

- `app/Application/Note/Services/CreateTransactionWorkspaceServiceStoreStockPackagePricingComposer.php`
- `app/Application/Note/Services/CreateTransactionWorkspaceWorkItemPayloadMapper.php`

### Domain

- `app/Core/Note/WorkItem/ServiceDetail.php`

### Payment recorder audit-lines split

- `app/Application/Note/Services/CreateTransactionWorkspaceInlinePaymentAuditPayloadBuilder.php`
- `app/Application/Note/Services/CreateTransactionWorkspaceInlinePaymentSummaryBuilder.php`
- `app/Application/Note/Services/CreateTransactionWorkspaceInlinePaymentRecorder.php`

### Tests

- `tests/Feature/Note/CreateTransactionWorkspaceServiceStoreStockFeatureTest.php`
- `tests/Feature/Note/CreateTransactionWorkspacePartialCashFeatureTest.php`
- `tests/Unit/Application/Note/UseCases/CreateNoteRevisionPayloadNoteBuilderTest.php`
- `tests/Unit/Core/Note/WorkItem/ServiceDetailTest.php`

## Behavior proven

### Payment matrix

Focused create transaction payment matrix passed earlier:

- full cash
- full transfer
- skip
- partial transfer
- partial cash

Owner-provided proof:

- partial cash targeted: PASS, 1 test / 8 assertions
- create payment matrix: PASS, 5 tests / 33 assertions

### Baseline item behavior

Focused baseline passed:

- `CreateTransactionWorkspaceServiceStoreStockFeatureTest`
- `CreateTransactionWorkspaceServiceExternalPurchaseFeatureTest`

Owner-provided proof:

- combined baseline: PASS, 2 tests / 15 assertions

### Package pricing behavior

`CreateTransactionWorkspaceServiceStoreStockFeatureTest` now covers:

1. manual service + store-stock existing behavior
2. package total above sparepart minimum
3. package total equal sparepart minimum
4. package total below sparepart minimum rejected without side effect

Owner-provided proof:

- service + store-stock package target: PASS, 4 tests / 30 assertions
- focused create transaction blast-radius: PASS, 10 tests / 71 assertions

Focused blast-radius command:

`php artisan test --filter='CreateTransactionWorkspaceServiceStoreStockFeatureTest|CreateTransactionWorkspaceServiceExternalPurchaseFeatureTest|CreateTransactionWorkspaceFullCashFeatureTest|CreateTransactionWorkspaceFullTransferFeatureTest|CreateTransactionWorkspaceSkipFeatureTest|CreateTransactionWorkspacePartialTransferFeatureTest|CreateTransactionWorkspacePartialCashFeatureTest'`

Result:

- PASS: 10 tests / 71 assertions

### Full verification

Owner reported final `make verify` PASS after fixing stale `ServiceDetailTest`.

Exact final pass count/assertion count was not pasted in this session. Do not invent it.

Before final closure/docs claiming exact count, either:

- use owner-provided exact output, or
- rerun `make verify` locally and paste proof.

## Known failed states and fixes

### RED 1 - request validation rejected zero service price

Failure:

- Laravel rule rejected `items.0.service.price_rupiah min:1`
- custom validator rejected `Harga servis wajib lebih dari 0`

Fix:

- rules allow `pricing_mode`
- rules allow `package_total_rupiah`
- rules allow `service.price_rupiah min:0`
- custom validator allows zero service price only for package auto split with valid store-stock line

### RED 2 - normalizer dropped package fields

Failure:

- custom validator still failed because `pricing_mode` and `package_total_rupiah` were not preserved

Fix:

- `StoreTransactionWorkspaceItemNormalizer` preserves `pricing_mode`
- `StoreTransactionWorkspaceItemNormalizer` preserves `package_total_rupiah`
- meaningful detector extracted to keep file under audit-lines limit

### RED 3 - mapper rejected service price zero

Failure:

- `CreateTransactionWorkspaceWorkItemPayloadMapper` rejected `0` through service price guard

Fix:

- package pricing composer added
- mapper runs composer before payload mapping
- mapper allows zero service price only for `package_auto_split`

### RED 4 - domain rejected service price zero

Failure:

- `ServiceDetail` rejected `Money::zero()`

Fix:

- `ServiceDetail` now allows zero service price
- `ServiceDetail` rejects negative service price

### RED 5 - PHPStan constructor/unused helper

Failures:

- unused mapper helper
- `CreateNoteRevisionPayloadNoteBuilderTest` instantiated mapper with old 3-argument constructor

Fix:

- unused helper removed
- unit test injects `CreateTransactionWorkspaceServiceStoreStockPackagePricingComposer`

### RED 6 - audit-lines

Failures:

- `StoreTransactionWorkspaceItemNormalizer.php` >100 lines
- `StoreTransactionWorkspaceServiceItemValidator.php` >100 lines
- `CreateTransactionWorkspaceInlinePaymentRecorder.php` >100 lines

Fixes:

- `StoreTransactionWorkspaceMeaningfulItemDetector` extracted
- `StoreTransactionWorkspaceServicePriceValidator` extracted
- `CreateTransactionWorkspaceInlinePaymentAuditPayloadBuilder` extracted
- `CreateTransactionWorkspaceInlinePaymentSummaryBuilder` extracted

### RED 7 - stale ServiceDetail unit test

Failure:

- `ServiceDetailTest` still expected zero price to throw exception

Fix:

- test now accepts zero service price
- test rejects negative service price

## Remaining gaps

- Exact final `make verify` count/assertion count not pasted.
- Docs `0009` and `0028` may still need final proof section with exact `make verify` output unless already updated locally.
- No browser/manual QA.
- No UI package input implementation proof beyond backend payload contract.
- No explicit package allocation audit table/event beyond persisted service/store-stock facts.
- No edit/revision/refund package recalculation support.
- No external purchase package pricing support.
- No pecahan/cash denomination work in this slice.

## Suggested next active step

Do not start broad repo audit.

First verify current local proof state minimally:

1. Confirm whether docs proof sections were appended to:
   - `docs/03_blueprints/finance/0009_create_transaction_domain_risk_handoff.md`
   - `docs/99_archive/handoff/v2/edit_refund_sniper/0028_create_transaction_modular_payment_hardening_handoff.md`

2. If docs not updated, append concise final proof:
   - focused package tests
   - focused blast-radius PASS 10/71
   - owner-reported `make verify` PASS with count unknown unless exact output is available

3. If exact final make verify count is needed, rerun `make verify` and paste output.

4. After docs closure, owner manually commits/pushes.

## Do not do next

- Do not implement external purchase package pricing yet.
- Do not implement edit/revision/refund package recalculation yet.
- Do not touch payment/mixed payment/pecahan.
- Do not add audit table unless explicitly selected as next target.
- Do not ask for long source paste if repo connector can read committed files.
