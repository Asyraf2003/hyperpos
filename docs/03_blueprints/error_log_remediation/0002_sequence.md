# Error Log Remediation Sequence

- Status: Planning sequence.
- Scope: execution order for fixes and verification across `docs/04_lifecycle/error_log/`.
- Non-goal: this document is not a source patch, not a commit, and not a claim that every issue is finished.

## Sequence Principles

- The error log is a single chain.
- Execution rules:
  - only one active slice at a time
  - do not move to the next slice before the active slice has complete proof
  - do not order work by file number
  - order by dependency, source boundary, and domain impact
  - a fixed issue with weak proof goes into a verification slice
  - if source conflicts with docs, source and test proof win
  - if a newer ADR conflicts with an older ADR, the newer ADR wins unless a more specific document gives a more precise rule
  - seeder is outside the main workflow and remains future scope

## Legend

- Trust status:
  - `trusted`: the document, source, and test proof are sufficient for the claimed scope
  - `weak`: a patch or claim exists, but proof is incomplete or the gap is large
  - `contradicted`: documents, source, or tests conflict with each other
  - `unknown`: not enough has been read yet
- Affected layer:
  - domain
  - application
  - infrastructure
  - HTTP/controller
  - Blade
  - JS
  - security
  - docs

## Global Repair Order

### Slice 0 - Baseline Intake and Source Reality

- Goals:
  - inventory all 29 error logs
  - apply source priority
  - create the initial trust status
  - build the dependency graph
  - make no patch
- Why first:
  - without a baseline, every later patch can become a ritual of moving the bug from one shelf to another, a classic software hobby when no one is watching
- Stop gate before moving on:
  - every error log is mapped
  - weak / contradicted issues are marked
  - conflicts `#001/#003` and `#021/#022` are recorded
  - the first active slice is selected
  - no source changes have been made

### Slice 1 - Current vs Historical Operational Row Foundation

- Issues:
  - `docs/04_lifecycle/error_log/0004_refunded_work_items_survive_revisions_and_inflate_stock.md`
  - `docs/04_lifecycle/error_log/0012_canceled_note_rows_re_enter_payment_flows.md`
- Why this order:
  - the current vs historical row boundary determines payment selection, refund selection, inventory reversal, workspace projection, and reporting
  - if the row boundary is wrong, settlement/refund tests can pass on the wrong data
- Can be combined:
  - analysis of current operational rows
  - payment / refund row visibility
  - historical anchor preservation
- Must be split:
  - reporting rewrite
  - new projection schema
  - DB uniqueness for inventory reversal
  - seeder
- Stop gate:
  - current rows and historical rows have a source boundary
  - canceled / refunded / superseded rows do not enter payable / refundable paths without a policy
  - inventory reversal idempotency proof is not broken

#### Issue Card 004

- error_log path: `docs/04_lifecycle/error_log/0004_refunded_work_items_survive_revisions_and_inflate_stock.md`
- current document status: Fixed with proof
- trust status: `trusted` for Note / Payment / current operational flow; `weak` for direct reporting from `work_items`
- provisional root cause: refunded historical `work_items` remain attached as active rows and duplicate inventory reversal can inflate stock
- affected layer: domain, application, infrastructure, HTTP/controller, security, docs
- upstream dependency: current / historical row model, revision lifecycle
- downstream dependency: `#012`, `#013`, `#014`, `#017`, reporting audit
- RED proof needed: re-run or preserve a test showing stale refunded historical row is not a current operational row and duplicate reversal does not happen
- minimal patch boundary: current projection / workspace row source and reversal idempotency only
- focused test target: `RevisionAfterRefundPreservesHistoricalWorkItemsFeatureTest`, `ReverseIssuedInventoryOperationFeatureTest`
- wider regression target: `tests/Feature/Note`, `tests/Feature/Payment`, inventory-focused tests
- closure proof: targeted + Note / Payment proof, residual reporting gap recorded
- handoff template: `"004 current/historical row boundary: source=<paths>, red=<command>, green=<command>, wider=<command>, residual_reporting=<yes/no>"`

#### Issue Card 012

- error_log path: `docs/04_lifecycle/error_log/0012_canceled_note_rows_re_enter_payment_flows.md`
- current document status: Patched, with verification gap and residual audit note
- trust status: `weak`
- provisional root cause: canceled rows are included in `note->workItems()`, but payment / status consumers still treat all rows as active
- affected layer: domain, application, payment, status correction, security, docs
- upstream dependency: `#004` current / historical row boundary
- downstream dependency: `#013`, `#014`, `#021` refund eligibility, payment selection
- RED proof needed: canceled selected row is rejected, canceled cannot transition to done, and a full-note / no-selected flow does not allocate a canceled row
- minimal patch boundary: payable component resolver, status transition service, billing projection if needed
- focused test target: `ResolveNotePayableComponentsTest`, `WorkItemStatusTransitionServiceTest`, selected-row HTTP payment test
- wider regression target: `tests/Feature/Note`, `tests/Feature/Payment`
- closure proof: focused unit / feature tests pass and the residual `fromNote()` audit is either closed or explicitly deferred
- handoff template: `"012 canceled rows: selected=<proof>, full_note=<proof>, status_transition=<proof>, billing_projection=<proof>, residual=<gap>"`

### Slice 2 - Settlement and Payment Basis Foundation

- Issues:
  - `docs/04_lifecycle/error_log/0001_refunds_counted_as_paid_in_note_totals.md`
  - `docs/04_lifecycle/error_log/0003_refunded_revised_notes_are_misclassified_as_underpaid.md`
  - `docs/04_lifecycle/error_log/0005_note_revision_silently_drops_overpaid_allocations.md`
  - `docs/04_lifecycle/error_log/0008_legacy_paid_notes_can_be_paid_again.md`
  - `docs/04_lifecycle/error_log/0017_workspace_edit_payments_ignore_existing_note_payments.md`
- Why this order:
  - payment / refund arithmetic is the foundation for paid status, outstanding, editability, refund eligibility, and concurrency
  - `#001` and `#003` have conflicting settlement semantics, so they must be verified together
  - `#008` and `#017` ensure legacy / component / existing payment basis does not create double payment
  - `#005` ensures downward revision does not hide overpaid allocation
- Can be combined:
  - settlement basis verification matrix
  - legacy / component allocation compatibility
  - inline payment outstanding
  - carry-forward current refund semantics
- Must be split:
  - explicit customer credit / overpaid-product feature
  - reporting rewrite
  - seeder
  - true parallel concurrency stress, which belongs in Slice 3
- Stop gate:
  - active refund on a normal note and revised historical refund both pass
  - legacy / component mixed allocation does not overpay
  - inline `pay_full` uses outstanding, not the full total
  - downward overpaid revision is either rejected with rollback or the explicit domain model exists

#### Issue Card 001

- error_log path: `docs/04_lifecycle/error_log/0001_refunds_counted_as_paid_in_note_totals.md`
- current document status: Patched
- trust status: `contradicted`
- provisional root cause: active refund is counted as allocated and then subtracted again, making refund neutral; later `#003` changes the settlement basis
- affected layer: application, infrastructure, domain, payment, docs
- upstream dependency: `#003` current-refund semantics
- downstream dependency: `#003`, `#008`, `#017`, `#026`
- RED proof needed: an active refund on a normal note with total 50,000, payment 50,000, and refund 10,000 produces net paid 40,000 and outstanding 10,000
- minimal patch boundary: paid status / refund reader semantics, not a generic reader revert without consumer proof
- focused test target: `NotePaidStatusPolicyTest`, payment allocation reader tests
- wider regression target: `tests/Feature/Note`, `tests/Feature/Payment`
- closure proof: active refund and revised historical refund both pass
- handoff template: `"001 conflict with 003: active_refund=<proof>, revised_historical=<proof>, selected_patch_boundary=<path>"`

#### Issue Card 003

- error_log path: `docs/04_lifecycle/error_log/0003_refunded_revised_notes_are_misclassified_as_underpaid.md`
- current document status: Fixed with proof
- trust status: `trusted`
- provisional root cause: historical refund after revision was subtracted again from carry-forward settlement
- affected layer: domain, application, infrastructure, payment, docs
- upstream dependency: `#001` conflict
- downstream dependency: `#005`, `#008`, `#017`
- RED proof needed: carry-forward settlement 200,000 with historical refund ledger 100,000 remains paid for revised total 200,000
- minimal patch boundary: current-refund settlement boundary in paid status policy / refund reader
- focused test target: `NotePaidStatusPolicyTest`
- wider regression target: Note + Payment feature suites
- closure proof: RED before patch, targeted 4 passed, relevant blast radius, Note + Payment proof
- handoff template: `"003 current refund boundary: source=<paths>, red=<output>, green=<output>, regression_001=<output>"`

#### Issue Card 005

- error_log path: `docs/04_lifecycle/error_log/0005_note_revision_silently_drops_overpaid_allocations.md`
- current document status: Fixed and verified
- trust status: `trusted`
- provisional root cause: revision payment replay silently capped the old allocation and hid the overpaid excess
- affected layer: domain, application, payment, docs
- upstream dependency: `#003` carry-forward settlement, `#004` current rows
- downstream dependency: explicit overpaid / customer credit future model
- RED proof needed: a downward revision where the old paid amount is greater than the revised payable rejects instead of silently capping
- minimal patch boundary: `NoteReplacementPaymentAllocationReconciler::rebuild()` and tests; no customer-credit feature yet
- focused test target: `NoteReplacementOverpaidAllocationReplayFeatureTest`
- wider regression target: product / service-stock replacement finance tests, Note + Payment
- closure proof: reject + rollback behavior proven, original allocations remain, Note + Payment pass
- handoff template: `"005 overpaid replay: behavior=reject_rollback, tests=<commands>, future_overpaid_model=<deferred>"`

#### Issue Card 008

- error_log path: `docs/04_lifecycle/error_log/0008_legacy_paid_notes_can_be_paid_again.md`
- current document status: Patched and locally verified for backend payment allocation / projection scope
- trust status: `trusted` for the backend scope
- provisional root cause: selected-row payment ignored legacy allocation and later mixed legacy / component totals
- affected layer: application, infrastructure, payment, UI data, docs
- upstream dependency: `#001/#003` settlement basis
- downstream dependency: `#017`, `#026`, reporting / migration
- RED proof needed: legacy paid note rejected; mixed legacy 40,000 + component 10,000 on a 100,000 total only allows 50,000
- minimal patch boundary: compatibility allocated-total reader and row-settlement projectors
- focused test target: `RecordNotePaymentHttpFeatureTest`, `NoteOperationalRowSettlementProjectorTest`
- wider regression target: `tests/Feature/Payment`, `tests/Feature/Note`
- closure proof: targeted mixed allocation pass, focused pass, Note + Payment pass; migration double-count risk documented
- handoff template: `"008 compatibility allocation: legacy=<proof>, mixed=<proof>, migration_risk=<noted>"`

#### Issue Card 017

- error_log path: `docs/04_lifecycle/error_log/0017_workspace_edit_payments_ignore_existing_note_payments.md`
- current document status: Fixed and verified
- trust status: `trusted`
- provisional root cause: inline payment `pay_full` and policy ignored the existing allocated total
- affected layer: application, payment, workspace, UI data, docs
- upstream dependency: `#008` compatibility allocation
- downstream dependency: `#026` concurrency, selected-row projection
- RED proof needed: an existing legacy 40,000 on a total 100,000 makes `pay_full` record 60,000, not 100,000
- minimal patch boundary: inline payment amount resolver and recorder using `PaymentAllocationReaderPort`
- focused test target: `CreateTransactionWorkspaceInlinePaymentRecorderFeatureTest`
- wider regression target: selected-row payment tests, Note + Payment
- closure proof: targeted 1 passed, focused selected-row + `#017` pass, Note + Payment pass
- handoff template: `"017 inline payment: outstanding=<proof>, selected_row_regression=<proof>, wider=<proof>"`

### Slice 3 - Payment and Revision Concurrency Serialization

- Issues:
  - `docs/04_lifecycle/error_log/0010_revision_reallocation_can_lose_concurrent_payments.md`
  - `docs/04_lifecycle/error_log/0026_concurrent_note_payments_can_over_allocate_balances.md`
- Why this order:
  - concurrency must lock in the settlement invariant that Slice 2 already corrected
  - locking the wrong math only makes the bug stand in line more politely; the world does not need a nicer race condition
- Can be combined:
  - same-note row lock protocol
  - transaction boundary verification
  - source anchor verification
- Must be split:
  - idempotency token feature
  - database-specific stress tests if the environment is not ready
