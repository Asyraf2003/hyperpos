<?php

declare(strict_types=1);

namespace App\Adapters\Out\EmployeeFinance;

use Illuminate\Support\Facades\DB;

final class DatabaseEmployeeDetailPageQuery
{
    public function __construct(
        private EmployeeDetailCurrentIdentityMapper $currentIdentityMapper,
        private EmployeeDetailVersionIdentityMapper $versionIdentityMapper,
        private EmployeeDetailInitialIdentityMetaFactory $initialIdentityMetaFactory,
        private EmployeeDetailTimelineEntryMapper $timelineEntryMapper,
        private EmployeeDetailVersionLookup $versionLookup,
    ) {
    }

    public function findById(string $employeeId): ?array
    {
        $row = DB::table('employees')
            ->select([
                'id',
                'employee_name',
                'phone',
                'salary_basis_type',
                'default_salary_amount',
                'employment_status',
                'started_at',
                'ended_at',
            ])
            ->where('id', $employeeId)
            ->first();

        if ($row === null) {
            return null;
        }

        $latestUnpaidDebtId = $this->latestUnpaidDebtId($employeeId);
        $versionRows = $this->versionLookup->timelineRows($employeeId);
        $createdVersion = $this->versionLookup->createdVersion($employeeId);
        $firstRecordedVersion = $this->versionLookup->firstRecordedVersion($employeeId);
        $currentIdentity = $this->currentIdentityMapper->map($row);
        $currentIdentity['latest_unpaid_debt_id'] = $latestUnpaidDebtId;

        $initialSource = $createdVersion ?? $firstRecordedVersion;
        $initialIdentity = $initialSource === null ? null : $this->versionIdentityMapper->map($initialSource);
        $initialIdentitySource = $this->initialIdentityMetaFactory->sourceLabel($createdVersion, $firstRecordedVersion);

        return [
            'summary' => $currentIdentity,
            'page' => [
                'heading' => 'Profil saat ini dan riwayat versi karyawan',
                'current_identity' => $currentIdentity,
                'initial_identity' => $initialIdentity,
                'initial_identity_source' => $initialIdentitySource,
                'initial_identity_meta' => $this->initialIdentityMetaFactory->meta($initialIdentitySource),
                'timeline' => $versionRows
                    ->map(fn (object $version): array => $this->timelineEntryMapper->map($version))
                    ->values()
                    ->all(),
            ],
        ];
    }

    private function latestUnpaidDebtId(string $employeeId): ?string
    {
        $debtId = DB::table('employee_debts')
            ->where('employee_id', $employeeId)
            ->where('status', 'unpaid')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->value('id');

        return $debtId !== null ? (string) $debtId : null;
    }
}
