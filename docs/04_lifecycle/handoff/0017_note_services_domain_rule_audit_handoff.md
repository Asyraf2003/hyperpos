# Note Services Domain Rule Audit Handoff

## FACT

- Scope audit hanya Laravel saat ini. Go Echo API dan PostgreSQL tidak disentuh.
- `app/Application/Note/Services` berisi banyak service karena area nota mengorkestrasi reader/writer, audit, transaksi, payment allocation, refund, inventory, dan view data.
- Mayoritas service di folder ini bukan kandidat pindah langsung ke `Core` karena masih bergantung pada port, clock, audit, transaction manager, reader/writer, atau payload UI.
- Golden-master lintas reporting sudah ditambahkan di `tests/Feature/Reporting/TransactionReportingReconciliationFeatureTest.php`.
- Proof terakhir: `make verify` hijau dengan `2 skipped, 1153 passed (6540 assertions)`.

## GAP

- Ada beberapa rule murni yang masih berada di Application.
- Ada satu boundary smell konkret: `CurrentRevisionPackageProductNameResolver` melakukan query `DB::table('products')` langsung dari Application service.
- Belum ada owner decision apakah service kecil yang pure harus dipindah ke `Core` sekarang atau dibiarkan sampai domain note/payment lebih stabil.

## AUDIT MATRIX

| Area | File | Data | Decision |
| --- | --- | --- | --- |
| Revision settlement math | `app/Application/Note/Services/BuildNoteRevisionSettlement.php` | Pure calculation, no port/framework, already covered by `BuildNoteRevisionSettlementTest`. Returns Application DTO. | Candidate extract to Core policy/value factory after moving/decoupling DTO. High value. |
| Operational note status | `app/Application/Note/Services/NoteOperationalStatusEvaluator.php` | Pure status rule, no dependency, covered by unit tests. | Safe candidate to move to Core policy. Small first extraction. |
| Work item operational status | `app/Application/Note/Services/WorkItemOperationalStatusResolver.php` | Pure line status rule with domain exception, used by UI mappers and refund guards. | Candidate to move to Core after adding direct unit coverage if needed. |
| Payment status label/value | `app/Application/Note/Services/NotePaymentStatusResolver.php` | Pure status rule, no dependency. | Candidate, but low urgency because mostly presentation/payment label. |
| Store stock subtotal | `app/Application/Note/Services/StoreStockLinesSubtotal.php` | Pure `StoreStockLine` + `Money` calculation. | Move to Core only if used beyond correction flow. Low risk, low impact. |
| External purchase subtotal | `app/Application/Note/Services/ExternalPurchaseLinesSubtotal.php` | Pure `ExternalPurchaseLine` + `Money` calculation. | Same as store stock subtotal. Low risk, low impact. |
| Selected refund buckets | `app/Application/Note/Services/SelectedRowsRefundBucketsBuilder.php` | Pure calculation over Payment domain allocations, covered by unit tests. Output DTO is Application Payment DTO. | Do not move directly; extract Core allocation policy only if DTO boundary is cleaned first. |
| Package external purchase pricing | `CreateTransactionWorkspaceServiceExternalPurchasePackagePricingComposer.php` | Pure array payload math plus DomainException, but tied to workspace payload shape. | Keep Application for now; optional extract pure package pricing rule later. |
| Package store stock pricing | `CreateTransactionWorkspaceServiceStoreStockPackagePricingComposer.php` | Business pricing plus `ProductReaderPort`. | Do not move as-is; split catalog lookup from pure pricing if refactored. |
| Package product line composer | `CreateTransactionWorkspaceServiceStoreStockPackageProductLinesComposer.php` | Needs `ProductReaderPort` and validates product existence/current price. | Keep Application; pure line total math can be extracted later if needed. |
| Current revision row settlement | `CurrentRevision/*SettlementSummaryBuilder.php` | Mostly deterministic settlement math, but used for read model projection/presentation labels. | Keep Application until read model semantics are stable. |
| Current revision product name | `CurrentRevision/CurrentRevisionPackageProductNameResolver.php` | Static helper mixes pure snapshot fallback with direct `DB` query. | Fix boundary by moving current-name lookup behind a Product reader/adapter; do not move to Core. |
| Inline payment amount | `CreateTransactionWorkspaceInlinePaymentAmountResolver.php` | Contains real payment rule but depends on allocation/refund ports. | Keep Application; optionally extract pure `outstanding(total, paid, refunded)` policy later. |

## DECISION

- Do not move the whole `Services` folder. That would break the hexagonal boundary instead of improving it.
- First safe extraction candidate is the small pure status rule, not settlement or package pricing.
- First boundary hardening candidate is `CurrentRevisionPackageProductNameResolver` because Application currently performs direct DB access.

## RECOMMENDED NEXT STEPS

1. Extract `NoteOperationalStatusEvaluator` to a Core policy and leave the Application class as a compatibility wrapper or update imports in one small slice.
2. Run targeted unit tests for note status and affected detail/refund UI mappers.
3. Run `make verify`.
4. In a separate slice, replace `CurrentRevisionPackageProductNameResolver::currentNames()` DB access with a port-backed Application collaborator.

## PROOF

- Inspected `app/Application/Note/Services`, `app/Application/Note/UseCases`, `app/Core/Note`, `tests/Unit/Application/Note`, and `tests/Feature/Note`.
- Existing protection found for candidate rules:
  - `tests/Unit/Application/Note/Services/BuildNoteRevisionSettlementTest.php`
  - `tests/Unit/Application/Note/Services/NoteOperationalStatusEvaluatorTest.php`
  - `tests/Unit/Application/Note/Services/SelectedRowsRefundBucketsBuilderTest.php`
  - `tests/Unit/Application/Note/Services/CreateTransactionWorkspaceInlinePaymentAmountResolverTest.php`
  - `tests/Unit/Application/Note/Services/WorkItemFactoryTest.php`
  - `tests/Feature/Note/EditTransactionWorkspacePackageAutoSplitCharacterizationTest.php`
