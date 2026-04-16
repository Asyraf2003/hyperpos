<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Presenters\Admin\Product\Concerns;

trait FormatsProductDetailIdentity
{
    private function subtitle(array $product): string
    {
        return sprintf(
            'Kode: %s · Merek: %s · Ukuran: %s',
            $product['kode_barang'] ?: '-',
            $product['merek'],
            $product['ukuran'] !== null ? (string) $product['ukuran'] : '-',
        );
    }

    private function currentIdentity(array $product): array
    {
        return [
            'kode_barang' => $product['kode_barang'] ?: '-',
            'nama_barang' => $product['nama_barang'],
            'merek' => $product['merek'],
            'ukuran' => $product['ukuran'] !== null ? (string) $product['ukuran'] : '-',
            'harga_jual_label' => $this->rupiah((int) $product['harga_jual']),
            'reorder_point_qty' => $this->qtyLabel($product['reorder_point_qty'] ?? null),
            'critical_threshold_qty' => $this->qtyLabel($product['critical_threshold_qty'] ?? null),
        ];
    }

    private function initialIdentity(?array $initial): ?array
    {
        if ($initial === null) {
            return null;
        }

        return [
            'kode_barang' => ($initial['kode_barang'] ?? null) ?: '-',
            'nama_barang' => $initial['nama_barang'],
            'merek' => $initial['merek'],
            'ukuran' => $initial['ukuran'] !== null ? (string) $initial['ukuran'] : '-',
            'harga_jual_label' => $this->rupiah((int) $initial['harga_jual']),
            'reorder_point_qty' => $this->qtyLabel($initial['reorder_point_qty'] ?? null),
            'critical_threshold_qty' => $this->qtyLabel($initial['critical_threshold_qty'] ?? null),
            'changed_at' => $initial['changed_at'],
        ];
    }

    private function identityChangeBadge(bool $hasIdentityChanges): array
    {
        if ($hasIdentityChanges) {
            return ['label' => 'Pernah berubah', 'tone' => 'warning'];
        }

        return ['label' => 'Belum berubah', 'tone' => 'secondary'];
    }

    private function rupiah(int $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    private function qtyLabel(?int $value): string
    {
        return $value !== null ? number_format($value, 0, ',', '.') : '-';
    }
}
