<?php

declare(strict_types=1);

namespace App\Application\EmployeeFinance\UseCases;

use App\Core\EmployeeFinance\EmployeeDebt\EmployeeDebt;
use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\AuditLogPort;
use App\Ports\Out\EmployeeFinance\EmployeeDebtAdjustmentWriterPort;
use App\Ports\Out\EmployeeFinance\EmployeeDebtReaderPort;
use App\Ports\Out\EmployeeFinance\EmployeeDebtWriterPort;
use App\Ports\Out\TransactionManagerPort;
use App\Ports\Out\UuidPort;
use InvalidArgumentException;
use Throwable;

final class AdjustEmployeeDebtPrincipalHandler
{
    public function __construct(
        private EmployeeDebtReaderPort $debtReader,
        private EmployeeDebtWriterPort $debtWriter,
        private EmployeeDebtAdjustmentWriterPort $adjustmentWriter,
        private AuditLogPort $auditLog,
        private UuidPort $uuidPort,
        private TransactionManagerPort $transactionManager,
    ) {
    }

    public function handle(string $debtId, string $type, int $amount, string $reason, string $actorId): string
    {
        if ($type !== 'increase') {
            throw new DomainException('Pengurangan hutang harus lewat pembayaran, bukan lewat halaman tambah hutang.');
        }

        if (trim($reason) === '') {
            throw new DomainException('Catatan penambahan hutang wajib diisi.');
        }

        $this->transactionManager->begin();

        try {
            $debt = $this->debtReader->findById($debtId);

            if ($debt === null) {
                throw new InvalidArgumentException('Data hutang karyawan tidak ditemukan.');
            }

            $before = $this->snapshot($debt);

            $debt->adjustPrincipal($type, Money::fromInt($amount));
            $this->debtWriter->save($debt);

            $after = $this->snapshot($debt);
            $adjustmentId = $this->uuidPort->generate();

            $this->adjustmentWriter->record([
                'id' => $adjustmentId,
                'employee_debt_id' => $debtId,
                'adjustment_type' => $type,
                'amount' => $amount,
                'reason' => $reason,
                'performed_by_actor_id' => $actorId,
                'before_total_debt' => $before['total_debt'],
                'after_total_debt' => $after['total_debt'],
                'before_remaining_balance' => $before['remaining_balance'],
                'after_remaining_balance' => $after['remaining_balance'],
            ]);

            $this->auditLog->record('employee_debt_principal_adjusted', [
                'employee_debt_id' => $debtId,
                'adjustment_id' => $adjustmentId,
                'adjustment_type' => $type,
                'amount' => $amount,
                'reason' => $reason,
                'performed_by_actor_id' => $actorId,
                'before' => $before,
                'after' => $after,
            ]);

            $this->transactionManager->commit();

            return $adjustmentId;
        } catch (Throwable $e) {
            $this->transactionManager->rollBack();
            throw $e;
        }
    }

    private function snapshot(EmployeeDebt $debt): array
    {
        return [
            'total_debt' => $debt->getTotalDebt()->amount(),
            'remaining_balance' => $debt->getRemainingBalance()->amount(),
            'status' => $debt->getStatus()->value,
        ];
    }
}
