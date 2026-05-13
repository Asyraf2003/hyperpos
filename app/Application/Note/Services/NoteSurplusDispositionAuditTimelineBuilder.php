<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Ports\Out\Note\NoteSurplusDispositionAuditTimelineReaderPort;

final class NoteSurplusDispositionAuditTimelineBuilder
{
    public function __construct(
        private readonly NoteSurplusDispositionAuditTimelineReaderPort $timeline,
        private readonly NoteSurplusDispositionAuditTimelineRowMapper $mapper,
    ) {
    }

    /**
     * @return list<array{
     *   label:string,
     *   remaining_label:string,
     *   remaining_rupiah:int,
     *   event_name:string,
     *   disposition_id:string,
     *   refund_payment_id:?string,
     *   note_revision_settlement_id:string,
     *   note_revision_id:string,
     *   disposition_type:string,
     *   amount_rupiah:int,
     *   before_pending_rupiah:int,
     *   after_pending_rupiah:int,
     *   refund_due_rupiah:int,
     *   active_refund_paid_rupiah:int,
     *   remaining_refund_due_rupiah:int,
     *   effective_date:?string,
     *   actor_id:?string,
     *   actor_role:?string,
     *   reason:?string,
     *   occurred_at:string
     * }>
     */
    public function build(string $noteRootId): array
    {
        return array_map(
            fn (array $row): array => $this->mapper->map($row),
            $this->timeline->findSurplusAuditEventsByNoteRootId($noteRootId),
        );
    }
}
