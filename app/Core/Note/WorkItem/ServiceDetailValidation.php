<?php

declare(strict_types=1);

namespace App\Core\Note\WorkItem;

use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;

trait ServiceDetailValidation
{
    private static function assertValid(
        string $serviceName,
        Money $servicePriceRupiah,
        string $partSource,
        Money $packageProfitRupiah,
        ?Money $packageBaseServicePriceRupiah,
        Money $packageServiceExtraRupiah,
    ): void {
        self::assertServiceIdentity($serviceName, $servicePriceRupiah);
        self::assertPackageMoney(
            $packageProfitRupiah,
            $packageBaseServicePriceRupiah,
            $packageServiceExtraRupiah
        );
        self::assertPartSource($partSource);
    }

    private static function assertServiceIdentity(string $serviceName, Money $servicePriceRupiah): void
    {
        if (trim($serviceName) === '') {
            throw new DomainException('Service name wajib ada.');
        }

        if ($servicePriceRupiah->amount() < 0) {
            throw new DomainException('Service price rupiah tidak boleh negatif.');
        }
    }

    private static function assertPackageMoney(
        Money $packageProfitRupiah,
        ?Money $packageBaseServicePriceRupiah,
        Money $packageServiceExtraRupiah,
    ): void {
        if ($packageProfitRupiah->amount() < 0) {
            throw new DomainException('Package profit rupiah tidak boleh negatif.');
        }

        if ($packageBaseServicePriceRupiah !== null && $packageBaseServicePriceRupiah->amount() < 0) {
            throw new DomainException('Package base service price rupiah tidak boleh negatif.');
        }

        if ($packageServiceExtraRupiah->amount() < 0) {
            throw new DomainException('Package service extra rupiah tidak boleh negatif.');
        }
    }

    private static function assertPartSource(string $partSource): void
    {
        $normalizedPartSource = trim($partSource);

        if (in_array($normalizedPartSource, [
            self::PART_SOURCE_NONE,
            self::PART_SOURCE_CUSTOMER_OWNED,
        ], true) === false) {
            throw new DomainException('Part source pada service detail tidak valid.');
        }
    }
}
