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
            'identity_change_badge' => $this->identityChangeBadge($detail['has_identity_changes']),
            'timeline' => $this->timeline($payload['timeline']),
        ];
    }
}
