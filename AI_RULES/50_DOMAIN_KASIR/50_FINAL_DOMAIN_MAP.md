# P0 - Final Domain Map

## Tujuan
Mengunci peta domain final project kasir agar AI tidak mengaburkan istilah dan source of truth.

## Domain map
- `products` = master barang
- `product_inventory` + `inventory_movements` = source of truth stok
- `supplier_invoices` + items = pintu masuk stok dan basis avg_cost / COGS
- `customer_orders` = Nota Pelanggan
- `customer_transactions` = Kasus
- `customer_transaction_lines` = Rincian

## Aturan
- Laporan membaca domain final, bukan menciptakan source of truth baru.
- Jangan mencampur istilah domain final dengan istilah sementara lama.
