# Error Log Finance Residual Implementation Blueprint, DoD, and CLI Workflow

## Status

Planning blueprint.

This document is not an implementation patch.

This document does not mark any `docs/error_log/*.md` finding as fixed.

This document exists to turn ADR-0018 finance lifecycle direction and related carry-forward settlement decisions into a rigid implementation workflow for remaining finance-related error logs.

HyperPOS is a rigid finance-sensitive POS and operational system.

This is not a prototype, demo, or reduced-scope system.

## Source Of Truth

- docs/adr/0018-note-revision-settlement-external-product-lifecycle.md
- docs/adr/2026-05-04-note-revision-carry-forward-settlement.md
- docs/blueprint/v2/note-finance/2026-04-29-note-finance-stabilization-blueprint.md
- docs/blueprint/v2/note-finance/2026-04-29-note-finance-current-projection-addendum.md
- docs/handoff/v2/note-finance/2026-04-30-adr-0016-completion-handoff.md
- docs/audit/codex-security/2026-05-06-error-log-solution-and-adr-coverage-summary.md
- docs/error_log/001-refunds-counted-as-paid-in-note-totals.md
- docs/error_log/003-refunded-revised-notes-are-misclassified-as-underpaid.md
- docs/error_log/004-refunded-work-items-survive-revisions-and-inflate-stock.md
- docs/error_log/005-note-revision-silently-drops-overpaid-allocations.md
- docs/error_log/006-client-controlled-price-basis-bypasses-minimum-price-checks.md
- docs/error_log/008-legacy-paid-notes-can-be-paid-again.md
- docs/error_log/011-cashier-revision-path-mutates-settled-note-state.md
- docs/error_log/012-canceled-note-rows-re-enter-payment-flows.md
- docs/error_log/013-forged-row-refund-can-auto-finalize-unpaid-notes.md
- docs/error_log/014-refund-endpoint-can-cancel-open-or-unpaid-note-rows.md
- docs/error_log/017-workspace-edit-payments-ignore-existing-note-payments.md
- docs/error_log/021-refunds-can-be-recorded-on-open-notes.md
- docs/adr/0019-note-access-boundary-cashier-date-window-and-transaction-capability-enforcement.md
- docs/adr/0022-payment-allocation-concurrency-and-over-allocation-protection.md
- User owner decisions in planning sessions
- User command output from local repository
- Current source code at execution time

## Decision Boundary

This blueprint executes finance lifecycle decisions.

ADR-0018 owns domain lifecycle direction for:

- note revision
- settlement
- refund
- payment allocation
- inventory lifecycle
- external product lifecycle
- current projection
- historical ledger

The carry-forward settlement ADR owns current carry-forward decisions for revised notes with prior payment/refund.

ADR-0019 owns actor access, cashier date window, and admin transaction capability.

ADR-0022 owns payment concurrency and over-allocation protection.

This blueprint must not silently redefine ADR-0019 or ADR-0022.

When a finding crosses access and finance, both layers must pass:

1. access/capability layer
2. finance domain eligibility layer

When a finding crosses concurrency and finance, ADR-0022 transaction protocol must be followed during implementation.

## Explicit Scope

This blueprint covers finance residual implementation planning for:

- note paid/refunded settlement classification
- refund counted as paid or subtracted incorrectly
- revised/refunded note settlement
- carry-forward money after revision
- overpaid/kembalian/customer-credit decision gate
- current-only payable components
- current-only refundable components
- canceled rows entering payment/refund flows
- forged row refund validation
- open/unpaid note refund eligibility
- legacy paid notes paid again
- workspace edit preserving existing note money
- client-controlled price basis and minimum price enforcement
- refunded work items surviving revisions and stock/report impact
- bridge issue where settled note revision crosses access and domain boundary

## Non Goals

Do not patch application source from this document alone.

Do not create or modify production code before source inventory and characterization tests.

Do not change ADR-0019 access/capability policy.

Do not change ADR-0020 public surface, output, storage, or attachment policy.

Do not change ADR-0022 payment concurrency policy.

Do not implement idempotency or DB concurrency constraints here.

Do not rewrite refund engine unless fresh proof shows actual finance ledger bug and owner approves.

Do not block cashier edit/refund globally as a final solution.

Do not rewrite/delete payment/refund/history to hide mismatch.

Do not solve seeder credential safety here.

Do not redesign the whole UI.

Do not change locked domain terms.

Do not mark finance error logs fixed without proof.

## Error Log Coverage

| Error Log | Finance Theme | Coverage Direction |
|---|---|---|
| 001 refunds counted as paid in note totals | settlement classification | normalize paid/refund settlement policy |
| 003 refunded revised notes misclassified as underpaid | carry-forward settlement | settlement-level carry-forward fix, no double subtraction |
| 004 refunded work items survive revisions and inflate stock | revision/history/inventory | current projection + historical anchors + inventory movement proof |
| 005 note revision silently drops overpaid allocations | overpaid/carry-forward surplus | decision gate for overpaid/kembalian/customer-credit model |
| 006 client-controlled price basis bypasses minimum price checks | server-side price authority | server source of truth for price basis/minimum snapshot |
| 008 legacy paid notes can be paid again | paid-state detection | settlement policy must include legacy/current money correctly |
| 011 cashier revision path mutates settled note state | access + domain bridge | ADR-0019 access + ADR-0018 domain eligibility both required |
| 012 canceled note rows re-enter payment flows | current-only payable | payable components must exclude canceled/legacy rows |
| 013 forged row refund can auto-finalize unpaid notes | refund row validation | row ownership/current-state/payment eligibility validation |
| 014 refund endpoint can cancel open or unpaid note rows | refund eligibility | refund only allowed for eligible current settled/payment-backed rows |
| 017 workspace edit payments ignore existing note payments | carry-forward existing money | edit/revision must preserve and reallocate existing money |
| 021 refunds can be recorded on open notes | refund eligibility | reject refund when note/payment state is not eligible |

## Locked Finance Decisions Already Available

### Note Revision Remains Supported

Final fix must not remove note revision.

Final fix must not globally block edit after payment/refund.

Temporary containment is allowed only with explicit owner decision and must not become final domain.

### Ledger And History Must Not Be Destroyed

Payment, refund, inventory movement, and historical work item anchors are financial/historical events.

Forbidden:

- cascade delete financial history
- rewrite refund allocation silently
- detach financial allocation from historical anchor without replacement audit model
- delete old work item that anchors payment/refund/history
- nullable FK as shortcut without immutable snapshot model

### Current Projection Must Be Separate From History

The system must distinguish:

1. current operational state
2. historical ledger/audit trail

Current UI and current settlement must not accidentally count legacy rows as current.

Historical report/audit must remain able to explain old versions and events.

### Carry-Forward Settlement

Previous money on a revised note becomes carry-forward settlement for the edited note.

Rules:

1. revised total > carry-forward:
   - note is partial/open
   - outstanding is difference

2. revised total = carry-forward:
   - note is paid/lunas
   - outstanding is zero

3. revised total < carry-forward:
   - surplus must become explicit overpaid/kembalian/refund-due/customer-credit according to separate decision
   - system must not pretend note is unpaid

4. allocation priority:
   - product components first
   - service components after products

### Refund Engine Caution

Refund engine should not be changed again unless fresh proof shows actual finance ledger bug.

Settlement/status/current projection consumers should be tested before generic reader or refund engine patches.

## Decision Gates Still Open

### Gate 1 — Overpaid/Kembalian Storage Model

Required for:

- error_log 005
- downward revision where carry-forward exceeds revised total
- any flow where previous money exceeds current total

Options:

A. immediate refund due
B. customer credit
C. explicit overpaid balance
D. forced refund workflow before final close

Current status:

- not decided in final storage/workflow form
- implementation must stop before production patch if it needs final surplus storage

Temporary characterization may assert:

- surplus detected
- note is not unpaid
- no double subtraction
- no silent money loss

Production implementation needs owner decision before storing final surplus behavior.

### Gate 2 — Current Projection Storage Detail

Required for:

- current-only payable/refundable components
- legacy/canceled row exclusion
- note detail/current UI correctness
- report boundary

Current status:

- current projection direction exists
- exact table/column/model may depend on existing source state
- do not decide schema silently during patch

Implementation must inspect existing migrations/models/readers before schema decisions.

### Gate 3 — Refund Eligibility For Open/Partial Notes

Required for:

- error_log 014
- error_log 021
- forged row refund cases

Default safe direction:

- refund requires eligible payment-backed/current row state
- open/unpaid rows must not be refund-finalized through forged request
- refund eligibility must be server-side and row-level

If business wants refund on unpaid/open rows as non-money row cancellation, it must be modeled separately from money refund.

### Gate 4 — Server Price Basis Authority

Required for:

- error_log 006

Default direction:

- request/client may submit selected item/quantity
- server must resolve price basis and minimum price authority
- old minimum snapshot rule must be enforced by server
- client-submitted price basis is not trusted

Exact source of truth must be inspected from current product/pricing code before patch.

### Gate 5 — ADR-0019 Bridge For Error Log 011

Required for:

- cashier revision path mutates settled note state

Rule:

- access allowed does not imply domain operation valid
- domain operation valid does not imply actor has route access
- official audited lifecycle may allow cashier correction/revision only when both ADR-0019 and ADR-0018 rules pass

Implementation must not globally block cashier edit as shortcut.

## Risk Model

### Risk 1 — Double-Subtracted Refund

Bad flow:

1. note has gross payment
2. historical refund exists
3. revision captures net carry-forward
4. settlement subtracts historical refund again
5. note appears underpaid

Required prevention:

- settlement consumer must know whether allocation input is gross ledger or current carry-forward
- no double subtraction
- revised total equal to carry-forward must be paid

### Risk 2 — Legacy/Canceled Rows Enter Current Payment

Bad flow:

1. row becomes canceled/legacy
2. payable component resolver still includes it
3. user pays or refunds old/canceled row
4. settlement/current projection becomes wrong

Required prevention:

- payable/refundable component resolvers must be current-only
- canceled/legacy rows excluded from current mutation flows
- historical rows remain audit-only

### Risk 3 — Forged Row Refund

Bad flow:

1. request submits row id not eligible/current/owned by note
2. refund endpoint accepts it
3. row cancel/refund finalizes note incorrectly

Required prevention:

- server validates row belongs to note
- row is current/eligible
- row has payment-backed refundable amount
- note/payment state allows refund
- request amount does not exceed eligible refundable amount

### Risk 4 — Open/Unpaid Refund

Bad flow:

1. note or row has no valid paid/refundable settlement
2. refund request is accepted
3. system cancels row or finalizes note via refund path

Required prevention:

- refund money flow requires paid/refundable settlement
- non-money cancellation must be separate domain action if allowed
- refund endpoint must not be used as generic row delete

### Risk 5 — Client-Controlled Price Basis

Bad flow:

1. request sends price basis or old minimum values
2. server trusts client
3. minimum price protection bypassed
4. report/profit/pricing becomes unsafe

Required prevention:

- server resolves price basis
- server enforces minimum price/snapshot
- client values are hints only when safe

### Risk 6 — Existing Money Lost During Edit

Bad flow:

1. note has previous payment/refund
2. workspace edit/revision ignores existing money
3. payment appears lost or note reopens incorrectly

Required prevention:

- revision captures and carries forward existing valid money
- existing money reallocated by priority
- no silent drop of previous settlement
- surplus becomes explicit when needed

## Preferred Implementation Slices

### Slice 1 — Settlement Classification Normalization

Covers:

- 001
- 008
- part of 003
- part of 017

Goal:

- paid/open/refunded/outstanding state uses one explicit settlement policy
- legacy/current money distinction is clear
- paid notes cannot be paid again due to legacy detection gap

Tests:

1. refunded amount does not count as paid settlement
2. paid-state detects existing valid settlement
3. legacy paid note cannot be paid again
4. outstanding resolver returns already paid when fully settled
5. operational status matches settlement

### Slice 2 — Carry-Forward Settlement After Revision

Covers:

- 003
- 017
- part of 005

Goal:

- revised notes preserve existing valid money as carry-forward
- refund is not double-subtracted
- product-first allocation priority is preserved

Tests:

1. previous payment 300000, refund 100000, revised total 200000 => paid/outstanding 0
2. carry-forward 200000, revised total 250000 => partial/outstanding 50000
3. carry-forward 200000, revised total 150000 => surplus detected, not unpaid
4. carry-forward allocation fills product before service
5. existing payment is not silently lost during workspace edit

Stop before final surplus storage if owner decision is still missing.

### Slice 3 — Current-Only Payable Components

Covers:

- 012
- part of 004
- part of 008

Goal:

- payment selection excludes canceled/legacy/superseded rows
- current note payable components are active/current only
- historical rows remain audit anchors

Tests:

1. canceled row excluded from payable components
2. legacy/superseded row excluded from current payment flow
3. current active row remains payable
4. historical row remains readable for audit/history
5. note total/payment selection does not include legacy rows

### Slice 4 — Current-Only Refundable Components And Refund Eligibility

Covers:

- 013
- 014
- 021
- part of 004

Goal:

- refund endpoint validates note/row/current/payment eligibility
- forged row id cannot refund/cancel/finalize note
- open/unpaid rows cannot be money-refunded

Tests:

1. forged row id rejected
2. row from another note rejected
3. legacy/superseded row rejected for new refund
4. canceled row rejected for refund
5. unpaid row rejected for money refund
6. open/unpaid note cannot be finalized by refund endpoint
7. valid current paid row can be refunded through official flow

### Slice 5 — Server-Side Price Basis Authority

Covers:

- 006

Goal:

- client cannot bypass minimum price by controlling price basis
- server resolves authoritative price/minimum snapshot

Tests:

1. forged price basis ignored
2. below-minimum client price rejected
3. server minimum snapshot enforced
4. valid server-authorized price passes
5. report/profit source remains explainable

### Slice 6 — Revision, Inventory, And Historical Work Item Boundary

Covers:

- 004
- part of 003
- part of 017

Goal:

- revision does not destroy historical anchors
- refunded work items do not inflate current stock/report
- inventory movements remain traceable
- current projection excludes historical rows

Tests:

1. refunded old work item remains historical anchor
2. old refunded work item excluded from current stock/payment/refund selection
3. inventory reversal or movement source remains traceable
4. current projection uses active/current rows
5. report does not double-count superseded rows

### Slice 7 — Error Log 011 Bridge Access + Domain

Covers:

- 011

Goal:

- cashier revision path cannot mutate settled note state outside official audited lifecycle
- ADR-0019 access layer and ADR-0018 domain eligibility both enforced

Tests:

1. cashier route access passes only if within date window
2. cashier cannot use unofficial workspace mutation to bypass domain lifecycle
3. official audited correction/revision path remains available when domain policy allows
4. settled note mutation requires correct domain reason/audit where applicable
5. handler preserves any required row lock from concurrency patch if applicable

## Recommended Slice Order

The safest order:

1. Slice 1 settlement classification normalization
2. Slice 2 carry-forward settlement after revision
3. Slice 3 current-only payable components
4. Slice 4 current-only refundable components and refund eligibility
5. Slice 5 server-side price basis authority
6. Slice 6 revision/inventory/historical boundary
7. Slice 7 error_log 011 bridge access + domain
8. final finance blast-radius suite
9. docs/error_log updates only after proof

Reason:

- settlement must be correct before deciding payable/refundable behavior
- carry-forward money must be stable before overpaid/current-only refinements
- payment eligibility comes before refund eligibility
- price basis is isolated and can be done after core settlement
- inventory/report boundary should follow current/legacy decisions
- bridge issue should preserve both ADR-0019 and ADR-0018 rules

## Source Inventory Requirements

Before implementation, identify exact files for:

- note paid status policy
- outstanding resolver
- operational status resolver
- payment allocation readers
- payment component allocation readers
- refund readers
- note replacement/revision handlers
- note replacement payment allocation reconciler
- payable component resolver
- refundable component resolver
- selected active work item resolver
- note detail/current projection readers
- workspace edit/update handler
- refund endpoint/use case
- payment endpoint/use case
- price/minimum price validators
- product/stock price source
- inventory movement writers/readers
- reporting queries affected by current/history split
- tests already covering refund/revision/payment

## Suggested Discovery Commands

Run before implementation:

    grep -RIn "NotePaidStatusPolicy\|Outstanding\|OperationalStatus\|PaymentAllocation\|PaymentComponentAllocation\|RefundComponent\|NoteReplacement\|PayableComponent\|Refundable\|SelectedActiveWorkItems\|price_basis\|minimum\|min_price\|work_items\|inventory_movements" app tests database 2>/dev/null || true

Run route discovery:

    php artisan route:list | grep -Ei "note|payment|refund|workspace|product|price|inventory|report" || true

Run document snapshot:

    sed -n '1,260p' docs/adr/0018-note-revision-settlement-external-product-lifecycle.md
    sed -n '1,260p' docs/adr/2026-05-04-note-revision-carry-forward-settlement.md
    sed -n '1,260p' docs/blueprint/v2/note-finance/2026-04-29-note-finance-stabilization-blueprint.md
    sed -n '1,260p' docs/blueprint/v2/note-finance/2026-04-29-note-finance-current-projection-addendum.md

Run error log snapshot:

    sed -n '1,220p' docs/error_log/001-refunds-counted-as-paid-in-note-totals.md
    sed -n '1,220p' docs/error_log/003-refunded-revised-notes-are-misclassified-as-underpaid.md
    sed -n '1,220p' docs/error_log/004-refunded-work-items-survive-revisions-and-inflate-stock.md
    sed -n '1,220p' docs/error_log/005-note-revision-silently-drops-overpaid-allocations.md
    sed -n '1,220p' docs/error_log/006-client-controlled-price-basis-bypasses-minimum-price-checks.md
    sed -n '1,220p' docs/error_log/008-legacy-paid-notes-can-be-paid-again.md
    sed -n '1,220p' docs/error_log/011-cashier-revision-path-mutates-settled-note-state.md
    sed -n '1,220p' docs/error_log/012-canceled-note-rows-re-enter-payment-flows.md
    sed -n '1,220p' docs/error_log/013-forged-row-refund-can-auto-finalize-unpaid-notes.md
    sed -n '1,220p' docs/error_log/014-refund-endpoint-can-cancel-open-or-unpaid-note-rows.md
    sed -n '1,220p' docs/error_log/017-workspace-edit-payments-ignore-existing-note-payments.md
    sed -n '1,220p' docs/error_log/021-refunds-can-be-recorded-on-open-notes.md

## Characterization Test Rules

Every implementation slice must start with characterization tests.

Rules:

1. Prefer feature/use-case tests over generic reader tests.
2. Test observable business behavior.
3. Do not encode generic reader semantics before consumer semantics are known.
4. Use exact money examples from ADR/error log when available.
5. Assert status, outstanding, mutation eligibility, and persisted rows.
6. Preserve audit/history rows.
7. Do not mark fixed if only a low-level unit test passes.
8. Do not weaken existing tests to hide failure.

## CLI Workflow

Rules:

1. Start every slice with git status.
2. Read ADR-0018, carry-forward ADR, this blueprint, and relevant error logs.
3. Read current source before writing tests.
4. Add red characterization test first.
5. Run targeted test and confirm expected failure.
6. Patch smallest safe application/domain boundary.
7. Run targeted test again.
8. Run relevant finance blast-radius tests.
9. Show diff.
10. Update error_log only with proof.
11. Commit only after owner approval.

## Required Commands For Execution Sessions

### Start Session Snapshot

    git status --short --untracked-files=all
    git rev-parse --abbrev-ref HEAD
    git rev-parse --short HEAD
    git log --oneline -5

### Blueprint Snapshot

    sed -n '1,320p' docs/blueprint/v2/note-finance/2026-05-06-error-log-finance-residual-implementation-blueprint.md

### Test Pattern

Targeted test first:

    php artisan test --filter=TargetedTestName

Potential blast-radius suites after exact files are known:

    php artisan test tests/Feature/Payment
    php artisan test tests/Feature/Note
    php artisan test tests/Feature/Cashier
    php artisan test tests/Feature/Admin
    php artisan test tests/Feature/Reporting

Run only relevant suites for the selected slice and source changes.

### Final Diff Snapshot

    git status --short --untracked-files=all
    git diff --stat
    git diff -- docs/blueprint/v2/note-finance/2026-05-06-error-log-finance-residual-implementation-blueprint.md
    git diff -- app routes tests docs/error_log docs/adr docs/blueprint

## DoD For Planning

Planning is complete only when:

- finance residual blueprint exists
- covered error logs are mapped
- locked ADR-0018 and carry-forward decisions are listed
- open decision gates are explicit
- preferred implementation slices are defined
- slice order is defined
- source inventory requirements are defined
- discovery commands are defined
- characterization test rules are defined
- CLI workflow is defined
- implementation DoD is defined
- stop conditions are defined
- ADR-0019 access scope is not redefined
- ADR-0022 concurrency scope is not redefined
- no app source patch is made during planning

## DoD For Implementation

Implementation is complete only when all relevant conditions for the selected slice are proven.

### Source Boundary

- finance logic lives in application/domain/service boundary, not UI-only
- payment/refund/history rows are not destroyed
- current and historical rows are separated by policy/projection
- current payment selection excludes canceled/legacy rows
- current refund selection excludes canceled/legacy/unpaid rows
- carry-forward settlement does not double-subtract refund
- existing money is not silently lost during revision
- overpaid/surplus is not treated as unpaid
- price basis is server-authoritative
- client price basis cannot bypass minimum price
- refund endpoint cannot act as generic row delete
- paid status/outstanding/operational status agree
- report/current projection does not double-count legacy rows when affected

### Tests

- red characterization test exists before patch
- targeted test fails before patch for expected reason
- targeted test passes after patch
- relevant payment/refund/revision/reporting tests pass
- no unrelated tests are weakened
- no test is changed merely to hide a failure
- exact money examples are covered where relevant
- surplus behavior is covered as detection if storage decision remains open
- verification gap is documented when full final model needs owner decision

### Documentation

- docs/error_log finding is updated only after proof
- proof quality is stated explicitly
- ADR is not rewritten casually during implementation
- any new domain decision gets ADR/addendum
- any deviation from this blueprint is recorded with reason
- residual gaps remain visible

### Git

- git status is checked before and after
- diff contains only files in approved slice
- commit message references narrow fix
- owner reviews proof before commit
- no untracked unexpected file is left unreviewed

## Finance Blast-Radius Suite

After finance residual slices are complete, run the narrowest available suite covering:

- payment allocation
- refund allocation
- note revision
- workspace edit
- paid status
- outstanding resolver
- operational status
- current projection
- reporting if touched
- cashier/admin affected flows
- inventory if touched

Suggested final proof should include:

- targeted tests per error_log
- relevant payment suite
- relevant note suite
- relevant refund suite
- relevant reporting suite if touched
- make/audit command if project rules require it
- final git diff stat
- final docs/error_log updates
- owner acceptance

## Error Log Update Rule

Do not update `docs/error_log/*.md` before implementation proof.

When updating a finance error log, include:

- status
- exact patch scope
- tests added
- targeted command output
- blast-radius command output
- residual gaps
- owner decision reference if applicable
- commit hash after commit, if committed
- owner acceptance note if applicable

Allowed statuses:

- Reported
- Accepted risk
- Planned
- Patched with verification gap
- Fixed with proof
- Deferred with owner acceptance

Forbidden behavior:

- marking fixed because a patch exists
- hiding missing finance proof
- claiming domain decision not recorded
- changing settlement semantics without ADR/update
- deleting known gap without evidence

## Stop Conditions

Stop immediately if:

- patch changes refund engine without fresh proof and owner approval
- patch blocks cashier edit/refund globally as final solution
- patch rewrites/deletes payment/refund/history
- patch mixes current and historical rows without boundary
- patch encodes generic reader semantics before consumer behavior is proven
- patch silently drops existing note money
- patch treats carry-forward surplus as unpaid
- patch silently adjusts money without owner decision
- patch trusts client-controlled price basis
- patch lets open/unpaid row use money refund path
- patch lets forged row id refund/cancel/finalize a note
- patch changes ADR-0019 access policy without opening that scope
- patch changes ADR-0022 concurrency policy without opening that scope
- full surplus storage is needed but owner decision is still missing
- current projection schema change is needed but schema decision is not documented
- failing test reason is not understood
- broad refactor is needed before exact affected files are proven
- error_log update is attempted before proof

## Handoff Rule

If session context becomes risky or implementation is paused, create handoff with:

- active blueprint
- selected slice
- owner decisions
- open decision gates
- files changed
- tests added
- command proof
- failing tests
- residual gaps
- stop conditions triggered, if any
- safest next step
- exact opening prompt for next session

## Recommended Execution Sequence After Planning

After this blueprint is accepted, next implementation should start with:

1. local baseline proof
2. read ADR-0018
3. read carry-forward ADR
4. read this blueprint
5. read error logs 001, 003, 008, and 017 for Slice 1/2 dependency
6. source inventory for settlement readers/resolvers and revision reconciler
7. select Slice 1 settlement classification normalization
8. create characterization test
9. confirm red
10. patch application/domain settlement boundary
11. confirm green
12. run relevant payment/note blast-radius tests
13. update error_log only after proof

Do not begin with generic reader gross-back patch unless consumer semantics are proven.

## Final Rule

Finance residual fixes must preserve money, history, auditability, and current operational correctness.

If the system cannot explain payment, refund, revision, current state, and history together, the implementation is not done.

For HyperPOS, one rupiah and one stock unit are not rounding errors. They are production facts.
