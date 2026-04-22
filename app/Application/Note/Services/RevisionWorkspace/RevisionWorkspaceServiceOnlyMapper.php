<?php

declare(strict_types=1);

namespace App\Application\Note\Services\RevisionWorkspace;

use App\Core\Note\Revision\NoteRevisionLineSnapshot;

final class RevisionWorkspaceServiceOnlyMapper
{
    /**
     * @return array<string, mixed>
     */
    public function map(NoteRevisionLineSnapshot $line): array
    {
        $payload = $line->payload();
        $service = is_array($payload['service'] ?? null) ? $payload['service'] : [];

        return [
            'entry_mode' => 'service',
            'description' => '',
            'part_source' => (string) ($service['part_source'] ?? 'none'),
            'service' => [
                'name' => (string) ($service['service_name'] ?? ($line->serviceLabel() ?? '')),
                'price_rupiah' => (int) ($service['service_price_rupiah'] ?? ($line->servicePriceRupiah() ?? 0)),
                'notes' => '',
            ],
            'product_lines' => [],
            'external_purchase_lines' => [],
        ];
    }
}
