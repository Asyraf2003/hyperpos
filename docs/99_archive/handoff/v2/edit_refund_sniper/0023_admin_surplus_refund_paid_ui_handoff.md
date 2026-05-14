# Handoff 0023 - HyperPOS refund/edit/payment sniper - admin surplus refund_paid submit UI

## Final goal

Menyelesaikan remaining technical debt refund/edit/payment setelah ADR 0030 S12 tanpa broad repo adventure.

Scope aktif sekarang hanya admin UI/transport untuk mencatat surplus refund_paid dari refund_due.

Do not reopen ADR 0030 S12 kecuali ada regression proof baru.

## Current scope

Active debt:

refund_paid_ui_submit_form_admin_transport

Meaning:

Admin harus bisa mencatat actual cash-out refund_paid dari active refund_due surplus lewat note detail/admin UI, memakai existing backend foundation note_revision_surplus_refund_payments.

## Locked non-goals

Do not use customer_refunds for surplus refund_paid.

Do not require customer_payment_id.

Do not create refund_component_allocations.

Do not trigger refunded lifecycle.

Do not trigger inventory reversal.

Do not implement customer_credit.

Do not implement customer_balance_entries.

Do not implement PostgreSQL.

Do not implement Go API.

Do not implement dashboard.

Do not merge revision submit and payment.

Do not implement reversal/cancel in this slice unless newer ADR/source proof explicitly requires it.

## Known completed before this handoff

ADR 0030 S12 closed and locally verified.

Full make verify for S12 previously passed:

1021 tests / 5485 assertions.

ADR 0030 after-S12 inspection showed no heading after S12.

Remaining ADR 0030 gap only future schema caveat:

surplus_refund_paid_rupiah

remaining_refund_due_rupiah

0022 handoff continuity fixed:

docs/99_archive/handoff/v2/edit_refund_sniper/0022_adr_0030_s12_docs_closure_handoff.md exists

README Latest Handoff points to 0022

0022 contains S12 closed/proof anchors

0022 has no literal Markdown fence token

## Docs/source chain already proven

Docs-only debt matrix was run and passed from allowed docs.

Closed/proven by later handoffs:

refund_paid backend foundation: closed by 0015

refund_paid audit timeline read model: closed by 0016

report/cash ledger backend read model: closed by 0017

report screen/export visibility: closed by 0018

ADR 0030 S12 carry-forward/doc closure: closed by 0021/0022

Remaining explicit active debt selected:

refund_paid UI/admin transport submit path.

## Source-map proof before patch

Targeted source map showed:

routes: NONE for refund_paid

requests: NONE for refund_paid

controllers: only CreateNoteRevisionSurplusRefundDueController existed

Blade partial only rendered refund_due form

backend RecordNoteRevisionSurplusRefundPayment use case existed:

RecordNoteRevisionSurplusRefundPaymentCommand

RecordNoteRevisionSurplusRefundPaymentGuard

RecordNoteRevisionSurplusRefundPaymentFactory

RecordNoteRevisionSurplusRefundPaymentHandler

RecordNoteRevisionSurplusRefundPaymentResult

RecordNoteRevisionSurplusRefundPaymentResultFactory

RecordNoteRevisionSurplusRefundPaymentAuditEventFactory

## HTTP transport work completed in current session

Added RED test:

tests/Feature/Note/RecordNoteRevisionSurplusRefundPaymentControllerFeatureTest.php

RED proof:

php -l passed.

Targeted test failed 4/4 because route returned 404.

This proved missing HTTP route/controller/request.

Production patch applied:

app/Adapters/In/Http/Requests/Note/RecordNoteRevisionSurplusRefundPaymentRequest.php

app/Adapters/In/Http/Controllers/Admin/Note/RecordNoteRevisionSurplusRefundPaymentController.php

routes/web/note.php

Route added:

POST /admin/notes/revision-surplus-dispositions/{dispositionId}/refund-paid

Route name:

admin.notes.revision-surplus-dispositions.refund-paid.store

Controller behavior:

Builds RecordNoteRevisionSurplusRefundPaymentCommand with:

noteRevisionSurplusDispositionId

amountRupiah

effectiveDate

reason

actorId

actorRole = admin

idempotencyKey

occurredAt = null

sourceChannel = web_admin

requestId

correlationId

Failure redirects back with error bag refund_paid.

Success redirects to admin.notes.show using note_root_id from result data.

Success flash:

Refund paid berhasil dicatat.

HTTP GREEN proof:

php -l passed for:

app/Adapters/In/Http/Requests/Note/RecordNoteRevisionSurplusRefundPaymentRequest.php

app/Adapters/In/Http/Controllers/Admin/Note/RecordNoteRevisionSurplusRefundPaymentController.php

routes/web/note.php

Feature test:

tests/Feature/Note/RecordNoteRevisionSurplusRefundPaymentControllerFeatureTest.php

Result:

PASS 4 tests / 25 assertions.

## UI work started but not patched

Added RED UI test:

tests/Feature/Note/AdminNoteSurplusRefundPaidUiFeatureTest.php

RED proof:

php -l passed.

Test failed because page does not contain:

Catat Refund Paid

Failure location:

tests/Feature/Note/AdminNoteSurplusRefundPaidUiFeatureTest.php:50

Expected UI contract from RED test:

Admin note detail renders refund_paid action when refund_due has remaining amount.

Expected visible:

Catat Refund Paid

72.000

route admin.notes.revision-surplus-dispositions.refund-paid.store with dispositionId

name="amount_rupiah"

value="72000"

max="72000"

name="effective_date"

name="reason"

name="idempotency_key"

data-refund-paid-form

data-refund-paid-max-rupiah="72000"

Must not render:

customer_credit

customer_balance_entries

## Latest source context proof after UI RED

Command printed exact context and passed:

[RESULT] PASS: refund_paid UI payload context printed

Important source facts:

app/Application/Note/Services/NoteRevisionSurplusDispositionActionViewDataBuilder.php currently:

injects NoteRevisionSurplusDispositionReaderPort only

build(string $noteRootId)

loops reader->findPendingByNoteRootId($noteRootId)

returns only:

has_pending_refund_due_action

pending_items

item contains:

note_revision_settlement_id

note_revision_id

note_root_id

surplus_rupiah

active_disposition_rupiah

unresolved_pending_rupiah

disposition_type = refund_due

amount_default_rupiah

reason_required

It does not expose:

has_pending_refund_paid_action

refund_paid_items

remaining_refund_due_rupiah

active_refund_paid_rupiah

disposition_id

resources/views/shared/notes/partials/payment-summary-actions.blade.php currently:

renders Refund Due action only

shows “Tandai Refund Due”

posts to admin.notes.revision-settlements.refund-due.store

does not render “Catat Refund Paid”

NoteDetailPageDataBuilder wires:

NoteRevisionSurplusDispositionActionViewDataBuilder $surplusDispositions

then:

$surplusDisposition = $this->surplusDispositions->build($note->id())

NoteDetailNotePayloadBuilder places:

'surplus_disposition' => $surplusDisposition

Existing read seams discovered:

app/Ports/Out/Note/NoteRevisionSurplusDispositionReaderPort.php

findPendingBySettlementId

findPendingByNoteRootId

app/Ports/Out/Note/NoteRevisionSurplusRefundDueSourceReaderPort.php

findActiveRefundDueByDispositionIdForUpdate only

This is write-path locking source, not ideal for read-only UI payload.

app/Ports/Out/Note/NoteRevisionSurplusRefundPaymentReaderPort.php

findActiveByDispositionIdAndIdempotencyKey

sumActiveAmountByDispositionId

likely also sumActiveAmountByNoteRootId, based on earlier BuildCreateNoteRevisionSettlementTest/source map.

app/Adapters/Out/Note/DatabaseNoteRevisionSurplusRefundPaymentSumQuery.php can sum active payments by disposition id and note root id.

## Likely next minimum patch

Do not patch blindly.

Likely seam:

Option A, simplest but maybe less clean:

Extend NoteRevisionSurplusDispositionActionViewDataBuilder to also inject NoteRevisionSurplusRefundPaymentReaderPort.

Add a new reader method or use existing sums if available.

Need active refund_due dispositions by note root, not pending settlements.

Problem:

Current NoteRevisionSurplusDispositionReaderPort only returns pending surplus settlements before refund_due, not existing active refund_due disposition rows.

So using it alone cannot build refund_paid action.

Cleaner minimum:

Add a read-only method to NoteRevisionSurplusRefundDueSourceReaderPort:

findActiveRefundDueByNoteRootId(string $noteRootId): array

Implement in DatabaseNoteRevisionSurplusRefundDueSourceReaderAdapter using:

note_revision_surplus_dispositions

where note_root_id = noteRootId

where disposition_type = refund_due

where status = active

left/sum active note_revision_surplus_refund_payments by disposition id

calculate:

refundDueRupiah = disposition.amount_rupiah

activeRefundPaidRupiah = sum active payments

remainingRefundDueRupiah = refundDue - activeRefundPaid

return only remaining > 0 for UI builder or let builder filter.

Then update NoteRevisionSurplusDispositionActionViewDataBuilder:

inject NoteRevisionSurplusRefundDueSourceReaderPort

keep existing refund_due pending_items behavior unchanged

add refund_paid_items from active refund_due sources with remainingRefundDueRupiah > 0

return:

has_pending_refund_paid_action

refund_paid_items

Each refund_paid item should include:

note_revision_surplus_disposition_id

note_revision_settlement_id

note_revision_id

note_root_id

refund_due_rupiah

active_refund_paid_rupiah

remaining_refund_due_rupiah

amount_default_rupiah = remaining_refund_due_rupiah

effective_date_default = date?

Avoid direct date in domain service unless a ClockPort is already available.

For minimum UI test, only input name is asserted, not default value.

Existing controller validation requires effective_date, so form can use today via Blade date('Y-m-d') or later inject date default.

Keep patch minimal.

Then update Blade partial:

After refund_due block or before audit timeline, render refund_paid form if:

$note['surplus_disposition']['has_pending_refund_paid_action'] ?? false

and refund_paid_items not empty.

Form:

method POST

action route('admin.notes.revision-surplus-dispositions.refund-paid.store', ['dispositionId' => item['note_revision_surplus_disposition_id']])

@csrf

data-refund-paid-form

data-refund-paid-max-rupiah="{{ remaining }}"

amount input:

name amount_rupiah

value remaining

max remaining

effective_date input:

name effective_date

type date

value date('Y-m-d') or injected date default if available

reason textarea

idempotency_key hidden or text hidden:

name idempotency_key

Need inspect existing idempotency patterns before choosing hidden value strategy.

## Potential immediate next command before patch

Run a small targeted idempotency pattern read:

grep only routes/views/controllers/tests for idempotency_key, not broad app scan.

Suggested command from root:

grep -R "idempotency_key" -n resources/views/shared/notes app/Adapters/In/Http/Controllers app/Adapters/In/Http/Requests tests/Feature/Note | head -80

If too broad, restrict further before patch.

## Required next proof

Before patch:

Either inspect idempotency UI pattern, or explicitly decide no prior pattern exists and create minimal safe hidden field.

Then patch UI/payload only.

Expected next commands after patch:

php -l app/Application/Note/Services/NoteRevisionSurplusDispositionActionViewDataBuilder.php

php -l app/Adapters/Out/Note/DatabaseNoteRevisionSurplusRefundDueSourceReaderAdapter.php

php -l app/Ports/Out/Note/NoteRevisionSurplusRefundDueSourceReaderPort.php

php -l resources/views/shared/notes/partials/payment-summary-actions.blade.php

php artisan test tests/Feature/Note/AdminNoteSurplusRefundPaidUiFeatureTest.php --filter=AdminNoteSurplusRefundPaidUiFeatureTest

php artisan test tests/Feature/Note/RecordNoteRevisionSurplusRefundPaymentControllerFeatureTest.php --filter=RecordNoteRevisionSurplusRefundPaymentControllerFeatureTest

php artisan test tests/Feature/Note/NoteDetailSurplusDispositionPayloadFeatureTest.php --filter=NoteDetailSurplusDispositionPayloadFeatureTest

## Known files changed in current working tree

Created:

tests/Feature/Note/RecordNoteRevisionSurplusRefundPaymentControllerFeatureTest.php

app/Adapters/In/Http/Requests/Note/RecordNoteRevisionSurplusRefundPaymentRequest.php

app/Adapters/In/Http/Controllers/Admin/Note/RecordNoteRevisionSurplusRefundPaymentController.php

tests/Feature/Note/AdminNoteSurplusRefundPaidUiFeatureTest.php

Modified:

routes/web/note.php

Not yet modified for UI:

app/Application/Note/Services/NoteRevisionSurplusDispositionActionViewDataBuilder.php

app/Ports/Out/Note/NoteRevisionSurplusRefundDueSourceReaderPort.php

app/Adapters/Out/Note/DatabaseNoteRevisionSurplusRefundDueSourceReaderAdapter.php

resources/views/shared/notes/partials/payment-summary-actions.blade.php

tests/Feature/Note/NoteDetailSurplusDispositionPayloadFeatureTest.php

## Current verification proof

HTTP RED:

4 failed because 404.

HTTP GREEN:

4 passed / 25 assertions.

UI RED:

1 failed / 2 assertions.

Failure:

Expected page to contain Catat Refund Paid.

Page was OK but UI form absent.

Latest payload context:

PASS: refund_paid UI payload context printed.

## Gaps remaining

UI payload patch not done.

UI Blade render patch not done.

Payload feature regression not updated.

Focused regression after UI patch not run.

Docs handoff/ADR update not done.

No wider Note/Payment suite after this slice.

No full make verify after this slice.

No browser/manual QA.

No commit/push proof, owner handles git manually.

## Safest next active step

Do not run git ceremony.

Do not scan whole repo.

Do not reopen S12.

Do not touch docs first.

Next active step:

Targeted inspect idempotency_key usage in transport/UI patterns, then patch read-only refund_paid UI payload seam.

## Opening prompt for next session

Kita lanjut HyperPOS refund/edit/payment sniper dari handoff ini.

Mode:

Jangan broad repo scan.

Jangan mulai dari git status/log/diff.

Owner handle commit/push/manual sync.

Local command output owner adalah source of truth tertinggi.

S12 closed, jangan reopen tanpa regression proof.

Active debt hanya admin surplus refund_paid submit UI.

HTTP transport untuk refund_paid sudah GREEN: 4 passed / 25 assertions.

UI RED sudah valid: AdminNoteSurplusRefundPaidUiFeatureTest gagal karena “Catat Refund Paid” belum dirender.

Latest payload context PASS membuktikan NoteRevisionSurplusDispositionActionViewDataBuilder hanya expose refund_due pending_items, Blade hanya render Tandai Refund Due, dan belum ada refund_paid action payload/render.

Files already added/changed:

tests/Feature/Note/RecordNoteRevisionSurplusRefundPaymentControllerFeatureTest.php

app/Adapters/In/Http/Requests/Note/RecordNoteRevisionSurplusRefundPaymentRequest.php

app/Adapters/In/Http/Controllers/Admin/Note/RecordNoteRevisionSurplusRefundPaymentController.php

routes/web/note.php

tests/Feature/Note/AdminNoteSurplusRefundPaidUiFeatureTest.php

Current proof:

php -l passed for new HTTP test.

HTTP RED: 4 failed due 404.

HTTP patch applied route/request/controller.

php -l passed for request/controller/routes.

HTTP GREEN: 4 passed / 25 assertions.

php -l passed for AdminNoteSurplusRefundPaidUiFeatureTest.

UI RED: 1 failed because missing Catat Refund Paid.

Payload context command PASS.

Next safest step:

First inspect scoped idempotency_key UI/transport pattern only, then patch minimal payload/render:

likely add read-only findActiveRefundDueByNoteRootId to NoteRevisionSurplusRefundDueSourceReaderPort and DatabaseNoteRevisionSurplusRefundDueSourceReaderAdapter

extend NoteRevisionSurplusDispositionActionViewDataBuilder to include has_pending_refund_paid_action and refund_paid_items

render refund_paid form in resources/views/shared/notes/partials/payment-summary-actions.blade.php

update/extend NoteDetailSurplusDispositionPayloadFeatureTest

rerun UI RED test, HTTP transport test, payload test

Do not touch:

customer_refunds

customer_payment_id

refund_component_allocations

inventory reversal

refunded lifecycle

customer_credit

customer_balance_entries

PostgreSQL

Go API

dashboard

reversal/cancel

Start with this command only:

grep -R "idempotency_key" -n resources/views/shared/notes app/Adapters/In/Http/Controllers app/Adapters/In/Http/Requests tests/Feature/Note | head -80

Then decide hidden idempotency_key strategy based on output.

## Progress

Final Goal Progress: 18% for post-S12 debt cleanup, because HTTP path is green and UI RED/source context are proven, but UI patch is not done.

Main Process Progress: 72% for admin refund_paid submit path, because backend + HTTP are green, UI failure and payload seam are isolated.

Sub-step Progress: 0% for UI GREEN patch, because no UI production patch has been applied.

Proof: HTTP GREEN 4/25, UI RED missing Catat Refund Paid, payload context PASS from latest owner output.

Session Context Health: 82% handoff created; continue in new session before patching UI.
