# Handoff 0010 - Phase 5 Seeder CreateOnly Contract Hardening

## FACT

- Phase 5 addressed `docs/04_lifecycle/error_log/2026-05-28_full_repo_audit/0002_seeder_role_contract.md`.
- Original issue: active `DatabaseSeeder` used `Database\Seeders\CreateOnly\CreateUserSeeder`, and the active seeded cashier account wrote non-canonical `actor_accesses.role = user`.
- Canonical role contract is `admin` / `kasir`.
- Active seeder path remains CreateOnly.
- CreateOnly seeder write behavior was consolidated through shared create-only support:
  - `database/seeders/CreateOnly/Support/CreateOnlySeeder.php`
  - `database/seeders/CreateOnly/Support/CreateOnlyMasterSeeder.php`
- `CreateUserSeeder` now seeds:
  - `admin@gmail.com => admin`
  - `kasir@gmail.com => kasir`
- CreateOnly scenario seeders were refactored to use shared create-only helpers instead of scattered local insert helpers or raw write paths.

## REFERENCES

- `docs/04_lifecycle/error_log/2026-05-28_full_repo_audit/0002_seeder_role_contract.md`
- `docs/04_lifecycle/error_log/2026-05-28_full_repo_audit/9999_summary_matrix.md`
- `database/seeders/DatabaseSeeder.php`
- `database/seeders/CreateOnly/Support/CreateOnlySeeder.php`
- `database/seeders/CreateOnly/Support/CreateOnlyMasterSeeder.php`
- `database/seeders/CreateOnly/CreateUserSeeder.php`
- `database/seeders/CreateOnly/CreateMasterBasicSeeder.php`
- `database/seeders/CreateOnly/CreateMasterDenseWeekSeeder.php`
- `database/seeders/CreateOnly/CreateMasterDenseYearSeeder.php`
- `database/seeders/CreateOnly/CreateInventorySeeder.php`
- `database/seeders/CreateOnly/CreateSupplierProcurementSeeder.php`
- `database/seeders/CreateOnly/CreateSupplierPaymentSeeder.php`
- `database/seeders/CreateOnly/CreateOperationalExpenseSeeder.php`
- `database/seeders/CreateOnly/CreateEmployeeDebtSeeder.php`
- `database/seeders/CreateOnly/CreateEmployeeDebtPaymentSeeder.php`
- `database/seeders/CreateOnly/CreateEmployeeDebtAdjustmentSeeder.php`
- `database/seeders/CreateOnly/CreatePayrollDisbursementSeeder.php`
- `database/seeders/CreateOnly/CreateAdminCashierAreaAccessSeeder.php`
- `database/seeders/CreateOnly/CreateAuditBaselineSeeder.php`
- `tests/Feature/MobileApi/Auth/MobileApiAuthenticationFeatureTest.php`
- `tests/Feature/Auth/WebPageAccessFeatureTest.php`
- `tests/Feature/Seeder/ProductSeederIdempotencyFeatureTest.php`

## SCOPE-IN

- Fix active CreateOnly seeded role contract.
- Keep active `DatabaseSeeder` path on CreateOnly.
- Consolidate CreateOnly write behavior through shared create-only helpers.
- Prove fresh seed, idempotency, extended scenario seed, targeted auth, and full verify.

## SCOPE-OUT

- Do not restore product scenario seeders in this slice.
- Do not patch legacy `database/seeders/UserSeeder.php`.
- Do not claim product scenario idempotency tests are closed.
- Do not claim PostgreSQL production cutover readiness.
- Do not touch git from this handoff.

## DECISION

`0002_seeder_role_contract.md` is closed for current scope.

Final status:

`FIXED WITH EXTENDED SEED + FULL VERIFY PROOF`

## PROOF

### Static scan

Final static scan showed raw insert/update helpers only remained in:

- shared base `database/seeders/CreateOnly/Support/CreateOnlySeeder.php`
- Laravel entrypoint / base usage
- out-of-scope legacy `database/seeders/UserSeeder.php`

CreateOnly scenario seeders now route writes through shared create-only helpers.

### Fresh seed proof

Command:

php artisan migrate:fresh --seed --force

Proof:

Database\Seeders\CreateOnly\CreateUserSeeder RUNNING/DONE
Database\Seeders\CreateOnly\CreateMasterBasicSeeder RUNNING/DONE

Role proof:

admin@gmail.com => admin
kasir@gmail.com => kasir

Admin cashier area proof:

admin@gmail.com => active: 1
Idempotency proof

Command:

php artisan db:seed --force

Proof after second seed:

admin@gmail.com
kasir@gmail.com

Role proof stayed stable:

admin@gmail.com => admin
kasir@gmail.com => kasir
Extended scenario seed proof

Command:

make seed-create-all-v3

Proof:

supplier_invoices created=24
supplier_invoice_lines created=72
supplier_receipts created=24
supplier_receipt_lines created=72
supplier_payments created=24
supplier_payment_proof_attachments created=12
operational_expenses created=45
create-only employee debts: planned=6 created=6
create-only employee debt payments: planned_debts=4 created_debts=4 planned_payments=6 created_payments=6
create-only employee debt adjustments: planned_debts=3 created_debts=3 planned_adjustments=3 created_adjustments=3
create-only payroll disbursements: planned=6 created=6
Targeted auth regression

Commands:

php artisan test --filter=MobileApiAuthenticationFeatureTest
php artisan test --filter=WebPageAccessFeatureTest

Proof:

MobileApiAuthenticationFeatureTest: 7 passed (25 assertions)
WebPageAccessFeatureTest: 8 passed (20 assertions)
Full verify

Command:

make verify

Proof:

Tests: 2 skipped, 1118 passed (6285 assertions)
Duration: 76.37s
GAP
tests/Feature/Seeder/ProductSeederIdempotencyFeatureTest.php still contains 2 explicit skipped tests.
Product scenario seeders remain pending restoration under database/seeders/Product.
This is a separate follow-up and is not a blocker for 0002_seeder_role_contract.md.
NEXT

Recommended next audit item after this closed slice:

docs/04_lifecycle/error_log/2026-05-28_full_repo_audit/0003_route_security_boundary.md

Optional follow-up before or after route security:

Restore or formally retire product scenario seeders referenced by ProductSeederIdempotencyFeatureTest.
