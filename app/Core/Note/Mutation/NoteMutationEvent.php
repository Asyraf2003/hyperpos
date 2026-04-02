<?php

declare(strict_types=1);

namespace App\Core\Note\Mutation;

use App\Core\Shared\Exceptions\DomainException;
use DateTimeImmutable;

final class NoteMutationEvent
{
    private function __construct(
        private string $id,
        private string $noteId,
        private string $mutationType,
        private string $actorId,
        private string $actorRole,
        private string $reason,
        private DateTimeImmutable $occurredAt,
        private ?string $relatedCustomerPaymentId,
        private ?string $relatedCustomerRefundId,
    ) {
    }

    public static function create(
        string $id,
        string $noteId,
        string $mutationType,
        string $actorId,
        string $actorRole,
        string $reason,
        DateTimeImmutable $occurredAt,
        ?string $relatedCustomerPaymentId = null,
        ?string $relatedCustomerRefundId = null,
    ): self {
        self::assertValid($id, $noteId, $mutationType, $actorId, $actorRole, $reason);

        return new self(
            trim($id),
            trim($noteId),
            trim($mutationType),
            trim($actorId),
            trim($actorRole),
            trim($reason),
            $occurredAt,
            self::normalizeNullable($relatedCustomerPaymentId),
            self::normalizeNullable($relatedCustomerRefundId),
        );
    }

    public static function rehydrate(
        string $id,
        string $noteId,
        string $mutationType,
        string $actorId,
        string $actorRole,
        string $reason,
        DateTimeImmutable $occurredAt,
        ?string $relatedCustomerPaymentId = null,
        ?string $relatedCustomerRefundId = null,
    ): self {
        return self::create(
            $id,
            $noteId,
            $mutationType,
            $actorId,
            $actorRole,
            $reason,
            $occurredAt,
            $relatedCustomerPaymentId,
            $relatedCustomerRefundId,
        );
    }

    public function id(): string { return $this->id; }
    public function noteId(): string { return $this->noteId; }
    public function mutationType(): string { return $this->mutationType; }
    public function actorId(): string { return $this->actorId; }
    public function actorRole(): string { return $this->actorRole; }
    public function reason(): string { return $this->reason; }
    public function occurredAt(): DateTimeImmutable { return $this->occurredAt; }
    public function relatedCustomerPaymentId(): ?string { return $this->relatedCustomerPaymentId; }
    public function relatedCustomerRefundId(): ?string { return $this->relatedCustomerRefundId; }

    private static function assertValid(
        string $id,
        string $noteId,
        string $mutationType,
        string $actorId,
        string $actorRole,
        string $reason,
    ): void {
        if (trim($id) === '') throw new DomainException('Note mutation event id wajib ada.');
        if (trim($noteId) === '') throw new DomainException('Note id wajib ada.');
        if (trim($mutationType) === '') throw new DomainException('Mutation type wajib ada.');
        if (trim($actorId) === '') throw new DomainException('Actor id wajib ada.');
        if (trim($actorRole) === '') throw new DomainException('Actor role wajib ada.');
        if (trim($reason) === '') throw new DomainException('Reason wajib ada.');
    }

    private static function normalizeNullable(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim($value);

        return $normalized === '' ? null : $normalized;
    }
}
