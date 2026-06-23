<?php

declare(strict_types=1);

namespace App\Application\Note\Services\CurrentRevision;

use App\Core\Note\Revision\NoteRevisionLineSnapshot;

final class CurrentRevisionServiceAmountResolver
{
    /**
     * @param array<string, mixed> $payload
     * @param list<array<string, int|string>> $components
     */
    public function resolve(NoteRevisionLineSnapshot $line, array $payload, array $components): int
    {
        $service = is_array($payload['service'] ?? null) ? $payload['service'] : [];
        $fromPayload = (int) ($service['service_price_rupiah'] ?? 0);
        $packageProfit = (int) ($payload['package_profit_rupiah'] ?? 0);

        if ($fromPayload > 0 || $packageProfit > 0) {
            return $fromPayload + $packageProfit;
        }

        $fromLine = (int) ($line->servicePriceRupiah() ?? 0);
        if ($fromLine > 0) {
            return $fromLine;
        }

        $componentTotal = array_reduce(
            $components,
            static fn (int $sum, array $component): int => $sum + (int) ($component['component_total_rupiah'] ?? 0),
            0,
        );

        return max($line->subtotalRupiah() - $componentTotal, 0);
    }
}
