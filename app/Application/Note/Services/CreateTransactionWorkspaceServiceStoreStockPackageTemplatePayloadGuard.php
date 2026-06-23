<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Application\ServiceProductTemplate\DTO\ServiceProductTemplatePackageLookupRow;
use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\ServiceProductTemplate\ServiceProductTemplateLookupReaderPort;

final class CreateTransactionWorkspaceServiceStoreStockPackageTemplatePayloadGuard
{
    public function __construct(private readonly ServiceProductTemplateLookupReaderPort $templates)
    {
    }

    /**
     * @param array<string, mixed> $item
     * @param list<array<string, mixed>> $productLines
     */
    public function assert(array $item, array $productLines): void
    {
        $template = $this->activePackageForPrimaryProduct($productLines);
        $service = is_array($item['service'] ?? null) ? $item['service'] : [];
        $serviceName = trim((string) ($service['name'] ?? ''));

        if ($serviceName === '' || $this->normalized($serviceName) !== $this->normalized($template->serviceName)) {
            throw new DomainException('Payload paket servis + produk tidak sesuai template aktif.');
        }

        if (count($productLines) !== count($template->productLines)) {
            throw new DomainException('Payload paket servis + produk tidak sesuai template aktif.');
        }

        foreach ($template->productLines as $index => $templateLine) {
            $line = $productLines[$index] ?? [];
            $productId = trim((string) ($line['product_id'] ?? ''));
            $qty = $line['qty'] ?? null;

            if ($productId !== $templateLine->productId || $qty !== $templateLine->qty) {
                throw new DomainException('Payload paket servis + produk tidak sesuai template aktif.');
            }
        }
    }

    /** @param list<array<string, mixed>> $productLines */
    private function activePackageForPrimaryProduct(array $productLines): ServiceProductTemplatePackageLookupRow
    {
        $line = $productLines[0] ?? [];
        $productId = trim((string) ($line['product_id'] ?? ''));

        if ($productId === '') {
            throw new DomainException('Paket servis + produk wajib memakai template aktif.');
        }

        $template = $this->templates->findActivePackageByProductId($productId);

        if ($template === null) {
            throw new DomainException('Paket servis + produk wajib memakai template aktif.');
        }

        return $template;
    }

    private function normalized(string $value): string
    {
        return mb_strtolower(preg_replace('/\s+/', ' ', trim($value)) ?? trim($value));
    }
}
