# Go Style

## Purpose

Keep Go code predictable across terminal Codex, GPT web, and human work.

## General Style

- Prefer small packages with clear responsibility.
- Prefer explicit structs over `map[string]any`.
- Prefer constructor functions for dependencies.
- Prefer context-aware methods for IO and DB operations.
- Return typed domain/application errors where behavior depends on error kind.
- Keep public DTO names stable and boring.

## Naming

- Use domain language, not framework language, in domain and application.
- Use `CreateXCommand`, `UpdateXCommand`, `XResult`, `XRepository`, `XQuery`.
- Use `Handler` only in HTTP adapter packages.
- Use `Store` or `Repository` consistently by owner decision; do not mix casually.

## Error Style

- Domain errors describe domain conflicts.
- Application errors map domain and port failures.
- HTTP adapters map errors to public envelopes.
- Do not build HTTP messages inside domain.

## Context Rule

Use `context.Context` for:

- use case execution;
- repository calls;
- external service calls;
- transaction execution.

Do not store `context.Context` in structs.

## Dependency Rule

Dependencies are passed explicitly.

No hidden global DB, logger, clock, or capability registry in use cases.

## Forbidden Patterns

- SQL in handlers.
- Echo imports in domain/application.
- Business logic in middleware.
- Public API DTO built from raw database rows.
- Generic helper package that becomes a dumping ground.
- Large files justified by convenience.
- Panic for expected domain or validation errors.

