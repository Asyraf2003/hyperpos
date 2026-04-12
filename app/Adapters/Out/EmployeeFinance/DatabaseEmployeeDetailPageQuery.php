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

        $versionRows = DB::table('employee_versions')
            ->select([
                'id',
                'revision_no',
                'event_name',
                'changed_by_actor_id',
                'change_reason',
                'changed_at',
                'snapshot_json',
            ])
            ->where('employee_id', $employeeId)
            ->orderByDesc('revision_no')
            ->get();

        $createdVersion = $this->createdVersion($employeeId);
        $firstRecordedVersion = $this->firstRecordedVersion($employeeId);
        $currentIdentity = $this->currentIdentityMapper->map($row);
        $initialSource = $createdVersion ?? $firstRecordedVersion;
        $initialIdentity = $initialSource === null ? null : $this->versionIdentityMapper->map($initialSource);
        $initialIdentitySource = $this->initialIdentityMetaFactory->sourceLabel($createdVersion, $firstRecordedVersion);

        return [
            'summary' => $currentIdentity,
            'page' => [
                'heading' => 'Ringkasan Karyawan',
                'subtitle' => 'Profil saat ini dan riwayat versi karyawan.',
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

    private function createdVersion(string $employeeId): ?object
    {
        return DB::table('employee_versions')
            ->where('employee_id', $employeeId)
            ->where('event_name', 'employee_created')
            ->orderBy('revision_no')
            ->first([
                'revision_no',
                'event_name',
                'changed_by_actor_id',
                'change_reason',
                'changed_at',
                'snapshot_json',
            ]);
    }

    private function firstRecordedVersion(string $employeeId): ?object
    {
        return DB::table('employee_versions')
            ->where('employee_id', $employeeId)
            ->orderBy('revision_no')
            ->first([
                'revision_no',
                'event_name',
                'changed_by_actor_id',
                'change_reason',
                'changed_at',
                'snapshot_json',
            ]);
    }
}
