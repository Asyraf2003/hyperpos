<?php

declare(strict_types=1);

namespace App\Application\Reporting\Services;

use InvalidArgumentException;

/**
 * @phpstan-type CashChangeDenominationRow array{
 *   denomination:int,
 *   count:int,
 *   total_rupiah:int
 * }
 */
final class CashChangeDenominationCalculator
{
    private const DENOMINATIONS = [
        100000,
        50000,
        20000,
        10000,
        5000,
        2000,
        1000,
        500,
    ];

    /**
     * @return list<CashChangeDenominationRow>
     */
    public function calculate(int $changeRupiah): array
    {
        return $this->aggregate([$changeRupiah]);
    }

    /**
     * @param list<int> $changeAmountsRupiah
     * @return list<CashChangeDenominationRow>
     */
    public function aggregate(array $changeAmountsRupiah): array
    {
        $countsByDenomination = [];

        foreach (self::DENOMINATIONS as $denomination) {
            $countsByDenomination[$denomination] = 0;
        }

        foreach ($changeAmountsRupiah as $changeRupiah) {
            $this->assertValidChangeAmount($changeRupiah);

            $remaining = $changeRupiah;

            foreach (self::DENOMINATIONS as $denomination) {
                if ($remaining < $denomination) {
                    continue;
                }

                $count = intdiv($remaining, $denomination);
                $countsByDenomination[$denomination] += $count;
                $remaining -= $count * $denomination;
            }

            if ($remaining !== 0) {
                throw new InvalidArgumentException(
                    'Change amount cannot be represented by configured cash denominations.'
                );
            }
        }

        $rows = [];

        foreach ($countsByDenomination as $denomination => $count) {
            if ($count === 0) {
                continue;
            }

            $rows[] = [
                'denomination' => $denomination,
                'count' => $count,
                'total_rupiah' => $denomination * $count,
            ];
        }

        return $rows;
    }

    private function assertValidChangeAmount(int $changeRupiah): void
    {
        if ($changeRupiah < 0) {
            throw new InvalidArgumentException('Change amount must not be negative.');
        }
    }
}
