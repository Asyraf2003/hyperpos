<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Shared\Exceptions\DomainException;

trait CreateTransactionWorkspaceServiceStoreStockPackageAutoSplitBranches
{
    /**
     * @param array<string, mixed> $item
     * @param array<string, mixed> $pricedLines
     * @return array<string, mixed>
     */
    private function composeWithTemplate(
        array $item,
        array $pricedLines,
        int $serviceTotal
    ): array {
        $this->rules->assertExactActiveTemplatePayload($item, $pricedLines['product_lines']);
        [$serviceFee, $packageProfit] = $this->splitServiceTotal($serviceTotal);
        $service = $this->service($item);

        $service['price_rupiah'] = $serviceFee;
        $service['package_profit_rupiah'] = $packageProfit;
        $service['package_base_service_price_rupiah'] = $serviceFee;
        $service['package_service_extra_rupiah'] = 0;

        return $this->withServiceAndLines($item, $service, $pricedLines);
    }

    /**
     * @param array<string, mixed> $item
     * @param array<string, mixed> $pricedLines
     * @return array<string, mixed>
     */
    private function composeWithoutTemplate(
        array $item,
        array $pricedLines,
        int $serviceTotal
    ): array {
        [$serviceFee, $packageProfit] = $this->splitServiceTotal($serviceTotal);
        $service = $this->service($item);
        $service['price_rupiah'] = $serviceFee;
        $service['package_profit_rupiah'] = $packageProfit;
        $service['package_base_service_price_rupiah'] = null;
        $service['package_service_extra_rupiah'] = 0;

        return $this->withServiceAndLines($item, $service, $pricedLines);
    }

    /**
     * @return array{0:int,1:int}
     */
    private function splitServiceTotal(int $serviceTotal): array
    {
        $serviceFee = intdiv($serviceTotal, 5);

        return [$serviceFee, $serviceTotal - $serviceFee];
    }
}
