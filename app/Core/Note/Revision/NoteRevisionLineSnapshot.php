<?php

declare(strict_types=1);

namespace App\Core\Note\Revision;

use App\Core\Note\Revision\Concerns\NoteRevisionLineSnapshotAccessors;
use App\Core\Note\Revision\Concerns\NoteRevisionLineSnapshotValidation;

final class NoteRevisionLineSnapshot
{
    use NoteRevisionLineSnapshotAccessors;
    use NoteRevisionLineSnapshotValidation;

    /**
     * @param array<string, mixed> $payload
     */
    private function __construct(
        private string $id,
        private string $noteRevisionId,
        private ?string $workItemRootId,
        private int $lineNo,
        private string $transactionType,
        private string $status,
        private int $subtotalRupiah,
        private ?string $serviceLabel,
        private ?int $servicePriceRupiah,
        private array $payload,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function create(
        string $id,
        string $noteRevisionId,
        ?string $workItemRootId,
        int $lineNo,
        string $transactionType,
        string $status,
        int $subtotalRupiah,
        ?string $serviceLabel = null,
        ?int $servicePriceRupiah = null,
        array $payload = [],
    ): self {
        $id = trim($id);
        $noteRevisionId = trim($noteRevisionId);
        $transactionType = trim($transactionType);
        $status = trim($status);

        self::assertValidState(
            $id,
            $noteRevisionId,
            $lineNo,
            $transactionType,
            $status,
            $subtotalRupiah,
            $servicePriceRupiah,
        );

        return new self(
            $id,
            $noteRevisionId,
            $workItemRootId !== null && trim($workItemRootId) !== '' ? trim($workItemRootId) : null,
            $lineNo,
            $transactionType,
            $status,
            $subtotalRupiah,
            $serviceLabel !== null && trim($serviceLabel) !== '' ? trim($serviceLabel) : null,
            $servicePriceRupiah,
            $payload,
        );
    }
}
