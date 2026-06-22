# ADR-0036 - Supplier Invoice Tax Rounding Residue Confirmation

Status: Draft

Date: 2026-06-22

## Context

Supplier invoice tax is treated as landed cost/modal barang.

Current rule rejects tax allocation when the taxed line total cannot be divided evenly by `qty_pcs`.

Example:

```text
line total after tax = 22000
qty = 3
unit cost = 7333.333...
```

This is financially strict, but it can block real invoice edits because supplier tax and quantity do not always produce an integer unit cost.

## Decision

Allow supplier invoice tax allocation even when the taxed line total would create a fractional unit cost, but only with explicit operator confirmation.

When this happens, the UI must show a concise alert before submit completion:

```text
Total setelah pajak tidak habis dibagi qty, sehingga modal per pcs akan dibulatkan dan selisih pembulatan akan dicatat. Lanjutkan?
```

The operator must have two choices:

1. Continue.
2. Return to edit/correct the invoice.

If continued, the system may use integer unit cost rounding and record the remaining difference as explicit rounding residue / rounding adjustment.

The rounding residue must not be silently hidden inside normal profit, inventory, or payment math.

## Scope

This ADR only records the policy.

Implementation is intentionally separate and must define:

- where rounding residue is persisted;
- how it appears in audit/log/reporting;
- whether rounding uses floor, nearest, or another deterministic rule;
- how UI confirmation is submitted safely;
- how tests protect legacy invoice edits, received invoice rules, inventory value, COGS, and reporting.

## Consequences

Positive:

- Editing real supplier invoices becomes more flexible.
- Operator can continue when the only issue is small unit-cost rounding.
- The system remains transparent because the user is warned before continuing.

Negative:

- Inventory value and COGS become more complex than strict qty * unit_cost.
- Reporting must account for rounding residue explicitly.
- Implementation must avoid silently shifting cost between inventory and profit.

## Non-goals

This ADR does not approve silent rounding.

This ADR does not remove landed-cost tax treatment.

This ADR does not implement inventory revaluation for received invoices.

This ADR does not decide the final persistence schema.
