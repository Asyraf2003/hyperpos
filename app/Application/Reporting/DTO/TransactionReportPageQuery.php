<?php

declare(strict_types=1);

namespace App\Application\Reporting\DTO;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

final class TransactionReportPageQuery
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
        $periodMode = is_string($validated['period_mode'] ?? null)
            ? $validated['period_mode']
            : 'monthly';

        return new self(
            $periodMode,
            is_string($validated['reference_date'] ?? null) ? $validated['reference_date'] : null,
            is_string($validated['date_from'] ?? null) ? $validated['date_from'] : null,
            is_string($validated['date_to'] ?? null) ? $validated['date_to'] : null,
        );
    }

    public function fromTransactionDate(): string
    {
        if ($this->periodMode === 'custom' && $this->dateFrom !== null) {
            return CarbonImmutable::parse($this->dateFrom)->toDateString();
        }

        $reference = $this->resolvedReferenceDate();

        return match ($this->periodMode) {
            'weekly' => $reference->startOfWeek(CarbonInterface::MONDAY)->toDateString(),
            'monthly' => $reference->startOfMonth()->toDateString(),
            default => $reference->toDateString(),
        };
    }

    public function toTransactionDate(): string
    {
        if ($this->periodMode === 'custom' && $this->dateTo !== null) {
            return CarbonImmutable::parse($this->dateTo)->toDateString();
        }

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
            'date_from' => $this->fromTransactionDate(),
            'date_to' => $this->toTransactionDate(),
            'range_label' => $this->fromTransactionDate() . ' s/d ' . $this->toTransactionDate(),
        ];
    }

    private function resolvedReferenceDate(): CarbonImmutable
    {
        if ($this->referenceDate !== null) {
            return CarbonImmutable::parse($this->referenceDate);
        }

        if ($this->dateFrom !== null) {
            return CarbonImmutable::parse($this->dateFrom);
        }

        return CarbonImmutable::parse('today');
    }
}
