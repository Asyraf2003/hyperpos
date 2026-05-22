# Workflow Reporting V2 - Execution Order, Guard, and Test Discipline

Date: 2026-04-14

## Purpose

This document describes the step-by-step workflow for implementing reporting v2.

It is meant to keep the team from:

- jumping to the dashboard first
- building export with a separate query
- mixing report, chart, and domain logic
- tolerating small mismatches
- stacking large files that are hard to audit

This document is the execution workflow for the blueprint:

- `docs/99_archive/handoff/v2/report/00-reporting-blueprint-handoff.md`

---

## Global Workflow Rules

### 1. Work on one active report in one work page

A single work page may only have one active focus.

Safe examples:

- page 1 only Transaction Cash Flow
- page 2 only Operational Expense
- page 3 only Employee Debt

### 2. Do not start from the dashboard

The dashboard may only be wired after the source report is stable.

### 3. Do not start from export

PDF and Excel may only be created after the screen dataset is stable.

### 4. Exactness matters more than cosmetics

If you must choose between:

- fast-looking UI
- correct numbers

choose correct numbers.

### 5. Keep files small

The target code file size is around 100 lines.

If a file grows beyond about 100 lines, split it by responsibility.

---

## Official Execution Order

1. Transaction Cash Flow
2. Operational Expense
3. Employee Debt
4. Operational Cash Profit
5. Supplier Debt
6. Stock and Inventory Value
7. Transaction Report
8. Dashboard wiring
9. PDF and Excel parity

---

## Work Template Per Report

Every report must pass the same stages.

## Stage A - Lock the contract

Lock first:

- report goal
- grain
- date basis
- source domain
- inclusion rule
- summary cards
- main total
- dashboard consumer
- export dependency

### Stage A output

- report contract is written
- no ambiguous term remains unlocked

---

## Stage B - Source mapping proof

Prove that the source domain actually exists and matches the repo.

Must map:

- table
- date field
- amount field
- status inclusion
- important relation fields

### Stage B output

- final source list
- guard list
- gaps that may not be assumed

---

## Stage C - Query and builder

Build the query and builder according to the contract.

Split files if needed:

- raw query
- DTO
- builder
- reconciliation service
- use case handler

### Stage C guard

- do not put all logic in one large query class
- do not mix query and output formatting
- do not mix export mapping with the core query

---

## Stage D - Reconciliation

Before controller and dashboard, the report must have reconciliation.

Minimum reconciliation types:

- detail to summary
- daily to weekly
- weekly to monthly
- report source to parent report when there is a dependency

---

## Stage E - Screen contract

Only after query and reconciliation are stable may the screen contract be opened.

Must be locked:

- filters
- summary cards
- table columns
- default sort
- empty state
- no stale-zero behavior

---

## Stage F - Export contract

After the screen is stable, open export.

Must be locked:

- PDF source = screen source
- Excel source = screen source
- filter parity
- total parity
- formatting does not change values

---

## Stage G - Dashboard dependency

After the report screen and export are stable, connect the dashboard.

Must be locked:

- which widget consumes this report
- which metric is critical
- whether the widget needs a fallback to live source
- whether the chart can be hybrid

---

## Stage H - Feature test monster

After all the contracts above are stable, run the feature test monster.

---

## Required Test Paths Per Report

## 1. Formula exactness

Use a small dataset that can be checked by hand.

### Minimum assertions

- every component is correct
- the final total is correct
- rupiah is integer
- no uncontrolled rounding exists

## 2. Read-after-write

After the domain mutation commits:

- call the report
- the new number must appear immediately

## 3. Period parity
