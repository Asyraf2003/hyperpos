<?php

declare(strict_types=1);

namespace App\Adapters\Out\ProductCatalog;

use App\Core\ProductCatalog\Product\Product;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\ProductCatalog\ProductReaderPort;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

final class DatabaseProductReaderAdapter implements ProductReaderPort
{
    public function getById(string $productId): ?Product
    {
        $row = $this->baseSelect()
            ->where('id', $productId)
            ->first();

        if ($row === null) {
            return null;
        }

        return $this->mapRowToProduct($row);
    }

    /**
     * @return array<int, Product>
     */
    public function findAll(): array
    {
        $rows = $this->applyOrdering($this->baseSelect())->get();

        return $this->mapRowsToProducts($rows);
    }

    /**
     * @return array<int, Product>
     */
    public function search(string $query): array
    {
        $normalizedQuery = trim($query);

        if ($normalizedQuery === '') {
            return $this->findAll();
        }

        $rows = $this->applyOrdering($this->applySearch($this->baseSelect(), $normalizedQuery))->get();

        return $this->mapRowsToProducts($rows);
    }

    public function findPaginated(int $perPage = 10): LengthAwarePaginator
    {
        $paginator = $this->applyOrdering($this->baseSelect())->paginate($perPage);

        $paginator->setCollection(
            $paginator->getCollection()->map(fn (object $row): Product => $this->mapRowToProduct($row))
        );

        return $paginator;
    }

    public function searchPaginated(string $query, int $perPage = 10): LengthAwarePaginator
    {
        $normalizedQuery = trim($query);

        if ($normalizedQuery === '') {
            return $this->findPaginated($perPage);
        }

        $paginator = $this->applyOrdering(
            $this->applySearch($this->baseSelect(), $normalizedQuery)
        )->paginate($perPage);

        $paginator->setCollection(
            $paginator->getCollection()->map(fn (object $row): Product => $this->mapRowToProduct($row))
        );

        return $paginator;
    }

    private function baseSelect(): Builder
    {
        return DB::table('products')
            ->select(['id', 'kode_barang', 'nama_barang', 'merek', 'ukuran', 'harga_jual']);
    }

    private function applySearch(Builder $query, string $keyword): Builder
    {
        return $query->where(function (Builder $builder) use ($keyword): void {
            $builder
                ->where('kode_barang', 'like', '%' . $keyword . '%')
                ->orWhere('nama_barang', 'like', '%' . $keyword . '%')
                ->orWhere('merek', 'like', '%' . $keyword . '%');
        });
    }

    private function applyOrdering(Builder $query): Builder
    {
        return $query
            ->orderBy('nama_barang')
            ->orderBy('merek')
            ->orderBy('ukuran')
            ->orderBy('id');
    }

    /**
     * @param iterable<object> $rows
     * @return array<int, Product>
     */
    private function mapRowsToProducts(iterable $rows): array
    {
        $products = [];

        foreach ($rows as $row) {
            $products[] = $this->mapRowToProduct($row);
        }

        return $products;
    }

    private function mapRowToProduct(object $row): Product
    {
        return Product::rehydrate(
            (string) $row->id,
            $row->kode_barang !== null ? (string) $row->kode_barang : null,
            (string) $row->nama_barang,
            (string) $row->merek,
            $row->ukuran !== null ? (int) $row->ukuran : null,
            Money::fromInt((int) $row->harga_jual),
        );
    }
}
