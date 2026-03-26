# P0 - Payment Lifecycle

## Tujuan
Mengunci lifecycle pembayaran sesuai keputusan domain yang sudah diambil.

## Aturan
- Target akhir lifecycle pembayaran adalah partial payment eksplisit.
- `paid` tidak bisa cancel; jika perlu pembalikan, jalurnya adalah refund.
- Delete hanya boleh untuk `draft` dan tidak boleh menciptakan konsekuensi domain yang bertentangan.

## Implikasi
- Jangan menyusun flow yang memperbolehkan cancel pada status paid.
- Jangan membuat shortcut yang mem-bypass refund untuk kasus yang seharusnya refund.
