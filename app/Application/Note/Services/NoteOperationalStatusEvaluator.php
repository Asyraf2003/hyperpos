<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Note\Note\NoteOperationalStatusPolicy;

final class NoteOperationalStatusEvaluator
{
    public const STATUS_OPEN = NoteOperationalStatusPolicy::STATUS_OPEN;
    public const STATUS_CLOSE = NoteOperationalStatusPolicy::STATUS_CLOSE;

    private NoteOperationalStatusPolicy $policy;

    public function __construct(?NoteOperationalStatusPolicy $policy = null)
    {
        $this->policy = $policy ?? new NoteOperationalStatusPolicy();
    }

    public function resolve(int $grandTotalRupiah, int $netPaidRupiah): string
    {
        return $this->policy->resolve($grandTotalRupiah, $netPaidRupiah);
    }

    public function isOpen(int $grandTotalRupiah, int $netPaidRupiah): bool
    {
        return $this->policy->isOpen($grandTotalRupiah, $netPaidRupiah);
    }

    public function isClose(int $grandTotalRupiah, int $netPaidRupiah): bool
    {
        return $this->policy->isClose($grandTotalRupiah, $netPaidRupiah);
    }
}
