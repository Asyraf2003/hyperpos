<?php

declare(strict_types=1);

namespace App\Core\Note\WorkItem;

use App\Core\Shared\ValueObjects\Money;

final class ServiceDetail
{
    use ServiceDetailValidation;

    public const PART_SOURCE_NONE = 'none';
    public const PART_SOURCE_CUSTOMER_OWNED = 'customer_owned';

    private function __construct(
        private string $serviceName,
        private Money $servicePriceRupiah,
        private string $partSource,
        private Money $packageProfitRupiah,
        private ?Money $packageBaseServicePriceRupiah,
        private Money $packageServiceExtraRupiah,
    ) {
    }

    public static function create(
        string $serviceName,
        Money $servicePriceRupiah,
        string $partSource,
        ?Money $packageProfitRupiah = null,
        ?Money $packageBaseServicePriceRupiah = null,
        ?Money $packageServiceExtraRupiah = null,
    ): self {
        $profit = $packageProfitRupiah ?? Money::zero();
        $extra = $packageServiceExtraRupiah ?? Money::zero();

        self::assertValid($serviceName, $servicePriceRupiah, $partSource, $profit, $packageBaseServicePriceRupiah, $extra);

        return new self(trim($serviceName), $servicePriceRupiah, trim($partSource), $profit, $packageBaseServicePriceRupiah, $extra);
    }

    public static function rehydrate(
        string $serviceName,
        Money $servicePriceRupiah,
        string $partSource,
        ?Money $packageProfitRupiah = null,
        ?Money $packageBaseServicePriceRupiah = null,
        ?Money $packageServiceExtraRupiah = null,
    ): self {
        return self::create($serviceName, $servicePriceRupiah, $partSource, $packageProfitRupiah, $packageBaseServicePriceRupiah, $packageServiceExtraRupiah);
    }

    public function serviceName(): string
    {
        return $this->serviceName;
    }

    public function servicePriceRupiah(): Money
    {
        return $this->servicePriceRupiah;
    }

    public function packageProfitRupiah(): Money
    {
        return $this->packageProfitRupiah;
    }

    public function packageBaseServicePriceRupiah(): ?Money
    {
        return $this->packageBaseServicePriceRupiah;
    }

    public function packageServiceExtraRupiah(): Money
    {
        return $this->packageServiceExtraRupiah;
    }

    public function totalPriceRupiah(): Money
    {
        return $this->servicePriceRupiah->add($this->packageProfitRupiah);
    }

    public function partSource(): string
    {
        return $this->partSource;
    }
}
