<?php

declare(strict_types=1);

namespace App\Application\EmployeeFinance\UseCases;

use App\Core\EmployeeFinance\Payroll\DisbursementMode;
use App\Core\EmployeeFinance\Payroll\PayrollDisbursement;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\EmployeeFinance\EmployeeReaderPort;
use App\Ports\Out\EmployeeFinance\PayrollDisbursementWriterPort;
use App\Ports\Out\TransactionManagerPort;
use App\Ports\Out\UuidPort;
use DateTimeImmutable;
use InvalidArgumentException;
use Throwable;

class DisbursePayrollHandler
{
    public function __construct(
        private EmployeeReaderPort $employeeReader,
        private PayrollDisbursementWriterPort $payrollWriter,
        private UuidPort $uuidPort,
        private TransactionManagerPort $transactionManager
    ) {
    }

    public function handle(
        string $employeeId,
        int $amount,
        string $disbursementDateString,
        string $modeValue,
        ?string $notes = null
    ): string {
        $this->transactionManager->begin();

        try {
            // Validasi karyawan harus ada dan valid
            $employee = $this->employeeReader->findById($employeeId);
            if (!$employee) {
                throw new InvalidArgumentException("Karyawan tidak ditemukan.");
            }

            $id = $this->uuidPort->generate();
            $moneyAmount = Money::fromInt($amount);
            
            // Konversi input string (misal format '2026-03-25') menjadi object Date
            $disbursementDate = new DateTimeImmutable($disbursementDateString);
            $mode = DisbursementMode::from($modeValue);

            $payroll = PayrollDisbursement::disburse(
                $id,
                $employeeId,
                $moneyAmount,
                $disbursementDate,
                $mode,
                $notes
            );

            $this->payrollWriter->save($payroll);

            $this->transactionManager->commit();

            return $id;
        } catch (Throwable $e) {
            $this->transactionManager->rollBack();
            throw $e;
        }
    }
}
