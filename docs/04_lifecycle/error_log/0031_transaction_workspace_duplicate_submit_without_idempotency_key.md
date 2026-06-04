# 031 - Transaction workspace duplicate submit can create duplicate financial rows without idempotency key

Status: Reported
Keparahan: High
Klasifikasi: financial integrity / duplicate submit / incomplete idempotency coverage

## Ringkasan

Create transaction workspace sudah memiliki idempotency service, tetapi proteksi itu hanya aktif jika payload membawa `idempotency_key`.

Form create workspace saat ini tidak mengirim hidden `idempotency_key`, dan validation rules masih membuat field tersebut `nullable`. Akibatnya submit ganda dengan payload yang sama dapat membuat dua `notes`, dua `work_items`, dua payment records, dua allocation records, dan dua projection rows.

Ini adalah masalah integritas finansial karena duplicate submit dapat menggandakan nota dan pembayaran tanpa perubahan bisnis yang sah.

## Bukti awal

Test karakterisasi saat ini membuktikan perilaku duplikasi:

`tests/Feature/Note/CreateTransactionWorkspaceDuplicateSubmitFeatureTest.php`

Pada `test_duplicate_create_workspace_submit_currently_creates_duplicate_notes_without_idempotency_guard()`:

- request pertama dan kedua POST ke `route('notes.workspace.store')` dengan payload yang sama;
- kedua response redirect sukses;
- assertion menghitung `2` rows untuk `notes`, `work_items`, `work_item_service_details`, `customer_payments`, `customer_payment_cash_details`, `payment_component_allocations`, dan `note_history_projection`.

Handler create workspace memang memanggil idempotency service:

`app/Application/Note/UseCases/CreateTransactionWorkspaceHandler.php`

- `replay($payload)` dipanggil sebelum transaction.
- `start($payload)` dipanggil setelah transaction dimulai.
- `succeed($payload, $result)` dipanggil sebelum commit.

Tetapi resolver mengembalikan `null` jika key kosong:

`app/Application/Note/Services/CreateTransactionWorkspaceIdempotencyScopeResolver.php`

- membaca `$payload['idempotency_key'] ?? ''`;
- jika hasil trim kosong, langsung `return null`.

Validation rules memperbolehkan key kosong:

`app/Adapters/In/Http/Requests/Note/StoreTransactionWorkspaceRules.php`

- `'idempotency_key' => ['nullable', 'string', 'max:120']`.

Form Blade create workspace memiliki form POST dan CSRF, tetapi tidak ditemukan hidden `idempotency_key`:

`resources/views/cashier/notes/workspace/create.blade.php`

- `<form action="{{ $formAction ?? route('notes.workspace.store') }}" method="POST" novalidate id="cashier-note-workspace-form">`
- `@csrf`

Search lokal:

`rg -n "idempotency_key|<form|@csrf|submit" resources/views/cashier/notes/workspace/create.blade.php public/assets/static/js/pages/cashier-note-workspace`

Output hanya menemukan form, CSRF, dan submit listeners; tidak menemukan generation atau hidden input `idempotency_key`.

## Jalur rentan

Authenticated cashier membuka form create transaction workspace -> browser submit payload tanpa `idempotency_key` -> request pertama membuat nota, item, payment, allocation, dan projection -> request kedua dengan payload sama juga diproses sebagai operasi baru -> data finansial ganda masuk ke database.

## Root cause

Idempotency guard sudah ada, tetapi coverage tidak lengkap karena key tidak diwajibkan dan tidak dihasilkan oleh UI normal.

Secara praktis, fitur idempotency hanya melindungi caller yang secara eksplisit mengirim key. Jalur UI utama tidak termasuk dalam proteksi tersebut.

## Dampak

- Nota ganda untuk transaksi yang sama.
- Payment ganda.
- Allocation ganda.
- Cash ledger/report receivable dapat ikut salah jika membaca data yang sudah tergandakan.
- Rekonsiliasi manual dibutuhkan untuk membedakan transaksi asli dan duplicate submit.

## Kontrol yang sudah ada

Test yang sama membuktikan jalur idempotency bekerja jika key dikirim:

- same actor, same operation, same key, same payload tidak membuat nota kedua;
- same key dengan payload berbeda ditolak;
- failed attempt rollback kemudian retry dengan key sama dapat sukses satu kali.

Kontrol tersebut belum cukup karena UI normal tidak mengirim key.

## Remediasi yang disarankan

Candidate patch direction:

- Generate `idempotency_key` pada render form create workspace.
- Kirim key sebagai hidden input.
- Pertimbangkan membuat `idempotency_key` wajib untuk route create workspace setelah UI siap.
- Pertahankan reject untuk same key dengan payload berbeda.
- Ubah test karakterisasi duplicate submit tanpa key menjadi regression expectation yang sesuai keputusan owner.
- Tambahkan browser-level atau feature test bahwa form rendered membawa hidden key.

## Keputusan owner yang mungkin dibutuhkan

- Apakah key dibuat server-side saat render form atau client-side JS.
- Apakah route harus menolak request tanpa key, atau server memberi fallback key untuk compatibility.
- Apakah duplicate submit guard cukup untuk create workspace saja, atau harus diseragamkan dengan edit workspace dan payment flows lain.

## Verification gap

Belum ada patch.

Belum ada proof bahwa duplicate submit dari UI normal menghasilkan satu nota saja.

Belum ada test baru yang memastikan rendered form membawa `idempotency_key`.
