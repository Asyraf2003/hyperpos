<?php

declare(strict_types=1);

namespace App\Adapters\Out\EmployeeFinance;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class DatabaseEmployeeDetailPageQuery
{
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

        $currentIdentity = $this->identityFromCurrentRow($row);
        $initialSource = $createdVersion ?? $firstRecordedVersion;
        $initialIdentity = $initialSource !== null ? $this->identityFromVersionRow($initialSource) : null;
        $initialIdentitySource = $this->initialIdentitySourceLabel($createdVersion, $firstRecordedVersion);

        return [
            'summary' => $currentIdentity,
            'page' => [
                'heading' => 'Ringkasan Karyawan',
                'subtitle' => 'Profil saat ini dan riwayat versi karyawan.',
                'current_identity' => $currentIdentity,
                'initial_identity' => $initialIdentity,
                'initial_identity_source' => $initialIdentitySource,
                'initial_identity_meta' => $this->initialIdentityMeta($initialIdentitySource),
                'timeline' => $versionRows
                    ->map(fn (object $version): array => $this->timelineEntry($version))
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

    private function identityFromCurrentRow(object $row): array
    {
        $defaultSalaryAmount = $row->default_salary_amount !== null ? (int) $row->default_salary_amount : null;
        $salaryBasisType = (string) $row->salary_basis_type;
        $employmentStatus = (string) $row->employment_status;

        return [
            'id' => (string) $row->id,
            'employee_name' => (string) $row->employee_name,
            'phone' => $row->phone !== null ? (string) $row->phone : null,
            'salary_basis_type' => $salaryBasisType,
            'salary_basis_label' => $this->salaryBasisLabel($salaryBasisType),
            'default_salary_amount' => $defaultSalaryAmount,
            'default_salary_amount_formatted' => $defaultSalaryAmount !== null
                ? number_format($defaultSalaryAmount, 0, ',', '.')
                : null,
            'default_salary_amount_label' => $defaultSalaryAmount !== null
                ? 'Rp'.number_format($defaultSalaryAmount, 0, ',', '.')
                : '-',
            'employment_status' => $employmentStatus,
            'employment_status_label' => $this->employmentStatusLabel($employmentStatus),
            'started_at' => $row->started_at !== null ? (string) $row->started_at : null,
            'ended_at' => $row->ended_at !== null ? (string) $row->ended_at : null,
        ];
    }

    private function identityFromVersionRow(object $row): array
    {
        $snapshot = $this->decodeSnapshot((string) $row->snapshot_json);
        $defaultSalaryAmount = isset($snapshot['default_salary_amount']) && $snapshot['default_salary_amount'] !== null
            ? (int) $snapshot['default_salary_amount']
            : null;
        $salaryBasisType = (string) ($snapshot['salary_basis_type'] ?? 'manual');
        $employmentStatus = (string) ($snapshot['employment_status'] ?? 'active');

        return [
            'employee_name' => (string) ($snapshot['employee_name'] ?? '-'),
            'phone' => isset($snapshot['phone']) && $snapshot['phone'] !== null ? (string) $snapshot['phone'] : null,
            'salary_basis_type' => $salaryBasisType,
            'salary_basis_label' => $this->salaryBasisLabel($salaryBasisType),
            'default_salary_amount' => $defaultSalaryAmount,
            'default_salary_amount_formatted' => $defaultSalaryAmount !== null
                ? number_format($defaultSalaryAmount, 0, ',', '.')
                : null,
            'default_salary_amount_label' => $defaultSalaryAmount !== null
                ? 'Rp'.number_format($defaultSalaryAmount, 0, ',', '.')
                : '-',
            'employment_status' => $employmentStatus,
            'employment_status_label' => $this->employmentStatusLabel($employmentStatus),
            'started_at' => isset($snapshot['started_at']) && $snapshot['started_at'] !== null ? (string) $snapshot['started_at'] : null,
            'ended_at' => isset($snapshot['ended_at']) && $snapshot['ended_at'] !== null ? (string) $snapshot['ended_at'] : null,
            'changed_at' => Carbon::parse((string) $row->changed_at)->format('Y-m-d H:i'),
        ];
    }

    private function initialIdentitySourceLabel(?object $createdVersion, ?object $firstRecordedVersion): string
    {
        if ($createdVersion !== null) {
            return 'created_version';
        }

        if ($firstRecordedVersion !== null) {
            return 'first_recorded_version';
        }

        return 'unavailable';
    }

    private function initialIdentityMeta(string $source): array
    {
        return match ($source) {
            'created_version' => [
                'title' => 'Identitas Awal',
                'badge_tone' => 'info',
                'badge_label' => 'Versi Awal',
                'note' => null,
                'show_values' => true,
            ],
            'first_recorded_version' => [
                'title' => 'Identitas Awal',
                'badge_tone' => 'warning',
                'badge_label' => 'Versi Tercatat Pertama',
                'note' => 'Data awal resmi tidak tersedia. Yang ditampilkan adalah versi pertama yang berhasil terekam di histori.',
                'show_values' => true,
            ],
            default => [
                'title' => 'Identitas Awal',
                'badge_tone' => 'secondary',
                'badge_label' => 'Tidak Tersedia',
                'note' => 'Riwayat awal karyawan belum tersedia.',
                'show_values' => false,
            ],
        };
    }

    private function timelineEntry(object $row): array
    {
        $snapshot = $this->decodeSnapshot((string) $row->snapshot_json);
        $defaultSalaryAmount = isset($snapshot['default_salary_amount']) && $snapshot['default_salary_amount'] !== null
            ? (int) $snapshot['default_salary_amount']
            : null;
        $salaryBasisType = (string) ($snapshot['salary_basis_type'] ?? 'manual');
        $employmentStatus = (string) ($snapshot['employment_status'] ?? 'active');

        return [
            'revision_label' => 'Revisi '.(int) $row->revision_no,
            'event_name' => $this->eventLabel((string) $row->event_name),
            'changed_at' => Carbon::parse((string) $row->changed_at)->format('Y-m-d H:i'),
            'actor_label' => $row->changed_by_actor_id !== null ? 'Actor '.$row->changed_by_actor_id : null,
            'reason_label' => $row->change_reason !== null ? (string) $row->change_reason : null,
            'snapshot' => [
                'employee_name' => (string) ($snapshot['employee_name'] ?? '-'),
                'phone' => isset($snapshot['phone']) && $snapshot['phone'] !== null ? (string) $snapshot['phone'] : '-',
                'salary_basis_label' => $this->salaryBasisLabel($salaryBasisType),
                'default_salary_amount_label' => $defaultSalaryAmount !== null
                    ? 'Rp'.number_format($defaultSalaryAmount, 0, ',', '.')
                    : '-',
                'employment_status_label' => $this->employmentStatusLabel($employmentStatus),
                'started_at' => isset($snapshot['started_at']) && $snapshot['started_at'] !== null ? (string) $snapshot['started_at'] : '-',
                'ended_at' => isset($snapshot['ended_at']) && $snapshot['ended_at'] !== null ? (string) $snapshot['ended_at'] : '-',
            ],
        ];
    }

    private function decodeSnapshot(string $json): array
    {
        $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        return is_array($decoded) ? $decoded : [];
    }

    private function salaryBasisLabel(string $value): string
    {
        return match ($value) {
            'daily' => 'Harian',
            'weekly' => 'Mingguan',
            'monthly' => 'Bulanan',
            'manual' => 'Manual',
            default => ucfirst($value),
        };
    }

    private function employmentStatusLabel(string $value): string
    {
        return match ($value) {
            'active' => 'Aktif',
            'inactive' => 'Nonaktif',
            default => ucfirst($value),
        };
    }

    private function eventLabel(string $value): string
    {
        return match ($value) {
            'employee_created' => 'Karyawan dibuat',
            'employee_updated' => 'Profil diperbarui',
            'employee_deactivated' => 'Karyawan dinonaktifkan',
            'employee_reactivated' => 'Karyawan diaktifkan kembali',
            default => ucfirst(str_replace('_', ' ', $value)),
        };
    }
}
