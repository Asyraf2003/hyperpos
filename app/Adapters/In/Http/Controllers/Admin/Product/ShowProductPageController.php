<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Product;

use App\Adapters\In\Http\Presenters\Admin\Product\ProductDetailPagePresenter;
use App\Application\ProductCatalog\UseCases\GetProductDetailHandler;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

final class ShowProductPageController extends Controller
{
    public function __invoke(
        GetProductDetailHandler $useCase,
        ProductDetailPagePresenter $presenter,
        string $productId,
    ): View|RedirectResponse {
        $result = $useCase->handle($productId);

        if ($result->isFailure()) {
            return redirect()
                ->route('admin.products.index')
                ->with('error', $result->message() ?? 'Product tidak ditemukan.');
        }

        $page = $presenter->present($result->data());
        $page['linked_service_packages'] = $this->linkedServicePackages($productId);

        return view('admin.products.show', [
            'page' => $page,
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function linkedServicePackages(string $productId): array
    {
        return DB::table('service_product_templates')
            ->join('products', 'products.id', '=', 'service_product_templates.product_id')
            ->join('service_catalog_items', 'service_catalog_items.id', '=', 'service_product_templates.service_catalog_item_id')
            ->where('service_product_templates.product_id', trim($productId))
            ->select([
                'service_product_templates.id',
                'service_product_templates.product_id',
                'service_product_templates.service_catalog_item_id',
                'service_product_templates.default_service_price_rupiah',
                'service_product_templates.default_package_total_rupiah',
                'service_product_templates.is_active',
                'products.nama_barang',
                'products.harga_jual',
                'service_catalog_items.name as service_name',
            ])
            ->orderByDesc('service_product_templates.is_active')
            ->orderBy('service_catalog_items.name')
            ->orderBy('service_product_templates.id')
            ->get()
            ->map(function (object $row): array {
                $productPrice = (int) $row->harga_jual;
                $servicePrice = (int) $row->default_service_price_rupiah;
                $minimumTotal = $productPrice + $servicePrice;
                $packageTotal = $row->default_package_total_rupiah !== null
                    ? (int) $row->default_package_total_rupiah
                    : $minimumTotal;

                return [
                    'id' => (string) $row->id,
                    'product_id' => (string) $row->product_id,
                    'service_catalog_item_id' => (string) $row->service_catalog_item_id,
                    'product_name' => (string) $row->nama_barang,
                    'service_name' => (string) $row->service_name,
                    'product_price' => $productPrice,
                    'service_price' => $servicePrice,
                    'minimum_total' => $minimumTotal,
                    'package_total' => $packageTotal,
                    'package_margin' => max(0, $packageTotal - $minimumTotal),
                    'is_active' => (bool) $row->is_active,
                ];
            })
            ->all();
    }
}
