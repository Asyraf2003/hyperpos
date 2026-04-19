<?php

declare(strict_types=1);

namespace App\Core\Note\Mutation;

use App\Core\Shared\Exceptions\DomainException;

final class NoteMutationSnapshot
{
    public const BEFORE = 'before';
    public const AFTER = 'after';

    private function __construct(
        private string $id,
        private string $noteMutationEventId,
        private string $snapshotKind,
        private string $payloadJson,
    ) {
    }

    public static function create(
        string $id,
        string $noteMutationEventId,
        string $snapshotKind,
        string $payloadJson,
    ): self {
        self::assertValid($id, $noteMutationEventId, $snapshotKind, $payloadJson);

        return new self(
            trim($id),
            trim($noteMutationEventId),
            trim($snapshotKind),
            trim($payloadJson),
        );
    }

    public static function rehydrate(
        string $id,
        string $noteMutationEventId,
        string $snapshotKind,
        string $payloadJson,
    ): self {
        return self::create($id, $noteMutationEventId, $snapshotKind, $payloadJson);
    }

    public function id(): string { return $this->id; }
    public function noteMutationEventId(): string { return $this->noteMutationEventId; }
    public function snapshotKind(): string { return $this->snapshotKind; }
    public function payloadJson(): string { return $this->payloadJson; }

    private static function assertValid(
        string $id,
        string $noteMutationEventId,
        string $snapshotKind,
        string $payloadJson,
    ): void {
        if (trim($id) === '') throw new DomainException('Note mutation snapshot id wajib ada.');
        if (trim($noteMutationEventId) === '') throw new DomainException('Note mutation event id wajib ada.');
        if (trim($payloadJson) === '') throw new DomainException('Payload json wajib ada.');

        $normalizedKind = trim($snapshotKind);

        if (!in_array($normalizedKind, [self::BEFORE, self::AFTER], true)) {
            throw new DomainException('Snapshot kind tidak valid.');
        }
    }
}
