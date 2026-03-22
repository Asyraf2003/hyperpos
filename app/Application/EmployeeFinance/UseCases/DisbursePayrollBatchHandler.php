<?php

declare(strict_types=1);

namespace App\Application\EmployeeFinance\UseCases;

use App\Application\Shared\DTO\Result;
use App\Core\EmployeeFinance\Payroll\DisbursementMode;
use App\Ports\Out\AuditLogPort;
use App\Ports\Out\TransactionManagerPort;
use App\Ports\Out\UuidPort;
use DateTimeImmutable;
use Throwable;

final class DisbursePayrollBatchHandler
{
    public function __construct(
        private PayrollBatchRowProcessor $rowProcessor,
        private AuditLogPort $auditLog,
        private UuidPort $uuidPort,
        private TransactionManagerPort $transactionManager,
    ) {
    }

    public function handle(
        string $performedByActorId,
        string $disbursementDateString,
        string $defaultModeValue,
        ?string $defaultNotes,
        array $rows,
    ): Result {
        if ($rows === []) {
            return Result::failure('Batch payroll minimal berisi satu baris.', ['payroll_batch' => ['BATCH_ROWS_REQUIRED']]);
        }

        $this->transactionManager->begin();

        try {
            $batchId = $this->uuidPort->generate();
            $date = new DateTimeImmutable($disbursementDateString);
            $defaultMode = DisbursementMode::from($defaultModeValue);
            $summary = ['payroll_ids' => [], 'employee_ids' => [], 'total_amount' => 0];

            foreach ($rows as $index => $row) {
                $processed = $this->rowProcessor->process(
                    $batchId,
                    $performedByActorId,
                    $date,
                    $defaultMode,
                    $defaultNotes,
                    $row,
                    $index,
                );

                if ($processed['error'] === true) {
                    $this->transactionManager->rollBack();
                    return $processed['result'];
                }

                $summary['payroll_ids'][] = $processed['payroll_id'];
                $summary['employee_ids'][] = $processed['employee_id'];
                $summary['total_amount'] += $processed['amount'];
            }

            $this->recordBatchAudit($batchId, $performedByActorId, $date, $defaultMode, $rows, $summary);
            $this->transactionManager->commit();

            return Result::success([
                'batch_id' => $batchId,
                'row_count' => count($rows),
                'payroll_ids' => $summary['payroll_ids'],
                'total_amount' => $summary['total_amount'],
            ], 'Batch payroll berhasil dicatat.');
        } catch (Throwable $e) {
            $this->transactionManager->rollBack();
            throw $e;
        }
    }

    private function recordBatchAudit(
        string $batchId,
        string $performedByActorId,
        DateTimeImmutable $date,
        DisbursementMode $defaultMode,
        array $rows,
        array $summary,
    ): void {
        $this->auditLog->record('payroll_batch_disbursement_recorded', [
            'batch_id' => $batchId,
            'performed_by_actor_id' => $performedByActorId,
            'disbursement_date' => $date->format('Y-m-d'),
            'default_mode' => $defaultMode->value,
            'row_count' => count($rows),
            'total_amount' => $summary['total_amount'],
            'employee_ids' => $summary['employee_ids'],
            'payroll_ids' => $summary['payroll_ids'],
        ]);
    }
}
