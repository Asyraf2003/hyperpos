CATATAN HANDOFF — KASIR BENGKEL — MASUK STEP 3

STATUS UMUM
- Domain inti dan keputusan besar SUDAH FINAL.
- Jangan buka ulang diskusi domain kecuali ada konflik nyata.
- Blueprint Induk, Workflow Induk, DoD Induk, dan ADR-001 s.d. ADR-012 sudah final.

STATUS STEP SEBELUMNYA
- Step 2: selesai 100%
- Skeleton hexagonal sudah hidup
- Baseline testing sudah hidup
- Baseline audit-hex sudah hidup
- Makefile baseline sudah hidup
- Ada vertical slice /health yang pass end-to-end

KEPUTUSAN IMPLEMENTASI YANG SUDAH TERKUNCI
- AppServiceProvider dipisah dari HexagonalServiceProvider
- NullCapabilityPolicyAdapter default false
- Money integer-only
- ensureNotNegative() eksplisit
- TransactionManagerPort pakai begin/commit/rollBack, bukan callable closure

VALIDASI YANG SUDAH PASS
- php scripts/audit-hex.php => OK
- php artisan test => PASS
- make check => PASS

TARGET CHAT INI
Masuk ke Workflow Step 3 secara aman.

BATAS SCOPE CHAT INI
- Fokus hanya fondasi domain master paling dasar
- Jangan lompat ke transaksi penuh
- Jangan masuk stock movement penuh
- Jangan masuk costing penuh
- Jangan masuk reporting penuh

URUTAN YANG DIIZINKAN UNTUK STEP 3
1. Product entity/value object minimum
2. Product repository port
3. Product application use case minimum
4. adapter persistence placeholder
5. test baseline master product

ATURAN KERJA
- zero assumption
- blueprint dulu untuk step ini
- lalu workflow step-by-step
- satu langkah per balasan
- tunggu feedback sebelum lanjut
