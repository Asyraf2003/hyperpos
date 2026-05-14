# Handoff 0020 - ADR 0030 Cashier Edit Payment Calculator Consumption

## Status

Closed, verified, and pushed by owner.

Owner statement accepted as FACT:
- `make verify` after this ADR 0030 cashier edit payment calculator consumption slice passed cleanly.
- Latest changes were pushed safely by owner.
- Owner handles git commit/push manually.

Exact final pushed commit hash was not pasted in chat.
Exact final `make verify` assertion count was not pasted in chat.

## Active ADR

`docs/02_architecture/adr/0030_note_revision_payment_settlement_and_cashier_calculator_contract.md`

Locked ADR decisions preserved:
- Payment after edit or revision must be settlement-preview-driven.
- Backend application/service must provide payable amount and explanation.
- Blade/JS may display and assist only.
- Request validator may validate payload shape only.
- Final accepted payment amount must come from backend payable/settlement logic.
- `StoreNoteRevisionRequest` currently forces `inline_payment` to `skip` for revision submit.
- Do not merge revision submit + payment unless explicitly decided later.
- Do not implement `customer_credit`, `customer_balance_entries`, PostgreSQL, Go API, or dashboard.
- Do not use `customer_refunds`, `refund_component_allocations`, refunded lifecycle, or inventory reversal for surplus `refund_paid`.

## Baseline From Handoff 0019

Handoff 0019 closed:
- ADR 0030 request-validator boundary slice pushed.
- ADR 0030 backend payable explanation slice pushed.
- `make verify` after latest ADR 0030 work passed cleanly per owner.

Completed before this slice:
- `StoreTransactionWorkspacePaymentValidator` no longer validates `pay_full` cash received against payload grand total.
- `pay_partial` cash still requires received cash to cover explicit `amount_paid_rupiah`.
- `NoteOutstandingPaymentAmountResolver` provides backend payable amount and explanation.
- ADR 0030 anchors existed for backend settlement-preview boundary.

## This Slice Goal

Make cashier edit payment calculator/display consume backend payable context.

Specific goal:
- Cashier edit workspace must render backend settlement explanation.
- Payment modal must expose backend payable context to the calculator surface.
- Cashier workspace payment JavaScript must read the backend payable dataset.
- Blade/JS remain display/assist only.
- Backend payable/settlement logic remains the source of final payment truth.

## Files Changed

Production/source:
- `app/Application/Note/Services/EditTransactionWorkspacePageDataBuilder.php`
- `app/Application/Note/Services/EditTransactionWorkspacePaymentSettlementDataBuilder.php`
- `resources/views/cashier/notes/workspace/partials/payment-modal.blade.php`
- `resources/views/cashier/notes/workspace/partials/payment-modal-right.blade.php`
- `public/assets/static/js/pages/cashier-note-workspace/payment-flow.js`

Tests:
- `tests/Feature/Note/EditTransactionWorkspacePageFeatureTest.php`
- `tests/Feature/Note/CashierWorkspacePaymentFlowJavascriptContractTest.php`

Docs:
- `docs/02_architecture/adr/0030_note_revision_payment_settlement_and_cashier_calculator_contract.md`

## Problem Proven

### Problem 1 - Edit workspace did not render backend settlement explanation

RED test:
- `EditTransactionWorkspacePageFeatureTest::test_cashier_edit_workspace_renders_backend_payment_settlement_explanation_for_partially_paid_note`

Scenario:
- Existing note total: `100000`
- Existing paid allocation: `40000`
- Backend payable: `60000`

RED proof:
- Failure: rendered edit workspace did not contain `Settlement pembayaran backend`.
- Proof: `1 failed / 3 assertions`.

### Problem 2 - Payment modal did not expose backend payable DOM contract

RED test:
- `EditTransactionWorkspacePageFeatureTest::test_cashier_edit_workspace_exposes_backend_payable_amount_to_payment_calculator`

Scenario:
- Existing note total: `100000`
- Existing paid allocation: `40000`
- Backend payable: `60000`

RED proof:
- Failure: rendered modal did not contain `data-backend-payable-rupiah="60000"`.
- Proof: `1 failed / 3 assertions`.

### Problem 3 - Payment JavaScript did not consume backend payable dataset

RED test:
- `CashierWorkspacePaymentFlowJavascriptContractTest::test_payment_flow_consumes_backend_payable_dataset_for_edit_calculator`

RED proof:
- Failure: `payment-flow.js` did not contain `dataset.backendPayableRupiah`.
- Proof: `1 failed / 1 assertions`.

## Patch Summary

### Patch 1 - Backend settlement explanation in edit workspace

`EditTransactionWorkspacePageDataBuilder` now includes backend payment settlement data for edit workspace.

A small extracted service was added to keep file length inside audit limit:
- `EditTransactionWorkspacePaymentSettlementDataBuilder`

The extracted service wraps:
- `NoteOutstandingPaymentAmountResolver::resolveFull()`

and returns nullable page data for:
- `amount_rupiah`
- `grand_total_rupiah`
- `net_paid_rupiah`
- `outstanding_rupiah`
- `explanation`

### Patch 2 - Blade render for backend explanation

`payment-modal-right.blade.php` now renders backend settlement explanation for edit workspace:
- `Settlement pembayaran backend`
- gross total
- net paid
- payable now

Audit cleanup:
- Removed `@php/@endphp` directive.
- Blade remains presentational and audit-safe.

### Patch 3 - Payment modal backend payable DOM contract

`payment-modal.blade.php` now exposes backend settlement data on `#workspace-payment-modal`:
- `data-backend-payable-rupiah`
- `data-backend-payment-basis`

The basis is expected to be:
- `backend_outstanding_settlement`

### Patch 4 - JavaScript dataset consumption

`payment-flow.js` now reads:
- `dataset.backendPayableRupiah`
- `dataset.backendPaymentBasis`

The JavaScript trusts backend payable only when:
- `backendPaymentBasis === "backend_outstanding_settlement"`

Behavior:
- Edit/full mode uses backend payable as calculator payable target when backend payable is present.
- Partial mode caps against effective backend payable when present.
- Create mode remains compatible because no backend payable dataset is required.

## Verification Proof Collected In Chat

Explanation render:
- RED: `1 failed / 3 assertions`
- Targeted GREEN: `1 passed / 6 assertions`
- Focused edit workspace after first patch: `3 passed / 19 assertions`

Modal backend payable DOM contract:
- RED: `1 failed / 3 assertions`
- Targeted GREEN: `1 passed / 4 assertions`
- Focused edit workspace after DOM patch: `4 passed / 23 assertions`

JavaScript backend payable dataset consumption:
- RED: `1 failed / 1 assertions`
- Targeted GREEN: `1 passed / 3 assertions`
- Combined edit workspace + JS contract: `5 passed / 26 assertions`

Payment adjacency after JS patch:
- `CashierDetailRenderedBillingRowsPaymentFeatureTest`: `2 passed`
- `CashierHybridPaymentSettleIntentFeatureTest`: `1 passed`
- Combined adjacency proof: `3 passed / 14 assertions`

Audit blockers encountered and fixed:
- `audit-lines` failed because `EditTransactionWorkspacePageDataBuilder.php` reached `104 lines`.
- Fixed by extracting `EditTransactionWorkspacePaymentSettlementDataBuilder`.
- `audit-lines` then passed.
- `audit-blade` failed because `payment-modal-right.blade.php` used `@php/@endphp`.
- Fixed by rendering directly from `$workspacePaymentSettlement['explanation']`.
- Owner later reported final `make verify` passed cleanly.

Final verification:
- Owner reported `make verify` safe/pass after the slice.
- Owner reported latest changes pushed safely.

## Verification Gaps

- Exact final `make verify` output was not pasted.
- Exact final pushed commit hash was not pasted.
- Browser/manual cashier modal QA was not separately reported.
- Runtime JavaScript behavior is covered by static JS contract and PHP render tests, not by browser-executed test.

## Out Of Scope Preserved

No work was done for:
- revision submit + payment merge
- customer credit
- customer balance entries
- PostgreSQL
- Go API
- dashboard
- `customer_refunds` for surplus `refund_paid`
- `refund_component_allocations` for surplus `refund_paid`
- refunded lifecycle trigger for surplus `refund_paid`
- inventory reversal for surplus `refund_paid`
- reporting/export changes

## Current Safe State

ADR 0030 cashier edit calculator consumption slice is closed.

The system now has a safer path:
- backend resolver provides payable/explanation,
- edit page consumes and renders it,
- modal exposes backend payable as DOM contract,
- JS reads backend payable dataset for calculator assistance,
- backend remains the source of final payment truth.

## Next Safe Step

Do not reopen this slice unless new regression proof appears.

Next ADR 0030 work should be chosen from remaining gaps only after reading current ADR tail and latest source.

Potential next candidates:
- browser/manual cashier modal QA for edit payment calculator display,
- stronger runtime/browser JS test if test infrastructure exists later,
- next ADR 0030 scenario matrix item not yet covered by proof.

Do not start:
- revision submit + payment merge,
- dashboard,
- customer credit,
- customer balance entries,
- PostgreSQL,
- Go API.

## Opening Prompt For Next Session

Lanjut HyperPOS ADR 0030 refund/edit/payment logic.

Owner statement accepted as FACT:
- Handoff 0020 exists at `docs/99_archive/handoff/v2/edit_refund_sniper/0020_adr_0030_cashier_edit_payment_calculator_consumption_handoff.md`.
- ADR 0030 cashier edit payment calculator consumption slice is closed.
- `make verify` after the slice passed cleanly per owner.
- Latest changes were pushed safely by owner.
- Owner handles git commit/push manually.

Current ADR:
`docs/02_architecture/adr/0030_note_revision_payment_settlement_and_cashier_calculator_contract.md`

Locked decisions:
- Payment after edit or revision must be settlement-preview-driven.
- Backend application/service must provide payable amount and explanation.
- Blade/JS may display and assist only.
- Request validator may validate payload shape only.
- Final accepted payment amount must come from backend payable/settlement logic.
- `StoreNoteRevisionRequest` currently forces `inline_payment` to `skip` for revision submit.
- Do not merge revision submit + payment unless explicitly decided later.
- Do not implement `customer_credit`, `customer_balance_entries`, PostgreSQL, Go API, or dashboard.
- Do not use `customer_refunds`, `refund_component_allocations`, refunded lifecycle, or inventory reversal for surplus `refund_paid`.

Completed in Handoff 0020:
- Edit workspace renders backend settlement explanation.
- Payment modal exposes backend payable DOM contract.
- `payment-flow.js` consumes backend payable dataset.
- Audit-lines blocker fixed by extracting `EditTransactionWorkspacePaymentSettlementDataBuilder`.
- Audit-blade blocker fixed by removing `@php/@endphp`.
- Final `make verify` and push are safe per owner.

Next step:
Read current ADR 0030 tail and latest source before choosing the next remaining ADR 0030 gap. Do not ask git status/log/diff as ceremony.
