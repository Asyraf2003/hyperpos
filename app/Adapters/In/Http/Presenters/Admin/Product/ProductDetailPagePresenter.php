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

        return [
            'heading' => $product['nama_barang'],
            'subtitle' => $this->subtitle($product),
            'actions' => [
                'back_url' => route('admin.products.index'),
                'edit_identity_url' => route('admin.products.edit', ['productId' => $product['id']]) . '#product-master-form',
                'stock_adjustment_url' => route('admin.products.edit', ['productId' => $product['id']]) . '#product-stock-adjustment-form',
            ],
            'current_identity' => $this->currentIdentity($product),
            'initial_identity' => $this->initialIdentity($detail['initial_identity']),
            'initial_identity_meta' => $this->initialIdentityMeta(
                $detail['initial_identity_source'] ?? 'unavailable',
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
            ];
        }

        if ($source === 'first_recorded_version') {
            return [
                'title' => 'Versi Pertama yang Tercatat',
                'badge_label' => 'Histori awal tidak lengkap',
                'badge_tone' => 'danger',
                'note' => 'Data ini bukan identitas awal asli, melainkan versi paling awal yang tersedia di histori.',
            ];
        }

        return [
            'title' => 'Riwayat Awal Tidak Tersedia',
            'badge_label' => 'Belum ada histori',
            'badge_tone' => 'secondary',
            'note' => 'Sistem belum memiliki versi awal yang bisa ditampilkan.',
        ];
    }
}
