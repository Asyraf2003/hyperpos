# Audit Blueprints

This folder contains audit runtime and audit-readiness blueprints.

Use this folder for analysis and planning before changing runtime audit behavior.

## Purpose

The audit blueprint folder exists to document how HyperPOS should preserve precise audit records without unnecessarily blocking user-facing activity.

Primary topics:

- canonical audit runtime planning
- transactional audit outbox planning
- audit write-path classification
- canonical `audit_events` / `audit_event_snapshots` transition work
- legacy `audit_logs` compatibility analysis
- PostgreSQL-ready audit schema planning
- API-ready audit boundary planning
- audit verification and retry strategy

## Source Of Truth

Current higher-priority references:

- `docs/02_architecture/adr/0028_mysql_to_postgresql_and_api_migration_readiness.md`
- `docs/03_blueprints/db/go_postgres_migration_readiness/findings/04_transaction_idempotency_audit.md`
- `database/migrations/2026_04_06_230100_create_audit_events_and_snapshots_tables.php`
- `app/Ports/Out/AuditEventWriterPort.php`
- `app/Adapters/Out/Audit/DatabaseAuditEventWriterAdapter.php`
- local owner command output

## Current Documents

| Document | Purpose | Status |
|---|---|---|
| `0001_transactional_outbox_audit_runtime.md` | Analysis and blueprint for moving heavy audit materialization away from the user-facing write path while keeping durable audit capture. Transaction-heavy flows are intentionally skipped first. | Draft |

## Scope Boundary

This folder may discuss audit runtime design, but it does not prove implementation.

A document in this folder does not mean:

- migration has been created;
- runtime binding has changed;
- audit outbox exists;
- worker/processor exists;
- performance improved;
- PostgreSQL compatibility is proven;
- API parity is proven.

Those claims require runtime source proof and local command output.

## Recommended Workflow

1. Read the relevant audit blueprint.
2. Confirm scope and exclusions.
3. Inspect the current source path before implementation.
4. Create or update a mutation/audit matrix when the change touches a new flow.
5. Implement one active slice only.
6. Prove syntax, focused tests, audit materialization, retry behavior, and rollback behavior where relevant.
7. Move completed proof to handoff or lifecycle docs.

## Guardrails

- Do not treat JSON payloads as the only source of audit routing truth.
- Keep query-critical audit facts in explicit columns.
- Do not introduce MySQL enum for audit status.
- Do not refactor transaction/payment/refund allocation flows from this folder without a separate readiness proof.
- Do not duplicate audit/business logic for API controllers.
- Do not claim async audit safety without durable outbox and retry proof.
