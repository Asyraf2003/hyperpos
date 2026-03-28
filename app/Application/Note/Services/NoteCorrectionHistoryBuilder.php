<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Ports\Out\AuditLogReaderPort;

final class NoteCorrectionHistoryBuilder
{
    public function __construct(
        private readonly AuditLogReaderPort $audits,
    ) {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function build(string $noteId): array
    {
        return array_map(
            fn (array $entry): array => $this->mapEntry($entry),
            $this->audits->findLatestNoteCorrections($noteId),
        );
    }

    /**
     * @param array{event:string,context:array<string,mixed>,created_at:string} $entry
     * @return array<string, mixed>
     */
    private function mapEntry(array $entry): array
    {
        $context = $entry['context'];
        $before = is_array($context['before']['note'] ?? null) ? $context['before']['note'] : [];
        $after = is_array($context['after']['note'] ?? null) ? $context['after']['note'] : [];

        return [
            'event_label' => $entry['event'] === 'paid_service_only_work_item_corrected'
                ? 'Correction Nominal Service'
                : 'Correction Status Work Item',
            'created_at' => $entry['created_at'],
            'reason' => $context['reason'] ?? null,
            'performed_by_actor_id' => $context['performed_by_actor_id'] ?? null,
            'target_status' => $context['target_status'] ?? null,
            'refund_required_rupiah' => $context['refund_required_rupiah'] ?? null,
            'before_total_rupiah' => $before['total_rupiah'] ?? null,
            'after_total_rupiah' => $after['total_rupiah'] ?? null,
        ];
    }
}
