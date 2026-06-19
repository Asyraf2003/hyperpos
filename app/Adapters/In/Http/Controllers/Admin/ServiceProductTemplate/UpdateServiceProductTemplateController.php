<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\ServiceProductTemplate;

use App\Adapters\In\Http\Controllers\Admin\ServiceProductTemplate\Concerns\ValidatesServiceProductTemplateForm;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

final class UpdateServiceProductTemplateController extends Controller
{
    use ValidatesServiceProductTemplateForm;

    public function __invoke(Request $request, string $templateId): RedirectResponse
    {
        $template = DB::table('service_product_templates')
            ->where('id', trim($templateId))
            ->first();

        if ($template === null) {
            return redirect()
                ->route('admin.service-product-templates.index')
                ->with('error', 'Paket service tidak ditemukan.');
        }

        $data = $this->validated($request);

        if ((bool) $template->is_active && $this->activeTemplateExists((string) $data['product_id'], trim($templateId))) {
            return back()
                ->withErrors(['product_id' => 'Produk ini sudah punya paket aktif lain. Nonaktifkan paket lama dulu.'])
                ->withInput();
        }

        $servicePrice = $this->servicePrice((string) $data['service_catalog_item_id']);
        $packageTotal = (int) $data['default_package_total_rupiah'];
        $minimumTotal = $this->productPrice((string) $data['product_id']) + $servicePrice;

        if ($packageTotal < $minimumTotal) {
            return back()
                ->withErrors(['default_package_total_rupiah' => $this->minimumTotalMessage($minimumTotal)])
                ->withInput();
        }

        DB::table('service_product_templates')
            ->where('id', trim($templateId))
            ->update([
                'product_id' => (string) $data['product_id'],
                'service_catalog_item_id' => (string) $data['service_catalog_item_id'],
                'default_service_price_rupiah' => $servicePrice,
                'default_package_total_rupiah' => $packageTotal,
                'sort_order' => 0,
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('admin.service-product-templates.index')
            ->with('success', 'Paket service berhasil diperbarui.');
    }

    private function minimumTotalMessage(int $minimumTotal): string
    {
        return sprintf(
            'Total paket minimal %s karena harga produk + jasa adalah batas bawah.',
            number_format($minimumTotal, 0, ',', '.')
        );
    }
}
