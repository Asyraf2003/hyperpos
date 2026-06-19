<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\ServiceProductTemplate\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

trait ValidatesServiceProductTemplateForm
{
    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        return $request->validate([
            'product_id' => [
                'required',
                'string',
                Rule::exists('products', 'id')->whereNull('deleted_at'),
            ],
            'service_catalog_item_id' => [
                'required',
                'string',
                Rule::exists('service_catalog_items', 'id')->where('is_active', true),
            ],
            'default_package_total_rupiah' => ['required', 'integer', 'min:1'],
        ]);
    }

    private function productPrice(string $productId): int
    {
        return (int) DB::table('products')
            ->where('id', trim($productId))
            ->whereNull('deleted_at')
            ->value('harga_jual');
    }

    private function servicePrice(string $serviceCatalogItemId): int
    {
        return (int) DB::table('service_catalog_items')
            ->where('id', trim($serviceCatalogItemId))
            ->where('is_active', true)
            ->value('default_price_rupiah');
    }

    private function activeTemplateExists(string $productId, ?string $exceptTemplateId = null): bool
    {
        $query = DB::table('service_product_templates')
            ->where('product_id', trim($productId))
            ->where('is_active', true);

        if ($exceptTemplateId !== null && trim($exceptTemplateId) !== '') {
            $query->where('id', '!=', trim($exceptTemplateId));
        }

        return $query->exists();
    }
}
