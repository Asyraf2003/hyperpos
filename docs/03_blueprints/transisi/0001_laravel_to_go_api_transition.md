# Laravel to Go Pure API Transition Blueprint

## Status

Blueprint draft for owner review.

This document is not implementation proof.

This document does not authorize broad rewrite, direct cutover, PostgreSQL cutover, or immediate Go write ownership.

## Purpose

Define a practical step-by-step transition path from the current Laravel application to a Go pure API architecture without forcing a big-bang rewrite.

The main goal is to move one slice at a time, keep current business truth stable, and make each migration step provable.

## FACT

- The canonical location for active design documents in this repo is `docs/03_blueprints/`.
- This repo already has migration-readiness constraints in:
  - `docs/02_architecture/adr/0028_mysql_to_postgresql_and_api_migration_readiness.md`
  - `docs/03_blueprints/db/0012_postgresql_go_api_transition_audit.md`
  - `docs/03_blueprints/db/0013_go_postgres_migration_readiness_stage_0.md`
  - `docs/03_blueprints/db/0014_migration_readiness_dependency_grid.md`
- ADR-0028 explicitly says:
  - Laravel/MySQL remains current runtime.
  - Go API is out of scope until stable application use cases exist.
  - New work must stay PostgreSQL-ready and API-ready.
- Existing transition audit already recommends:
  - do not start with a broad Go rewrite;
  - start with read-only Go APIs;
  - move write ownership only after parity, locking, idempotency, and audit proof exist.
- The user-provided Go tree proves a separate Go project already exists with:
  - `cmd/api`
  - `internal/modules/auth`
  - `internal/modules/system`
  - `internal/platform/postgres`
  - `internal/transport/http/middleware`
  - migrations for auth and authorization
- The user explicitly said login is not the main problem.

## SCOPE-IN

- Blueprint for staged transition order.
- Slice-by-slice migration strategy.
- Ownership boundary between Laravel and Go.
- Recommended proof gate before each slice moves.
- Document placement for the new transition topic.

## SCOPE-OUT

- No production code change in Laravel.
- No production code change in Go.
- No endpoint implementation.
- No PostgreSQL runtime migration execution.
- No API contract freeze for a specific endpoint yet.
- No cutover runbook.

## GAP

- The current Go source code was not inspected directly in this session; the proven Go context comes from the user-provided tree only.
- The exact Laravel route-to-use-case inventory for all business flows was not rebuilt in this session.
- No current response-contract inventory was generated in this step.
- No proof yet exists for Laravel-vs-Go parity on any business endpoint.

## CONSTRAINTS

### C1 - No big-bang rewrite

Do not stop Laravel feature delivery and attempt to rebuild the whole system in Go at once.

### C2 - Laravel remains write owner first

Until write contracts are frozen and proven, Laravel remains the mutation owner for:

- note create/edit
- payment allocation
- refund
- inventory mutation
- revision/settlement
- procurement payment mutation
- audit-sensitive writes

### C3 - Go starts from pure API boundary

Go should expose transport/use-case/repository boundaries cleanly and must not mirror Blade/web assumptions.

### C4 - Read-first, write-later

The first successful transition slices should be read-only or operationally light.

### C5 - Proof gate per slice

No slice is considered migrated only because code exists.

Each slice needs:

- contract definition
- focused test proof
- parity verification against Laravel
- rollback path if the slice is user-facing

## TARGET SHAPE

Target long-term shape:

- Go becomes the pure API application surface.
- Laravel is gradually reduced from business runtime toward compatibility shell, admin shell, or retired component.
- PostgreSQL becomes the long-term database target.
- Domain rules live in Go application/domain modules, not in UI or JS payload assembly.
- Cross-cutting concerns are explicit:
  - authn
  - authz
  - idempotency
  - audit
  - transaction boundary
  - projection/read models

## RECOMMENDED TRANSITION MODEL

Use the Strangler Fig model with bounded-context ownership.

Meaning:

1. Freeze one small contract.
2. Rebuild that contract in Go.
3. Compare Laravel and Go outputs.
4. Route traffic for that slice to Go only after proof.
5. Keep other slices in Laravel.

Do not migrate by framework layer first.

Do not migrate "all controllers", then "all services", then "all models".

Migrate by bounded context and by risk.

## MIGRATION ORDER

### Phase 0 - Baseline and Contract Inventory

Goal:

- map what exists before moving anything.

Required output:

- route inventory
- use-case inventory
- table-write map
- projection/read-model map
- auth boundary map
- error envelope contract
- pagination/filter/sort contract

Why first:

- without this, each Go slice will guess the Laravel behavior.

Exit proof:

- documented inventory for the chosen first slice
- explicit source-of-truth tables
- explicit owner for each endpoint

### Phase 1 - Shared API Foundation in Go

Goal:

- finish the reusable API skeleton once, not per feature.

Expected scope:

- config loading
- request ID
- panic recovery
- auth middleware
- authz middleware
- response envelope
- error mapping
- DB transaction helper
- audit adapter interface
- id generator strategy
- time/UTC policy

Why here:

- these are foundation pieces every later slice will need.

Exit proof:

- focused unit tests
- one reference endpoint proving the stack shape

### Phase 2 - Read-Only Low-Risk Slices

Goal:

- prove delivery mechanics without touching finance mutations.

Recommended first slices:

1. `GET /health`
2. `GET /me`
3. master-data read APIs:
   - service catalog
   - product search
   - customer search
4. simple reporting/projection reads

Why first:

- low rollback cost
- low lock complexity
- low audit complexity
- easy parity comparison

Exit proof:

- same auth behavior where required
- same filter/search semantics
- response parity on selected fixtures

### Phase 3 - Read-Only Operational Slices

Goal:

- move operational reads that are more useful but still not mutation-heavy.

Recommended candidates:

- dashboard summary
- supplier list
- supplier invoice list/detail read
- note history/timeline read
- inventory list/read
- reporting read models

Why before writes:

- these slices force DTO and query discipline without changing write truth.

Exit proof:

- parity fixtures
- pagination/sort/filter proof
- date-range and money-field verification

### Phase 4 - Sidecar Write Slices With Low Financial Blast Radius

Goal:

- move small writes that do not yet carry the main cashier/payment/refund invariants.

Recommended candidates only if clearly isolated:

- attachment upload metadata write
- non-financial admin toggles
- simple role-assignment admin write already modeled in Go auth module

Do not include in this phase:

- cashier transaction create/edit
- payment recording
- refund
- stock movement
- procurement payment mutation

Exit proof:

- transaction boundary documented
- audit facts documented
- idempotency decision documented
- rollback behavior documented
- focused integration tests

### Phase 5 - Domain Write Slice Migration

Goal:

- move one mutation-heavy bounded context at a time.

Recommended order:

1. product/service catalog write
2. procurement non-payment write
3. inventory adjustment or stock admin write
4. transaction create/edit
5. payment allocation
6. refund/revision/settlement

Reason for this order:

- the farther down the list, the more concurrency, audit, and lifecycle risk exists.

Hard gate:

No write slice may move unless its contract document includes:

- request DTO
- response DTO
- domain invariants
- table write map
- lock targets
- idempotency rule
- audit facts
- projection sync effect
- failure and rollback path

### Phase 6 - Traffic Shift and Laravel Reduction

Goal:

- move runtime ownership endpoint by endpoint after proof exists.

Possible techniques:

- Nginx/API gateway route split
- Laravel proxy to Go for migrated paths
- frontend config switch by path group

Exit proof:

- real runtime metrics
- error-rate comparison
- rollback switch tested

## SLICE SELECTION RULE

When choosing the next thing to move, score each candidate by:

1. business risk
2. dependency depth
3. contract clarity
4. parity testability
5. rollback simplicity
6. user value

Pick the highest-value slice with low-to-medium risk first.

Default rule:

- never choose refund, allocation, or revision as the first migrated write.

## LARAVEL TO GO MAPPING MODEL

For each Laravel slice, create this mapping before coding:

1. Laravel entrypoint
   - route
   - controller
   - request validator
2. Laravel application path
   - use case
   - service
   - transaction manager
3. Laravel persistence path
   - repositories
   - raw SQL
   - projection writes
4. Domain invariants
5. Public contract
   - request
   - response
   - errors
6. Go target
   - transport handler
   - use case
   - ports
   - postgres adapter
7. Proof plan
   - unit test
   - integration test
   - parity fixture

## PRACTICAL "ONE BY ONE" WORKFLOW

Use this exact loop for each slice:

1. choose one endpoint/use case only
2. document current Laravel behavior
3. freeze contract and invariants
4. list all tables read/written
5. classify risk:
   - read-only
   - light write
   - heavy write
6. implement Go transport/use case/repository
7. write focused tests in Go
8. run parity check against Laravel output
9. enable the slice behind controllable routing
10. observe
11. only then mark the slice migrated

If step 8 fails, stop and fix parity before moving to another slice.

## RECOMMENDED FIRST 10 SLICES

This is the recommended migration queue given the current facts and the user's note that login is not the main concern.

1. system health
2. current principal / me
3. service catalog search
4. product search/read
5. customer search/read
6. supplier list read
7. supplier invoice list read
8. supplier invoice detail read
9. note history/timeline read
10. one isolated admin write with low financial risk

Deferred until later:

- create transaction
- edit transaction
- payment receive/allocate
- refund
- note revision
- settlement
- surplus disposition
- inventory auto-reversal

## RECOMMENDED DOCUMENT SET PER SLICE

Before implementing a slice, prepare:

1. one blueprint or contract doc for the slice
2. one workflow doc if execution is multi-step
3. one parity proof record or handoff after verification

Suggested folder shape:

- `docs/03_blueprints/transisi/`
  - transition master blueprint
  - per-slice subdocs later

## OWNER DECISIONS NEEDED SOON

These decisions are still needed before implementation planning becomes narrower:

1. Will Laravel stay as temporary web/admin shell while Go owns API?
2. Will Go read MySQL first, or only PostgreSQL?
3. Will routing split happen at reverse proxy level or Laravel proxy level?
4. What is the first business slice after `health` and `me`?
5. Which API envelope becomes canonical when Laravel and Go differ slightly?

## RECOMMENDED DEFAULT DECISIONS

Unless the owner decides otherwise, use these defaults:

### D1 - Laravel stays temporarily

Laravel remains the web/admin shell during the transition.

### D2 - Go reads current proven DB first

If PostgreSQL runtime is not yet proven, Go may read the current database path first for early read-only slices, as long as the contract stays PostgreSQL-ready.

### D3 - Read-only first

First business migration slice should be a read-only master-data or projection endpoint.

### D4 - One slice, one owner, one proof gate

Do not migrate multiple heavy bounded contexts in one batch.

## ACTIVE STEP

Create the master transition blueprint only.

Step goal:

- establish the canonical migration order and boundaries for Laravel-to-Go pure API transition.

Targeted output:

- this blueprint document in `docs/03_blueprints/transisi/`.

Expected proof:

- file exists with migration order, constraints, and slice workflow.

Boundary:

- documentation only.

## PROOF

- New folder created: `docs/03_blueprints/transisi/`
- New file created: `docs/03_blueprints/transisi/README.md`
- New file created: `docs/03_blueprints/transisi/0001_laravel_to_go_api_transition.md`

## NEXT

Wait for owner feedback on the migration queue.

Recommended next step after approval:

- create the first slice contract blueprint for `service catalog search` or `product search/read`.

## PROGRESS

5%

Reason:

- the transition master blueprint now exists, but no slice contract, code, or runtime proof exists yet.
