<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Presenters\Admin\Product;

use App\Adapters\In\Http\Presenters\Admin\Product\Concerns\FormatsProductDetailIdentity;
use App\Adapters\In\Http\Presenters\Admin\Product\Concerns\FormatsProductDetailTimeline;

final class ProductDetailPagePresenter
{
    use FormatsProductDetailIdentity;
    use FormatsProductDetailTimeline;

    public function present(array $payload): array
    {
        $detail = $payload['detail'];
        $product = $detail['product'];
        $initialIdentitySource = $detail['initial_identity_source'] ?? 'unavailable';

        return [
            'heading' => $product['nama_barang'],
            'subtitle' => $this->subtitle($product),
            'actions' => [
                'back_url' => route('admin.products.index'),
                'edit_identity_url' => route('admin.products.edit', ['productId' => $product['id']]) . '#product-master-form',
                'stock_adjustment_url' => route('admin.products.stock.edit', ['productId' => $product['id']]),
            ],
            'current_identity' => $this->currentIdentity($product),
            'initial_identity' => $initialIdentitySource === 'created_version'
                ? $this->initialIdentity($detail['initial_identity'])
                : null,
            'initial_identity_meta' => $this->initialIdentityMeta(
                $initialIdentitySource,
                $detail['has_identity_changes']
            ),
            'timeline' => $this->timeline($payload['timeline']),
        ];
    }

    private function initialIdentityMeta(string $source, bool $hasIdentityChanges): array
    {
        if ($source === 'created_version') {
            return [
                'title' => 'Identitas Awal',
                'badge_label' => $hasIdentityChanges ? 'Pernah berubah' : 'Belum berubah',
                'badge_tone' => $hasIdentityChanges ? 'warning' : 'secondary',
                'note' => null,
                'show_values' => true,
            ];
        }

        if ($source === 'first_recorded_version') {
            return [
                'title' => 'Histori Awal Tidak Lengkap',
                'badge_label' => 'Data awal asli tidak tersedia',
                'badge_tone' => 'danger',
                'note' => 'Sistem hanya memiliki versi pertama yang sempat tercatat, bukan identitas awal asli produk.',
                'show_values' => false,
            ];
        }

        return [
            'title' => 'Riwayat Awal Tidak Tersedia',
            'badge_label' => 'Belum ada histori',
            'badge_tone' => 'secondary',
            'note' => 'Sistem belum memiliki versi awal yang bisa ditampilkan.',
            'show_values' => false,
        ];
    }
}
