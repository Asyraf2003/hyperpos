# 021 - Refunds can be recorded on open notes

## Status

Patched, with verification gap.

## Severity

High.

## Summary

The refund endpoint allowed refunds on an open parent `Nota` when selected rows were already `close`.

Sebelum perubahan vulnerable, `RecordClosedNoteRefundController` menolak refund kecuali seluruh nota sudah closed secara operasional. Flow yang berubah memindahkan validasi hanya ke selected rows. `SelectedNoteRowsRefundAmountResolver` memeriksa apakah selected rows berstatus close dan membatasi nominal refund berdasarkan net paid line terpilih, tetapi tidak memeriksa apakah parent `Nota` itu sendiri sudah closed.

Karena cashier refund route diregistrasikan sebelum group `EnsureCashierNoteAccess`, controller menjadi titik enforcement state-policy server-side yang efektif untuk route ini.

UI juga mengekspos form refund setiap kali ada line close, bahkan ketika parent note masih memiliki line open.

## Vulnerable Path

Authenticated cashier session
-> open note has one paid/close line and one open/unpaid line
-> cashier submits refund for selected close line
-> `cashier.notes.refunds.store` is reached
-> route is outside `EnsureCashierNoteAccess`
-> controller no longer checks whole-note close status
-> selected-row resolver accepts close selected row
-> refund transaction records refund/allocation
-> open parent note financial state is mutated by refund

## Root Cause

The refund policy was narrowed from whole-note closed validation to selected-row closed validation.

Validasi selected-row tidak cukup untuk business rule ini karena endpoint refund tersebut dinamai dan dimaksudkan sebagai perilaku closed-note refund. Sebuah line bisa close sementara parent note masih open.

## Patch Summary

`app/Adapters/In/Http/Controllers/Note/RecordClosedNoteRefundController.php` diubah untuk menegakkan invariant whole-note closed sebelum resolver atau pencatatan refund dijalankan.

The controller now:

- loads the note through `NoteReaderPort`
- rejects when the note is missing
- rejects when `NoteOperationalStatusResolver::isClose($note)` is false
- returns a refund validation error before mutation

`tests/Feature/Payment/RecordSelectedRowsClosedNoteRefundHttpFeatureTest.php` was updated so the open-note scenario is rejected and no refund allocation is created.

## Verification

Reported successful checks:

- `php -l app/Adapters/In/Http/Controllers/Note/RecordClosedNoteRefundController.php`
- `php -l tests/Feature/Payment/RecordSelectedRowsClosedNoteRefundHttpFeatureTest.php`

Reported failed check:

- `php artisan test tests/Feature/Payment/RecordSelectedRowsClosedNoteRefundHttpFeatureTest.php`

Failure reason:

`vendor/autoload.php` is missing in the environment.

## Verification Gap

The patch is source-level reviewed from the submitted report and patch summary, but the feature test is not proven passing in the current environment.

Future verification must run:

- `php artisan test tests/Feature/Payment/RecordSelectedRowsClosedNoteRefundHttpFeatureTest.php`

after dependencies are installed.

## Relations

Direct follow-up to #014.

#014 covers refund endpoint validation allowing open/unpaid rows to be canceled or refunded.

#021 covers a different refund-state gap: selected rows can be close, but the parent `Nota` is still open. The required invariant is whole-note closed before refund mutation.

Related to #013 because forged or invalid refund flows can produce incorrect refund/finalization behavior.

Related to #018 because refund lifecycle state affects whether notes remain mutable or terminal.

Terkait dengan #009 dan #011 karena route mutasi harus menegakkan state guard sebelum menjalankan perubahan note/payment/refund.

## Related #022 - Cashier refund route bypasses note access guard

#022 is a direct follow-up on the same cashier refund endpoint. #021 covers missing whole-note close-state enforcement in the controller. #022 covers route placement outside `EnsureCashierNoteAccess`, allowing direct refund mutation against notes the cashier should not be able to access.
