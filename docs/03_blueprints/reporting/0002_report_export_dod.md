# Report and Dashboard Export Definition of Done

## Status

Draft.
This file defines the completion criteria for report and dashboard exports.
It is not proof that the export implementation is complete.

## Metadata

- Date: 2026-05-01
- Scope: PDF and Excel export for reports and dashboard

## Purpose

Lock the completion criteria for PDF and Excel export so the feature is not treated as done just because a download button exists.

A download button without parity is a dangerous decoration. It looks nice, it can be clicked, and it can quietly stab the report.

## Global DoD

An export may only be considered complete if all of the following are true:

- screen source is proven
- export dataset uses the same source as the screen
- PDF does not use its own business query
- Excel does not use its own business query
- screen filter = export filter
- screen summary = export summary
- detail sum = summary
- PDF maximum = 1 month
- Excel maximum = 1 year
- access control matches the screen
- Excel rupiah values are numeric
- PDF is readable for printing
- export does not slow down the normal page load
- targeted tests pass
- relevant existing report tests pass
- audit / performance proof is recorded if the scope touches that area

## Source Contract DoD

Proof is required for:

- screen route
- PDF export route
- Excel export route
- controller
- use case
- read model / query
- Blade / screen columns
- filter inputs
- date basis
- status inclusion
- total fields

There may be no GAP in:

- the main source of nominal amounts
- the main source of dates
- the main source of status inclusion
- the main source of totals

The only gaps that may remain temporarily are:

- final PDF styling
- optional chart rendering
- optional export audit event
- queued export

## PDF DoD

PDF export is complete if:

- it accepts only a maximum range of 1 month
- metadata appears
- period appears
- basis date appears
- `generated_at` appears
- `generated_by` appears if an actor is available
- summary appears
- detail appears or an empty state appears
- the total matches the dataset
- the file opens correctly
- the filename is safe and clear
- no internal error leaks out
- unauthorized access is rejected

### PDF Validation

- range greater than 1 month is rejected
- invalid date / month is rejected
- user without access is rejected

### PDF Proof

- test PDF generation for 1 month
- test invalid range
- test unauthorized access
- test content metadata / summary
- manually open / download when possible

## Excel DoD

Excel export is complete if:

- it accepts a maximum range of 1 year / 366 days
- the workbook has a Metadata sheet
- the workbook has a Summary sheet
- the workbook has a Details sheet
- a Reconciliation sheet exists when the report is complex
- rupiah is numeric
- quantity is numeric
- dates are consistent
- summary matches the dataset
- detail row count matches the dataset
- filter metadata matches the request
- the file opens correctly
- the filename is safe and clear
- unauthorized access is rejected

### Excel Validation

- range greater than 1 year is rejected
- invalid date / year / month is rejected
- user without access is rejected

### Excel Proof

- test Excel generation for 1 year
- test invalid range
- test unauthorized access
- test Metadata sheet
- test numeric rupiah
- test summary / detail parity

## Screen PDF Excel Parity DoD

The following must pass:

- screen summary = dataset summary
- PDF summary = dataset summary
- Excel summary = dataset summary
- detail sum = summary
- filter parity
- period boundary parity
- empty state parity
- refund / payment / outstanding parity for the Transaction Report

### Mismatch Rule

- 1 rupiah difference = fail
- 1 quantity difference = fail
- missing row = fail
- wrong period inclusion = fail

## Dashboard Export DoD

Dashboard PDF is complete if:

- it is a 1-month snapshot
- it does not use JS / chart data as the source
- summary reconciles with the dashboard payload
- period metadata appears
- `generated_at` appears
- `generated_by` appears if available
- PDF is readable
- it does not slow down `/admin/dashboard`

Dashboard Excel is complete if:

- maximum range is 1 year
- the workbook is multi-sheet
- a summary sheet exists
- metric sheets exist
- values are numeric
- the dashboard summary reconciles with the source report / dashboard dataset
- chart / DOM is not used as the source data

## Performance DoD

Minimum:

- the normal screen page does not run export generation
- the export action only runs when the export route is called
- query count does not show a brutal N+1
- PDF for 1 month finishes within the agreed threshold
- Excel for 1 year finishes within the agreed threshold or is marked as a queued candidate

### GAP

- the final threshold numbers have not been locked
- the threshold must be decided after the first dataset / report audit

Temporary threshold recommendation for local proof:

- PDF for 1 month: target `< 3 seconds` on a realistic dataset
- Excel for 1 year: target `< 10 seconds` on a realistic dataset
- normal dashboard / report page: must not increase significantly because of the export button

## Security DoD

Required:

- export route uses auth
- export route uses the same role / access middleware as the screen
- forbidden users cannot export
- filenames do not contain excessive sensitive data
- errors do not leak SQL / internal stack traces
- the export does not open a public temporary URL without access control

Optional, to be discussed in an audit session:

- export audit event
- export row-count metadata
- export checksum metadata
- export history

## Auditability DoD

For every export implementation handoff, write:

- report key
- export format
- actor / access rule
- period rule
- source dataset
- tests run
- proof output
- remaining GAP
