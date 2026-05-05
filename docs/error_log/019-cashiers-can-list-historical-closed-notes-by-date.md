# 019 - Cashiers can list historical closed notes by date

## Status

Patched, with verification gap.

## Severity

High.

## Summary

Tabel cashier note history menerima query parameter `date` yang dikendalikan client dan memakainya sebagai anchor date untuk window history dua hari. Karena `/cashier/notes/table` hanya dilindungi middleware cashier-area dan transaction-entry, serta tidak berada di dalam guard per-nota `EnsureCashierNoteAccess`, kasir terautentikasi dapat melakukan query ke window historis arbitrary.

Dampak masalah ini meningkat setelah query cashier history berubah dari `openOnly=true` menjadi `openOnly=false`. Dengan `openOnly=false`, shared note history rows query tidak lagi memfilter `notes.note_state = open`, sehingga nota historis yang sudah closed ikut dikembalikan.

The JSON table response disclosed sensitive cashier-facing note data, including note IDs, transaction dates, customer labels/names/phones, grand totals, paid totals, outstanding totals, line summary counts, payment labels, work labels, and action URLs.

## Vulnerable path

Authenticated cashier session
-> GET /cashier/notes/table?date=2025-01-15
-> route passes auth, cashier-area, transaction-entry middleware
-> request validates client-controlled date format
-> controller forwards validated filters
-> CashierNoteHistoryCriteria uses client date as anchor
-> query searches previousDate..anchorDate
-> CashierNoteHistoryBaseQuery passes openOnly=false
-> NoteHistoryRowsQuery does not filter notes.note_state='open'
-> historical closed customer/financial note summaries are returned

## Root Cause

The table endpoint treated a client-supplied date as a trusted anchor for cashier history retrieval.

Endpoint ini juga bergantung pada middleware cashier/transaction yang terlalu luas, bukan menegakkan batas akses kasir today/yesterday langsung di level query.

Perubahan dari `openOnly=true` ke `openOnly=false` memperluas leak dari nota historis open menjadi nota historis closed.

## Patch Summary

`app/Adapters/Out/Note/Queries/CashierNoteHistoryCriteria.php` diubah agar anchor date cashier history selalu memakai tanggal saat ini dari server.

Client-supplied `date` input is no longer used to choose the query window.

A regression test was added in:

`tests/Feature/Note/CashierNoteHistoryTableClosurePolicyFeatureTest.php`

Test mengirim tanggal historis arbitrary (`2025-01-15`) dan memastikan query tetap hanya mengembalikan nota today/yesterday sambil mengecualikan nota yang lebih lama.

## Verification

Attempted:

`php artisan test --filter=CashierNoteHistoryTableClosurePolicyFeatureTest`

Result:

Gagal di environment yang dilaporkan karena `vendor/autoload.php` tidak ada dan dependencies belum terpasang.

## Verification Gap

Patch sudah direview pada level source dari laporan dan patch summary yang dikirim, tetapi regression test belum terbukti pass di environment ini.

Verifikasi berikutnya harus memasang dependencies atau dijalankan di environment project, lalu menjalankan:

`php artisan test --filter=CashierNoteHistoryTableClosurePolicyFeatureTest`

Recommended additional proof:

`php artisan route:list --path=cashier/notes`

to confirm route middleware ordering and guard placement.

## Relations

Related to #009, #011, #015, and #018 as part of the cashier access-boundary cluster.

Berbeda dari laporan tersebut karena masalah ini adalah disclosure data historis read-only melalui endpoint tabel kasir, bukan authorization untuk mutation/edit/refund workspace.

Related to #018 because both involve cashier access logic around closed/refunded note boundaries, but #019 specifically concerns date-window enumeration and closed historical note listing.

## Related #022 - Cashier refund route bypasses note access guard

#022 is related through cashier historical-note access boundaries. #019 covers read-only disclosure of historical closed notes through the cashier table route. #022 covers unauthorized refund mutation on closed or historical notes when the cashier refund route bypasses `EnsureCashierNoteAccess`.
