# Makefile And Scripts Contract

## Purpose

Terminal Codex, GPT web, humans, and CI need the same proof language.

Stable command names make handoff and verification easier.

## Required Make Targets

The future Go repo should provide:

```bash
make verify
make test
make test-unit
make test-api
make test-db
make lint
make fmt
make arch
make security
make migrate-up
make migrate-down
make seed
```

## Target Meaning

- `make verify`: full local quality gate.
- `make test`: all normal tests.
- `make test-unit`: fast domain/application unit tests.
- `make test-api`: Echo HTTP/API contract tests.
- `make test-db`: PostgreSQL repository and migration tests.
- `make lint`: static analysis.
- `make fmt`: formatting.
- `make arch`: forbidden import and package boundary checks.
- `make security`: security-focused scanners/checks.
- `make migrate-up`: apply migrations to configured local DB.
- `make migrate-down`: rollback one safe step or documented rollback path.
- `make seed`: load deterministic local/dev seed data.

## Script Rules

- Scripts must be deterministic.
- Scripts must print clear PASS/FAIL output.
- Scripts must exit non-zero on failure.
- Scripts must not require production credentials.
- Scripts must document required environment variables.

## Seeder Rules

Seeders must be separated by profile:

- `minimal`: only required baseline data.
- `dev`: realistic local data.
- `stress`: large-volume test data.
- `security`: roles, permissions, and capability matrix fixtures.

Seeders must not create production-like secrets unless they are generated locally and printed as local-only.

## Architecture Script Rules

Architecture checks should verify:

- forbidden imports;
- package responsibility boundaries;
- max file line rules;
- route to capability registry match;
- handler to use case path;
- repository SQL isolation.

## Forbidden Script Behavior

- Do not make `make verify` skip failing tests silently.
- Do not let scripts mutate developer data without explicit target name.
- Do not mix destructive DB reset into `make test`.
- Do not hide required services or env vars.

