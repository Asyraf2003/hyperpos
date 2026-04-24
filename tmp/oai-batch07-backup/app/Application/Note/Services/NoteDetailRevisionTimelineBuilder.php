<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Note\Revision\NoteRevision;

final class NoteDetailRevisionTimelineBuilder
{
    public function __construct(
        private readonly NoteRevisionTimelineSummaryBuilder $summaries,
    ) {
    }

    /**
     * @param list<NoteRevision> $timeline
     * @return array<string, mixed>
     */
    public function build(NoteRevision $current, array $timeline): array
    {
        $all = $this->indexRevisions(array_merge([$current], $timeline));
        $baselineRevision = $timeline === [] ? $current : $timeline[count($timeline) - 1];
        $currentParent = $this->findParentRevision($current, $all);

        return [
            'current' => [
                'revision_id' => $current->id(),
                'revision_number' => $current->revisionNumber(),
                'created_by_actor_id' => $current->createdByActorId(),
                'customer_name' => $current->customerName(),
                'customer_phone' => $current->customerPhone(),
                'transaction_date' => $current->transactionDate()->format('Y-m-d'),
                'grand_total_rupiah' => $current->grandTotalRupiah(),
                'line_count' => $current->lineCount(),
                'created_at' => $current->createdAt()->format('Y-m-d H:i:s'),
                'reason' => $current->reason(),
                'change_summary_lines' => $this->summaries->build($current, $currentParent),
            ],
            'baseline' => [
                'revision_id' => $baselineRevision->id(),
                'revision_number' => $baselineRevision->revisionNumber(),
                'customer_name' => $baselineRevision->customerName(),
                'customer_phone' => $baselineRevision->customerPhone(),
                'transaction_date' => $baselineRevision->transactionDate()->format('Y-m-d'),
                'grand_total_rupiah' => $baselineRevision->grandTotalRupiah(),
                'line_count' => $baselineRevision->lineCount(),
                'created_at' => $baselineRevision->createdAt()->format('Y-m-d H:i:s'),
                'reason' => $baselineRevision->reason(),
            ],
            'timeline' => array_map(
                fn (NoteRevision $revision): array => [
                    'revision_id' => $revision->id(),
                    'revision_number' => $revision->revisionNumber(),
                    'parent_revision_id' => $revision->parentRevisionId(),
                    'created_by_actor_id' => $revision->createdByActorId(),
                    'reason' => $revision->reason(),
                    'customer_name' => $revision->customerName(),
                    'customer_phone' => $revision->customerPhone(),
                    'transaction_date' => $revision->transactionDate()->format('Y-m-d'),
                    'grand_total_rupiah' => $revision->grandTotalRupiah(),
                    'line_count' => $revision->lineCount(),
                    'created_at' => $revision->createdAt()->format('Y-m-d H:i:s'),
                    'change_summary_lines' => $this->summaries->build(
                        $revision,
                        $this->findParentRevision($revision, $all),
                    ),
                ],
                $timeline
            ),
        ];
    }

    /**
     * @param array<string, NoteRevision> $all
     */
    private function findParentRevision(NoteRevision $revision, array $all): ?NoteRevision
    {
        $parentId = $revision->parentRevisionId();

        if ($parentId !== null && isset($all[$parentId])) {
            return $all[$parentId];
        }

        $fallback = null;

        foreach ($all as $candidate) {
            if ($candidate->id() === $revision->id()) {
                continue;
            }

            if ($candidate->revisionNumber() < $revision->revisionNumber()) {
                if ($fallback === null || $candidate->revisionNumber() > $fallback->revisionNumber()) {
                    $fallback = $candidate;
                }
            }
        }

        return $fallback;
    }

    /**
     * @param list<NoteRevision> $revisions
     * @return array<string, NoteRevision>
     */
    private function indexRevisions(array $revisions): array
    {
        $indexed = [];

        foreach ($revisions as $revision) {
            $indexed[$revision->id()] = $revision;
        }

        return $indexed;
    }
}
