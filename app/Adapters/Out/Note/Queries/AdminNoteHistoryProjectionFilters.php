<?php

declare(strict_types=1);

namespace App\Adapters\Out\Note\Queries;

use App\Application\Note\Services\WorkItemOperationalStatusResolver;
use Illuminate\Database\Query\Builder;

final class AdminNoteHistoryProjectionFilters
{
    public function applySearch(Builder $query, string $search): Builder
    {
        if ($search === '') {
            return $query;
        }

        $normalizedSearch = mb_strtolower(trim($search), 'UTF-8');

        return $query->where(function (Builder $builder) use ($search, $normalizedSearch): void {
            $builder
                ->where('note_id', 'like', '%' . $search . '%')
                ->orWhere('customer_name', 'like', '%' . $search . '%')
                ->orWhere('customer_name_normalized', 'like', '%' . $normalizedSearch . '%')
                ->orWhere('customer_phone', 'like', '%' . $search . '%');
        });
    }

    public function applyLineStatusFilter(Builder $query, string $lineStatus): Builder
    {
        return match ($lineStatus) {
            WorkItemOperationalStatusResolver::STATUS_OPEN => $query->where('has_open_lines', true),
            WorkItemOperationalStatusResolver::STATUS_CLOSE => $query->where('has_close_lines', true),
            WorkItemOperationalStatusResolver::STATUS_REFUND => $query->where('has_refund_lines', true),
            default => $query,
        };
    }
}
