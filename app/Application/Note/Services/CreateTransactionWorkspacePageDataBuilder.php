<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

final class CreateTransactionWorkspacePageDataBuilder
{
    /**
     * @return array<string, mixed>
     */
    public function build(): array
    {
        return [
            'itemTypeOptions' => [
                ['type' => 'product', 'label' => 'Produk'],
                ['type' => 'service', 'label' => 'Servis'],
                ['type' => 'service_store_stock', 'label' => 'Servis + Sparepart Toko'],
                ['type' => 'service_external', 'label' => 'Servis + Pembelian Luar'],
            ],
            'paymentDecisionOptions' => [
                ['value' => 'skip', 'label' => 'Skip'],
                ['value' => 'pay_full', 'label' => 'Bayar Penuh'],
                ['value' => 'pay_partial', 'label' => 'Bayar Sebagian'],
            ],
            'paymentMethodOptions' => [
                ['value' => 'cash', 'label' => 'Cash'],
                ['value' => 'transfer', 'label' => 'Transfer'],
            ],
        ];
    }
}
