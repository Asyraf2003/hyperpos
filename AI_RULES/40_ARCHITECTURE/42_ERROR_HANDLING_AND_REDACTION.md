# P0 - Error Handling and Redaction

## Tujuan
Menjamin error handling aman, konsisten, dan tidak membocorkan detail sensitif.

## Aturan
- Tidak boleh ada raw error leak ke user-facing output.
- Error harus mengikuti envelope/handler yang konsisten bila contract tersebut sudah dikunci.
- Redaction wajib untuk detail sensitif.
- Logging dan response harus diperlakukan berbeda bila diperlukan untuk keamanan.
