<?php

declare(strict_types=1);

namespace App\Ports\Out\EmployeeFinance;

interface EmployeeDebtPaymentReversalWriterPort
{
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
