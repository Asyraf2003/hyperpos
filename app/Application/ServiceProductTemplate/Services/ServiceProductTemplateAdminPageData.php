<?php

declare(strict_types=1);

namespace App\Application\ServiceProductTemplate\Services;

use Illuminate\Support\Facades\DB;

final class ServiceProductTemplateAdminPageData
{
    /**
     * @return list<array<string, mixed>>
     */
    public function templates(): array
    {
        return DB::table('service_product_templates')
            ->join('products', 'products.id', '=', 'service_product_templates.product_id')
            ->join('service_catalog_items', 'service_catalog_items.id', '=', 'service_product_templates.service_catalog_item_id')
            ->select([
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
            ])
            ->orderBy('products.nama_barang')
            ->orderBy('service_product_templates.sort_order')
            ->orderBy('service_product_templates.id')
            ->get()
            ->map(fn (object $row): array => [
                'id' => (string) $row->id,
                'product_id' => (string) $row->product_id,
                'service_catalog_item_id' => (string) $row->service_catalog_item_id,
                'default_service_price_rupiah' => (int) $row->default_service_price_rupiah,
                'default_package_total_rupiah' => $row->default_package_total_rupiah !== null
                    ? (int) $row->default_package_total_rupiah
                    : null,
                'is_active' => (bool) $row->is_active,
                'sort_order' => (int) $row->sort_order,
                'kode_barang' => $row->kode_barang !== null ? (string) $row->kode_barang : '',
                'nama_barang' => (string) $row->nama_barang,
                'harga_jual' => (int) $row->harga_jual,
                'service_name' => (string) $row->service_name,
            ])
            ->all();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function template(string $templateId): ?array
    {
        $row = DB::table('service_product_templates')
            ->where('id', trim($templateId))
            ->first();

        if ($row === null) {
            return null;
        }

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

    /**
     * @return list<array{id:string,label:string}>
     */
    public function productOptions(): array
    {
        return DB::table('products')
            ->whereNull('deleted_at')
            ->select(['id', 'kode_barang', 'nama_barang', 'harga_jual'])
            ->orderBy('nama_barang')
            ->orderBy('kode_barang')
            ->get()
            ->map(fn (object $row): array => [
                'id' => (string) $row->id,
                'label' => trim(sprintf(
                    '%s%s · Harga jual %s',
                    $row->kode_barang !== null && $row->kode_barang !== '' ? (string) $row->kode_barang . ' - ' : '',
                    (string) $row->nama_barang,
                    number_format((int) $row->harga_jual, 0, ',', '.'),
                )),
            ])
            ->all();
    }

    /**
     * @return list<array{id:string,label:string}>
     */
    public function serviceOptions(?string $includeId = null): array
    {
        $trimmedIncludeId = trim((string) $includeId);

        return DB::table('service_catalog_items')
            ->where(function ($query) use ($trimmedIncludeId): void {
                $query->where('is_active', true);

                if ($trimmedIncludeId !== '') {
                    $query->orWhere('id', $trimmedIncludeId);
                }
            })
            ->select(['id', 'name', 'default_price_rupiah', 'is_active'])
            ->orderBy('name')
            ->get()
            ->map(fn (object $row): array => [
                'id' => (string) $row->id,
                'label' => sprintf(
                    '%s · Default %s%s',
                    (string) $row->name,
                    number_format((int) $row->default_price_rupiah, 0, ',', '.'),
                    (bool) $row->is_active ? '' : ' · nonaktif',
                ),
            ])
            ->all();
    }
}
