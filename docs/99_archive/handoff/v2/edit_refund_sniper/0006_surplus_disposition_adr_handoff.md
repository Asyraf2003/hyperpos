# Handoff 0006 - Surplus Disposition ADR

## Metadata

- Date: 2026-05-13
- Sequence: 0006
- Scope: surplus disposition ADR
- Previous handoff: docs/99_archive/handoff/v2/edit_refund_sniper/0005_reporting_downward_surplus_characterization_handoff.md
- Latest proven commit or push proof: commit 1947 was pushed as 871c072e for handoff 0005. ADR 0026 is validated locally by anchor check in this handoff command.

## Status

docs-only update

## Session Goal

Close the domain decision gap for note revision surplus disposition.

The session clarified that refund is not done globally.

The session also clarified that edit/revision and refund are both valid functions with different business meanings.

## Facts

- Phase 1A and Phase 1B revision settlement foundation were previously completed.
- Phase 1C-B downward surplus revision commit was previously completed.
- note_revision_settlements exists.
- NoteRevisionSettlement DTO exists.
- BuildNoteRevisionSettlement exists.
- BuildCreateNoteRevisionSettlement exists.
- writer and reader ports plus DB adapter exist.
- successful revision commit persists note_revision_settlements.
- Downward paid revision can commit by capping replay allocation to revised payable total.
- Customer payment remains preserved.
- note_revision_settlements records surplus as overpaid_pending.
- AllocatePaymentAcrossComponents remains strict and was not changed.
- Reporting downward surplus characterization proved transaction summary reporting normal path reads capped payment_allocations amount, not gross customer_payments amount.
- Targeted reporting characterization proof passed with 1 test and 13 assertions.
- Broader owner-provided test proof passed with 973 tests and 5169 assertions.
- Handoff 0005 was pushed in commit 1947 at 871c072e.
- Owner agreed with the combined surplus disposition model.
- Owner clarified edit/revision and refund are both valid business functions.
- Owner clarified edit/revision can represent note correction or replacement-note style correction.
- Owner clarified refund must remain a real refund record when the business event is refund.
- ADR 0026 was created as docs/02_architecture/adr/0026_note_revision_surplus_disposition.md.
- ADR 0026 status is Accepted by owner.

## Gaps

Blocks implementation:

- Surplus disposition production table contract is not implemented yet.
- Customer balance ledger table is not implemented yet.
- Refund due use case is not implemented yet.
- Customer credit use case is not implemented yet.
- Credit used use case is not implemented yet.
- Refund paid from surplus use case is not implemented yet.
- Actor/capability enforcement for surplus disposition is not implemented yet.
- UI display for backend-generated surplus/disposition state is not implemented yet.
- Report visibility for customer balance/refund due/customer credit is not implemented yet.

Does not block next docs/source-audit step:

- ADR 0026 now locks the domain direction.
- Next implementation can start from source audit and table contract, not from UI.

## Assumptions

No implementation assumption accepted.

## Decisions

- Decision source: owner statement.
  - Combined surplus disposition model is accepted.
- Decision source: ADR 0026.
  - overpaid_pending is the base holding state.
- Decision source: ADR 0026.
  - overpaid_pending is not revenue, not automatic refund paid, and not automatic customer credit.
- Decision source: ADR 0026.
  - disposition must be explicit and can become refund_due, customer_credit, split disposition, or high-trust manual adjustment.
- Decision source: ADR 0026.
  - refund_paid and credit_used are execution states, not automatic states created by revision itself.
- Decision source: ADR 0026.
  - unresolved pending surplus can participate in later same-note revision recalculation.
- Decision source: ADR 0026.
  - already disposed money cannot be silently reclaimed by later revision.
- Decision source: ADR 0026.
  - revision must not fake a refund.
- Decision source: ADR 0026.
  - refund must not fake a revision.

## Active Slice

Selected active slice:

Surplus Disposition ADR.

Scope in:

- close decision gap for overpaid_pending disposition
- clarify repeated edit behavior after surplus
- clarify disposed money behavior
- clarify edit versus refund boundary
- define implementation stop conditions
- create handoff 0006
- update README latest handoff pointer

Scope out:

- app production code
- database migration
- UI
- controller
- refund engine
- reporting query
- API
- customer balance implementation
- ledger/history rewrite

Files to touch:

- docs/02_architecture/adr/0026_note_revision_surplus_disposition.md
- docs/99_archive/handoff/v2/edit_refund_sniper/README.md
- docs/99_archive/handoff/v2/edit_refund_sniper/0006_surplus_disposition_adr_handoff.md

Files not touched:

- app/*
- database/*
- resources/*
- public/*

DB impact:

- None in this docs-only slice.
- Future DB impact is expected for surplus disposition/customer balance foundation, but only after table contract is locked.

UI impact:

- None in this slice.
- UI remains later because UI is not financial truth.

Report impact:

- None in production code.
- Future report impact expected after disposition states exist.

API impact:

- None.

Audit impact:

- ADR 0026 requires actor, role, reason, event type, source id, before state, after state, and amount for disposition.

## Source Audit Summary

Source and docs actually inspected in the session chain:

- path: docs/03_blueprints/finance/0006_note_revision_refund_ledger.md
  - relevant behavior: blueprint requires explicit surplus state and distinguishes revision, refund, customer balance, inventory, reporting, UI, and API
  - risk: treating blueprint direction as permission to implement all tables blindly
  - scope: decision source only

- path: docs/02_architecture/adr/0025_note_revision_carry_forward_settlement.md
  - relevant behavior: carry-forward settlement is decided but final overpaid/change/refund-due storage model was explicitly undecided
  - risk: implementing final surplus model without owner decision
  - scope: decision predecessor

- path: docs/03_blueprints/finance/0003_finance_residual.md
  - relevant behavior: Gate 1 Overpaid/Kembalian Storage Model was still open
  - risk: production patch before final surplus storage/workflow decision
  - scope: decision gap proof

- path: docs/03_blueprints/finance/0007_note_revision_refund_ledger_dod.md
  - relevant behavior: refund DoD remains broad and not globally done
  - risk: mistaking reporting characterization for full refund completion
  - scope: progress boundary

- path: docs/03_blueprints/finance/0008_note_revision_refund_ledger_workflow.md
  - relevant behavior: correct chain is decision, DB/domain, core/application, adapter, projection, reporting, UI, API, audit/docs
  - risk: starting UI/controller/query hack too early
  - scope: next-step ordering

- path: docs/02_architecture/adr/0026_note_revision_surplus_disposition.md
  - relevant behavior: locks overpaid_pending holding state and explicit disposition model
  - risk: future implementation must not violate the ADR
  - scope: active decision output

## Files Changed

- docs/02_architecture/adr/0026_note_revision_surplus_disposition.md
- docs/99_archive/handoff/v2/edit_refund_sniper/README.md
- docs/99_archive/handoff/v2/edit_refund_sniper/0006_surplus_disposition_adr_handoff.md

## Tests And Proof

Previous targeted reporting proof:

    php artisan test tests/Feature/Reporting/TransactionSummaryReportingQueryFeatureTest.php --filter=downward_revision_surplus_reporting_uses_capped_allocations_not_customer_payment_gross

Result:

    1 passed, 13 assertions

Previous broader owner-provided proof:

    973 passed, 5169 assertions

Commit and push proof for handoff 0005:

    commit 1947
    871c072e
    main -> main
    Done

ADR 0026 docs proof in this command:

    ADR 0026 anchors OK

Markdown safety proof expected after this handoff command:

    no output from fence scan

## Residual Risks

Blocks production implementation:

- customer balance or surplus disposition table contract is not designed yet
- customer identity stability must be inspected before customer credit implementation
- capability/actor policy for disposition must be wired in source
- audit storage path must be inspected before implementation
- disposition reports are not implemented
- UI is not implemented for backend-generated surplus/disposition state

Does not block next source audit:

- domain decision is now locked by ADR 0026
- next session can start with source audit for surplus disposition foundation

Needs owner decision:

- Whether first implementation should support refund_due only, customer_credit only, or split disposition in the first slice.
- Whether cashier can ever dispose surplus, or admin/capability-only is mandatory.

Future improvement:

- Add customer balance or surplus disposition ledger.
- Add use cases for convert pending to refund_due/customer_credit.
- Add refund_paid and credit_used execution states.
- Add current projection and report visibility after backend truth exists.
- Add UI only after backend truth exists.

## Next Active Step

Goal:

Start surplus disposition foundation source audit and table contract design.

Command if needed:

    grep -RIn -e "customer_name" -e "customer_id" -e "customer_key" -e "Audit" -e "audit" -e "actor_role" -e "capability" -e "customer_balance" -e "note_revision_settlements" app database docs/02_architecture/adr docs/03_blueprints tests | sed -n '1,260p'

Expected proof:

- current customer identity source map
- current audit/capability source map
- current note_revision_settlements integration map
- clear table contract gaps before any migration

Stop condition:

- stop before database migration if customer identity, audit source, or disposition actor policy is unclear.

## Next Session Opening Prompt

    Kita lanjut HyperPOS dari edit/refund sniper handoff.

    Baca berurutan:
    docs/01_standards/0001_index.md
    docs/01_standards/0002_decision_policy.md
    docs/99_archive/handoff/v2/edit_refund_sniper/README.md
    docs/99_archive/handoff/v2/edit_refund_sniper/SESSION_CONTRACT.md
    docs/99_archive/handoff/v2/edit_refund_sniper/0006_surplus_disposition_adr_handoff.md
    docs/02_architecture/adr/0026_note_revision_surplus_disposition.md

    Baseline FACT:
    - Saya selalu push setiap aksi.
    - Local dan repo identik setelah push kecuali ignored files.
    - Kalau saya menyatakan clean, pushed, latest, atau make verify pass, itu FACT.
    - Local command output dan owner statement menang atas GitHub/docs kalau ada konflik.
    - Jangan minta git status/log/diff/diff --check/make verify sebagai ritual.
    - Git dan make verify hanya dipakai kalau ada trigger nyata.

    Latest completed:
    - Phase 1A/1B revision settlement foundation selesai.
    - Phase 1C-B downward surplus revision commit selesai.
    - Reporting downward surplus characterization selesai.
    - Handoff 0005 pushed at 871c072e.
    - ADR 0026 surplus disposition accepted by owner.

    Locked ADR 0026 decisions:
    - overpaid_pending is the base holding state.
    - overpaid_pending is not revenue.
    - overpaid_pending is not automatic refund paid.
    - overpaid_pending is not automatic customer credit.
    - explicit disposition can become refund_due, customer_credit, split disposition, or high-trust manual adjustment.
    - refund_paid and credit_used are execution states, not automatic states created by revision itself.
    - unresolved pending surplus can participate in later same-note revision recalculation.
    - disposed money cannot be silently reclaimed by later revision.
    - revision must not fake a refund.
    - refund must not fake a revision.

    Current active target:
    - Start surplus disposition foundation source audit and table contract design.
    - Do not start from UI.
    - Do not start from controller.
    - Do not start from generic query patch.
    - Do not create database migration before customer identity, audit source, and actor policy are proven.

    Required response shape:
    FACT
    GAP
    ASSUMPTION
    DECISION
    ACTIVE STEP
    FILES TO TOUCH
    FILES NOT TO TOUCH
    COMMAND
    EXPECTED PROOF
    NEXT

## README Update Required

Yes.

README latest handoff pointer must point to:

    docs/99_archive/handoff/v2/edit_refund_sniper/0006_surplus_disposition_adr_handoff.md

## Session Context Health

67 percent.

Reason:

The session chain now includes Phase 1C-B proof, reporting characterization, ADR 0026 domain decision, and next implementation boundary. Next session should start from this handoff before any implementation.
