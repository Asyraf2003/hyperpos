# 0049 - Manual QA Supplier Invoice Revision Reason and Timestamp Display Gap

## Status

Open.

## Source

Manual QA after closing targeted owner-facing Indonesian language cleanup and reason visibility slices from 0047/0048.

Owner performed manual checks after full verification passed.

## Summary

Automated verification passed for the targeted cleanup, but manual QA found new follow-up anomalies:

1. Product edit reason flow appears correct.
2. Supplier invoice edit does not behave like product edit:
   - edit succeeds/fails unexpectedly from manual perspective,
   - no visible revision/version is created,
   - latest reason does not appear,
   - owner expects supplier invoice edit to show version/reason like product edit.
3. Closed/paid note correction history manual checks 11-15 were reported failed or not working as expected and need reproduction.
4. Refund flow from item 16 onward can succeed, but displayed time is wrong:
   - owner local time during test: around 10:11 Asia/Makassar.
   - displayed time: around 02:07.
   - this suggests a UTC/local timezone presentation gap, likely 8 hours behind local time.

## Manual QA Result Reported By Owner

### Product Edit

Result:

- Test 1: OK, edit product reason exists and contains value.
- Test 2: failed validation as expected when reason is missing.
- Test 3: failed validation as expected when reason is only spaces.
- Tests 4-6: failed validation as expected for invalid/too short/too long/invalid retry cases.
- Test 7: OK, repeated product edit reason flow works.

Interpretation:

Product edit reason behavior appears correct and should not be the main target of this issue.

### Supplier Invoice Edit

Result:

- Test 8: failed.
- Editing supplier invoice does not show version/revision or reason.
- Owner expected supplier invoice revision/reason behavior to be similar to product edit.
- Test 9: failed because the first supplier invoice edit/revision flow already failed.
- Test 10: failed because supplier invoice without version does not show revision behavior like product.

Interpretation:

The prior source-level patch only made detail page capable of reading/displaying latest supplier invoice version reason if such version exists.

Manual QA suggests the supplier invoice edit/update flow may not actually be creating the supplier invoice version/revision record or may not be bumping `last_revision_no`.

Need inspect:

- supplier invoice edit form,
- update request,
- update controller,
- update handler/service,
- `supplier_invoice_versions` writer,
- `supplier_invoices.last_revision_no`,
- detail query/view behavior.

Important: do not assume display layer is the only issue. The likely gap may be update-side revision creation.

### Reason Display / XSS / Note History Checks

Result:

- Tests 11-15 reported failed.

From the manual checklist, these corresponded to:

- supplier invoice reason with HTML/XSS text,
- closed/paid note correction reason display,
- correction reason with HTML/XSS text,
- note detail without correction history,
- note detail with both revision timeline and correction history.

Interpretation:

Needs reproduction. Do not assume all failures share one root cause.

Potential branches:

1. Supplier invoice reason HTML cannot be tested because supplier invoice reason flow itself is broken.
2. Closed/paid note correction history may be unavailable in the owner’s manual data because the tested note does not have `note_mutation_events`.
3. Shared note detail may display correction history only when `note['correction_history'] !== []`, so data setup matters.
4. If correction history exists but does not show, inspect shared note detail payload and view include again.

### Refund Flow and Timestamp

Result:

- Tests 16 onward can succeed.
- Owner noticed displayed time mismatch:
  - actual local time: around 10:11 Asia/Makassar,
  - displayed time: around 02:07.
- Difference is approximately 8 hours, matching UTC vs Asia/Makassar offset.

Interpretation:

Likely timezone display/config issue, not a financial amount issue.

Need inspect:

- `config/app.php` timezone,
- `.env` `APP_TIMEZONE`,
- DB timestamp storage expectations,
- use of `now()`,
- use of `date()`,
- `ViewDateFormatter`,
- note/refund/audit timestamp display,
- any raw `created_at`, `updated_at`, `occurred_at`, `refunded_at` display.

This issue must distinguish:

- date-only business dates such as `refunded_at`,
- timestamp display such as audit/mutation/history `created_at` or `occurred_at`.

## Risk Assessment

### Financial Report Risk

Low from the previous 0047/0048 patches because they mainly affected labels, reason metadata, display, and audit payload.

### New Risk From Manual QA

Medium for user trust and audit traceability:

- Supplier invoice edit may not have reliable visible revision reason.
- Timestamp display may mislead owner/cashier about when actions happened.

### Financial Data Integrity

No direct evidence yet that amounts, allocations, refunds, or reports are wrong.

Need explicitly verify after fixes that:

- supplier invoice totals do not change incorrectly,
- payments/receipts/tax are unaffected,
- refund amount/report output stays unchanged,
- only revision metadata/time display is changed.

## Non-Goals

Do not reopen 0047 language cleanup broadly.

Do not rename:

- DB enum/internal values,
- route names,
- request field names,
- DTO keys,
- audit event names,
- public API contracts.

Do not touch `docs/99_archive`.

Do not redesign the full supplier invoice lifecycle unless reproduction proves it is necessary.

Do not convert all timestamps globally without proving impact.

## Required Investigation

### Supplier Invoice Revision Flow

Find the update path:

- route for supplier invoice update,
- controller,
- request validation,
- handler/service,
- persistence adapter,
- revision writer.

Answer:

1. Does update require or accept reason?
2. Does update create `supplier_invoice_versions`?
3. Does update increment/bump `supplier_invoices.last_revision_no`?
4. Does detail page query the same version number that update creates?
5. Does edit form preserve/display reason errors correctly?
6. Is reason escaped in detail display?

### Note Correction History Manual Failure

Reproduce using deterministic seeded data or existing tests:

1. Note with no correction history must not show empty correction card.
2. Note with correction history must show:
   - `Riwayat Mutasi Nota`,
   - mutation label,
   - `Alasan:`,
   - escaped reason text.
3. HTML reason must render as plain text, not HTML.

If automated tests already cover this but manual data does not show it, record as documentation/data mismatch, not code bug.

### Timestamp Mismatch

Reproduce exact mismatch:

1. Perform a refund or correction at local time Asia/Makassar.
2. Capture DB stored values:
   - `created_at`,
   - `updated_at`,
   - `occurred_at`,
   - `refunded_at`,
   - audit log timestamp if available.
3. Capture UI displayed value.
4. Determine whether UI displays UTC, server time, or local business timezone.

Expected owner-facing behavior:

- timestamps should be understandable in owner’s local operational timezone,
- date-only fields should remain date-only and must not be shifted across days.

## Recommended Tests

Add focused tests only after reproducing the real path.

Candidate test areas:

- `tests/Feature/Procurement/UpdateSupplierInvoiceFeatureTest.php`
- `tests/Feature/Procurement/ProcurementInvoiceDetailPageFeatureTest.php`
- `tests/Feature/Note/CashierNoteCorrectionHistoryReasonViewFeatureTest.php`
- timestamp formatter/unit test around `ViewDateFormatter` if needed
- focused HTTP test for refund/correction timestamp display if the bug is presentation-level

## Acceptance Criteria

### Supplier Invoice

- Editing supplier invoice with reason creates a revision/version record.
- `supplier_invoices.last_revision_no` points to the latest version.
- Supplier invoice detail displays the latest revision reason.
- Re-editing supplier invoice displays the newest reason, not an older one.
- Supplier invoice with no version/reason does not crash and does not show empty misleading reason block.
- Reason HTML is escaped.

### Note Correction History

- Note with correction history displays reason.
- Note without correction history does not show empty correction history block.
- Reason HTML is escaped.
- Existing automated tests still pass.

### Timestamp

- Owner-facing timestamp display matches Asia/Makassar operational expectation.
- No unwanted date shifting for date-only fields.
- Refund/report amount behavior remains unchanged.

## Proof Required Before Close

Run targeted tests for any changed areas.

Then run:

```bash
make verify
```

Record:

- focused test output,
- full verify output,
- `git status --short`,
- `git diff --stat`.

## Closure Rule

Do not close this issue only because automated tests pass.

Close only after:

- supplier invoice manual QA succeeds,
- note correction manual QA is either fixed or proven data/setup-specific,
- timestamp mismatch is fixed or explicitly documented with acceptable business decision,
- `make verify` passes.
