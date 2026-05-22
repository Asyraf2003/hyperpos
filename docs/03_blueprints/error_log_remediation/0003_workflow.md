# Error Log Remediation Workflow

- **Status:** Planning workflow.
- **Scope:** execution workflow for fixes in `docs/04_lifecycle/error_log/`.
- **Non-goal:** this document is not a source patch, not a fix claim, and not an instruction to create a seeder.

## Purpose

- This workflow is a rigid procedure for processing every issue in `docs/04_lifecycle/error_log/` as one dependent chain, not as a list of independent issues.
- Its main goals are:
  - make every claim based on documents, source, and command proof
  - separate document status from actual source status
  - prevent patches that only move bugs from one flow to another
  - keep fixes aligned across domain, application, infrastructure, HTTP/controller, Blade, native JS, security, audit, and documentation
  - stop closure when proof is insufficient

## Non-Goals

- This session must not:
  - implement bug fixes
  - create or change seeder code
  - run production source changes
  - treat `Patched`, `Fixed`, or `Fixed with proof` as true without cross-verification
  - claim all `docs/04_lifecycle/error_log/` issues are done
  - commit or push
  - use UI hiding as a security boundary
  - close an issue without RED proof, focused proof, and the required docs alignment
- Seeder is outside the main workflow for this session. Seeder may only be mentioned as a dependency, residual risk, or future scope.

## Source Priority

- Decisions must follow this priority:
  - `docs/04_lifecycle/error_log/`
  - the primary source of issue listings
  - map issue, status, scope, proof, gap, risk, and relation
  - document status is not trusted automatically
  - `docs/03_blueprints/security/`
  - security, auditability, redaction, authorization, upload/proof, access control, hardening boundaries
  - blueprint dated `2026-05-06` for ADR-0019 as the access boundary
  - blueprint dated `2026-05-06` for ADR-0020 as output, URL, storage, attachment, and disclosure
  - documents dated `2026-05-05` and `2026-05-06`
  - search for relevant patch notes, workflow, owner decisions, and blueprints
  - newer documents have more weight when they are more specific
  - `docs/02_architecture/adr/`
  - newer ADRs win over older ADRs if they conflict
  - if an older and newer ADR conflict, record the conflict, the document paths, and the decision used
  - if a more specific document exists than a general ADR, the specific document can win for that slice
  - `docs/03_blueprints/`
  - the new workflow must align with:
    - `docs/03_blueprints/error_log_remediation/strict-closure-protocol.md`
    - `docs/03_blueprints/finance/finance-residual-workflow.md`
    - relevant ADR / security workflows
  - source and local command output still win over the old workflow if there is a conflict

## Evidence Labels

- Use these labels consistently:
  - **FACT:** proven by a document, source, or command output
  - **GAP:** not proven yet, proof is missing, the test has not run, the source has not been checked, or the scope is not clear
  - **RISK:** possible bug, security issue, regression, data corruption, leakage, or audit failure
  - **DECISION:** a workflow decision recommended for execution
  - **DOD:** a completion condition that can be verified

## Trust Status for Error Logs

- Every error log must receive a work-confidence status:
  - **trusted:** the document has a root cause, source map, and enough RED/GREEN or targeted/focused proof for the claimed scope
  - **weak:** there is a patch or a claim, but proof is missing, the test failed, only syntax checks exist, or the residual is still large
  - **contradicted:** the document conflicts with another document, the source, or test proof
  - **unknown:** the document/source has not been read enough yet
- Trust status is not the same as document status.

## Main Principles

- Only one active slice at a time.
- Do not move to the next slice before the active slice has complete proof.
- Source and test proof win over document status.
- Newer ADRs win over older ADRs, unless a more specific document gives a more precise rule.
- RED proof must exist before a patch, unless it is impossible and the reason is recorded.
- The patch must be minimal and stay on the correct boundary.
- UI Blade and native JS must be reviewed whenever the issue affects screen, form, link, config, or action behavior.
- Security boundaries are always server-side.
- Audit / log / redaction must be verified for mutation, payment, refund, proof attachment, capability, and sensitive read.
- A closure document is only allowed after source, tests, UI / security review, and residual gaps are recorded.

## Step-by-Step Workflow

### Step 0 - Baseline Intake

- Collect:
  - branch and HEAD at the time of execution
  - `git status --short --untracked-files=all`
  - all `docs/04_lifecycle/error_log/*.md`
  - the actual number of error logs
  - relevant blueprint / security / ADR / workflow documents
  - the status of each issue document
  - proof claimed by each issue
  - issues with conflicts or residual gaps
- Gate:
  - all error logs are mapped
  - all issues have an initial trust status
  - all relationships between issues are recorded
  - no source patch has been made
- Stop condition:
  - the number of error logs does not match the audit document without explanation
  - an error_log file cannot be read
  - a document claims `fixed` but the proof cannot be traced

### Step 1 - Cluster and Dependency Mapping

- Group issues by dependency and domain impact, not by file number.
- Minimum clusters:
  - current vs historical operational rows
  - settlement / payment basis
  - revision and payment concurrency
  - access / capability / date-window boundary
  - refund lifecycle and terminal state
  - price basis authority
  - output context, Blade, JS, unsafe URL
  - storage / public helper / attachment proof
  - seeder credential safety as future scope
  - final global verification
- Gate:
  - every issue has upstream and downstream dependencies
  - issues that may be combined in one slice are clearly identified
  - issues that must be split are clearly identified
  - issues that must not be worked on yet because proof / source map / dependency is unclear are marked
- Stop condition:
  - a finance / refund / access issue is treated independently even though it depends on settlement, current projection, or access boundary
  - a UI issue is worked on before the server-side guard is clear
  - seeder enters the active workflow without explicit instruction

### Step 2 - Source Inspection

- For the active slice, read the current source, not only the documents.
- Must find:
  - production files
  - route / controller / middleware
  - policy / use case / service
  - adapter / repository / query
  - Blade / view partial
  - native JS / config sink
  - audit / logging / redaction path
  - existing tests
- Gate:
  - the provisional root cause matches the current source
  - the source map is complete up to the affected layer
  - if the source differs from the document, the conflict is recorded
- Stop condition:
  - the source does not show the same root cause as the document
  - a patch is claimed to exist, but the current source does not contain it
  - a source conflict touches an ADR and no decision has been made yet

### Step 3 - RED Proof

- Create or run a characterization test that proves the bug in the current source.
- RED proof must show:
  - the command
  - the relevant failure
  - the important assertion or output
  - why the failure matches the root cause, not a fixture error
- For issues already claimed fixed:
  - if the proof is strong, re-run targeted / focused proof
  - if the proof is weak, move it into a verification slice
  - if source and tests conflict, treat it as contradicted
- Gate:
  - RED is valid or the reason RED is impossible is recorded
  - the fixture is real
  - the failure is not caused by environment dependency, missing vendor, or irrelevant setup
- Stop condition:
  - the test fails for an unknown reason
  - the test only proves syntax
  - the test locks in behavior that conflicts with the ADR / domain decision

### Step 4 - Minimal Production Patch Boundary

- A patch may begin only after valid RED proof.
- Patch rules:
  - the patch boundary must match the root cause
  - do not patch a generic reader if consumer semantics are still unclear
  - do not patch Blade / JS to cover up server-side authorization
  - do not patch UI to hide data corruption
  - do not refactor broadly without source map and proof
  - do not change seeder inside the main workflow
- Gate:
  - patch files match the active slice
  - no file outside the scope is changed
  - the patch does not weaken existing tests
  - the patch does not remove audit/history without a domain decision
- Stop condition:
  - the patch requires a large domain redesign without an owner decision
  - the patch requires a new ADR or an ADR conflict resolution
  - the patch breaks admin read access while fixing a mutation
  - the patch blocks global cashier edit / refund without policy

### Step 5 - UI Blade Impact Check

- Required if the issue touches:
  - button / link / action
  - form
  - workspace
  - note detail
  - refund / payment UI
  - JSON config in Blade
  - data rendered to HTML / attribute / script
  - public / sensitive attachment link
  - count / stat visible to the user
- Checklist:
  - the action is not rendered when backend policy rejects it
  - backend still rejects direct requests
  - no raw user-controlled HTML exists
  - JSON in script context uses safe encoding
  - URL-like attributes do not accept unsafe schemes
  - `can_edit_workspace` or similar flags match server policy
  - no hidden input is treated as domain / security authority
- Gate:
  - view paths are recorded
  - rendered response is tested when relevant
  - negative search is done for unsafe strings / actions / links
  - UI is not the only guard
- Stop condition:
  - a direct route still mutates even though the button is hidden
  - raw JSON / HTML sink still exists
  - JS config can breakout with `</script>`

### Step 6 - Native JS Impact Check

- Required if the issue touches:
  - workspace JS
  - selected-row behavior
  - inline payment / refund
  - page config JSON
  - return / back URL
