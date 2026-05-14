# AI Pro Repo Logic Audit Triage Handoff

## Scope

This handoff records the triage result from the AI Pro repo-wide logic/security/math audit during the edit refund sniper track.

Primary audit focus:
- security / authorization
- transaction math
- payment / refund / allocation / settlement
- inventory / stock movement / COGS
- profit / laba reporting
- idempotency / concurrency
- UI-to-logic consistency

Seeder is excluded as the primary bug source.
Cosmetic UI is excluded.
UI connected to business logic is included.

## Source of Truth

Local command output from owner is the highest source of truth.

AI Pro findings are treated as leads until proven locally.

Finding status levels:
- Confirmed RED: local test/source proof reproduces unsafe behavior.
- Fixed GREEN: local patch plus focused tests pass.
- Suspected: plausible from source reading but no RED proof yet.
- False positive: disproven by source/test proof.
- Needs narrowing: finding wording is too broad or stale.

## AI Pro Findings Received

AI Pro reported 8 findings:

1. HP-AUTH-001 — P0 — cashier can call refund_due cashier route wired to admin controller.
2. HP-REFUND-001 — P0 — selected-row refund race can double refund and double reverse stock.
3. HP-SURPLUS-001 — P0 — surplus refund_due race can exceed pending surplus.
4. HP-UI-001 — P1 — surplus action UI is role-agnostic in shared note view.
5. HP-INV-001 — P1 — inventory reversal idempotency race risk.
6. HP-ROWS-001 — P1 — concurrent add rows can duplicate line_no.
7. HP-REPORT-001 — P1 — operational profit may omit surplus_refund_paid cash-out.
8. HP-IDEMP-001 — P1 — refund_paid UI idempotency key is deterministic and stale-tab collision-prone.

## Confirmed and Fixed

### HP-AUTH-001 — Cashier refund_due route used admin controller

Status: Fixed GREEN.

Severity: P0.

### Bug mechanism

A cashier route existed:

- POST /cashier/notes/revision-settlements/{settlementId}/refund-due

It was wired to:

- AdminCreateNoteRevisionSurplusRefundDueController

The cashier route sat inside the cashier notes group but outside EnsureCashierNoteAccess.

The admin controller created the command using admin semantics, including admin actor role / admin source channel.

### Impact

A cashier request could enter an admin-only refund_due creation path.

The RED response redirected to the admin note detail path instead of being denied.

### RED proof

Added test:

- tests/Feature/Note/CreateNoteRevisionSurplusRefundDueControllerFeatureTest.php
- test_cashier_cannot_create_refund_due_through_cashier_route

Initial RED result:

- php -l tests/Feature/Note/CreateNoteRevisionSurplusRefundDueControllerFeatureTest.php
- PASS.

Focused RED:

- php artisan test tests/Feature/Note/CreateNoteRevisionSurplusRefundDueControllerFeatureTest.php --filter=cashier

Result:

- cashier cannot access admin refund due route: PASS.
- cashier cannot create refund due through cashier route: FAIL.

Failure:

- Expected redirect: http://localhost:8000/cashier/dashboard
- Actual redirect: http://localhost:8000/admin/notes/note-root-http-001

This confirmed the cashier route was not denied and entered the admin refund_due path.

### Patch

Changed:

- routes/web/note.php
- tests/Feature/Note/CreateNoteRevisionSurplusRefundDueControllerFeatureTest.php

Patch summary:

- Restored/kept the admin refund_due route.
- Removed the cashier refund_due route.
- Changed the new cashier route regression test to call the hardcoded deleted cashier URL.
- Expected response for the deleted cashier URL is 404.
- Kept admin route behavior intact.

### GREEN proof

Syntax:

- php -l routes/web/note.php
- PASS: No syntax errors detected.

- php -l tests/Feature/Note/CreateNoteRevisionSurplusRefundDueControllerFeatureTest.php
- PASS: No syntax errors detected.

Route proof:

- php artisan route:list | grep "revision-settlements.*refund-due" || true

Result:

- Only admin route remains:
  - POST admin/notes/revision-settlements/{settlementId}/refund-due

Focused cashier proof:

- php artisan test tests/Feature/Note/CreateNoteRevisionSurplusRefundDueControllerFeatureTest.php --filter=cashier

Result:

- PASS: 2 tests / 6 assertions.

Full controller feature proof:

- php artisan test tests/Feature/Note/CreateNoteRevisionSurplusRefundDueControllerFeatureTest.php

Result:

- PASS: 6 tests / 28 assertions.

Adjacent UI/payload proof:

- php artisan test tests/Feature/Note/AdminNoteSurplusRefundPaidUiFeatureTest.php tests/Feature/Note/NoteDetailSurplusDispositionPayloadFeatureTest.php

Result:

- PASS: 3 tests / 32 assertions.

## Out of Scope for HP-AUTH-001 Fix

Not changed:
- controller
- request
- handler
- guard
- UI
- refund_paid
- docs before proof
- git sync / push

Owner handles commit/push manually.

## Remaining Findings Not Yet Confirmed

### HP-REFUND-001 — selected-row refund race

Status: Suspected.

Reason:
AI Pro described plausible race risk, but no local RED proof has been produced yet.

Required proof:
- concurrent or invariant test showing double refund / over-refund / duplicate stock reversal.

### HP-SURPLUS-001 — refund_due race exceeds pending surplus

Status: Suspected.

Reason:
AI Pro described plausible aggregate race risk, but no local RED proof has been produced yet.

Required proof:
- concurrent test or transaction-level invariant proving active refund_due total can exceed settlement surplus.

### HP-UI-001 — shared UI surplus action role-agnostic

Status: Suspected.

Reason:
Shared note detail and shared surplus partial can be risky, but HP-AUTH-001 route removal changes exploitability.

Required proof:
- cashier DOM test proving cashier detail renders admin-only surplus action.
- admin DOM test proving intended action remains available.

### HP-INV-001 — inventory reversal idempotency race

Status: Suspected.

Required proof:
- duplicate reversal test or DB invariant proof.

### HP-ROWS-001 — duplicate line_no under concurrent add rows

Status: Suspected.

Required proof:
- concurrent add rows test showing duplicate line_no or missing DB unique constraint impact.

### HP-REPORT-001 — operational profit may omit surplus_refund_paid

Status: Needs narrowing.

Reason:
Do not claim all transaction reporting omits surplus_refund_paid.
Existing transaction report / cash ledger paths may already include surplus_refund_paid.
The remaining question should be narrowed to operational profit/dashboard calculation only.

Required proof:
- identify exact report query.
- seed surplus_refund_paid.
- compare cash ledger vs operational profit output.

### HP-IDEMP-001 — refund_paid deterministic idempotency key

Status: Suspected / design risk.

Reason:
Potential stale-tab collision is plausible, but it is not proven as financial corruption.
Likely UX/idempotency semantics risk.

Required proof:
- same key, different amount stale-tab scenario.
- expected behavior decision: reject stale form vs allow new key per attempt.

## Recommended Next Sniper Target

Next safest target:

- HP-SURPLUS-001 refund_due race/idempotency.

Reason:
- Adjacent to the just-fixed HP-AUTH-001 route.
- Affects surplus settlement integrity.
- Likely smaller than selected-row refund plus inventory reversal race.

Required first step:
Read only:
- app/Application/Note/UseCases/CreateNoteRevisionSurplusRefundDueHandler.php
- app/Application/Note/UseCases/CreateNoteRevisionSurplusRefundDueGuard.php
- app/Adapters/Out/Note/DatabaseNoteRevisionSurplusDispositionAdapter.php
- migration for note_revision_surplus_dispositions
- tests/Feature/Note/CreateNoteRevisionSurplusRefundDueControllerFeatureTest.php

Then produce a minimal RED/invariant test plan before patch.

## Do Not Do

Do not:
- accept all AI Pro findings as confirmed.
- broad audit entire repo again.
- patch concurrency risks without RED/invariant proof.
- touch seeders as primary bug source.
- ignore UI-to-logic boundary.
- manage git push/sync here.
