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
                ['type' => 'product', 'label' => 'Produk', 'help' => 'Penjualan barang dari stok toko.'],
                ['type' => 'service', 'label' => 'Servis', 'help' => 'Servis biasa tanpa sparepart toko.'],
                ['type' => 'service_store_stock', 'label' => 'Servis + Sparepart Toko', 'help' => 'Servis dengan sparepart dari stok toko.'],
                ['type' => 'service_external', 'label' => 'Servis + Pembelian Luar', 'help' => 'Servis dengan sparepart yang dibeli dari luar.'],
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
