<?php

declare(strict_types=1);

namespace App\Ports\Out\EmployeeFinance;

interface PayrollDisbursementReversalWriterPort
{
    /**
     * @return array{
     *   id:string,
     *   employee_id:string,
     *   amount:int,
     *   disbursement_date:string,
     *   mode:string,
     *   notes:?string
     * }|null
     */
    public function findPayrollSnapshotForReversal(string $payrollId): ?array;

    public function payrollAlreadyReversed(string $payrollId): bool;

    /**
     * @param array{
     *   id:string,
     *   payroll_disbursement_id:string,
     *   reason:string,
     *   performed_by_actor_id:string
     * } $record
     */
    public function record(array $record): void;
}
