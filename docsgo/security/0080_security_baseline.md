# Security Baseline

## Purpose

Security rules must be explicit before API growth accelerates.

## Required Controls

Every protected endpoint must pass:

```text
request id -> recovery -> rate limit when needed -> authn -> authz -> capability check -> validation -> use case
```

## Authentication

- Authentication belongs in middleware/platform boundary.
- Handlers may read authenticated principal from request context.
- Domain must not know token format.
- Passwords, tokens, and secrets must never be logged.

## Authorization

- Permissions must be named and documented.
- Authorization must happen before use case execution.
- Use cases may still enforce ownership/domain-level authorization when business ownership matters.

## Capability Security

- Disabled capability must return before use case execution.
- Capability changes must be auditable.
- Capability metadata exposed to UI must not leak secret implementation detail.

## Input Security

- Validate all path params, query params, and body fields.
- Do not accept unknown mutation fields unless the request contract explicitly allows extension.
- Use allow-lists for sort, filter, enum, and operation names.
- Reject ambiguous money, quantity, and timestamp formats.

## Output Security

- Use one error envelope.
- Redact internal errors.
- Do not expose stack traces, SQL, secrets, tokens, internal file paths, or raw driver errors.
- Do not return fields that are not part of the public DTO.

## Abuse Controls

High-risk endpoints must define whether they need:

- rate limit;
- idempotency key;
- replay protection;
- audit log;
- IP/device metadata;
- admin approval;
- elevated permission.

## Secret Handling

- Secrets must come from environment/config provider.
- No secrets in repo docs, fixtures, logs, seed output, or handoff.
- Seeder credentials must be generated or explicitly marked local-only.

## Forbidden Security Behavior

- Do not rely on hidden UI buttons as authorization.
- Do not use capability control as the only authorization.
- Do not log request bodies for sensitive mutations.
- Do not store plaintext credentials.
- Do not bypass middleware in API tests unless the test clearly targets lower-level code.

