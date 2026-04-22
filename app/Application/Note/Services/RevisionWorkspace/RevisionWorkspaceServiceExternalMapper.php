<?php

declare(strict_types=1);

namespace App\Application\Note\Services\RevisionWorkspace;

use App\Core\Note\Revision\NoteRevisionLineSnapshot;
use App\Core\Shared\Exceptions\DomainException;

final class RevisionWorkspaceServiceExternalMapper
{
    /**
     * @return array<string, mixed>
     */
    public function map(NoteRevisionLineSnapshot $line): array
    {
        $payload = $line->payload();
        $service = is_array($payload['service'] ?? null) ? $payload['service'] : [];
        $externalLine = $this->singleExternalLine($payload);

        return [
            'entry_mode' => 'service',
            'description' => '',
            'part_source' => 'external_purchase',
            'service' => [
                'name' => (string) ($service['service_name'] ?? ($line->serviceLabel() ?? '')),
                'price_rupiah' => (int) ($service['service_price_rupiah'] ?? ($line->servicePriceRupiah() ?? 0)),
                'notes' => '',
            ],
            'product_lines' => [],
            'external_purchase_lines' => [[
                'label' => (string) ($externalLine['cost_description'] ?? $externalLine['label'] ?? ''),
                'qty' => max((int) ($externalLine['qty'] ?? 1), 1),
                'unit_cost_rupiah' => (int) ($externalLine['unit_cost_rupiah'] ?? 0),
            ]],
        ];
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function singleExternalLine(array $payload): array
    {
        $externalLines = is_array($payload['external_purchase_lines'] ?? null) ? $payload['external_purchase_lines'] : [];

        if (count($externalLines) !== 1 || ! is_array($externalLines[0])) {
            throw new DomainException('Revision servis + pembelian luar hanya mendukung 1 external purchase line.');
        }

        return $externalLines[0];
    }
}
