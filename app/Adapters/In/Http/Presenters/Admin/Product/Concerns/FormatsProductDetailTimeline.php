<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Presenters\Admin\Product\Concerns;

trait FormatsProductDetailTimeline
{
    private function timeline(array $entries): array
    {
        return array_map(fn (array $entry): array => $this->timelineEntry($entry), $entries);
    }

    private function timelineEntry(array $entry): array
    {
        return [
            'revision_label' => 'Rev ' . $entry['revision_no'],
            'event_name' => $entry['event_name'],
            'changed_at' => $entry['changed_at'],
            'actor_label' => $entry['changed_by_actor_id'] !== null
                ? 'Actor: ' . $entry['changed_by_actor_id']
                : null,
            'reason_label' => $entry['change_reason'],
            'snapshot' => $this->timelineSnapshot($entry['snapshot']),
        ];
    }

    private function timelineSnapshot(array $snapshot): array
    {
        return [
            'kode_barang' => ($snapshot['kode_barang'] ?? null) ?: '-',
            'nama_barang' => (string) ($snapshot['nama_barang'] ?? '-'),
            'merek' => (string) ($snapshot['merek'] ?? '-'),
            'ukuran' => isset($snapshot['ukuran']) ? (string) $snapshot['ukuran'] : '-',
            'harga_jual_label' => $this->rupiah((int) ($snapshot['harga_jual'] ?? 0)),
            'reorder_point_qty' => $this->qtyLabel(
                isset($snapshot['reorder_point_qty']) ? (int) $snapshot['reorder_point_qty'] : null
            ),
            'critical_threshold_qty' => $this->qtyLabel(
                isset($snapshot['critical_threshold_qty']) ? (int) $snapshot['critical_threshold_qty'] : null
            ),
            'deleted_at' => array_key_exists('deleted_at', $snapshot)
                ? (($snapshot['deleted_at'] ?? null) ?: '-')
                : null,
        ];
    }
}
