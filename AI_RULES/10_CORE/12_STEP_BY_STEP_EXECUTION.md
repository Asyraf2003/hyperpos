# P0 - Step-by-Step Execution

## Tujuan
Menjaga eksekusi tetap terkontrol, dapat diaudit, dan mudah divalidasi user.

## Aturan
- Workflow harus step-by-step.
- Hanya satu step aktif per respons kerja.
- Setelah satu step selesai, berhenti dan tunggu feedback user sebelum lanjut ke step berikutnya.
- Jangan lompat ke step lain walau terlihat efisien jika step aktif belum divalidasi.

## Definisi step aktif
Step aktif adalah unit kerja terkecil yang:
- punya tujuan jelas
- punya bukti selesai
- punya batas masuk/keluar yang jelas

## Larangan
- Jangan menggabungkan banyak keputusan besar dalam satu step tanpa pemisahan bukti.
- Jangan menganggap approval implisit.
