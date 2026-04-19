<?php

declare(strict_types=1);

namespace App\Application\Payment\Services;

use App\Application\Payment\DTO\PayableNoteComponent;

final class SortPayableNoteComponents
{
    /**
     * @param list<PayableNoteComponent> $components
     * @return list<PayableNoteComponent>
     */
    public static function byPriority(array $components): array
    {
        usort($components, function (PayableNoteComponent $left, PayableNoteComponent $right): int {
            $leftPriority = PaymentComponentTypePriority::forType($left->componentType());
            $rightPriority = PaymentComponentTypePriority::forType($right->componentType());

            if ($leftPriority !== $rightPriority) {
                return $leftPriority <=> $rightPriority;
            }

            return $left->orderIndex() <=> $right->orderIndex();
        });

        return $components;
    }
}
