# Handoff 0019 - ADR 0030 Payment Settlement And Cashier Calculator Contract

## Status

Closed locally and pushed by owner.

Owner statement accepted as FACT:
- ADR 0030 request-validator boundary slice pushed.
- ADR 0030 backend payable explanation slice pushed.
- `make verify` after latest ADR 0030 work passed cleanly.
- Owner handles git commit/push manually.

Exact final commit hash was not pasted in chat.
Exact final `make verify` assertion count after ADR 0030 was not pasted in chat.

## Baseline

Handoff 0018 was closed before this slice.

Accepted baseline:
- `refund_paid` backend foundation completed and verified.
- `refund_paid` audit timeline read model completed and pushed.
- transaction report dataset support for `surplus_refund_paid` completed.
- transaction cash ledger support for `surplus_refund_paid` outflow completed.
- report screen visibility for `surplus_refund_paid` and `remaining_refund_due` completed.
- Excel export parity completed.
- PDF export view data and Blade render visibility completed.
- final `make verify` after 0018 slice passed: `1014 passed / 5455 assertions`.

## ADR

Active ADR:

`docs/02_architecture/adr/0030_note_revision_payment_settlement_and_cashier_calculator_contract.md`

Locked ADR decisions:
- Payment after edit or revision must be settlement-preview-driven.
- Backend application/service must provide payable amount and explanation.
- Blade/JS may display and assist only.
- Request validator may validate shape only.
- Final accepted payment amount must come from backend payable/settlement logic.
- `StoreNoteRevisionRequest` currently forces `inline_payment` to `skip` for revision submit.
- Do not merge revision submit + payment unless explicitly decided later.
- Do not use `customer_refunds` for surplus `refund_paid`.
- Do not require `customer_payment_id` for surplus `refund_paid`.
- Do not create `refund_component_allocations` for surplus `refund_paid`.
- Do not trigger note refunded lifecycle for surplus `refund_paid`.
- Do not trigger inventory reversal for surplus `refund_paid`.
- Do not implement `customer_credit`.
- Do not implement `customer_balance_entries`.
- Do not implement PostgreSQL.
- Do not implement Go API.
- Do not start dashboard.

ADR exact anchors were patched and verified:
- `Payment after edit or revision must be settlement-preview-driven.`
- `Backend application/service must provide payable amount and explanation.`
- `Blade/JS may display and assist only.`
- `StoreNoteRevisionRequest currently forces inline_payment to skip for revision submit.`

## Completed Slice 1 - Request Validator Boundary

Files touched:
- `app/Adapters/In/Http/Requests/Note/StoreTransactionWorkspacePaymentValidator.php`
- `tests/Unit/Adapters/In/Http/Requests/Note/StoreTransactionWorkspacePaymentValidatorTest.php`
- `docs/02_architecture/adr/0030_note_revision_payment_settlement_and_cashier_calculator_contract.md`

Problem proven:
- `pay_full` cash validation used payload grand total as the cash target.
- Existing paid allocations / backend settlement payable were not considered at request-validator level.
- A payment after edit could be rejected before backend settlement/payable logic determined the accepted amount.

RED proof:
- Scenario: payload grand total `100000`, backend payable intent represented by cash received `60000`.
- Test: `StoreTransactionWorkspacePaymentValidatorTest::test_pay_full_cash_received_is_not_validated_against_payload_grand_total`
- Failure field: `inline_payment.amount_received_rupiah`.
- Error: `Uang masuk cash tidak boleh kurang dari total yang dibayar.`
- Proof: `1 failed / 1 assertions`.

Patch:
- `pay_full` cash no longer compares `amount_received_rupiah` against payload grand total.
- `pay_partial` cash still compares `amount_received_rupiah` against explicit `amount_paid_rupiah`.
- Request validator remains shape/boundary validation only.
- Backend settlement/payable logic remains responsible for accepted payable amount.

GREEN and regression proof:
- Targeted validator proof: `1 passed / 1 assertions`.
- Validator regression proof: `2 passed / 2 assertions`.
- Focused validator + workspace update adjacency: `4 passed / 18 assertions`.

Regression locked:
- `pay_partial` cash with `amount_paid_rupiah = 60000` and `amount_received_rupiah = 50000` still fails on `inline_payment.amount_received_rupiah`.

## Completed Slice 2 - Backend Payable Explanation Contract

Files touched:
- `app/Application/Note/Services/NoteOutstandingPaymentAmountResolver.php`
- `tests/Feature/Note/NoteOutstandingPaymentAmountResolverFeatureTest.php`
- likely ADR 0030 docs if final local patch added implementation notes.

Problem proven:
- `NoteOutstandingPaymentAmountResolver::resolveFull()` already returned payable amount fields:
  - `amount_rupiah`
  - `grand_total_rupiah`
  - `net_paid_rupiah`
  - `outstanding_rupiah`
- It did not return backend-built `explanation`.
- This violated ADR 0030 contract: backend application/service must provide payable amount and explanation.

Baseline proof:
- `NoteOutstandingPaymentAmountResolverFeatureTest`
- `1 passed / 5 assertions`

RED proof:
- Test: `NoteOutstandingPaymentAmountResolverFeatureTest::test_active_refund_reopens_outstanding_amount_for_normal_note`
- Failure: result data missing `explanation`.
- Exact failure: `Failed asserting that an array has the key 'explanation'.`
- RED result: `1 failed / 6 assertions`.

Patch:
- `NoteOutstandingPaymentAmountResolver::resolveFull()` now returns backend-built `explanation`.
- `NoteOutstandingPaymentAmountResolver::resolvePartial()` should also return backend-built `explanation`.

Minimum explanation contract:
- `basis = backend_outstanding_settlement`
- `gross_total_rupiah`
- `net_paid_rupiah`
- `outstanding_rupiah`

Owner reported:
- changes pushed.
- `make verify` passed cleanly after latest ADR 0030 work.

## Known Source Facts

`StoreNoteRevisionRequest` currently normalizes revision submit payment to:

- `inline_payment.decision = skip`
- `inline_payment.payment_method = null`
- `inline_payment.paid_at = null`
- `inline_payment.amount_paid_rupiah = null`
- `inline_payment.amount_received_rupiah = null`

So revision submit and payment remain separated unless a later ADR explicitly merges them.

Relevant source candidates for next slice:
- `app/Application/Note/Services/EditTransactionWorkspacePageDataBuilder.php`
- `app/Application/Note/Services/NoteBillingProjectionBuilder.php`
- `app/Application/Note/Services/NoteBillingProjectionRowMapper.php`
- `app/Application/Note/Services/SelectedNoteRowsPaymentAmountResolver.php`
- `app/Application/Note/Services/SelectedNoteRowsOutstandingTotalResolver.php`
- `app/Application/Payment/Services/ResolveNotePayableComponents.php`
- `app/Application/Payment/Services/ResolveNotePayableComponentsSelectedRows.php`
- `app/Application/Payment/Services/RecordAndAllocateNotePaymentOperation.php`

## Gaps

- Exact final commit hash for ADR 0030 slices was not pasted in chat.
- Exact final `make verify` assertion count after ADR 0030 was not pasted in chat.
- Browser/manual cashier edit-payment QA was not separately reported.
- Next cashier calculator/display consumption slice has not started.
- No proof yet that cashier edit UI consumes backend payable explanation instead of deriving its own final payment truth.

## Next Safe Step

Do not reopen completed validator or resolver slices unless new regression proof appears.

Next active step:
- Read current ADR 0030 tail.
- Read `NoteOutstandingPaymentAmountResolver`.
- Read `NoteOutstandingPaymentAmountResolverFeatureTest`.
- Read cashier/edit page data builders and billing projection services.
- Choose first RED for cashier calculator/display consumption of backend payable explanation.
- Do not start JS/Blade patch before RED proves missing backend data consumption contract.
- Do not patch production before RED.

Recommended first investigation files:
- `app/Application/Note/Services/EditTransactionWorkspacePageDataBuilder.php`
- `app/Application/Note/Services/NoteBillingProjectionBuilder.php`
- `app/Application/Note/Services/NoteBillingProjectionRowMapper.php`
- `app/Application/Note/Services/SelectedNoteRowsPaymentAmountResolver.php`
- `app/Application/Note/Services/SelectedNoteRowsOutstandingTotalResolver.php`
- `tests/Feature/Note/EditTransactionWorkspacePageFeatureTest.php`
- `tests/Feature/Note/CashierDetailRenderedBillingRowsPaymentFeatureTest.php`
- `tests/Feature/Note/CashierHybridPaymentSettleIntentFeatureTest.php`

## Opening Prompt For Next Session

Lanjut HyperPOS ADR 0030 refund/edit/payment logic.

Owner statement accepted as FACT:
- Handoff 0019 exists or should be verified at `docs/99_archive/handoff/v2/edit_refund_sniper/0019_adr_0030_payment_settlement_calculator_contract_handoff.md`.
- ADR 0030 request-validator boundary slice pushed.
- ADR 0030 backend payable explanation slice pushed.
- `make verify` after latest ADR 0030 work passed cleanly.
- Owner handles git commit/push manually.

Current locked ADR:
`docs/02_architecture/adr/0030_note_revision_payment_settlement_and_cashier_calculator_contract.md`

Locked decisions:
- Payment after edit or revision must be settlement-preview-driven.
- Backend application/service must provide payable amount and explanation.
- Blade/JS may display and assist only.
- Request validator may validate shape only.
- Final payment amount acceptance must come from backend payable/settlement logic.
- `StoreNoteRevisionRequest` currently forces `inline_payment` skip for revision submit.
- Do not merge revision submit + payment unless explicitly decided later.
- Do not implement `customer_credit`, `customer_balance_entries`, PostgreSQL, Go API, dashboard.
- Do not use `customer_refunds`, `refund_component_allocations`, refunded lifecycle, or inventory reversal for surplus `refund_paid`.

Completed:
1. `StoreTransactionWorkspacePaymentValidator` no longer validates `pay_full` cash received against payload grand total.
2. `pay_partial` cash still requires received cash to cover explicit `amount_paid_rupiah`.
3. `NoteOutstandingPaymentAmountResolver` now provides backend payable amount and explanation.
4. `make verify` passed after push per owner.

Next active step:
Read current ADR 0030 tail and source anchors for cashier/edit page payment preview consumption.
Do not patch production before RED.
Do not ask git status/log/diff as ceremony.
