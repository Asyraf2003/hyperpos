<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

final class CreateNotePageDataBuilder
{
    public function __construct(
        private readonly NoteProductOptionsBuilder $products,
    ) {
    }

    /**
     * @return array{
     * lineTypes:list<array{value:string,label:string}>,
     * productOptions:list<array{id:string,label:string,price_rupiah:int}>
     * }
     */
    public function build(): array
    {
        return [
            'lineTypes' => [
                ['value' => 'product', 'label' => 'Produk'],
                ['value' => 'service', 'label' => 'Servis'],
            ],
            'productOptions' => $this->products->build(),
        ];
    }
}
