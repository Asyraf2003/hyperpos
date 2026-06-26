# 0045 Manual Full Refund Lunas Edit Report Lifecycle Mismatch

## Status

Reported by owner on 2026-06-26 08:03 WITA. Forensic active. No fix has been
claimed yet.

This log captures manual UI/runtime evidence after the automated closure of
0043/0044 exposed a remaining end-to-end lifecycle mismatch.

## Scope

Audit the full note lifecycle from DB to Blade/JS/UI for:

- create transaction with product, service package, and service-only rows
- partial payment
- edit/revision that removes a product row and adds another partial payment
- full settlement
- full refund
- re-opened note action availability
- edit-after-refund behavior
- transaction cash report
- operational cash profit report
- service package profit report
- stock/inventory report
- per-note transaction report, screen/PDF/Excel headings and values

Primary concern: UI actions must match backend-allocatable financial components,
not only total-vs-net-cash summaries.

## FACT

Owner manual scenario:

1. Initial product stock was increased by `10` for all products used in the
   manual test.
2. Created transaction:
   - product: `17500`
   - service x product/package: `112500`
   - service-only: `60000`
   - total: `190000`
3. Paid partially:
   - paid: `55000`
   - remaining debt: `135000`
4. Edited transaction:
   - removed the standalone product row
   - paid partially again: `37500`
   - resulting transaction: `172500`
   - paid: `92500`
   - remaining debt: `80000`
5. Settled/lunasi the remaining debt.
6. Refunded everything.
7. Re-opened the transaction and the UI still allowed `lunasi` for `37500`,
   apparently from the service-package product component.
8. Executing that payment produced:
   - `Tidak ada komponen note yang bisa dialokasikan untuk payment ini.`

Manual report values observed after the scenario:

- Laporan kas transaksi:
  - Total Kejadian: `4`
  - Kas Masuk: `Rp 172.500`
  - Tunai Masuk: `Rp 172.500`
  - Transfer Masuk: `Rp 0`
  - Kas Keluar: `Rp 37.500`
  - Nilai Bersih: `Rp 135.000`
- Laba kas operasional:
  - Uang Masuk: `Rp 172.500`
  - Pengembalian Dana: `Rp 37.500`
  - Pembelian Eksternal: `Rp 0`
  - HPP Stok Toko: `Rp 8.000`
  - Harga Beli Produk: `Rp 8.000`
  - Biaya Operasional: `Rp 0`
  - Gaji: `Rp 0`
  - Hutang Karyawan: `Rp 0`
  - Laba Kas Operasional: `Rp 127.000`
- Laba paket service:
  - Jumlah Paket: `1`
  - Nilai Paket Terjual: `Rp 112.500`
  - Total Sparepart: `Rp 37.500`
  - HPP Sparepart: `Rp 0`
  - Margin Sparepart: `Rp 37.500`
  - Komponen Service: `Rp 75.000`
  - Refund Komponen Produk: `Rp 37.500`
  - Refund Komponen Service: `Rp 0`
  - Gross Profit Paket: `Rp 112.500`
- Stok dan nilai persediaan:
  - Produk Snapshot: `3`
  - Produk Bermutasi: `3`
  - Qty Tersedia: `30`
  - Nilai Persediaan: `Rp 30.000`
  - Qty Masuk Pembelian: `30`
  - Qty Keluar Penjualan: `10`
  - Qty Balik Refund/Reversal: `2`
  - Qty Koreksi/Revisi: `8`
  - Selisih Qty Periode: `30`
  - Selisih Nilai Pokok Periode: `Rp 30.000`
- Laporan transaksi per nota:
  - Jumlah Nota: `1`
  - Nilai Bruto Transaksi: `Rp 172.500`
  - Pembayaran Dialokasikan: `Rp 172.500`
  - Dana Dikembalikan: `Rp 37.500`
  - Kas Bersih: `Rp 135.000`
  - Refund Due: `Rp 0`
  - Surplus Refund Paid: `Rp 0`
  - Sisa Refund Due: `Rp 0`
  - Sisa Tagihan: `Rp 37.500`
  - Note status count: `1 close`, `1 refund`

Additional manual UI evidence:

- Refund action remained visible/active on the service row, but execution failed
  with a guard that refund can only be recorded for a closed/lunas note.
- Edit remained possible after refund.
- After editing and removing all rows except the package, the UI showed a
  `112500` bill but partial/full payment was unavailable.
- Saving that note made the status become `lunas` unexpectedly.

## GAP

The following are not yet proven from DB/source in this session:

- exact note id and revision ids for the manual scenario
- whether report values are reading stale/current projections correctly
- whether payment UI availability is based on report-style outstanding instead
  of backend-allocatable components
- whether refund UI availability is based on note status, row status, or a stale
  modal payload
- whether edit-after-refund should be blocked or allowed with explicit surplus /
  refund_due / paid-state recalculation
- whether PDF/Excel exports use the same source and headings as the screen
  reports for this scenario

## ROOT CAUSE CANDIDATE

Preliminary candidate from the manual evidence:

- `Sisa Tagihan` and the `lunasi` UI appear to be derived from
  `gross total - net paid after refund`.
- The payment allocator appears to derive payable capacity from active,
  non-refunded component balances.
- After full refund of the only remaining product component, those two models
  disagree:
  - UI/report sees `37500` outstanding.
  - backend allocation sees no component that can receive payment.

This is not safe to patch as a UI-only hide rule until DB/source proves the
canonical lifecycle invariant.

## DECISION

Do not start a broad rewrite yet.

First prove the exact invariant boundary:

1. Is a fully refunded component considered canceled/non-payable?
2. Should a refunded amount reduce payment credit, reduce active bill, or both?
3. Should a refunded note remain editable?
4. If edit is allowed after refund, how are prior payments, refunds, inventory
   reversal, payable components, and note status replayed?
5. Should reports show accounting cash history, current collectible debt, or both
   as separate columns?

## ACTIVE STEP

Forensic step 1:

- locate the latest manual note in DB
- map note/revision/payment/refund/allocation/inventory rows
- map Blade/JS action availability for bayar/lunasi/refund/edit
- map report query sources for screen/PDF/Excel
- then add characterization tests before changing behavior

## PROOF

Current proof is owner manual evidence only. Automated/source/DB proof is still
pending.

## NEXT SAFE STEP

Read the local source and DB state for the latest matching note. Update this log
again before any patch with:

- actual note id
- actual allocation/refund rows
- source paths that decide each action
- first characterization test target
