# 012 - Canceled note rows re-enter payment flows

## Status

Patched, with verification gap and residual audit note.

Patch supplied and unit tests added/updated, but tests could not run in the patch environment because phpunit/vendor dependencies were missing.

## Severity

High.

## Source

Audit report #012: Canceled note rows re-enter payment flows.

## Relasi Dengan Error Log Lain

### Berkaitan Dengan

- 008-legacy-paid-notes-can-be-paid-again.md
- 004-refunded-work-items-survive-revisions-and-inflate-stock.md

### Jenis Keterkaitan

Direct payment allocation relationship with #008.

Direct inactive/historical row lifecycle relationship with #004.

### Alasan

Laporan #012 dan #008 sama-sama membahas payment allocation integrity pada selected-row payment flow.

- #008 terjadi karena selected-row payment validation mengabaikan legacy payment_allocations.
- #012 terjadi karena selected-row/payment component flow menerima canceled work item sebagai payable component.

Laporan #012 juga berkaitan dengan #004 karena keduanya menunjukkan risiko inactive/historical rows tetap hadir di operational note object.

- #004 membahas refunded/stale work_items yang survive revision dan memicu duplicate inventory reversal.
- #012 membahas canceled work_items yang direhydrate ke note->workItems(), lalu masuk payment/status correction flow.

Namun #012 bukan bug identik dengan #004/#008 karena root cause dan patch berbeda.

## Update Log

### Update 1

Initial audit log entry untuk laporan #012.

Alasan update:

- Laporan menunjukkan canceled work item rows dapat masuk payment allocation flow.
- Laporan juga menunjukkan canceled row dapat diubah kembali lewat status correction flow.
- Patch menambahkan filter canceled row pada selected-row payable component resolution.
- Patch menambahkan guard agar canceled work item tidak dapat ditransisikan ke status lain.
- Verification masih gap karena tests gagal dijalankan akibat missing vendor/phpunit.

## Ringkasan Indonesia

Bug terjadi setelah reader note mengembalikan semua work_items, termasuk yang statusnya CANCELED.

Sebelumnya, canceled work_items difilter sebelum Note direhydrate.

Setelah perubahan, DatabaseNoteReaderAdapter memuat semua work_items. NoteMapper memang menghitung total_rupiah dengan mengecualikan canceled rows, tetapi list work items penuh tetap masuk ke Note::rehydrate().

Akibatnya domain object menjadi inkonsisten:

- note->totalRupiah() hanya menghitung active rows
- note->workItems() masih berisi canceled rows

Beberapa operational flow kemudian memakai note->workItems() tanpa mengecek status canceled.

Dampaknya:

1. Payment component resolver dapat membangun payable component dari canceled row.
2. Selected-row payment bisa memilih canceled row.
3. Payment allocation bisa tersimpan ke canceled component.
4. Note bisa auto-close saat total allocated mencapai active-only note total, walaupun active rows sebenarnya tidak mendapat component allocation yang benar.
5. Status correction flow dapat menemukan canceled work item dan mengubahnya menjadi done, sehingga canceled/refunded line seolah hidup kembali.

## Dampak

Dampak utama:

- uang bisa dialokasikan ke canceled component
- active component bisa tetap unpaid pada level component allocation
- note closure bisa salah karena payment ditempel ke inactive row
- canceled/refunded work line bisa reachable lagi oleh status correction
- financial/payment integrity rusak
- correction/status audit menjadi misleading

Severity High tepat karena payment allocation, note close, dan correction status adalah bagian core POS financial integrity. Tidak Critical karena butuh authenticated cashier/admin, target note dengan canceled work item, dan tidak menghasilkan unauthenticated compromise/RCE/account takeover.

## Jalur Risiko

Workflow payment risk:

1. User login sebagai cashier/admin.
2. Target note memiliki canceled work_item.
3. DatabaseNoteReaderAdapter memuat canceled work_item ke note->workItems().
4. Note total tetap active-only karena NoteMapper mengecualikan canceled row dari sum.
5. Payment/billing component flow melihat canceled row sebagai component.
6. User memilih selected_row_ids milik canceled row.
7. Resolver menerima selected component.
8. RecordAndAllocateNotePaymentOperation mengalokasikan payment ke canceled component.
9. Auto-close membandingkan allocated total dengan active-only note total.
10. Note dapat terlihat settled walaupun allocation component-nya salah.

Workflow status correction risk:

1. User login sebagai cashier/admin.
2. Target note memiliki canceled work_item.
3. Status correction flow mencari row di note->workItems().
4. Canceled work_item ditemukan.
5. Service lama mengizinkan transisi ke done/open tanpa canceled-state guard.
6. Canceled row dapat resurrect.

## Root Cause

Root cause:

Reader mengubah semantic note object tanpa semua consumer diperbarui.

Setelah canceled rows masuk ke note->workItems(), semua operational consumer harus memilih secara eksplisit:

1. perlu melihat semua rows, termasuk canceled/historical
2. hanya boleh melihat active/payable/editable rows

Bug terjadi karena payment component resolver dan status transition flow tetap memperlakukan note->workItems() seolah hanya berisi active rows.

## Patch Summary

Patch diterapkan pada:

app/Application/Payment/Services/ResolveNotePayableComponentsSelectedRows.php

Perubahan:

- import WorkItem
- saat iterasi note->workItems(), skip item dengan:
  WorkItem::STATUS_CANCELED
- canceled rows tidak lagi bisa matched terhadap selected_row_ids
- jika selected_row_ids menunjuk canceled row, resolver menganggapnya invalid

Patch juga diterapkan pada:

app/Application/Note/Services/WorkItemStatusTransitionService.php

Perubahan:

- private apply() menolak transisi dari CANCELED ke status lain
- jika work item status CANCELED dan target bukan CANCELED, throw DomainException:
  Work item CANCELED tidak dapat diubah ke status lain.

Test ditambahkan/diubah:

tests/Unit/Application/Payment/Services/ResolveNotePayableComponentsTest.php
tests/Unit/Application/Note/Services/WorkItemStatusTransitionServiceTest.php

Test intent:

- selected canceled row IDs rejected as invalid for payment selection
- canceled work item cannot transition to done

## Scope In

- Selected-row payment component resolution.
- Canceled work item selection rejection.
- Work item status transition guard.
- Prevention of canceled-row resurrection via status correction.
- Unit test coverage for selected rows and status transition.

## Scope Out

- DatabaseNoteReaderAdapter row-loading strategy.
- NoteMapper active/canceled semantic redesign.
- All non-selected payment component flows.
- Billing projection UI filtering.
- Full HTTP payment/correction E2E.
- DB-level constraints for canceled rows.
- Inventory side effects.
- Legacy payment issue from #008.
- Refunded stale work item issue from #004.

## Residual Audit Note

Patch summary specifically says selected-row payment component resolution was updated to skip canceled work items.

However, original finding also notes that general payment component generation can iterate note->workItems().

Therefore, follow-up audit should confirm whether any payment flow can still call full-note component generation without selected rows and include canceled rows.

Important paths to inspect:

- ResolveNotePayableComponents::fromNote()
- RecordAndAllocateNotePaymentOperation when selectedRowIds is empty
- billing projection rows shown to UI
- selected-row resolver behavior when no rows are selected
- full payment/pay_full flow

If fromNote() can still include canceled rows in a reachable payment path, this issue may only be partially fixed. Software, naturally, prefers partial exorcisms.

## Proof Dari Patch Session

User reported:

- vulnerability still present in current HEAD paths
- minimal remediation implemented in operational flows accepting canceled rows
- selected-row payment component resolution skips canceled work items
- status-transition guard blocks changing already canceled work item into any other status
- tests added/updated for both fixes
- committed on current branch with commit:
  58bfa48
- PR metadata created via make_pr

Changed files:

app/Application/Note/Services/WorkItemStatusTransitionService.php
app/Application/Payment/Services/ResolveNotePayableComponentsSelectedRows.php
tests/Unit/Application/Note/Services/WorkItemStatusTransitionServiceTest.php
tests/Unit/Application/Payment/Services/ResolveNotePayableComponentsTest.php

Reported diff size:

+86
-0

Testing reported:

./vendor/bin/phpunit tests/Unit/Application/Payment/Services/ResolveNotePayableComponentsTest.php tests/Unit/Application/Note/Services/WorkItemStatusTransitionServiceTest.php

Result:

Failed because ./vendor/bin/phpunit not found.

php artisan test tests/Unit/Application/Payment/Services/ResolveNotePayableComponentsTest.php tests/Unit/Application/Note/Services/WorkItemStatusTransitionServiceTest.php

Result:

Failed because vendor/autoload.php not present.

## Verification Gap

Tests were added but did not pass in the patch environment.

Missing proof:

- selected canceled row is rejected in passing unit test
- canceled work item cannot transition to done in passing unit test
- normal active selected row payment still works
- canceled row does not appear as payable in UI billing projection
- full-note/no-selected payment flow does not allocate to canceled components
- correction/status route returns expected failure and does not persist changes

## Recommended Follow-up

Minimum verification commands:

composer install
php artisan test tests/Unit/Application/Payment/Services/ResolveNotePayableComponentsTest.php tests/Unit/Application/Note/Services/WorkItemStatusTransitionServiceTest.php

Recommended additional audit commands:

grep -R "fromNote(\$note" -n app/Application app/Adapters
grep -R "note->workItems()" -n app/Application app/Adapters | grep -E "Payment|Billing|Status|Correction|Inventory"

Recommended test additions if not already covered:

1. Full payment with selectedRowIds empty must not allocate to canceled rows.
2. Billing projection should not expose canceled rows as payable outstanding rows.
3. Status correction by workItemId should reject canceled to done.
4. Canceled to canceled should be idempotent or explicitly allowed according to domain decision.
5. Active rows remain payable after canceled row filtering.

## Kesimpulan

Laporan #012 valid sebagai High severity payment/status integrity issue.

Bug sebelumnya membuat canceled rows kembali masuk operational note object, tetapi payment dan status correction services belum siap membedakan active vs canceled rows. Akibatnya canceled components bisa menerima payment allocation dan canceled work item bisa dihidupkan kembali lewat status transition.

Patch minimal sudah tepat untuk dua jalur yang disebut: selected-row payment selection dan status transition. Namun masih ada residual audit penting pada full-note payment component generation, karena patch yang dilaporkan fokus pada selected-row resolver, bukan seluruh resolver fromNote().

## Related Selected-Row Refund Finding From Error Log 013

### Related Error Log

- 013-forged-row-refund-can-auto-finalize-unpaid-notes.md

### Update

Update 2.

### Reason

A later audit report found a separate High severity issue in selected-row refund/cancellation behavior.

This is not the same root cause as #012.

- #012 is about canceled work_items re-entering payment and status correction flows.
- #013 is about open/unpaid selected rows entering refund flow, being canceled, and triggering note refund finalization when active total reaches zero.

Both findings show that row-level flows must explicitly validate active/canceled/paid/refundable state before mutating financial note state.

## Related Row-State Validation Finding From Error Log 014

### Related Error Log

- 014-refund-endpoint-can-cancel-open-or-unpaid-note-rows.md

### Update

Update 3.

### Reason

A later audit report found a separate row-state validation issue.

This is not the same root cause as #012.

- #012 is about canceled rows re-entering payment/status flows.
- #014 is about open/unpaid rows entering selected-row refund/cancel flow.

Both findings require operational flows to explicitly validate row state before payment, refund, cancellation, or correction mutation.
