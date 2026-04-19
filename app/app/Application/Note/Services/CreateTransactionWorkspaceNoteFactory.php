<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Note\Note\Note;
use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\UuidPort;
use DateTimeImmutable;

final class CreateTransactionWorkspaceNoteFactory
{
    public function __construct(
        private readonly UuidPort $uuid,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function make(array $payload): Note
    {
        return Note::create(
            $this->uuid->generate(),
            $this->requiredString($payload['customer_name'] ?? null, 'Nama customer wajib diisi.'),
            $this->nullableString($payload['customer_phone'] ?? null),
            $this->parseDate($payload['transaction_date'] ?? null, 'Tanggal nota wajib valid dengan format Y-m-d.')
        );
    }

    private function requiredString(mixed $value, string $message): string
    {
        if (! is_string($value) || trim($value) === '') {
            throw new DomainException($message);
        }

        return trim($value);
    }

    private function nullableString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function parseDate(mixed $value, string $message): DateTimeImmutable
    {
        if (! is_string($value)) {
            throw new DomainException($message);
        }

        $normalized = trim($value);
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $normalized);

        if ($parsed === false || $parsed->format('Y-m-d') !== $normalized) {
            throw new DomainException($message);
        }

        return $parsed;
    }
}
