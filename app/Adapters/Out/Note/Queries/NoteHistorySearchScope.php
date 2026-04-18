<?php

declare(strict_types=1);

namespace App\Adapters\Out\Note\Queries;

use Illuminate\Database\Query\Builder;

final class NoteHistorySearchScope
{
    public function apply(Builder $query, string $search): void
    {
        if ($search === '') {
            return;
        }

        $query->where(function ($subQuery) use ($search): void {
            $subQuery->where('notes.id', 'like', '%' . $search . '%')
                ->orWhere('notes.customer_name', 'like', '%' . $search . '%')
                ->orWhere('notes.customer_phone', 'like', '%' . $search . '%');
        });
    }
}
