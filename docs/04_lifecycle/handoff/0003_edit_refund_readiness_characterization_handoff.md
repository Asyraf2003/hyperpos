# Edit Refund Readiness Characterization Handoff

## Metadata
- Date: 2026-05-23
- Slice / topic: Edit/refund readiness analysis and characterization planning
- Workflow step: Phase 1B, Phase 1C, Phase 1D planning, and Phase 1D source-map correction
- Status: continue in next session
- Progress: docs/source-map complete for current session; next step is test-only characterization patch

## Target Work Page

Target repo:

- `Asyraf2003/hyperpos`

Target domain:

- transaction create/edit
- active note revision submit
- ordinary refund
- revision settlement carry-forward
- surplus/refund_due/refund_paid readiness
- current/historical row boundary
- projection consistency

The next active implementation-safe page is:

- Phase 1D-1 - Missing carry-forward settlement feature characterization

Target test file for next session:

- `tests/Feature/Note/NoteRevisionSettlementCarryForwardFeatureTest.php`

## References Used

- Handoff rules:
  - `docs/04_lifecycle/handoff/README.md`
- Handoff template:
  - `docs/01_standards/0005_handoff_template.md`
- Existing matrix:
  - `docs/03_blueprints/db/0015_create_edit_transaction_contract_matrix.md`
- New readiness analysis:
  - `docs/03_blueprints/db/0016_edit_refund_readiness_analysis.md`
- New characterization plan:
  - `docs/03_blueprints/db/0017_edit_refund_characterization_plan.md`
- Finance blueprint:
  - `docs/03_blueprints/finance/0006_note_revision_refund_ledger.md`
- ADR:
  - `docs/02_architecture/adr/0027_note_revision_surplus_disposition_transaction_contract.md`
  - `docs/02_architecture/adr/0029_note_revision_surplus_refund_paid_execution.md`
  - `docs/02_architecture/adr/0030_note_revision_payment_settlement_and_cashier_calculator_contract.md`
- Existing source/tests inspected:
  - `routes/web/note.php`
  - `app/Application/Note/Services/BuildCreateNoteRevisionSettlement.php`
  - `app/Application/Note/Services/BuildNoteRevisionSettlement.php`
  - `app/Application/Note/DTO/NoteRevisionSettlement.php`
  - `tests/Unit/Application/Note/Services/BuildCreateNoteRevisionSettlementTest.php`
  - `tests/Feature/Note/CreateNoteRevisionSurplusRefundPaidCarryForwardFeatureTest.php`
  - `tests/Feature/Note/NoteReplacementOverpaidAllocationReplayFeatureTest.php`
  - `tests/Support/SeedsMinimalNotePaymentFixture.php`

## Locked Facts

- Active create route is `POST /notes/workspace/store`.
- Active create controller is `StoreTransactionWorkspaceController`.
- Active create request is `StoreTransactionWorkspaceRequest`.
- Active create handler is `CreateTransactionWorkspaceHandler`.
- Active admin/cashier edit submit route is revision-based:
  - `PATCH /admin/notes/{noteId}/workspace`
  - `PATCH /cashier/notes/{noteId}/workspace`
- Active edit submit controller is `StoreNoteRevisionController`.
- Active edit submit request is `StoreNoteRevisionRequest`.
- Active edit submit handler is `CreateNoteRevisionHandler`.
- `StoreNoteRevisionRequest` forces `inline_payment.decision = skip`.
- Therefore active revision submit and inline payment submit are separate concerns.
- `UpdateTransactionWorkspaceController` and `UpdateTransactionWorkspaceHandler` exist, but no active route binding was proven from `routes/web/note.php`.
- Do not patch `UpdateTransactionWorkspaceHandler` as active edit behavior until route binding proof or dead-path decision exists.
- Active ordinary refund route uses:
  - `RecordClosedNoteRefundController`
  - `RecordClosedNoteRefundRequest`
  - `SelectedNoteRowsRefundPlanResolver`
  - `RecordSelectedRowsRefundPlanTransaction`
- Finance architecture direction is Ledger + Revision Snapshot + Current Projection.
- UI and API are transport adapters only.
- Backend must compute payable/refund/surplus/inventory truth.
- Blade/JavaScript may display and assist only.
- Customer credit remains blocked until stable customer identity contract exists.
- Phase 1D source-map found existing coverage:
  - `BuildCreateNoteRevisionSettlementTest` covers settlement builder unit math.
  - `CreateNoteRevisionSurplusRefundPaidCarryForwardFeatureTest` covers later revision after surplus `refund_paid`.
  - `NoteReplacementOverpaidAllocationReplayFeatureTest` covers downward paid revision creating `overpaid_pending` surplus settlement.
- Therefore the next useful test must target missing integration gaps, not duplicate existing surplus/refund_paid coverage.

## Scope Used

### SCOPE-IN

- Create/edit route inspection.
- Active edit/revision route distinction.
- Refund route inspection.
- Finance blueprint and ADR alignment.
- Readiness analysis doc creation.
- Characterization plan doc creation.
- Source-map correction after finding existing tests.
- Next test-only target selection.

### SCOPE-OUT

- Production code changes.
- Migration changes.
- UI/Blade/JavaScript changes.
- Report/export changes.
- API changes.
- Remote write.
- Full test suite run.
- Browser/manual QA.
- Implementation patch.
- Combined revision-plus-payment submit.
- Customer credit/customer_balance_entries implementation.
- Patching `UpdateTransactionWorkspaceHandler`.

## GAP

- No local test command output was produced in this session.
- No production runtime behavior was changed.
- Full implementation status of every settlement/disposition/refund-paid table, reader, writer, and report reader was not exhaustively mapped.
- Full canonical audit_events/audit_event_snapshots writer status was not exhaustively mapped.
- Full browser-executed UI behavior was not verified.
- Full report/export behavior after edit/refund/surplus/refund_paid was not verified.
- True two-connection concurrency stress proof was not performed.
- Existing route binding for `UpdateTransactionWorkspaceController` remains unproven.
- Source-map did not prove existing feature coverage for:
  - active revision after partial payment where revised total becomes underpaid/outstanding;
  - active revision after ordinary refund where refund must be counted exactly once.

## Locked Decisions

- Phase 1B create/edit source inspection addendum is closed.
- Phase 1C edit/refund readiness analysis is closed.
- Phase 1D characterization plan is closed as planning.
- Phase 1D source-map correction is closed.
- Next active step is test-only.
- Production files remain forbidden for Phase 1D-1.
- Do not start from UI.
- Do not generalize create flow into edit/refund.
- Do not merge revision submit and payment submit.
- Do not patch reports yet.
- Do not introduce customer credit.
- Do not touch high-risk delete/rebuild paths without direct characterization proof.
- Do not duplicate existing surplus refund_paid or downward surplus tests.
- First active test slice is:
  - `tests/Feature/Note/NoteRevisionSettlementCarryForwardFeatureTest.php`

## Files Created / Changed

### New files

- `docs/03_blueprints/db/0016_edit_refund_readiness_analysis.md`
- `docs/03_blueprints/db/0017_edit_refund_characterization_plan.md`

### Changed files

- `docs/03_blueprints/db/0015_create_edit_transaction_contract_matrix.md`
  - Added Phase 1B Inspection Addendum.
- `docs/03_blueprints/db/0017_edit_refund_characterization_plan.md`
  - Added Phase 1D Source Map Correction.

### Handoff file

- `docs/04_lifecycle/handoff/0003_edit_refund_readiness_characterization_handoff.md`

## Verification Proof

### Phase 1B addendum proof

Command output from operator:

```text
279:## Phase 1B Inspection Addendum
291:### Route / Controller / Request Map
316:### Table Write Map
340:### Persistence Adapter Write Map
353:### Projection Sync Output Map
387:### Existing Tests Inventory
397:### Missing Characterization Tests
411:### First Safest Test Patch Candidate
298:| Legacy / candidate update workspace controller | No active route found in inspected `routes/web/note.php` | ...
305:| `StoreNoteRevisionRequest` ... Forces `inline_payment.decision = skip` ...
435:Do not patch `UpdateTransactionWorkspaceHandler` until route binding is proven or a dead-path decision is recorded.

Meaning:

Phase 1B addendum exists.
Route guardrail exists.
StoreNoteRevisionRequest inline payment skip guardrail exists.
UpdateTransactionWorkspaceHandler no-patch guardrail exists.
Phase 1C readiness analysis proof

Command output from operator:

26:## FACT
108:## GAP
147:## ASSUMPTION
203:## DECISION
249:## Readiness Matrix
265:## Stop Conditions
280:## Recommended Next Slice
217:### D2 - Do not generalize create flow into edit/refund
227:StoreNoteRevisionRequest forcing inline_payment skip is a safety boundary.
233:Do not patch UpdateTransactionWorkspaceHandler as active production edit behavior until route binding proof or a dead-path decision is recorded.
245:### D7 - Customer credit remains blocked without customer identity contract

Meaning:

Phase 1C doc exists.
FACT/GAP/ASSUMPTION/DECISION separation exists.
Critical guardrails exist.
Phase 1D characterization plan proof

Command output from operator:

17:## FACT
96:## GAP
130:## ASSUMPTION
186:## DECISION
227:## Characterization Test Candidates
481:## Recommended Test Order
515:## First Active Test Slice Proposal
550:## Stop Conditions
200:### D3 - Do not merge revision submit and payment submit
214:### D6 - Do not touch high-risk delete/rebuild paths casually
519:Phase 1D-1 - Revision carry-forward settlement characterization.
530:### Production files allowed
532:None.

Meaning:

Phase 1D characterization plan exists.
Production files are forbidden.
Revision-payment merge is forbidden.
High-risk delete/rebuild paths are protected.
Phase 1D source-map correction proof

Command output from operator:

588:## Phase 1D Source Map Correction
598:### Existing settlement unit coverage
609:### Existing surplus refund_paid feature coverage
623:### Existing downward overpaid/surplus feature coverage
645:### G1 - Partial payment revision integration proof not yet found
656:### G2 - Ordinary refund carry-forward integration proof not yet found
678:### D1 - Update Phase 1D-1 target
711:## Updated First Active Test Slice

Meaning:

Source-map correction exists.
Existing coverage is recorded.
Remaining true gaps are recorded.
Updated first active test slice is recorded.
Risks / Follow-up Notes
Existing tests were inspected via source, but not run locally in this session.
Next session must not assume existing tests are green without command proof.
Next session must not write production code first.
Next session must not create duplicate tests for already-covered surplus refund_paid or downward surplus.
Next session must use existing fixtures where possible.
If fixture requires guessing columns, stop and source-map migrations/tests first.
If the new test is GREEN, record proof and continue to next gap.
If the new test is RED, only then propose a narrow backend patch.
If the new test cannot be built safely, record fixture/source-map GAP instead of guessing.
Next Step

Single next active step:

Create test-only characterization file:

tests/Feature/Note/NoteRevisionSettlementCarryForwardFeatureTest.php

Initial tests:

test_revision_after_partial_payment_carries_paid_amount_into_underpaid_settlement
test_revision_after_ordinary_refund_counts_refund_once_in_settlement

Expected command after patch:

php artisan test tests/Feature/Note/NoteRevisionSettlementCarryForwardFeatureTest.php

Adjacency command after targeted proof:

php artisan test \
  tests/Unit/Application/Note/Services/BuildCreateNoteRevisionSettlementTest.php \
  tests/Feature/Note/CreateNoteRevisionSurplusRefundPaidCarryForwardFeatureTest.php \
  tests/Feature/Note/NoteReplacementOverpaidAllocationReplayFeatureTest.php \
  tests/Feature/Note/NoteRevisionSettlementCarryForwardFeatureTest.php

