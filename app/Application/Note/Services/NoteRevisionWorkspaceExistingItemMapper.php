<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Note\Revision\NoteRevision;
use App\Core\Note\Revision\NoteRevisionLineSnapshot;
use App\Core\Shared\Exceptions\DomainException;

final class NoteRevisionWorkspaceExistingItemMapper
{
    /**
     * @return list<array<string, mixed>>
     */
    public function mapMany(NoteRevision $revision): array
    {
        $items = [];

        foreach ($revision->lines() as $line) {
            $items[] = $this->mapLine($line);
        }

        return $items;
    }

    /**
     * @return array<string, mixed>
     */
    private function mapLine(NoteRevisionLineSnapshot $line): array
    {
        return match ($line->transactionType()) {
            'service_only' => $this->mapServiceOnly($line),
            'store_stock_sale_only' => $this->mapProductOnly($line),
            default => throw new DomainException('Tipe line revision belum didukung untuk preload workspace edit.'),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function mapServiceOnly(NoteRevisionLineSnapshot $line): array
    {
        $payload = $line->payload();
        $service = is_array($payload['service'] ?? null) ? $payload['service'] : [];

        return [
            'entry_mode' => 'service',
            'description' => '',
            'part_source' => (string) ($service['part_source'] ?? 'none'),
            'service' => [
                'name' => (string) ($service['service_name'] ?? ($line->serviceLabel() ?? '')),
                'price_rupiah' => (int) ($service['service_price_rupiah'] ?? ($line->servicePriceRupiah() ?? 0)),
                'notes' => '',
            ],
            'product_lines' => [],
            'external_purchase_lines' => [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapProductOnly(NoteRevisionLineSnapshot $line): array
    {
        $payload = $line->payload();
        $storeLines = is_array($payload['store_stock_lines'] ?? null) ? $payload['store_stock_lines'] : [];

        if (count($storeLines) !== 1 || ! is_array($storeLines[0])) {
            throw new DomainException('Revision product preload hanya mendukung 1 store stock line.');
        }

        $storeLine = $storeLines[0];
        $qty = max((int) ($storeLine['qty'] ?? 1), 1);
        $subtotal = (int) ($storeLine['subtotal_rupiah'] ?? $line->subtotalRupiah());
        $unitPrice = (int) ($storeLine['selling_price_rupiah'] ?? intdiv($subtotal, $qty));

        return [
            'entry_mode' => 'product',
            'description' => '',
            'part_source' => 'store_stock',
            'service' => [
                'name' => '',
                'price_rupiah' => null,
                'notes' => '',
            ],
            'product_lines' => [[
                'product_id' => (string) ($storeLine['product_id'] ?? ''),
                'qty' => $qty,
                'unit_price_rupiah' => $unitPrice,
            ]],
            'external_purchase_lines' => [],
        ];
    }
}
