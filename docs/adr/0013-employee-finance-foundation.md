# ADR-013: Employee Finance Foundation

## Status
Proposed

## Context
Sistem membutuhkan pengelolaan SDM yang mencakup data identitas, penggajian manual, dan pengelolaan hutang sesuai Blueprint 1.3 (7).

## Decision
1. **Entitas Employee**:
   - `id`: UUID
   - `name`: String (max 100)
   - `phone_number`: String (format internasional, unique)
   - `default_salary_mode`: Enum (DAILY, WEEKLY, MONTHLY, MANUAL)
   - `join_date`: Date
   - `is_active`: Boolean

2. **Entitas PayrollEntry**:
   - Mencatat gaji yang dibayarkan secara manual.
   - Field: `id`, `employee_id`, `amount` (int), `period_start`, `period_end`, `payout_date`, `note`.

3. **Entitas EmployeeDebt & Payment**:
   - Hutang dan pembayaran dipisah untuk audit trail.
   - Hutang: `id`, `employee_id`, `amount`, `reason`, `date`.
   - Pembayaran: `id`, `debt_id`, `amount`, `date`, `source` (CASH/PAYROLL_DEDUCTION).

4. **Integrasi Keuangan**:
   - Pembayaran gaji tidak otomatis memotong stok atau berhubungan dengan inventory.
   - Semua angka adalah integer Rupiah.

## Consequences
- Admin harus menginput data karyawan secara manual sebelum bisa mencatat gaji/hutang.
- Memungkinkan tracking hutang karyawan yang presisi dan histori penggajian yang rapi.
