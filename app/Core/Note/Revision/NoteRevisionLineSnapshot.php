<?php

declare(strict_types=1);

namespace App\Core\Note\Revision;

use App\Core\Shared\Exceptions\DomainException;

final class NoteRevisionLineSnapshot
{
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

        if ($id === '') {
            throw new DomainException('Id snapshot line revision wajib diisi.');
        }

        if ($noteRevisionId === '') {
            throw new DomainException('Note revision id pada snapshot line wajib diisi.');
        }

        if ($lineNo <= 0) {
            throw new DomainException('Line number snapshot revision wajib lebih dari nol.');
        }

        if ($transactionType === '') {
            throw new DomainException('Transaction type snapshot revision wajib diisi.');
        }

        if ($status === '') {
            throw new DomainException('Status snapshot revision wajib diisi.');
        }

        if ($subtotalRupiah < 0) {
            throw new DomainException('Subtotal snapshot revision tidak boleh negatif.');
        }

        if ($servicePriceRupiah !== null && $servicePriceRupiah < 0) {
            throw new DomainException('Harga service snapshot revision tidak boleh negatif.');
        }

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

    public function id(): string
    {
        return $this->id;
    }

    public function noteRevisionId(): string
    {
        return $this->noteRevisionId;
    }

    public function workItemRootId(): ?string
    {
        return $this->workItemRootId;
    }

    public function lineNo(): int
    {
        return $this->lineNo;
    }

    public function transactionType(): string
    {
        return $this->transactionType;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function subtotalRupiah(): int
    {
        return $this->subtotalRupiah;
    }

    public function serviceLabel(): ?string
    {
        return $this->serviceLabel;
    }

    public function servicePriceRupiah(): ?int
    {
        return $this->servicePriceRupiah;
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return $this->payload;
    }
}
