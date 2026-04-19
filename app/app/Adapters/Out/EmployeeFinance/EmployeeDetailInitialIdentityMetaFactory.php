<?php

declare(strict_types=1);

namespace App\Adapters\Out\EmployeeFinance;

final class EmployeeDetailInitialIdentityMetaFactory
{
    public function sourceLabel(?object $createdVersion, ?object $firstRecordedVersion): string
    {
        if ($createdVersion !== null) {
            return 'created_version';
        }

        if ($firstRecordedVersion !== null) {
            return 'first_recorded_version';
        }

        return 'unavailable';
    }

    public function meta(string $source): array
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
}
