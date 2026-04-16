<?php

declare(strict_types=1);

namespace App\Application\Reporting\DTO;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

final class InventoryStockValueReportPageQuery
{
    private function __construct(
        private readonly string $periodMode,
        private readonly ?string $referenceDate,
        private readonly ?string $dateFrom,
        private readonly ?string $dateTo,
    ) {
    }

    public static function fromValidated(array $validated): self
    {
        return new self(
            is_string($validated['period_mode'] ?? null) ? $validated['period_mode'] : 'daily',
            is_string($validated['reference_date'] ?? null) ? $validated['reference_date'] : null,
            is_string($validated['date_from'] ?? null) ? $validated['date_from'] : null,
            is_string($validated['date_to'] ?? null) ? $validated['date_to'] : null,
        );
    }

    public function fromMutationDate(): string
    {

        $reference = $this->resolvedReferenceDate();

        return match ($this->periodMode) {
            'weekly' => $reference->startOfWeek(CarbonInterface::MONDAY)->toDateString(),
            'monthly' => $reference->startOfMonth()->toDateString(),
            default => $reference->toDateString(),
        };
    }

    public function toMutationDate(): string
    {

        $reference = $this->resolvedReferenceDate();

        return match ($this->periodMode) {
            'weekly' => $reference->endOfWeek(CarbonInterface::SUNDAY)->toDateString(),
            'monthly' => $reference->endOfMonth()->toDateString(),
            default => $reference->toDateString(),
        };
    }

    public function toViewData(): array
    {
        return [
            'period_mode' => $this->periodMode,
            'reference_date' => $this->resolvedReferenceDate()->toDateString(),
            'date_from' => $this->fromMutationDate(),
            'date_to' => $this->toMutationDate(),
            'range_label' => $this->fromMutationDate() . ' s/d ' . $this->toMutationDate(),
        ];
    }

    private function resolvedReferenceDate(): CarbonImmutable
    {
        return CarbonImmutable::parse($this->referenceDate ?? 'today');
    }
}
