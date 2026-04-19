<?php

declare(strict_types=1);

namespace App\Adapters\Out\Note;

use App\Ports\Out\Note\NoteMutationSnapshotWriterPort;
use Illuminate\Support\Facades\DB;

final class DatabaseNoteMutationSnapshotWriterAdapter implements NoteMutationSnapshotWriterPort
{
    public function createMany(array $snapshots): void
    {
        if ($snapshots === []) {
            return;
        }

        DB::table('note_mutation_snapshots')->insert(array_map(
            static fn ($snapshot): array => [
                'id' => $snapshot->id(),
                'note_mutation_event_id' => $snapshot->noteMutationEventId(),
                'snapshot_kind' => $snapshot->snapshotKind(),
                'payload_json' => $snapshot->payloadJson(),
                'created_at' => now()->format('Y-m-d H:i:s'),
            ],
            $snapshots
        ));
    }
}
