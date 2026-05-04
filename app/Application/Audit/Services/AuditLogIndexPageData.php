<?php

declare(strict_types=1);

namespace App\Application\Audit\Services;

use App\Ports\Out\AuditLogReaderPort;
use App\Ports\Out\Shared\PaginatedResult;

final class AuditLogIndexPageData
{
    public function __construct(
        private readonly AuditLogReaderPort $reader,
    ) {
    }

    /**
     * @return PaginatedResult<array{id:string,source:string,event:string,reason:string,actor_id:?string,actor_role:?string,entity_type:?string,entity_id:?string,bounded_context:?string,context:array<string,mixed>,context_json:string,created_at:string}>
     */
    public function listForAdmin(string $search = '', int $perPage = 20): PaginatedResult
    {
        return $this->reader->listForAdmin($search, $perPage);
    }
}
