# Report and Dashboard Export V1 Workflow

## Status

Draft.

## Metadata

- Date: 2026-05-01
- Scope: PDF and Excel export workflow

## Goal

Provide an implementation order for PDF and Excel export that does not damage reporting, dashboard behavior, performance, or auditability.

## Global Rules

- A single session may only have one active report / export target.
- Do not implement PDF and Excel for every report at once.
- Do not start with dashboard export.
- Do not start with the PDF layout.
- Start with the dataset contract.
- Build Excel before PDF so the numbers can be proven first.
- Build PDF only after dataset and Excel parity are clear.
- Dashboard export is last.
- Do not claim completion without tests / proof.
- Do not increase progress just because the blueprint exists.

## Phase 0 - Documentation Lock

Goal:

- create the export blueprint
- create the export workflow
- create the export DoD
- separate audit scope from execution scope

Output:

- blueprint path is clear
- workflow path is clear
- DoD path is clear
- no export code is created

Exit condition:

- the documents are approved
- the active implementation target is not yet opened

## Phase 1 - Audit Existing Report Screen

Goal:

Prove the source of the screen before export is created.

Active first target:

- Transaction Report

Audit checklist:

- screen route
- controller
- use case
- query / read model
- Blade table columns
- filter inputs
- summary cards
- total fields
- date basis
- status inclusion
- dashboard dependency
- existing tests

Output:

- report source map
- GAP list
- a decision on whether the dataset is reusable or needs a small dataset adapter

Forbidden:

- creating an export route
- adding PDF / Excel dependencies
- changing the report formula

Exit condition:

- the source map is proven from the repo
- the screen contract is written down
- the minimum GAP is closed or given an explicit blocker

## Phase 2 - Export Dataset Contract

Goal:

Create the dataset contract that will be used by the screen, PDF, and Excel.

Output contract:

- metadata
- filters
- summary rows
- detail rows
- totals
- reconciliation rows optional
- filename base

Rules:

- the dataset may not change formulas
- the dataset may wrap the existing report result
- the dataset must be enough for both PDF and Excel
- the dataset must be testable without rendering PDF / Excel

Exit condition:

- unit / feature tests for the dataset contract pass
- screen behavior does not change

## Phase 3 - Excel Export First

Goal:

Create Excel from the same dataset.

Why Excel first:

- numbers are easier to verify
- numeric types can be tested
- detail rows can be compared to the dataset
- the layout is simpler than PDF

Implementation checklist:

- export route for Excel
- controller is transport-only
- Excel writer adapter
- metadata sheet
- summary sheet
- detail sheet
- filename policy
- maximum range validation of 1 year
- role / middleware must match the screen

Test checklist:

- authorized user can export
- unauthorized user cannot export
- invalid range is rejected
- summary value equals dataset
- detail row count equals dataset
- rupiah is numeric, not a string
- filter parity

Exit condition:

- targeted tests pass
- no formula mismatch
- the normal screen tests still pass

## Phase 4 - PDF Export Second

Goal:

Create PDF from the same dataset.

Implementation checklist:

- export route for PDF
- controller is transport-only
- PDF writer adapter
- monthly range validation
- PDF view / template
- metadata header
- summary section
- detail table
- footer / page number if supported
- filename policy

Test checklist:

- authorized user can export
- unauthorized user cannot export
- invalid range is rejected
- PDF is generated for 1 month
- PDF content contains the key metadata
- PDF content contains the summary total
- PDF uses the same dataset as the screen / export contract

Exit condition:

- targeted PDF tests pass
- no screen regression
- no route / middleware regression

## Phase 5 - Parity Tests

Goal:

Prove screen / PDF / Excel parity.

Minimum tests:

- screen dataset total = export dataset total
- Excel total = export dataset total
- PDF visible total = export dataset total
- detail sum = summary
- filter parity
- boundary date
- empty state
- refund / payment / outstanding exactness for the Transaction Report

Exit condition:

- parity tests pass
- 1 rupiah mismatch fails

## Phase 6 - Performance Sanity

Goal:

Prove export does not damage page load.

Checks:

- normal report page load does not compute export
- export route query count is bounded
- PDF for 1 month stays within the acceptable local threshold
- Excel for 1 year stays within the acceptable local threshold or is marked as a queued-export candidate
