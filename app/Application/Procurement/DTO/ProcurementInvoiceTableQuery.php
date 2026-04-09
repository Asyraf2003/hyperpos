<?php

declare(strict_types=1);

namespace App\Application\Procurement\DTO;

final class ProcurementInvoiceTableQuery
{
    public function __construct(
        private readonly ?string $q,
        private readonly ?string $nomorFaktur,
        private readonly ?string $namaPt,
        private readonly string $paymentStatus,
        private readonly int $page,
        private readonly int $perPage,
        private readonly string $sortBy,
        private readonly string $sortDir,
        private readonly ?string $shipmentDateFrom,
        private readonly ?string $shipmentDateTo,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromValidated(array $data): self
    {
        return new self(
            self::nullableString($data['q'] ?? null),
            self::nullableString($data['nomor_faktur'] ?? null),
            self::nullableString($data['nama_pt'] ?? null),
            isset($data['payment_status']) ? (string) $data['payment_status'] : 'all',
            isset($data['page']) ? (int) $data['page'] : 1,
            isset($data['per_page']) ? (int) $data['per_page'] : 10,
            isset($data['sort_by']) ? (string) $data['sort_by'] : 'shipment_date',
            isset($data['sort_dir']) ? (string) $data['sort_dir'] : 'desc',
            self::nullableString($data['shipment_date_from'] ?? null),
            self::nullableString($data['shipment_date_to'] ?? null),
        );
    }

    public function q(): ?string
    {
        return $this->q;
    }

    public function nomorFaktur(): ?string
    {
        return $this->nomorFaktur;
    }

    public function namaPt(): ?string
    {
        return $this->namaPt;
    }

    public function paymentStatus(): string
    {
        return $this->paymentStatus;
    }

    public function page(): int
    {
        return $this->page;
    }

    public function perPage(): int
    {
        return $this->perPage;
    }

    public function sortBy(): string
    {
        return $this->sortBy;
    }

    public function sortDir(): string
    {
        return $this->sortDir;
    }

    public function shipmentDateFrom(): ?string
    {
        return $this->shipmentDateFrom;
    }

    public function shipmentDateTo(): ?string
    {
        return $this->shipmentDateTo;
    }

    private static function nullableString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $normalized = trim($value);

        return $normalized === '' ? null : $normalized;
    }
}
