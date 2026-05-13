<?php

declare(strict_types=1);

namespace App\Application\Audit\DTO;

use InvalidArgumentException;

final class AuditEventWriteSnapshotValidator
{
    /**
     * @param list<mixed> $snapshots
     * @return list<AuditEventSnapshotWrite>
     */
    public function validate(array $snapshots): array
    {
        $seen = [];

        foreach ($snapshots as $snapshot) {
            if (! $snapshot instanceof AuditEventSnapshotWrite) {
                throw new InvalidArgumentException('snapshots must contain AuditEventSnapshotWrite only.');
            }

            $kind = $snapshot->kind();

            if (isset($seen[$kind])) {
                throw new InvalidArgumentException('duplicate snapshot_kind: ' . $kind);
            }

            $seen[$kind] = true;
        }

        return array_values($snapshots);
    }
}
