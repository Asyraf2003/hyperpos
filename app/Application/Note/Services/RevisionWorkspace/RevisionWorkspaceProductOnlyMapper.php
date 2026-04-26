<?php

declare(strict_types=1);

namespace App\Application\Note\Services\RevisionWorkspace;

use App\Core\Note\Revision\NoteRevisionLineSnapshot;

final class RevisionWorkspaceProductOnlyMapper
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
        $storeLine = $this->products->singleStoreLine(
            $payload,
            'Revision product preload hanya mendukung 1 store stock line.'
        );

        $productLine = $this->products->map($storeLine, $line->subtotalRupiah());

        return [
            'entry_mode' => 'product',
            'description' => '',
            'part_source' => 'store_stock',
            'service' => [
                'name' => '',
                'price_rupiah' => null,
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
