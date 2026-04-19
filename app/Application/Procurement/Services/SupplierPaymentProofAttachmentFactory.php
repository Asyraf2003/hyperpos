<?php

declare(strict_types=1);

namespace App\Application\Procurement\Services;

use App\Core\Procurement\SupplierPaymentProofAttachment\SupplierPaymentProofAttachment;
use App\Ports\Out\ClockPort;
use App\Ports\Out\UuidPort;

final class SupplierPaymentProofAttachmentFactory
{
    public function __construct(
        private readonly UuidPort $uuid,
        private readonly ClockPort $clock,
    ) {
    }

    /**
     * @param list<array{
     * storage_path:string,
     * original_filename:string,
     * mime_type:string,
     * file_size_bytes:int
     * }> $proofFiles
     * @return array{
     * 0:list<SupplierPaymentProofAttachment>,
     * 1:list<string>
     * }
     */
    public function makeMany(string $supplierPaymentId, array $proofFiles, string $actorId): array
    {
        $attachments = [];
        $storedPaths = [];

        foreach ($proofFiles as $proofFile) {
            $attachment = SupplierPaymentProofAttachment::create(
                $this->uuid->generate(),
                $supplierPaymentId,
                trim((string) ($proofFile['storage_path'] ?? '')),
                trim((string) ($proofFile['original_filename'] ?? '')),
                trim((string) ($proofFile['mime_type'] ?? '')),
                (int) ($proofFile['file_size_bytes'] ?? 0),
                $this->clock->now(),
                $actorId,
            );

            $attachments[] = $attachment;
            $storedPaths[] = $attachment->storagePath();
        }

        return [$attachments, $storedPaths];
    }
}
