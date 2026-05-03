<?php

declare(strict_types=1);

namespace App\Ports\Out\EmployeeFinance;

interface EmployeeDebtPaymentReversalWriterPort
{
    /**
     * @return array{
     *   employee_debt_payment_id:string,
     *   employee_debt_id:string,
     *   employee_id:string,
     *   amount:int,
     *   payment_date:string,
     *   notes:?string,
     *   total_debt:int,
     *   remaining_balance:int,
     *   status:string
     * }|null
     */
    public function findPaymentSnapshotForReversal(string $paymentId): ?array;

    public function paymentAlreadyReversed(string $paymentId): bool;

    public function updateDebtAfterPaymentReversal(
        string $employeeDebtId,
        int $remainingBalance,
        string $status
    ): void;

    /**
     * @param array{
     *   id:string,
     *   employee_debt_payment_id:string,
     *   reason:string,
     *   performed_by_actor_id:string
     * } $record
     */
    public function record(array $record): void;
}
