/*
0050 - Timestamp Read-only Diagnostic SQL for Shared Hosting / MySQL

PURPOSE:
- Read-only diagnostic untuk legacy timestamp.
- Jalankan setelah full database backup.
- Tidak ada UPDATE/DELETE/INSERT/ALTER/DROP/TRUNCATE.
- Jangan pakai hasil ini untuk bulk repair otomatis.

DISPLAY RULE:
- raw_db = nilai asli di database.
- wita_if_raw_is_utc_like = raw_db + 8 jam.
- Jika owner ingat aksi terjadi sekitar WITA yang sama dengan kolom raw_db,
  row itu kemungkinan local-like dan tidak boleh ikut bulk +8.
- Jika owner ingat aksi terjadi sekitar nilai wita_if_raw_is_utc_like,
  row itu kemungkinan UTC-like.

HARD EXCLUDE:
- Jangan diagnosis sebagai repair candidate untuk date-only business fields:
  refunded_at, transaction_date, shipment_date, due_date, expense_date, payment_date.
*/

/* Runtime sanity check */
SELECT
    'runtime' AS section,
    @@global.time_zone AS mysql_global_time_zone,
    @@session.time_zone AS mysql_session_time_zone,
    UTC_TIMESTAMP() AS mysql_utc_now,
    NOW() AS mysql_session_now,
    DATE_ADD(UTC_TIMESTAMP(), INTERVAL 8 HOUR) AS wita_now_from_utc;

/* 1) audit_events.occurred_at */
SELECT
    'audit_events' AS table_name,
    id AS row_id,
    'occurred_at' AS field_name,
    occurred_at AS raw_db,
    DATE_ADD(occurred_at, INTERVAL 8 HOUR) AS wita_if_raw_is_utc_like,
    event_name AS event_label,
    aggregate_type,
    bounded_context
FROM audit_events
WHERE occurred_at IS NOT NULL
ORDER BY occurred_at DESC
LIMIT 20;

/*
Optional only if production table has audit_events.created_at.
If column does not exist, skip this query.

SELECT
    'audit_events' AS table_name,
    id AS row_id,
    'created_at' AS field_name,
    created_at AS raw_db,
    DATE_ADD(created_at, INTERVAL 8 HOUR) AS wita_if_raw_is_utc_like,
    event_name AS event_label,
    aggregate_type,
    bounded_context
FROM audit_events
WHERE created_at IS NOT NULL
ORDER BY created_at DESC
LIMIT 20;
*/

/* 2) audit_event_snapshots.created_at */
SELECT
    'audit_event_snapshots' AS table_name,
    id AS row_id,
    'created_at' AS field_name,
    created_at AS raw_db,
    DATE_ADD(created_at, INTERVAL 8 HOUR) AS wita_if_raw_is_utc_like,
    snapshot_kind AS event_label,
    audit_event_id,
    NULL AS extra_context
FROM audit_event_snapshots
WHERE created_at IS NOT NULL
ORDER BY created_at DESC
LIMIT 20;

/* 3) note_mutation_events.occurred_at */
SELECT
    'note_mutation_events' AS table_name,
    id AS row_id,
    'occurred_at' AS field_name,
    occurred_at AS raw_db,
    DATE_ADD(occurred_at, INTERVAL 8 HOUR) AS wita_if_raw_is_utc_like,
    mutation_type AS event_label,
    actor_role,
    note_id
FROM note_mutation_events
WHERE occurred_at IS NOT NULL
ORDER BY occurred_at DESC
LIMIT 20;

/* 4) note_revision_surplus_dispositions.occurred_at */
SELECT
    'note_revision_surplus_dispositions' AS table_name,
    id AS row_id,
    'occurred_at' AS field_name,
    occurred_at AS raw_db,
    DATE_ADD(occurred_at, INTERVAL 8 HOUR) AS wita_if_raw_is_utc_like,
    disposition_type AS event_label,
    status,
    note_root_id
FROM note_revision_surplus_dispositions
WHERE occurred_at IS NOT NULL
ORDER BY occurred_at DESC
LIMIT 20;

/* 5) note_revision_surplus_dispositions.created_at */
SELECT
    'note_revision_surplus_dispositions' AS table_name,
    id AS row_id,
    'created_at' AS field_name,
    created_at AS raw_db,
    DATE_ADD(created_at, INTERVAL 8 HOUR) AS wita_if_raw_is_utc_like,
    disposition_type AS event_label,
    status,
    note_root_id
FROM note_revision_surplus_dispositions
WHERE created_at IS NOT NULL
ORDER BY created_at DESC
LIMIT 20;

/* 6) note_revision_surplus_refund_payments.occurred_at */
SELECT
    'note_revision_surplus_refund_payments' AS table_name,
    id AS row_id,
    'occurred_at' AS field_name,
    occurred_at AS raw_db,
    DATE_ADD(occurred_at, INTERVAL 8 HOUR) AS wita_if_raw_is_utc_like,
    status AS event_label,
    audit_event_id,
    note_root_id
FROM note_revision_surplus_refund_payments
WHERE occurred_at IS NOT NULL
ORDER BY occurred_at DESC
LIMIT 20;

/* 7) note_revision_surplus_refund_payments.created_at */
SELECT
    'note_revision_surplus_refund_payments' AS table_name,
    id AS row_id,
    'created_at' AS field_name,
    created_at AS raw_db,
    DATE_ADD(created_at, INTERVAL 8 HOUR) AS wita_if_raw_is_utc_like,
    status AS event_label,
    audit_event_id,
    note_root_id
FROM note_revision_surplus_refund_payments
WHERE created_at IS NOT NULL
ORDER BY created_at DESC
LIMIT 20;

/* 8) note_revisions.created_at */
SELECT
    'note_revisions' AS table_name,
    id AS row_id,
    'created_at' AS field_name,
    created_at AS raw_db,
    DATE_ADD(created_at, INTERVAL 8 HOUR) AS wita_if_raw_is_utc_like,
    CONCAT('revision_number=', revision_number) AS event_label,
    note_root_id,
    created_by_actor_id
FROM note_revisions
WHERE created_at IS NOT NULL
ORDER BY created_at DESC
LIMIT 20;

/* 9) supplier_invoice_versions.changed_at */
SELECT
    'supplier_invoice_versions' AS table_name,
    id AS row_id,
    'changed_at' AS field_name,
    changed_at AS raw_db,
    DATE_ADD(changed_at, INTERVAL 8 HOUR) AS wita_if_raw_is_utc_like,
    event_name AS event_label,
    supplier_invoice_id,
    revision_no
FROM supplier_invoice_versions
WHERE changed_at IS NOT NULL
ORDER BY changed_at DESC
LIMIT 20;
