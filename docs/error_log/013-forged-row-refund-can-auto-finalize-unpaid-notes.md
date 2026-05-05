# 013 - Forged row refund can auto-finalize unpaid notes

## Status

Patched for auto-finalization, with verification gap and residual validation risk.

Patch supplied and syntax/status/commit were reported, but no focused behavior test was reported as passing.

## Severity

High.

## Source

Audit report #013: Forged row refund can auto-finalize unpaid notes.

## Relasi Dengan Error Log Lain

### Berkaitan Dengan

- 012-canceled-note-rows-re-enter-payment-flows.md
- 004-refunded-work-items-survive-revisions-and-inflate-stock.md
- 011-cashier-revision-path-mutates-settled-note-state.md
- 009-cashiers-can-rewrite-closed-paid-notes-via-workspace-update.md

### Jenis Keterkaitan

Direct selected-row lifecycle relationship with #012.

Indirect inventory/accounting relationship with #004.

Indirect note-state mutation relationship with #011 and #009.

### Alasan

Laporan #013 dan #012 sama-sama menunjukkan bahwa row-level operational flows menerima row state yang tidak semestinya diproses.

- #012 membahas canceled work item yang masuk payment/status correction flow.
- #013 membahas selected-row refund flow yang menerima open/unpaid row, lalu cancellation + auto-finalization membuat unpaid note menjadi refunded.

Laporan #013 juga berkaitan tidak langsung dengan #004 karena store-stock rows yang dicancel/refund dapat berdampak ke inventory/accounting consistency.

Laporan #013 berkaitan tidak langsung dengan #011/#009 karena sama-sama menyebabkan mutation pada note state yang seharusnya dilindungi, tetapi #013 bukan workspace revision authorization bug. Ini adalah refund/finalization business-logic bug.

Karena root cause, service, dan patch berbeda, laporan #013 dicatat sebagai file baru.

## Update Log

### Update 1

Initial audit log entry untuk laporan #013.

Alasan update:

- Laporan menunjukkan forged selected-row refund dapat mengubah unpaid note menjadi refunded.
- Patch menambahkan guard agar finalization hanya berjalan jika refund processing benar-benar menghasilkan allocation.
- Verification masih gap karena hanya php -l, git status, dan commit yang dilaporkan.

## Ringkasan Indonesia

Bug terjadi pada selected-row refund transaction flow.

Flow lama:

1. User memilih row untuk refund.
2. Resolver menerima selected row IDs selama row ada di note dan belum inactive/refunded.
3. Resolver tidak memastikan setiap row benar-benar paid/closed/refundable.
4. Jika row yang dipilih unpaid/open, payment buckets bisa kosong.
5. Bucket processor tidak membuat customer refund dan tidak membuat refund allocations.
6. CancelSelectedRowsAndSyncActiveNoteTotal tetap membatalkan selected rows.
7. Note total dihitung ulang dari remaining active rows.
8. Jika semua active rows dipilih, active total menjadi 0.
9. FinalizeRefundedNoteFromActiveRows otomatis close/refund note yang totalnya 0.
10. Note unpaid bisa menjadi refunded walau tidak ada refund allocation yang benar-benar dicatat.

Masalah intinya: finalizer memakai active total = 0 sebagai syarat cukup untuk refund finalization, tanpa membuktikan real refund terjadi.

## Dampak

Dampak utama:

- unpaid note bisa terlihat refunded
- tidak ada customer_refund/refund_component_allocations yang valid
- sales/receivable bisa disembunyikan
- audit trail bisa misleading
- cashier accountability rusak
- selected row cancellation bisa menghapus active total dari note
- store-stock/accounting bisa inconsistent jika inventory reversal hanya terjadi pada recorded refund buckets

Severity High tepat karena ini memengaruhi financial integrity dan fraud control pada POS/back-office. Tidak Critical karena membutuhkan authenticated cashier/admin, CSRF/session valid, dan target note/row ID.

## Jalur Risiko

Workflow risiko:

1. User login sebagai kasir/admin.
2. User memiliki akses ke refund selected rows endpoint.
3. User mengirim forged POST dengan selected_row_ids milik active unpaid rows.
4. SelectedNoteRowsRefundPlanResolver menerima row karena row valid dan belum inactive.
5. Plan dapat berisi unpaidRowIds dan paymentBuckets kosong.
6. RecordSelectedRowsRefundPlanBucketProcessor tidak membuat refund karena bucket kosong.
7. CancelSelectedRowsAndSyncActiveNoteTotal tetap cancel selected rows.
8. Jika semua active rows dipilih, note active total menjadi 0.
9. FinalizeRefundedNoteFromActiveRows menutup dan me-refund note karena total 0.
10. Note unpaid menjadi refunded tanpa real refund allocation.

## Root Cause

Root cause gabungan:

1. Selected-row refund resolver tidak mewajibkan row yang dipilih benar-benar paid/refundable.
2. Transaction flow tetap mencancel selected rows walau paymentBuckets kosong.
3. FinalizeRefundedNoteFromActiveRows hanya melihat total active note = 0.
4. Finalizer tidak memverifikasi bahwa refund processing menghasilkan customer refund/refund allocation.
5. State transition note refunded bisa terjadi tanpa real refund.

## Patch Summary

Patch diterapkan pada:

app/Application/Payment/Services/RecordSelectedRowsRefundPlanTransaction.php

Perubahan:

- finalization tidak lagi dipanggil default setelah row cancellation.
- `$finalized` default diisi `Result::success(['finalized' => false])`.
- `FinalizeRefundedNoteFromActiveRows::execute()` hanya dipanggil jika:
  allocation_count > 0
- jika allocation_count = 0, transaction tetap sync projection dan audit, tetapi tidak auto-finalize note menjadi refunded.
- behavior legitimate refund tetap dijaga: jika allocation ada, finalizer tetap jalan dan failure path tetap throw DomainException.

Efek patch:

- zero-refund unpaid-row cancellation flow tidak lagi otomatis mengubah note menjadi refunded.
- note finalization sekarang membutuhkan bukti minimal bahwa refund allocation benar-benar terjadi.

## Scope In

- Selected-row refund transaction finalization.
- Guarding FinalizeRefundedNoteFromActiveRows behind recorded refund allocations.
- Preventing zero-allocation refund plan from auto-finalizing note as refunded.
- Preserving legitimate paid/refundable selected-row refund behavior.

## Scope Out

- Resolver validation requiring selected rows to be paid/refundable.
- Preventing unpaid row cancellation through refund route.
- Inventory reversal consistency for unpaid rows.
- Full selected-row refund E2E test.
- UI behavior.
- Broader note-state finalization redesign.
- Payment/refund audit report query changes.

## Residual Risk

Patch prevents the most damaging state transition: unpaid zero-refund flow auto-finalizing the note as refunded.

However, the report also identifies a pre-existing selected-row validation weakness:

SelectedNoteRowsRefundPlanResolver may still accept open/unpaid rows and produce unpaidRowIds with zero payment buckets.

If that remains true, a forged selected-row refund may still cancel unpaid rows and reduce note total, even if it no longer marks the note refunded.

Follow-up audit must verify whether unpaid row cancellation through refund endpoint is intended. If not intended, resolver should reject rows that have no refundable paid allocation.

This is the sort of bug where the finalizer stopped signing the forged document, but the clerk may still be shredding invoices in the back room. Splendid little nightmare.

## Proof Dari Patch Session

User reported:

- vulnerability path still present at HEAD
- minimal guard implemented in selected-row refund transaction flow
- note finalization is not attempted by default
- finalization only runs when refund processing produced at least one allocation
- legitimate paid/refundable selected-row refunds still run finalization
- commit created with message:
  Guard note finalization behind recorded refund allocations

Testing reported:

- php -l app/Application/Payment/Services/RecordSelectedRowsRefundPlanTransaction.php
- git status --short
- git commit -m "Guard note finalization behind recorded refund allocations"

Changed file:

app/Application/Payment/Services/RecordSelectedRowsRefundPlanTransaction.php

Reported diff size:

+10
-3

## Verification Gap

Only PHP syntax validation and commit flow were reported.

Missing proof:

- forged unpaid selected-row refund no longer finalizes note as refunded
- allocation_count = 0 results in finalized=false
- no customer refund/refund allocation is created for unpaid rows
- legitimate selected-row refund with allocation_count > 0 still finalizes when appropriate
- finalization failure still rolls back transaction
- audit record clearly distinguishes finalized=false
- projection state remains consistent after zero-allocation cancellation

## Recommended Follow-up

Minimum regression test:

Scenario 1:

- note_state = open
- active row total > 0
- no payment allocations
- selected-row refund request selects all active rows
- bucket processing allocation_count = 0
- expect note not finalized as refunded
- expect finalized=false in audit/result data
- expect no customer_refunds
- expect no refund_component_allocations

Scenario 2:

- note has paid/refundable selected row
- selected-row refund creates allocation_count > 0
- expect finalization behavior unchanged if active total becomes 0

Scenario 3:

- unpaid selected-row refund should be rejected entirely if domain decision says refund route must only handle paid/refundable rows

Recommended command later:

php artisan test --filter=SelectedRowsRefund

Recommended audit search:

grep -R "FinalizeRefundedNoteFromActiveRows" -n app
grep -R "allocation_count" -n app/Application/Payment app/Application/Note
grep -R "unpaidRowIds" -n app

## Kesimpulan

Laporan #013 valid sebagai High severity financial-integrity issue.

Bug sebelumnya memungkinkan forged selected-row refund terhadap unpaid rows membuat active total menjadi nol, lalu finalizer otomatis mengubah note menjadi refunded tanpa customer refund atau refund allocation.

Patch minimal sudah tepat untuk root cause langsung dari auto-finalization: finalizer hanya dipanggil jika refund processing benar-benar menghasilkan allocation. Namun masih ada residual validation risk pada selected-row resolver/cancellation flow, karena unpaid rows mungkin masih bisa dicancel lewat refund endpoint.

## Related Resolver Precondition Fix From Error Log 014

### Related Error Log

- 014-refund-endpoint-can-cancel-open-or-unpaid-note-rows.md

### Update

Update 2.

### Reason

A later audit report fixed the residual validation weakness noted in #013.

This is not the same root cause as #013, but it is a direct follow-up.

- #013 fixed the auto-finalization consequence by requiring allocation_count > 0 before finalization.
- #014 fixes the earlier resolver precondition by rejecting open/unpaid rows before they enter refund/cancel flow.

Together, both fixes are needed:
1. selected-row refund must reject non-close rows
2. note refund finalization must not run when no refund allocation was recorded

## Related Refunded-Note Edit Visibility Finding From Error Log 015

### Related Error Log

- 015-refunded-notes-expose-edit-workspace.md

### Update

Update 3.

### Reason

A later audit report found a separate issue involving refunded-note lifecycle.

This is not the same root cause as #013.

- #013 is about refund finalization being triggered without recorded refund allocations.
- #015 is about refunded notes exposing the workspace Edit button through normal UI navigation.

Both should be considered when auditing refunded note state transitions and editability.

## Related Refunded-State Terminal Guard Finding From Error Log 018

### Related Error Log

- 018-refunded-notes-bypass-cashier-closed-note-guards.md

### Update

Update 4.

### Reason

A later audit report found a separate issue after notes enter refunded lifecycle state.

This is not the same root cause as #013.

- #013 is about auto-finalizing notes as refunded without recorded refund allocations.
- #018 is about already-refunded notes bypassing cashier closed-note guards and becoming mutable.

Together, refund lifecycle must be safe both when entering refunded state and after the note is in refunded state.

## Related #021 - Refunds can be recorded on open notes

#021 is related through refund lifecycle integrity. #013 covers invalid selected-row refund behavior that can affect note finalization, while #021 covers refund mutation being allowed before the parent `Nota` is operationally close.
