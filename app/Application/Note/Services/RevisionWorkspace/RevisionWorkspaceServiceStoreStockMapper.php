<?php

declare(strict_types=1);

namespace App\Application\Note\Services\RevisionWorkspace;

use App\Core\Note\Revision\NoteRevisionLineSnapshot;

final class RevisionWorkspaceServiceStoreStockMapper
{
    public function __construct(
        private readonly RevisionWorkspaceProductLineMapper $products,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function map(NoteRevisionLineSnapshot $line): array
    {
        $payload = $line->payload();
        $service = is_array($payload['service'] ?? null) ? $payload['service'] : [];
        $storeLine = $this->products->singleStoreLine(
            $payload,
            'Revision servis + sparepart toko hanya mendukung 1 store stock line.'
        );

        $productLine = $this->products->map($storeLine);

        return [
            'entry_mode' => 'service',
            'description' => '',
            'part_source' => 'store_stock',
            'service' => [
                'name' => (string) ($service['service_name'] ?? ($line->serviceLabel() ?? '')),
                'price_rupiah' => (int) ($service['service_price_rupiah'] ?? ($line->servicePriceRupiah() ?? 0)),
                'notes' => '',
            ],
            'selected_label' => $productLine['selected_label'],
            'product_lines' => [[
                'product_id' => $productLine['product_id'],
                'qty' => $productLine['qty'],
                'unit_price_rupiah' => $productLine['unit_price_rupiah'],
                'price_basis' => $productLine['price_basis'],
            ]],
            'external_purchase_lines' => [],
        ];
    }
}
