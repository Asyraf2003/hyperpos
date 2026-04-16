<?php

declare(strict_types=1);

namespace App\Adapters\Out\ProductCatalog;

use App\Adapters\Out\ProductCatalog\Concerns\ProductDetailSnapshotMapper;
use App\Adapters\Out\ProductCatalog\Concerns\ProductDetailVersionQueries;
use App\Ports\Out\ProductCatalog\ProductDetailReaderPort;
use Illuminate\Support\Facades\DB;

final class DatabaseProductDetailReaderAdapter implements ProductDetailReaderPort
{
    use ProductDetailSnapshotMapper;
    use ProductDetailVersionQueries;

    public function getDetail(string $productId): ?array
    {
        $product = DB::table('products')
            ->where('id', $productId)
            ->first([
                'id',
                'kode_barang',
                'nama_barang',
                'merek',
                'ukuran',
                'harga_jual',
                'reorder_point_qty',
                'critical_threshold_qty',
            ]);

        if ($product === null) {
            return null;
        }

        $createdVersion = $this->createdVersion($productId);
        $firstRecordedVersion = $this->firstRecordedVersion($productId);

        $current = $this->mapCurrentProduct($product);
        $initialSource = $createdVersion ?? $firstRecordedVersion;
        $initial = $initialSource === null ? null : $this->mapInitialIdentity($initialSource);

        return [
            'product' => $current,
            'initial_identity' => $initial,
            'initial_identity_source' => $this->initialIdentitySourceLabel($createdVersion, $firstRecordedVersion),
            'has_identity_changes' => $initial !== null && $this->identityChanged($current, $initial),
        ];
    }
}
