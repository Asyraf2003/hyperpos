<?php

declare(strict_types=1);

namespace App\Ports\Out;

use Illuminate\Pagination\LengthAwarePaginator;

interface AuditLogReaderPort
{
    /**
     * @return list<array{event:string,context:array<string,mixed>,created_at:string}>
     */
    public function findLatestNoteCorrections(string $noteId, int $limit = 10): array;

    /**
     * @return LengthAwarePaginator<int, array{id:string,source:string,event:string,reason:string,actor_id:?string,actor_role:?string,entity_type:?string,entity_id:?string,bounded_context:?string,context:array<string,mixed>,context_json:string,created_at:string}>
     */
    public function listForAdmin(string $search = '', int $perPage = 20): LengthAwarePaginator;
}
