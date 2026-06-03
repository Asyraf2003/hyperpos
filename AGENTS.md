# AGENTS.md

## Repository instruction source

This repository uses `docs/01_standards` as the canonical AI_RULES package.

Before giving technical guidance, planning implementation, editing files, or proposing commands, read and follow:

1. `docs/01_standards/0001_index.md`
2. `docs/01_standards/0002_decision_policy.md`
3. `docs/01_standards/0003_gpt_bootstrap_prompt.md`
4. `docs/01_standards/0004_session_start_protocol.md`
5. `docs/01_standards/core/0010_scope_and_facts.md`
6. `docs/01_standards/core/0011_blueprint_first.md`
7. `docs/01_standards/core/0012_step_by_step_execution.md`
8. `docs/01_standards/core/0013_proof_and_progress.md`
9. `docs/01_standards/workflow/0020_response_structure.md`
10. `docs/01_standards/workflow/0021_active_step_policy.md`
11. `docs/01_standards/output/0033_terminal_command_delivery.md`

If the user names a blueprint, ADR, handoff, error log, branch, commit, or command output, that reference defines the active scope until the user changes it.

## Mandatory working behavior

- Do not invent facts, repo state, file contents, test results, or completion status.
- Separate FACT, GAP, DECISION, ACTIVE STEP, PROOF, NEXT, and PROGRESS for technical work.
- Start from a blueprint before implementation.
- Use one active step per response.
- Do not continue to the next step without proof and user feedback.
- Progress may increase only when there is real proof.
- User command output is the primary proof.
- Remote connectors may be used for reading only unless the user explicitly asks for remote write.
- Default implementation delivery is local terminal commands for the user to run.
- Prefer `rg` for content search.
- Prefer `fd` for file discovery.
- Avoid `find` unless the user explicitly asks for POSIX `find`.
- Commands must state the execution context.
- Do not provide destructive commands without an explicit reason and safety boundary.
