<?php

declare(strict_types=1);

namespace App\Application\ServiceProductTemplate\Services;

use Illuminate\Support\Facades\DB;

final class ServiceProductTemplateAdminPageData
{
    use ServiceProductTemplateAdminProductOptions;
    use ServiceProductTemplateAdminServiceOptions;

    /** @return list<array<string, mixed>> */
    public function templates(): array
    {
        return DB::table('service_product_templates')
            ->join('products', 'products.id', '=', 'service_product_templates.product_id')
            ->join('service_catalog_items', 'service_catalog_items.id', '=', 'service_product_templates.service_catalog_item_id')
            ->select($this->templateSelectColumns())
            ->orderBy('products.nama_barang')
            ->orderBy('service_product_templates.sort_order')
            ->orderBy('service_product_templates.id')
            ->get()
            ->map(fn (object $row): array => $this->templateListRow($row))
            ->all();
    }

    /** @return array<string, mixed>|null */
    public function template(string $templateId): ?array
    {
        $row = DB::table('service_product_templates')
            ->where('id', trim($templateId))
            ->first();

        return $row !== null ? $this->templateFormRow($row) : null;
    }

    /** @return list<string> */
    private function templateSelectColumns(): array
    {
        return [
            'service_product_templates.id',
            'service_product_templates.product_id',
            'service_product_templates.service_catalog_item_id',
            'service_product_templates.default_service_price_rupiah',
            'service_product_templates.default_package_total_rupiah',
            'service_product_templates.is_active',
            'service_product_templates.sort_order',
            'products.kode_barang',
            'products.nama_barang',
            'products.harga_jual',
            'service_catalog_items.name as service_name',
        ];
    }

    /** @return array<string, mixed> */
    private function templateListRow(object $row): array
    {
        return $this->templateFormRow($row) + [
            'kode_barang' => $row->kode_barang !== null ? (string) $row->kode_barang : '',
            'nama_barang' => (string) $row->nama_barang,
            'harga_jual' => (int) $row->harga_jual,
            'service_name' => (string) $row->service_name,
        ];
    }

    /** @return array<string, mixed> */
    private function templateFormRow(object $row): array
    {
        return [
            'id' => (string) $row->id,
            'product_id' => (string) $row->product_id,
            'service_catalog_item_id' => (string) $row->service_catalog_item_id,
            'default_service_price_rupiah' => (int) $row->default_service_price_rupiah,
            'default_package_total_rupiah' => $row->default_package_total_rupiah !== null
                ? (int) $row->default_package_total_rupiah
                : null,
            'is_active' => (bool) $row->is_active,
            'sort_order' => (int) $row->sort_order,
        ];
    }
}
