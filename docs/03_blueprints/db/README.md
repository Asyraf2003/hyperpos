# db

Blueprint dan workflow untuk hardening database, audit timestamp, dan kesiapan migrasi MySQL ke PostgreSQL.

## File

| File | Jenis | Isi |
|---|---|---|
| `0001_temporal_audit_columns_blueprint.md` | Blueprint | Desain kolom temporal dan audit untuk tabel domain |
| `0002_mysql_postgresql_crud_readiness_blueprint.md` | Blueprint | Kesiapan CRUD database agar kompatibel dengan MySQL dan PostgreSQL |
| `0003_db_hardening_workflow.md` | Workflow | Urutan eksekusi hardening database dan proof yang wajib dikumpulkan |
| `0004_db_audit_matrix.md` | Matrix | Matriks audit tabel, timestamp, immutability, dan risiko migrasi |
| `0005_notes_timestamp_patch_blueprint.md` | Blueprint | Patch timestamp untuk tabel notes |
| `0006_customer_payment_refund_timestamp_patch_blueprint.md` | Blueprint | Patch timestamp untuk payment dan refund pelanggan |
| `0007_allocation_tables_timestamp_immutability_patch_blueprint.md` | Blueprint | Patch timestamp dan immutability untuk tabel allocation |
| `0008_supplier_procurement_timestamp_hardening_patch_blueprint.md` | Blueprint | Patch timestamp untuk supplier dan procurement |
| `0009_inventory_movement_timestamp_readiness_hardening_patch_blueprint.md` | Blueprint | Patch timestamp untuk inventory movement |
| `0010_inventory_projection_timestamp_policy_blueprint.md` | Blueprint | Policy timestamp untuk inventory projection |
| `0011_work_item_timestamp_readiness_hardening_patch_blueprint.md` | Blueprint | Patch timestamp untuk work item |

## Catatan

Folder ini hanya mengatur blueprint dan workflow database.

Perubahan runtime database tetap harus dibuktikan lewat migration diff, targeted test, audit command, dan output verifikasi lokal.
