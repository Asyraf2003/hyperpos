<?php

declare(strict_types=1);

namespace App\Application\MobileApi\Product\UseCases;

use App\Application\Note\Services\CashierNoteProductLookupData;
use App\Application\ProductCatalog\DTO\ProductLookupRow;

final readonly class SearchMobileApiProductsHandler
{
    private const MIN_QUERY_LENGTH = 2;
    private const DEFAULT_LIMIT = 20;

    public function __construct(private CashierNoteProductLookupData $lookupData)
    {
    }

    /**
     * @return array{rows:list<array{id:string,label:string,kode_barang:?string,nama_barang:string,merek:string,ukuran:?int,available_stock:int,default_unit_price_rupiah:int,minimum_unit_price_rupiah:int}>,meta:array{query:string,limit:int}}
     */
    public function handle(string $query): array
    {
        $normalizedQuery = trim($query);
        $limit = self::DEFAULT_LIMIT;

        if (mb_strlen($normalizedQuery) < self::MIN_QUERY_LENGTH) {
            return [
                'rows' => [],
                'meta' => [
                    'query' => $normalizedQuery,
                    'limit' => $limit,
                ],
            ];
        }

        $rows = [];

        foreach ($this->lookupData->searchProducts($normalizedQuery, $limit) as $product) {
            $rows[] = $this->toRow($product);
        }

        return [
            'rows' => $rows,
            'meta' => [
                'query' => $normalizedQuery,
                'limit' => $limit,
            ],
        ];
    }

    /**
     * @return array{id:string,label:string,kode_barang:?string,nama_barang:string,merek:string,ukuran:?int,available_stock:int,default_unit_price_rupiah:int,minimum_unit_price_rupiah:int}
     */
    private function toRow(ProductLookupRow $product): array
    {
        return [
            'id' => $product->id,
            'label' => $product->label(),
            'kode_barang' => $product->kodeBarang,
            'nama_barang' => $product->namaBarang,
            'merek' => $product->merek,
            'ukuran' => $product->ukuran,
            'available_stock' => $product->availableStock,
            'default_unit_price_rupiah' => $product->defaultUnitPriceRupiah,
            'minimum_unit_price_rupiah' => $product->minimumUnitPriceRupiah,
        ];
    }
}
