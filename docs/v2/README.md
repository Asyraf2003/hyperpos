# V2 Continuation Lane

Status: Active documentation lane

Purpose:

`docs/v2` stores continuation notes for an application that is already running while being improved gradually.

This folder is not a replacement for ADR, AI_RULES, or active blueprints.

Use this folder when the work is about:

- continuing unfinished or interrupted feature work
- tracking ambiguity discovered after previous implementation attempts
- recording UI/session recovery notes
- documenting safe continuation steps without rewriting the whole app

## Source of truth priority

Use this priority when a V2 note conflicts with other docs:

1. Current local command output
2. docs/AI_RULES
3. docs/adr
4. Active blueprint for the current scope
5. Latest relevant handoff
6. Older V2 notes or historical handoffs

## Folder map

### docs/v2/feature-continuation

Feature continuation lane.

This is for tracking incomplete, ambiguous, or interrupted feature work while the app remains operational.

Important file:

- docs/v2/feature-continuation/00-blueprint.md

Treat this file as a continuation control ledger, not as a permanent ADR.

Permanent decisions must be promoted into `docs/adr`.

### docs/v2/feature-continuation/handoffs

Feature continuation recovery notes.

These files are used to restart a specific feature continuation case without reading the entire repo history.

They are not permanent decisions unless promoted into ADR or active blueprint docs.

### docs/v2/ui

UI continuation and recovery notes.

This folder may contain live local gaps, session handoffs, or UI-specific recovery notes.

Do not treat UI handoff notes as final domain decisions.

## Rules

- Do not move files from this folder without backlink audit.
- Do not rename dated or referenced files without checking backlinks first.
- Do not treat V2 notes as permanent decisions by default.
- Promote long-lived decisions into ADR.
- Promote active implementation contracts into `docs/blueprints`.
- Keep app-code changes separate from docs-governance commits.

## Recommended session start

For V2 continuation work, read:

1. docs/README.md
2. docs/DOCS_HELP.md
3. docs/AI_USAGE_GUIDE.md
4. docs/AI_RULES/00_INDEX.md
5. docs/v2/README.md
6. The relevant V2 file for the current scope
7. The latest local command output from the operator
