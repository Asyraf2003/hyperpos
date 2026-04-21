<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

final class NotePseudoVersioningBuilder
{
    /**
     * @param array<string, mixed> $currentNote
     * @param array<string, mixed> $lineSummary
     * @param list<array<string, mixed>> $history
     * @return array<string, mixed>
     */
    public function build(array $currentNote, array $lineSummary, array $history): array
    {
        $timeline = array_map(
            static function (array $entry): array {
                return [
                    'event_label' => (string) ($entry['event_label'] ?? 'Mutasi Nota'),
                    'created_at' => (string) ($entry['created_at'] ?? '-'),
                    'reason' => $entry['reason'] ?? null,
                    'performed_by_actor_id' => $entry['performed_by_actor_id'] ?? null,
                    'target_status' => $entry['target_status'] ?? null,
                    'before_total_rupiah' => (int) ($entry['before_total_rupiah'] ?? 0),
                    'after_total_rupiah' => (int) ($entry['after_total_rupiah'] ?? 0),
                    'refund_required_rupiah' => (int) ($entry['refund_required_rupiah'] ?? 0),
                ];
            },
            $history,
        );

        $oldest = $timeline === [] ? null : $timeline[count($timeline) - 1];
        $baseline = null;

        if ($oldest !== null && (int) ($oldest['before_total_rupiah'] ?? 0) > 0) {
            $baseline = [
                'title' => 'Baseline Tersedia',
                'badge_label' => 'Pseudo Versioning',
                'badge_tone' => 'info',
                'note' => 'Baseline fase ini diturunkan dari snapshot sebelum mutasi paling awal yang tersedia. Ini bukan full initial note revision.',
                'total_rupiah' => (int) ($oldest['before_total_rupiah'] ?? 0),
                'refund_required_rupiah' => (int) ($oldest['refund_required_rupiah'] ?? 0),
                'target_status' => $oldest['target_status'] ?? null,
                'captured_at' => (string) ($oldest['created_at'] ?? '-'),
            ];
        }

        return [
            'current' => [
                'note_state' => (string) ($currentNote['note_state'] ?? '-'),
                'grand_total_rupiah' => (int) ($currentNote['grand_total_rupiah'] ?? 0),
                'net_paid_rupiah' => (int) ($currentNote['net_paid_rupiah'] ?? 0),
                'total_refunded_rupiah' => (int) ($currentNote['total_refunded_rupiah'] ?? 0),
                'outstanding_rupiah' => (int) ($currentNote['outstanding_rupiah'] ?? 0),
                'refund_required_rupiah' => (int) ($currentNote['refund_required_rupiah'] ?? 0),
                'line_summary_label' => (string) ($lineSummary['summary_label'] ?? 'Belum ada line.'),
            ],
            'baseline' => $baseline,
            'timeline' => $timeline,
        ];
    }
}
