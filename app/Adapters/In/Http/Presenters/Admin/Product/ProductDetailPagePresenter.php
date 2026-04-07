<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Presenters\Admin\Product;

final class ProductDetailPagePresenter
{
    /**
     * @param array{
     *   detail:array{
     *     product:array{
     *       id:string,
     *       kode_barang:?string,
     *       nama_barang:string,
     *       merek:string,
     *       ukuran:?int,
     *       harga_jual:int
     *     },
     *     initial_identity:?array{
     *       kode_barang:?string,
     *       nama_barang:string,
     *       merek:string,
     *       ukuran:?int,
     *       harga_jual:int,
     *       changed_at:string
     *     },
     *     has_identity_changes:bool
     *   },
     *   timeline:list<array{
     *     revision_no:int,
     *     event_name:string,
     *     changed_at:string,
     *     changed_by_actor_id:?string,
     *     change_reason:?string,
     *     snapshot:array<string, mixed>
     *   }>
     * } $payload
     *
     * @return array{
     *   heading:string,
     *   subtitle:string,
     *   actions:array{
     *     back_url:string,
     *     edit_identity_url:string,
     *     stock_adjustment_url:string
     *   },
     *   current_identity:array{
     *     kode_barang:string,
     *     nama_barang:string,
     *     merek:string,
     *     ukuran:string,
     *     harga_jual_label:string
     *   },
     *   initial_identity:?array{
     *     kode_barang:string,
     *     nama_barang:string,
     *     merek:string,
     *     ukuran:string,
     *     harga_jual_label:string,
     *     changed_at:string
     *   },
     *   identity_change_badge:array{
     *     label:string,
     *     tone:string
     *   },
     *   timeline:list<array{
     *     revision_label:string,
     *     event_name:string,
     *     changed_at:string,
     *     actor_label:?string,
     *     reason_label:?string,
     *     snapshot:array{
     *       kode_barang:string,
     *       nama_barang:string,
     *       merek:string,
     *       ukuran:string,
     *       harga_jual_label:string,
     *       deleted_at:?string
     *     }
     *   }>
     * }
     */
    public function present(array $payload): array
    {
        $detail = $payload['detail'];
        $product = $detail['product'];
        $initial = $detail['initial_identity'];

        return [
            'heading' => $product['nama_barang'],
            'subtitle' => sprintf(
                'Kode: %s · Merek: %s · Ukuran: %s',
                $product['kode_barang'] ?: '-',
                $product['merek'],
                $product['ukuran'] !== null ? (string) $product['ukuran'] : '-',
            ),
            'actions' => [
                'back_url' => route('admin.products.index'),
                'edit_identity_url' => route('admin.products.edit', ['productId' => $product['id']]) . '#product-master-form',
                'stock_adjustment_url' => route('admin.products.edit', ['productId' => $product['id']]) . '#product-stock-adjustment-form',
            ],
            'current_identity' => [
                'kode_barang' => $product['kode_barang'] ?: '-',
                'nama_barang' => $product['nama_barang'],
                'merek' => $product['merek'],
                'ukuran' => $product['ukuran'] !== null ? (string) $product['ukuran'] : '-',
                'harga_jual_label' => $this->rupiah((int) $product['harga_jual']),
            ],
            'initial_identity' => $initial === null ? null : [
                'kode_barang' => ($initial['kode_barang'] ?? null) ?: '-',
                'nama_barang' => $initial['nama_barang'],
                'merek' => $initial['merek'],
                'ukuran' => $initial['ukuran'] !== null ? (string) $initial['ukuran'] : '-',
                'harga_jual_label' => $this->rupiah((int) $initial['harga_jual']),
                'changed_at' => $initial['changed_at'],
            ],
            'identity_change_badge' => $detail['has_identity_changes']
                ? ['label' => 'Pernah berubah', 'tone' => 'warning']
                : ['label' => 'Belum berubah', 'tone' => 'secondary'],
            'timeline' => array_map(
                fn (array $entry): array => [
                    'revision_label' => 'Rev ' . $entry['revision_no'],
                    'event_name' => $entry['event_name'],
                    'changed_at' => $entry['changed_at'],
                    'actor_label' => $entry['changed_by_actor_id'] !== null
                        ? 'Actor: ' . $entry['changed_by_actor_id']
                        : null,
                    'reason_label' => $entry['change_reason'],
                    'snapshot' => [
                        'kode_barang' => ($entry['snapshot']['kode_barang'] ?? null) ?: '-',
                        'nama_barang' => (string) ($entry['snapshot']['nama_barang'] ?? '-'),
                        'merek' => (string) ($entry['snapshot']['merek'] ?? '-'),
                        'ukuran' => isset($entry['snapshot']['ukuran'])
                            ? (string) $entry['snapshot']['ukuran']
                            : '-',
                        'harga_jual_label' => $this->rupiah((int) ($entry['snapshot']['harga_jual'] ?? 0)),
                        'deleted_at' => array_key_exists('deleted_at', $entry['snapshot'])
                            ? (($entry['snapshot']['deleted_at'] ?? null) ?: '-')
                            : null,
                    ],
                ],
                $payload['timeline'],
            ),
        ];
    }

    private function rupiah(int $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}
