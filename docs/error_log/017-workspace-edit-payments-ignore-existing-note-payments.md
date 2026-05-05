# 017 - Workspace edit payments ignore existing note payments

## Status

Patched, with verification gap.

Patch supplied and PHP syntax checks passed, but no focused behavior/regression test was reported as passing.

## Severity

High.

## Source

Audit report #017: Workspace edit payments ignore existing note payments.

## Relasi Dengan Error Log Lain

### Berkaitan Dengan

- 008-legacy-paid-notes-can-be-paid-again.md
- 005-note-revision-silently-drops-overpaid-allocations.md
- 011-cashier-revision-path-mutates-settled-note-state.md

### Jenis Keterkaitan

Direct existing-payment/payment-allocation relationship with #008.

Direct financial allocation integrity relationship with #005.

Indirect workspace editability relationship with #011.

### Alasan

Laporan #017 dan #008 sama-sama membahas flow pembayaran yang mengabaikan existing note payments.

- #008 terjadi pada selected-row payment flow yang memakai projection component-only dan mengabaikan legacy payment_allocations.
- #017 terjadi pada workspace edit inline payment flow yang menganggap existing allocation total adalah zero, sehingga inline payment bisa dicatat terhadap total penuh note, bukan outstanding.

Laporan #017 berkaitan dengan #005 karena sama-sama menyentuh payment allocation integrity saat note berubah melalui workspace/revision/edit flow.

Laporan #017 berkaitan tidak langsung dengan #011 karena partially paid open note masih dapat diedit, lalu inline payment path berjalan pada note yang sudah memiliki financial state sensitif.

Karena root cause, service, dan patch berbeda, laporan #017 dicatat sebagai file baru.

## Update Log

### Update 1

Initial audit log entry untuk laporan #017.

Alasan update:

- Laporan menunjukkan workspace edit inline payment mengabaikan existing note payments.
- Patch memperbarui inline payment amount resolver agar pay_full menghitung outstanding, bukan full note total.
- Patch memperbarui inline payment recorder agar PaymentAllocationPolicy menerima existing allocated total.
- Verification masih gap karena hanya php -l yang dilaporkan pass.

## Ringkasan Indonesia

Bug terjadi pada update workspace flow untuk existing note.

Flow bermasalah:

1. User membuka/edit note yang sudah partially paid tetapi masih operationally open.
2. Workspace update mengganti item/header/total note.
3. Setelah update, handler mencatat inline payment.
4. Inline payment recorder yang dipakai berasal dari flow create note baru.
5. Recorder lama menganggap belum ada existing allocation.
6. PaymentAllocationPolicy dipanggil dengan existing note allocation = Money::zero().
7. pay_full dihitung dari full note total, bukan sisa outstanding.
8. Sistem dapat mencatat payment baru melebihi sisa tagihan.

Contoh dari laporan:

- Note total: 100.000
- Existing allocation: 40.000
- Outstanding seharusnya: 60.000
- pay_full lama resolve ke 100.000
- payment baru 100.000 diterima
- total payment menjadi 140.000 terhadap note 100.000

## Dampak

Dampak utama:

- open partially paid note bisa menerima inline payment berlebih saat workspace edit
- customer_payments dapat overstated
- payment_component_allocations dapat melebihi outstanding sebenarnya
- cash ledger/reporting bisa salah
- note settlement dan audit payment menjadi tidak akurat
- legacy payment_allocations bisa diabaikan walaupun masih didukung oleh settlement reader

Severity High tepat karena payment settlement dan cash reports adalah core financial-integrity data untuk POS/back-office. Tidak Critical karena membutuhkan authenticated cashier/admin, valid CSRF/session, target note yang editable/open, dan existing payment state.

## Jalur Risiko

Workflow risiko:

1. User login sebagai cashier/admin.
2. Target note masih editable/open tetapi sudah punya existing payment allocation.
3. User submit PATCH workspace edit dengan inline_payment.
4. UpdateTransactionWorkspaceHandler menjalankan workspace update.
5. Handler memanggil inline payment recorder.
6. Amount resolver lama menghitung pay_full dari full note total.
7. Recorder lama memanggil PaymentAllocationPolicy dengan existing allocation zero.
8. Policy menerima payment karena prior allocations tidak dihitung.
9. Customer payment dan component allocation baru dibuat.
10. Combined payment melebihi true outstanding.

## Root Cause

Root cause:

Inline payment services untuk create-note flow dipakai ulang pada update/edit-note flow tanpa membawa existing settlement state.

Kesalahan spesifik:

1. CreateTransactionWorkspaceInlinePaymentAmountResolver menghitung pay_full dari note total penuh.
2. resolvePartial membandingkan partial payment dengan note total penuh, bukan outstanding.
3. CreateTransactionWorkspaceInlinePaymentRecorder memanggil PaymentAllocationPolicy dengan existing allocated note total = Money::zero().
4. Existing payment allocation, termasuk legacy payment_allocations, tidak dipakai saat validasi inline payment edit.

## Patch Summary

Patch diterapkan pada:

app/Application/Note/Services/CreateTransactionWorkspaceInlinePaymentAmountResolver.php

Perubahan:

- tambahkan dependency PaymentAllocationReaderPort
- pay_full sekarang resolve ke outstanding amount
- outstanding amount dihitung:
  max(note total - existing allocated, 0)
- partial payment sekarang dibandingkan dengan outstanding amount
- jika partial payment >= outstanding, throw DomainException:
  Nominal pembayaran sebagian harus lebih kecil dari sisa tagihan.

Patch juga diterapkan pada:

app/Application/Note/Services/CreateTransactionWorkspaceInlinePaymentRecorder.php

Perubahan:

- tambahkan dependency PaymentAllocationReaderPort
- sebelum policy check, baca:
  getTotalAllocatedAmountByNoteId($note->id())
- PaymentAllocationPolicy::assertAllocatable() sekarang menerima existing allocated note total, bukan Money::zero()

Efek patch:

- inline payment pada existing note menghormati outstanding balance
- pay_full tidak lagi overpay full total jika note sudah partially paid
- policy check kembali punya data existing allocation
- legacy allocation total ikut dihitung lewat PaymentAllocationReaderPort jika reader fallback aktif

## Scope In

- Workspace edit inline payment path.
- Inline payment amount resolution.
- Existing note allocation awareness.
- pay_full based on outstanding.
- partial payment validation against outstanding.
- PaymentAllocationPolicy existing allocation input.

## Scope Out

- Selected-row payment bug from #008.
- Payment replay/downward revision bug from #005.
- Editability guard from #011.
- Full workspace HTTP feature test.
- Reporting query changes.
- Explicit overpayment/customer credit feature.
- Production cleanup for already-overpaid notes.

## Proof Dari Patch Session

User reported:

- vulnerability still existed in HEAD
- update-workspace inline payment path inspected
- allocation assumptions fixed in existing inline payment services
- PaymentAllocationReaderPort dependency added to both resolver and recorder
- committed on branch work with commit:
  dbbbf56
- PR message created via make_pr

Changed files:

app/Application/Note/Services/CreateTransactionWorkspaceInlinePaymentRecorder.php
app/Application/Note/Services/CreateTransactionWorkspaceInlinePaymentAmountResolver.php

Reported diff size:

+21
-4

Testing reported:

- php -l app/Application/Note/Services/CreateTransactionWorkspaceInlinePaymentRecorder.php
- php -l app/Application/Note/Services/CreateTransactionWorkspaceInlinePaymentAmountResolver.php

## Verification Gap

Only syntax validation was reported.

Missing proof:

- pay_full on partially paid note records only outstanding
- partial payment greater than or equal to outstanding is rejected
- existing legacy payment_allocations are counted through PaymentAllocationReaderPort
- no over-allocation occurs after workspace edit
- create-new-note inline payment behavior still works
- zero-outstanding note cannot record inline payment
- audit/payment records remain consistent

## Recommended Follow-up

Minimum regression test:

Scenario 1:

- note total: 100.000
- existing payment allocation: 40.000
- workspace edit inline_payment decision: pay_full
- expected recorded payment: 60.000
- expected total allocated after payment: 100.000
- expected no 140.000 overpayment state

Scenario 2:

- note total: 100.000
- existing payment allocation: 40.000
- inline partial payment: 60.000
- expected reject because partial payment must be less than outstanding

Scenario 3:

- note total: 100.000
- existing payment allocation: 40.000
- inline partial payment: 30.000
- expected success
- expected allocated total: 70.000

Scenario 4:

- legacy payment_allocations = 40.000
- component allocations empty
- pay_full should resolve to 60.000 if legacy fallback remains intended

Recommended command later:

php artisan test --filter=WorkspaceInlinePayment

Recommended audit search:

grep -R "CreateTransactionWorkspaceInlinePayment" -n app
grep -R "Money::zero()" -n app/Application/Note app/Application/Payment | grep -E "Allocation|Payment|Inline"
grep -R "pay_full" -n app/Application app/Adapters

## Kesimpulan

Laporan #017 valid sebagai High severity financial-integrity issue.

Bug sebelumnya memakai inline payment flow yang cocok untuk note baru pada note yang sedang diedit dan sudah punya pembayaran. Akibatnya payment bisa dicatat terhadap full note total, bukan outstanding balance.

Patch minimal sudah tepat: amount resolver dan recorder sekarang membaca existing allocated total dan menggunakan outstanding sebagai dasar payment. Namun patch masih membutuhkan behavior test karena php -l hanya membuktikan sintaks, bukan settlement benar.

## Related #008 update - Legacy inline payments ignored in row settlement

This update is related to #017 through the existing-payment visibility problem. #017 covers workspace edit payments ignoring existing note payments, while the #008 update covers selected-row row settlement and payment allocation paths ignoring legacy inline `payment_allocations` when only component allocation readers are used.
