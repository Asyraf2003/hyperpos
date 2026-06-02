# seeder

Seeder migration, scale-profile, and manual QA seed blueprints.

## Files

| File | Type | Contents |
|---|---|---|
| `0001_create_only_seed_scale_profiles.md` | Blueprint | CreateOnly seed scale profiles for owner-readable QA, normal 100M, peak, stress, and refund scaffold boundary. |
| `0001_legacy_to_clean.md` | Blueprint | Seeder migration design: base, domain, scenario, load levels. |
| `0002_legacy_to_clean_manifest.md` | Manifest | List of old seeders and their migration status. |
| `0003_legacy_to_clean_dod.md` | DoD | Seeder migration completion criteria. |
| `0004_legacy_to_clean_workflow.md` | Workflow | Seeder migration execution order |
| `0005_manual_qa_transaction_lifecycle_seed_analysis.md` | Analysis / Blueprint candidate | Evaluation of the missing small manual QA transaction lifecycle seed for create, edit/revision, payment, refund, reports, and note history projection. |

## Related ADR

`docs/02_architecture/adr/0023_seeder_credential_and_environment_safety.md`

## Placement Rule

Use this folder for seed design, seed scale planning, seed workflow, and manual QA seed readiness analysis.

Do not put seed-readiness analysis in `docs/04_lifecycle/error_log/` unless there is a confirmed bug or security finding that must be tracked as an issue.
