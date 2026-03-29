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
            'quickAddOptions' => [
                [
                    'label' => 'Produk',
                    'entry_mode' => 'product',
                    'part_source' => 'none',
                    'button_class' => 'btn-outline-secondary',
                    'help' => 'Penjualan stok toko tanpa servis.',
                ],
                [
                    'label' => 'Service',
                    'entry_mode' => 'service',
                    'part_source' => 'none',
                    'button_class' => 'btn-outline-primary',
                    'help' => 'Servis biasa tanpa part.',
                ],
                [
                    'label' => 'Service + Produk Milik Customer',
                    'entry_mode' => 'service',
                    'part_source' => 'customer_owned',
                    'button_class' => 'btn-outline-dark',
                    'help' => 'Servis dengan part milik customer.',
                ],
                [
                    'label' => 'Service + Pembelian Luar',
                    'entry_mode' => 'service',
                    'part_source' => 'external_purchase',
                    'button_class' => 'btn-outline-warning',
                    'help' => 'Servis dengan pembelian part dari luar.',
                ],
            ],
            'partSourceOptions' => [
                ['value' => 'none', 'label' => 'Tanpa Part'],
                ['value' => 'store_stock', 'label' => 'Part Stok Toko'],
                ['value' => 'customer_owned', 'label' => 'Part Milik Customer'],
                ['value' => 'external_purchase', 'label' => 'Pembelian Luar'],
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
            'workspaceReadyForSubmit' => false,
        ];
    }
}
