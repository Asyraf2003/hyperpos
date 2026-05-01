<?php

declare(strict_types=1);

namespace App\Adapters\Out\Audit;

final class AuditLogAdminEntrySorter
{
    /**
     * @param list<array{id:string,source:string,event:string,reason:string,actor_id:?string,actor_role:?string,entity_type:?string,entity_id:?string,bounded_context:?string,context:array<string,mixed>,context_json:string,created_at:string}> $entries
     */
    public function sortNewestFirst(array &$entries): void
    {
        usort($entries, static function (array $left, array $right): int {
            $time = strcmp($right['created_at'], $left['created_at']);

            if ($time !== 0) {
                return $time;
            }

            return strcmp(
                $right['source'] . ':' . $right['id'],
                $left['source'] . ':' . $left['id'],
            );
        });
    }
}
