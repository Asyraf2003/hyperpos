# Handoff 0021 - ADR 0030 S12 Surplus Refund Paid Carry Forward

## Status

Source/test patch locally verified for ADR 0030 S12.

Not yet globally verified.

Not yet documented in ADR 0030.

Owner handles git commit/push manually.

## Active ADR

`docs/02_architecture/adr/0030_note_revision_payment_settlement_and_cashier_calculator_contract.md`

## Slice

ADR 0030 S12:

`refund_paid exists then later revision -> refund_paid remains cash-out`

## Locked Decisions Preserved

- Payment after edit or revision must be settlement-preview-driven.
- Backend application/service must provide payable amount and explanation.
- Blade/JS may display and assist only.
- Request validator may validate payload shape only.
- Final accepted payment amount must come from backend payable/settlement logic.
- `StoreNoteRevisionRequest` currently forces `inline_payment` to skip for revision submit.
- Do not merge revision submit + payment unless explicitly decided later.
- Do not implement `customer_credit`.
- Do not implement `customer_balance_entries`.
- Do not implement PostgreSQL.
- Do not implement Go API.
- Do not implement dashboard.
- Do not use `customer_refunds`, `refund_component_allocations`, refunded lifecycle, or inventory reversal for surplus `refund_paid`.

## Problem Proven

A later revision could reclaim surplus `refund_paid` as if the money was still available.

Scenario:

- Original paid amount: `265000`
- Downward revised total: `143000`
- Surplus: `122000`
- `refund_due`: `122000`
- Active surplus `refund_paid`: `50000`
- Later revision total: `230000`

Expected later revision settlement:

- `carry_forward_paid_rupiah`: `265000`
- `carry_forward_refunded_rupiah`: `50000`
- `net_paid_rupiah`: `215000`
- `outstanding_rupiah`: `15000`
- `surplus_rupiah`: `0`
- `settlement_status`: `underpaid`

Actual RED result before patch:

- `carry_forward_paid_rupiah`: `265000`
- `carry_forward_refunded_rupiah`: `0`
- `net_paid_rupiah`: `265000`
- `outstanding_rupiah`: `0`
- `surplus_rupiah`: `35000`
- `settlement_status`: `overpaid_pending`

Conclusion:

The system treated actual surplus cash-out as still available money.

## RED Proof

Test file:

`tests/Feature/Note/CreateNoteRevisionSurplusRefundPaidCarryForwardFeatureTest.php`

Test:

`test_later_revision_does_not_reclaim_surplus_refund_paid_as_available_payment`

First invalid fixture failure was fixed:

- `service.price_rupiah` had to be integer, not string.
- Source mapper required integer service price.

Valid RED proof:

`assertDatabaseHas('note_revision_settlements', ...)` failed.

Actual row found:

- `gross_total_rupiah`: `230000`
- `carry_forward_paid_rupiah`: `265000`
- `carry_forward_refunded_rupiah`: `0`
- `net_paid_rupiah`: `265000`
- `outstanding_rupiah`: `0`
- `surplus_rupiah`: `35000`
- `settlement_status`: `overpaid_pending`

## Patch Summary

Minimal compatibility patch.

Surplus `refund_paid` is treated as cash-out during later revision settlement carry-forward.

Implementation decision:

`active surplus refund_paid by note_root_id` is added into `carryForwardRefunded`.

Formula after patch:

`carryForwardRefunded = ordinary/component refunded + activeSurplusRefundPaidByNoteRootId`

This avoids reclaiming already paid-out surplus cash.

## Files Changed

Production/source:

- `app/Ports/Out/Note/NoteRevisionSurplusRefundPaymentReaderPort.php`
- `app/Adapters/Out/Note/DatabaseNoteRevisionSurplusRefundPaymentAdapter.php`
- `app/Application/Note/Services/BuildCreateNoteRevisionSettlement.php`

Tests:

- `tests/Feature/Note/CreateNoteRevisionSurplusRefundPaidCarryForwardFeatureTest.php`
- `tests/Unit/Application/Note/Services/BuildCreateNoteRevisionSettlementTest.php`

## Source Changes

### Port

`NoteRevisionSurplusRefundPaymentReaderPort` now exposes:

`sumActiveAmountByNoteRootId(string $noteRootId): int`

Existing methods preserved:

- `findActiveByDispositionIdAndIdempotencyKey`
- `sumActiveAmountByDispositionId`

### Adapter

`DatabaseNoteRevisionSurplusRefundPaymentAdapter` now sums active surplus refund payments by note root:

- table: `note_revision_surplus_refund_payments`
- filter: `note_root_id`
- filter: `status = active`
- sum: `amount_rupiah`

### Settlement Builder

`BuildCreateNoteRevisionSettlement` now depends on:

`NoteRevisionSurplusRefundPaymentReaderPort`

It computes:

- component/legacy paid as before
- component/legacy refunded as ordinary refunded
- active surplus refund paid by note root
- carry-forward refunded as ordinary refunded plus active surplus refund paid

## Verification Proof

Syntax proof:

- `php -l app/Ports/Out/Note/NoteRevisionSurplusRefundPaymentReaderPort.php`
- `php -l app/Adapters/Out/Note/DatabaseNoteRevisionSurplusRefundPaymentAdapter.php`
- `php -l app/Application/Note/Services/BuildCreateNoteRevisionSettlement.php`
- `php -l tests/Unit/Application/Note/Services/BuildCreateNoteRevisionSettlementTest.php`
- `php -l tests/Feature/Note/CreateNoteRevisionSurplusRefundPaidCarryForwardFeatureTest.php`

Result:

All passed with no syntax errors.

Targeted GREEN proof:

Command:

`php artisan test tests/Feature/Note/CreateNoteRevisionSurplusRefundPaidCarryForwardFeatureTest.php --filter=later_revision_does_not_reclaim_surplus_refund_paid_as_available_payment`

Result:

`1 passed / 3 assertions`

Unit proof:

Command:

`php artisan test tests/Unit/Application/Note/Services/BuildCreateNoteRevisionSettlementTest.php`

Result:

`4 passed / 22 assertions`

Focused blast-radius proof:

Command:

`php artisan test tests/Feature/Note/CreateNoteRevisionSurplusRefundPaidCarryForwardFeatureTest.php tests/Feature/Note/RecordNoteRevisionSurplusRefundPaymentHandlerTest.php tests/Feature/Note/DatabaseNoteRevisionSurplusRefundPaymentAdapterTest.php tests/Feature/Note/CreateNoteRevisionSurplusRefundDueHandlerTest.php tests/Feature/Note/CashierNoteRevisionSubmitFeatureTest.php tests/Unit/Application/Note/Services/BuildCreateNoteRevisionSettlementTest.php`

Result:

`21 passed / 102 assertions`

Covered focused areas:

- S12 later revision carry-forward
- surplus refund_paid handler
- surplus refund_paid adapter/source reader
- refund_due handler
- cashier revision submit guard adjacency
- settlement builder unit behavior

## Verification Gaps

- Full `make verify` has not been run after this patch.
- ADR 0030 has not yet been updated with S12 implementation verification.
- No browser/manual QA was run.
- No reporting/export proof was run.
- No full Note+Payment suite was run.
- No commit/push proof is recorded in this handoff.

## Residual Design Gap

This patch folds surplus `refund_paid` into `carry_forward_refunded_rupiah`.

That is acceptable as a compatibility fix for S12 because it prevents reclaiming cash-out money.

Longer-term cleaner model may need explicit first-class settlement fields such as:

- `surplus_refund_paid_rupiah`
- `remaining_refund_due_rupiah`

Do not add those fields in this slice unless ADR explicitly opens a DB/schema slice.

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
- report/export query changes
- browser-executed JavaScript tests

## Current Safe State

S12 source/test patch is locally green in targeted, unit, and focused blast-radius tests.

The system no longer treats active surplus `refund_paid` as available carry-forward money during later revision settlement.

## Next Safe Step

Run full verification before docs closure:

`make verify`

If full verification passes:

1. Update ADR 0030 Implementation Verification with S12.
2. Include RED proof, source map, GREEN proof, focused proof, and residual gap.
3. Owner commits/pushes manually.

If full verification fails:

1. Treat failure output as source of truth.
2. Fix only the failing scope.
3. Do not update ADR 0030 to fixed until verification is clean or explicitly caveated.
