# ADR-0009: Reporting as Read Model

## Status

Accepted.

## Decision

**reporting diposisikan sebagai read model atas data domain final**.

Reporting reads finalized domain data and projection outputs. Reporting is not allowed to become the write authority for domain facts.

In other words, reporting bukan sumber kebenaran utama domain.

## Domain Final Sources

Reporting must derive numbers from final domain sources, including:

- notes and note revisions for customer transaction revenue;
- customer payments and refunds for cash movement;
- supplier invoices, receipts, and payments for procurement and payables;
- inventory movements for stock and costing reports;
- payroll and employee debt records for employee finance;
- expense entries for laporan biaya operasional.

## Consequences

- Reporting bugs are fixed by correcting read-model mapping or source-domain facts, not by editing report-only numbers.
- Report rows may cache or project data, but they must remain traceable to domain records.
- A 1 rupiah difference is treated as a defect.
