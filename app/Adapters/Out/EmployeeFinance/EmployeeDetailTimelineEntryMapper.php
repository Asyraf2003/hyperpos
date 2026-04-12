<?php

declare(strict_types=1);

namespace App\Adapters\Out\EmployeeFinance;

use Illuminate\Support\Carbon;

final class EmployeeDetailTimelineEntryMapper
{
    public function __construct(
        private EmployeeDetailSnapshotReader $snapshotReader,
        private EmployeeDetailLabelFormatter $labelFormatter,
    ) {
    }

    public function map(object $row): array
    {
        $snapshot = $this->snapshotReader->decode((string) $row->snapshot_json);
        $defaultSalaryAmount = $this->snapshotReader->nullableInt($snapshot, 'default_salary_amount');
        $salaryBasisType = (string) ($snapshot['salary_basis_type'] ?? 'manual');
        $employmentStatus = (string) ($snapshot['employment_status'] ?? 'active');
        $changedByActorId = $row->changed_by_actor_id ?? null;
        $changeReason = $row->change_reason ?? null;

        return [
            'revision_label' => 'Revisi '.(int) $row->revision_no,
            'event_name' => $this->labelFormatter->event((string) $row->event_name),
            'changed_at' => Carbon::parse((string) $row->changed_at)->format('Y-m-d H:i'),
            'actor_label' => $changedByActorId === null ? null : 'Actor '.(string) $changedByActorId,
            'reason_label' => $changeReason === null ? null : (string) $changeReason,
            'snapshot' => [
                'employee_name' => (string) ($snapshot['employee_name'] ?? '-'),
                'phone' => $this->snapshotReader->stringOr($snapshot, 'phone', '-'),
                'salary_basis_label' => $this->labelFormatter->salaryBasis($salaryBasisType),
                'default_salary_amount_label' => $defaultSalaryAmount === null ? '-' : 'Rp'.number_format($defaultSalaryAmount, 0, ',', '.'),
                'employment_status_label' => $this->labelFormatter->employmentStatus($employmentStatus),
                'started_at' => $this->snapshotReader->stringOr($snapshot, 'started_at', '-'),
                'ended_at' => $this->snapshotReader->stringOr($snapshot, 'ended_at', '-'),
            ],
        ];
    }
}
