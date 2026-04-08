<?php

declare(strict_types=1);

namespace App\Adapters\Out\ProductCatalog\Concerns;

use Illuminate\Support\Facades\DB;

trait ProductDetailVersionQueries
{
    private function createdVersion(string $productId): ?object
    {
        return DB::table('product_versions')
            ->where('product_id', $productId)
            ->where('event_name', 'product_created')
            ->orderBy('revision_no')
            ->first(['event_name', 'changed_at', 'snapshot_json']);
    }

    private function firstRecordedVersion(string $productId): ?object
    {
        return DB::table('product_versions')
            ->where('product_id', $productId)
            ->orderBy('revision_no')
            ->first(['event_name', 'changed_at', 'snapshot_json']);
    }

    public function getVersionTimeline(string $productId): array
    {
        return DB::table('product_versions')
            ->where('product_id', $productId)
            ->orderByDesc('revision_no')
            ->get(['revision_no', 'event_name', 'changed_at', 'changed_by_actor_id', 'change_reason', 'snapshot_json'])
            ->map(fn (object $row): array => [
                'revision_no' => (int) $row->revision_no,
                'event_name' => (string) $row->event_name,
                'changed_at' => (string) $row->changed_at,
                'changed_by_actor_id' => $row->changed_by_actor_id !== null ? (string) $row->changed_by_actor_id : null,
                'change_reason' => $row->change_reason !== null ? (string) $row->change_reason : null,
                'snapshot' => $this->decodeSnapshot((string) $row->snapshot_json),
            ])
            ->all();
    }
}
