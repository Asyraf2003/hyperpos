<?php

declare(strict_types=1);

namespace App\Application\Reporting\Services;

final class OperationalExpenseCategoryBreakdownBuilder
{
    public function build(array $rows): array
    {
        $categories = [];

        foreach ($rows as $row) {
            $categoryId = (string) ($row['category_id'] ?? '');

            if ($categoryId === '') {
                continue;
            }

            if (! isset($categories[$categoryId])) {
                $categories[$categoryId] = [
                    'category_id' => $categoryId,
                    'category_code' => (string) ($row['category_code'] ?? ''),
                    'category_name' => (string) ($row['category_name'] ?? ''),
                    'total_rows' => 0,
                    'total_amount_rupiah' => 0,
                ];
            }

            $categories[$categoryId]['total_rows']++;
            $categories[$categoryId]['total_amount_rupiah'] += (int) ($row['amount_rupiah'] ?? 0);
        }

        $categoryRows = array_values($categories);

        usort($categoryRows, static function (array $left, array $right): int {
            $byAmount = $right['total_amount_rupiah'] <=> $left['total_amount_rupiah'];

            if ($byAmount !== 0) {
                return $byAmount;
            }

            return strcmp($left['category_name'], $right['category_name']);
        });

        return $categoryRows;
    }
}
