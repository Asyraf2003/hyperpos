# 0050 - Timestamp Read-only Shared Hosting Runbook

## Scope

Runbook ini hanya untuk diagnostic timestamp legacy di production shared hosting.

Tidak ada repair/write di tahap ini.

## Hard Rule

Jangan jalankan query write:

- `UPDATE`
- `DELETE`
- `INSERT`
- `REPLACE`
- `ALTER`
- `DROP`
- `TRUNCATE`
- `CREATE`

Jangan shift date-only business fields:

- `refunded_at`
- `transaction_date`
- `shipment_date`
- `due_date`
- `expense_date`
- `payment_date`

Field tersebut adalah tanggal bisnis, bukan timestamp audit/history.

## File SQL

Gunakan:

```text
docs/04_lifecycle/sql/0050_timestamp_readonly_diagnostic_mysql.sql
```

SQL ini hanya membandingkan:

- `raw_db`
- `wita_if_raw_is_utc_like`

`wita_if_raw_is_utc_like` adalah simulasi `raw_db + 8 jam`.

## Urutan Production Diagnostic

### 1. Backup database

Sebelum query diagnostic production:

- Export full database dari panel hosting/phpMyAdmin.
- Simpan file backup lokal.
- Jangan lanjut kalau backup belum ada.

### 2. Jalankan runtime sanity query

Jalankan bagian pertama:

```sql
SELECT
    'runtime' AS section,
    @@global.time_zone AS mysql_global_time_zone,
    @@session.time_zone AS mysql_session_time_zone,
    UTC_TIMESTAMP() AS mysql_utc_now,
    NOW() AS mysql_session_now,
    DATE_ADD(UTC_TIMESTAMP(), INTERVAL 8 HOUR) AS wita_now_from_utc;
```

Catat hasil:

- `mysql_global_time_zone`
- `mysql_session_time_zone`
- `mysql_utc_now`
- `mysql_session_now`
- `wita_now_from_utc`

Tujuan: tahu apakah MySQL session shared hosting sedang UTC, SYSTEM, atau timezone lain.

### 3. Jalankan sample query per table

Mulai dari table paling relevan:

- `audit_events.occurred_at`
- `note_mutation_events.occurred_at`
- `note_revisions.created_at`
- `supplier_invoice_versions.changed_at`
- table surplus refund/disposition jika ada data

Jangan langsung jalankan semua kalau panel hosting lambat. Manusia bikin shared hosting, lalu kaget ketika lambat. Tentu saja.

### 4. Simpan bukti hasil

Untuk setiap query, simpan minimal:

- table name
- row id
- field name
- raw_db
- wita_if_raw_is_utc_like
- event label/type
- waktu aksi nyata menurut ingatan/log owner, kalau ada

Bisa screenshot atau export CSV dari phpMyAdmin.

## Classification Rule

### UTC-like

Row kemungkinan UTC-like jika:

`wita_if_raw_is_utc_like` kira-kira sama dengan waktu aksi nyata owner di WITA.

Contoh:

```text
raw_db: 2026-06-29 02:07
wita_if_raw_is_utc_like: 2026-06-29 10:07
owner ingat aksi sekitar 10:07 WITA
```

Kesimpulan:

```text
UTC-like
```

Untuk kondisi ini, display formatter sekarang sudah benar.

### Local-like

Row kemungkinan local-like jika:

`raw_db` kira-kira sama dengan waktu aksi nyata owner di WITA.

Contoh:

```text
raw_db: 2026-06-29 10:07
wita_if_raw_is_utc_like: 2026-06-29 18:07
owner ingat aksi sekitar 10:07 WITA
```

Kesimpulan:

```text
local-like
```

Untuk kondisi ini, display formatter akan menampilkan +8 jam dan terlihat salah.

### Unknown

Row wajib dianggap unknown jika:

- owner tidak ingat waktu aksi,
- event berasal dari seed/import/system lama,
- `raw_db` dan `wita_if_raw_is_utc_like` sama-sama tidak bisa dicocokkan,
- table berisi campuran UTC-like dan local-like,
- timestamp berhubungan dengan proses otomatis yang tidak punya jam manual.

Kesimpulan:

```text
unknown
```

Unknown tidak boleh ikut repair bulk.

## Decision Matrix

| Kondisi hasil diagnostic | Keputusan |
| --- | --- |
| Semua sample UTC-like | Tidak perlu repair data; formatter display sudah menyelesaikan UI |
| Semua sample local-like pada table/period sempit | Bisa rancang repair sempit nanti, setelah backup dan bukti kuat |
| Campuran UTC-like dan local-like | Jangan bulk repair |
| Banyak unknown | Jangan repair |
| Date-only field terlibat | Stop, query salah scope |

## Production Acceptance For Diagnostic

Diagnostic production dianggap selesai jika tersedia:

- Backup DB sudah dibuat.
- Runtime sanity query disimpan.
- Minimal 3 query kandidat utama sudah diekspor:
  - `audit_events.occurred_at`
  - `note_mutation_events.occurred_at`
  - `supplier_invoice_versions.changed_at` atau `note_revisions.created_at`
- Setiap sample diberi klasifikasi:
  - UTC-like
  - local-like
  - unknown
- Tidak ada query write dijalankan.
- Tidak ada date-only business field dianalisis sebagai repair candidate.

## Stop Condition

Stop dan jangan repair jika ditemukan:

- campuran UTC-like dan local-like dalam table yang sama,
- owner tidak punya waktu aksi pembanding,
- hasil query hanya seed/system event,
- ada keraguan apakah timestamp adalah audit timestamp atau business date.

Tahap ini hanya diagnostic. Repair write harus jadi sesi terpisah.
