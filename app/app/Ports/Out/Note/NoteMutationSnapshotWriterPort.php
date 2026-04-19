<?php

declare(strict_types=1);

namespace App\Ports\Out\Note;

use App\Core\Note\Mutation\NoteMutationSnapshot;

interface NoteMutationSnapshotWriterPort
{
    /**
     * @param list<NoteMutationSnapshot> $snapshots
     */
    public function createMany(array $snapshots): void;
}
