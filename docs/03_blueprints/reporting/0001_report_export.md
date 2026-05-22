# PDF and Excel Export for Reports and Dashboard Blueprint

## Status

Draft.

## Metadata

- Date: 2026-05-01
- Scope: Reporting, Dashboard, PDF Export, Excel Export
- Project: Hyperpos

## Goal

Build PDF and Excel exports for reports and dashboards without mismatching the numbers shown on screen.

The export must be an output adapter from an already stable report/dashboard dataset, not a new query, not a re-calculation in Blade, not a re-calculation in JavaScript, and not a DOM scrape.

Primary targets:

- screen = PDF = Excel for the main numbers
- screen filter = export filter
- summary = detail reconciliation
- dashboard export is not a source of truth
- reports still read the final domain
- export must not slow down the dashboard/report page load
- PDF must be safe for monthly printing
- Excel must be safe for analysis up to one year

## Background

Reporting V2 already locked in that exports may only be built after the screen/report is stable.

Export must not start before the data contract is clear because the main risk is not layout; it is number mismatch.

Risks to prevent:

- screen shows the correct number, PDF shows a different one
- Excel uses a separate query and the total is different
- dashboard chart uses numbers that do not reconcile with the report
- PDF prints too large a range and becomes unreadable
- Excel stores rupiah as a string and becomes hard to analyze
- export makes the normal dashboard/report slower

## Source of Truth Rule

Report and dashboard exports must use the same dataset / use case as the screen.

Official flow:

Request filter
→ Controller
→ Application use case
→ Reporting reader / query
→ Report dataset / dashboard payload
→ Screen renderer
→ PDF renderer
→ Excel renderer

Forbidden:

- export query separate from the screen query
- re-calculate business numbers in Blade
- re-calculate business numbers in JavaScript
- export from the DOM
- export from rendered chart data
- export with a different filter from the screen
- formatting that changes numeric values

## Export Types

### Report PDF

Purpose:

- monthly printing
- human archive
- owner / admin discussion
- compact period evidence

Policy:

- period basis: 1 month
- maximum range: 1 calendar month
- layout must be readable when printed
- summary appears first
- detail table may be paginated
- numbers must match the screen

### Report Excel

Purpose:

- analysis
- reconciliation
- manual filtering
- accounting pivots
- operational audit

Policy:

- maximum range: 1 year / 366 days
- rupiah values must be numeric integers
- rupiah formatting is only a visual Excel number format
- the main data table may not use merged cells
- a metadata sheet is required
- a summary sheet is required
- a detail sheet is required
- a reconciliation sheet is allowed when the report is complex

### Dashboard PDF

Purpose:

- monthly dashboard snapshot for the owner
- a short summary of the current business position for a selected period
- a single compact printable package

Policy:

- period basis: 1 month
- not a replacement for the detailed report
- contains summary, main indicators, and dashboard context
- charts may appear as visuals, but chart numbers are not the source of truth
- dashboard PDF must include report source / period metadata

### Dashboard Excel

Purpose:

- analysis workbook from the dashboard
- dashboard data breakdown in separate sheets
- cross-metric validation

Policy:

- maximum range: 1 year
- multi-sheet workbook
- each sheet represents a metric / section
- dashboard summary must reconcile to the report source
- chart / JS may not be used as the data source

## Period Policy

### PDF

PDF uses a monthly print basis.

Rules:

- main input: `month=YYYY-MM`
- `date_from` = first day of the month
- `date_to` = last day of the month or `today` for the current month if the screen policy already works that way
- a custom date range is only allowed if it does not exceed 1 month and does not violate the screen contract
- if the range is more than 1 month, the PDF request must be rejected with a clear validation message

Reason:

PDF is for human reading. A yearly detail PDF usually turns into an accounting novel nobody wants to read, including the person who printed it.

### Excel

Excel uses an analysis basis.

Rules:

- input may be `month`, `year`, or a custom date range
- maximum range: 366 days
- if the range is more than 366 days, the request is rejected
- exports over 1 year must be split by year or later moved to queued export if that proves necessary

Reason:

Excel is for analysis and reconciliation, so a larger range is still reasonable.

## Data Contract

Every export must include:

- `report_key`
- `report_title`
- `period_label`
- `date_from`
- `date_to`
- `basis_date_label`
- `generated_at`
- `generated_by`
- `filter_payload`
- `summary_rows`
- `detail_rows`
- `totals`
- `reconciliation_rows` optional
- `source_dataset_name`
- `source_screen_route`
- `source_export_route`

## Metadata Contract

Every PDF / Excel export must display:

- report / dashboard name
- period
- basis date
- generation time
- actor / generator
- active filter
- source dataset / use case
- a note that the export uses the same source as the screen
- page / page number for PDF
- app / report version if available

GAP:

- the official company / header source has not yet been proven
- the official application timezone source has not yet been proven
- the app version / export version source has not yet been proven

Until those gaps are closed, use the minimal metadata that is already proven by request / session / config.

## Formatting Contract

### Rupiah

Rules:

- the internal value remains integer rupiah
- PDF may display `Rp 15.000`
- Excel cell value must be numeric `15000`
- Excel number format may show `Rp #,##0`
- do not store `Rp 15.000` as a string in the main data cell

### Date

Rules:

- Indonesia display: `dd-mm-yyyy`
- metadata may also include ISO dates when needed
- Excel date cells must be date-compatible if the library supports it
- do not mix `yyyy-mm-dd` into the UI export unless it is technical metadata

### Quantity

Rules:

- quantity is numeric
- decimal policy follows the source report
- do not format quantity as a string in the Excel detail sheet

## PDF Display Contract

Default:

- size: A4
- dashboard: portrait
- report summary: portrait
- wide report detail table: landscape
- consistent margins
- header appears on at least the first page
- footer contains page number, `generated_at`, and report key
- table header repeats on new pages when the library supports it

PDF section order:

1. Report header
2. Period / filter metadata
3. Summary cards / table
4. Reconciliation note, if any
5. Detail table
6. Footer

PDF empty state:

- if there is no data, the PDF must still be generated successfully
- show an empty-state message with the same meaning as the screen
- total must be zero
- metadata must still appear
