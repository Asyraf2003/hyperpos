# Handoff Protocol

## Purpose

Work may move between terminal Codex, GPT web, humans, and future AI assistants.

This protocol keeps scope, proof, and file ownership clear.

## Required Handoff Fields

Every handoff must include:

- date;
- active scope;
- current branch or source snapshot if available;
- files included;
- files changed;
- files forbidden to touch;
- blueprint referenced;
- ADR/rules referenced;
- decisions made;
- proof collected;
- tests or commands run;
- gaps still open;
- next valid active step.

## Scope Packet Rule

Before sending work to another AI, create a scope packet.

The packet must say:

- what domain/API is active;
- which files are provided as context;
- which files may be edited;
- which files are read-only;
- which output format is expected;
- which command output counts as proof.

## Receiving AI Rule

The receiving AI must:

- read `docsgo/README.md`;
- read `docsgo/AGENTS.md`;
- read the scope packet;
- stay inside active scope;
- mark GAP instead of guessing missing files;
- return a handoff when done.

## Forbidden Handoff Behavior

- Do not say "continue from previous chat" without a written handoff.
- Do not send only code files without rules and proof requirements.
- Do not let archive docs override active blueprint.
- Do not ask another AI to edit files outside the packet.
- Do not claim tests passed from intention.

