<?php

declare(strict_types=1);

namespace App\Application\Reporting\DTO;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

final class EmployeeDebtReportPageQuery
{
    private function __construct(
        private readonly string $periodMode,
        private readonly ?string $referenceDate,
    ) {
    }

    public static function fromValidated(array $validated): self
    {
        return new self(
            is_string($validated['period_mode'] ?? null) ? $validated['period_mode'] : 'monthly',
            is_string($validated['reference_date'] ?? null) ? $validated['reference_date'] : null,
        );
    }

    public function fromRecordedDate(): string
    {

        $reference = $this->resolvedReferenceDate();

        return match ($this->periodMode) {
            'weekly' => $reference->startOfWeek(CarbonInterface::MONDAY)->toDateString(),
            'monthly' => $reference->startOfMonth()->toDateString(),
            default => $reference->toDateString(),
        };
    }

    public function toRecordedDate(): string
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
            'date_from' => $this->fromRecordedDate(),
            'date_to' => $this->toRecordedDate(),
            'range_label' => $this->fromRecordedDate() . ' s/d ' . $this->toRecordedDate(),
        ];
    }

    private function resolvedReferenceDate(): CarbonImmutable
    {
        return CarbonImmutable::parse($this->referenceDate ?? 'today');
    }
}
