<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Note\Revision\NoteRevision;

final class NoteRevisionTimelineParentResolver
{
    /**
     * @param array<string, NoteRevision> $all
     */
    public function resolve(NoteRevision $revision, array $all): ?NoteRevision
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
}
