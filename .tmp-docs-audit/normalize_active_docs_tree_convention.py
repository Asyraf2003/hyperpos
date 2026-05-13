from pathlib import Path

ROOT = Path("docs")
ARCHIVE = Path("docs/99_archive")

active_md_files = [
    path for path in ROOT.rglob("*.md")
    if ARCHIVE not in path.parents and path != ARCHIVE
]

changed = []

def replace_in_file(path: Path, replacements: dict[str, str]) -> None:
    text = path.read_text(encoding="utf-8")
    original = text

    for old, new in replacements.items():
        text = text.replace(old, new)

    if text != original:
        path.write_text(text, encoding="utf-8")
        changed.append(str(path))

def active_global_replacements() -> dict[str, str]:
    replacements = {
        "docs/error_log/": "docs/04_lifecycle/error_log/",
        "`docs/error_log`": "`docs/04_lifecycle/error_log`",
        "docs/error_log": "docs/04_lifecycle/error_log",

        "docs/04_lifecycle/error-log/": "docs/04_lifecycle/error_log/",
        "`error-log/`": "`error_log/`",
        "error-log/": "error_log/",

        "docs/03_blueprints/v2/feature-continuation": "docs/03_blueprints/feature_continuation",
        "docs/03_blueprints/v2/feature_continuation/00-blueprint.md": "docs/03_blueprints/feature_continuation/0001_blueprint.md",
        "docs/03_blueprints/v2 or docs/99_archive/handoff/v2": "docs/03_blueprints or docs/99_archive/handoff/v2",

        "docs/03_blueprints/v2/note_finance/2026-04-29-note-finance-stabilization-blueprint.md": "docs/03_blueprints/finance/0001_note_finance_stabilization.md",
        "docs/03_blueprints/v2/note_finance/2026-04-29-note-finance-current-projection-addendum.md": "docs/03_blueprints/finance/0002_note_finance_stabilization_addendum.md",
        "docs/03_blueprints/v2/note_finance/2026-05-06-error-log-finance-residual-implementation-blueprint.md": "docs/03_blueprints/finance/0003_finance_residual.md",
        "docs/03_blueprints/v2/note_finance/2026-05-12-note-revision-refund-ledger-blueprint.md": "docs/03_blueprints/finance/0006_note_revision_refund_ledger.md",
        "docs/03_blueprints/v2/note_finance/2026-05-12-note-revision-refund-ledger-dod.md": "docs/03_blueprints/finance/0007_note_revision_refund_ledger_dod.md",
        "docs/03_blueprints/v2/note_finance/2026-05-12-note-revision-refund-ledger-workflow.md": "docs/03_blueprints/finance/0008_note_revision_refund_ledger_workflow.md",
    }

    error_log_dir = Path("docs/04_lifecycle/error_log")
    for current in sorted(error_log_dir.glob("*.md")):
        stem = current.stem
        if stem == "README":
            continue

        number, _, title = stem.partition("_")
        if not number or not title:
            continue

        number4 = number
        number3 = str(int(number)).zfill(3)
        slug = title.replace("_", "-")
        current_path = current.as_posix()

        old_candidates = [
            f"docs/04_lifecycle/error_log/{number3}-{slug}.md",
            f"docs/04_lifecycle/error_log/{number4}-{slug}.md",
            f"docs/error_log/{number3}-{slug}.md",
            f"docs/error_log/{number4}-{slug}.md",
        ]

        for old in old_candidates:
            replacements[old] = current_path

    return replacements

for file_path in active_md_files:
    replace_in_file(file_path, active_global_replacements())

root_doc_replacements = {
    "Permanent decision records. Sequential numbered `NNNN_snake_title.md`.": "Permanent decision records. Sequential numbered `NNNN_snake_title.md`.",
    "- `error-log/` — individual bug/security findings, numbered `NNNN_snake_title.md`": "- `error_log/` — individual bug/security findings, numbered `NNNN_snake_title.md`",
    "| ADR | `NNNN_snake_title.md` | `0019-note-access-boundary.md` |": "| ADR | `NNNN_snake_title.md` | `0019_note_access_boundary_cashier_date_window_and_transaction_capability_enforcement.md` |",
    "| Blueprint | `NNNN_topic_name.md` | `finance-residual.md` |": "| Blueprint | `NNNN_topic_name.md` | `0003_finance_residual.md` |",
    "| DoD | `NNNN_topic_name_dod.md` | `finance-residual-dod.md` |": "| DoD | `NNNN_topic_name_dod.md` | `0004_finance_residual_dod.md` |",
    "| Workflow | `NNNN_topic_name_workflow.md` | `finance-residual-workflow.md` |": "| Workflow | `NNNN_topic_name_workflow.md` | `0005_finance_residual_workflow.md` |",
    "| Error log | `NNNN_snake_title.md` | `009-cashiers-can-rewrite.md` |": "| Error log | `NNNN_snake_title.md` | `0009_cashiers_can_rewrite_closed_paid_notes_via_workspace_update.md` |",
    "| Audit record | `YYYY-MM-DD-topic.md` | `2026-05-06-error-log-coverage.md` |": "| Audit record | `NNNN_topic_name.md` | `0002_error_log_solution_and_adr_coverage_summary.md` |",
    "| Handoff aktif | `YYYY-MM-DD-topic-handoff.md` | `2026-05-12-kotlin-skeleton-handoff.md` |": "| Handoff aktif | `NNNN_topic_handoff.md` | `0001_scope_handoff.md` |",
    "| Handoff aktif | `YYYY-MM-DD-topic-handoff.md` | `2026-05-12-skeleton-handoff.md` |": "| Handoff aktif | `NNNN_topic_handoff.md` | `0001_scope_handoff.md` |",
    "| Folder | `kebab-case` | `error_log/`, `01_standards/` |": "| Folder | `NN_prefix_snake_case` for L1, `snake_case` for subfolders | `01_standards/`, `error_log/` |",
    "| Folder | `kebab-case` | `error-log/`, `01_standards/` |": "| Folder | `NN_prefix_snake_case` for L1, `snake_case` for subfolders | `01_standards/`, `error_log/` |",
    "Formal audit records dengan date prefix `YYYY-MM-DD-topic.md`.": "Formal audit records dengan numbered snake_case filename `NNNN_topic_name.md`.",
    "Naming: `YYYY-MM-DD-topic.md`.": "Naming: `NNNN_topic_name.md`.",
    "Naming: `YYYY-MM-DD-topic-handoff.md`.": "Naming: `NNNN_topic_handoff.md`.",
    "Known cleanup warnings:": "Known historical warnings:",
    "- docs/99_archive/handoff/handoff_template.md is legacy compared with docs/01_standards/0005_handoff_template.md.": "- docs/99_archive/handoff/handoff_template.md is historical if present; canonical template is docs/01_standards/0005_handoff_template.md.",
    "- docs/03_blueprints/feature_continuation/0001_blueprint.md is more like a control ledger than a normal blueprint.": "- docs/03_blueprints/feature_continuation/0001_blueprint.md is the active feature continuation control ledger.",
    "- Some docs reference stale paths such as docs/setting_control. Treat them as historical unless proven active.": "- Stale historical references are allowed only inside docs/99_archive.",
    "- Do not move docs before grep backlink audit.": "- Do not rename docs paths without grep backlink audit.",
}

for file_name in [
    "docs/README.md",
    "docs/0006_change_tree.md",
    "docs/0001_docs_help.md",
    "docs/04_lifecycle/README.md",
    "docs/05_audits/README.md",
]:
    path = Path(file_name)
    if path.exists():
        replace_in_file(path, root_doc_replacements)

print("changed_files:")
for item in sorted(set(changed)):
    print(item)
