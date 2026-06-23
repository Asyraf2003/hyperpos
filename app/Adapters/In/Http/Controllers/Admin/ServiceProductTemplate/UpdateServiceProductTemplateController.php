<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\ServiceProductTemplate;

use App\Adapters\In\Http\Controllers\Admin\ServiceProductTemplate\Concerns\ValidatesServiceProductTemplateForm;
use App\Application\ServiceProductTemplate\Services\ServiceProductTemplateAdminLineInput;
use App\Application\ServiceProductTemplate\Services\ServiceProductTemplateLineWriter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

final class UpdateServiceProductTemplateController extends Controller
{
    use ValidatesServiceProductTemplateForm;

    public function __construct(
        private readonly ServiceProductTemplateAdminLineInput $lineInput,
        private readonly ServiceProductTemplateLineWriter $lineWriter,
    ) {
    }

    public function __invoke(Request $request, string $templateId): RedirectResponse
    {
        $template = DB::table('service_product_templates')
            ->where('id', trim($templateId))
            ->first();

        if ($template === null) {
            return redirect()
                ->route('admin.service-product-templates.index')
                ->with('error', 'Service tidak ditemukan.');
        }

        $data = $this->validated($request);
        $lines = $this->lineInput->fromData($data);

        if ((bool) $template->is_active && $this->activeTemplateExists($lines[0]['product_id'], trim($templateId))) {
            return back()
                ->withErrors(['product_id' => 'Produk ini sudah punya paket aktif lain. Nonaktifkan paket lama dulu.'])
                ->withInput();
        }

        $servicePrice = (int) $data['default_service_price_rupiah'];
        $packageTotal = $this->nullableInt($data['default_package_total_rupiah'] ?? null);
        $minimumTotal = $this->lineInput->total($lines) + $servicePrice;

        if ($packageTotal !== null && $packageTotal < $minimumTotal) {
            return back()
                ->withErrors(['default_package_total_rupiah' => $this->minimumTotalMessage($minimumTotal)])
                ->withInput();
        }

        DB::transaction(function () use ($data, $lines, $packageTotal, $servicePrice, $templateId): void {
            DB::table('service_product_templates')
                ->where('id', trim($templateId))
                ->update([
                    'product_id' => $lines[0]['product_id'],
                    'service_catalog_item_id' => (string) $data['service_catalog_item_id'],
                    'default_service_price_rupiah' => $servicePrice,
                    'default_package_total_rupiah' => $packageTotal,
                    'sort_order' => (int) $data['sort_order'],
                    'updated_at' => now(),
                ]);

            $this->lineWriter->replace(trim($templateId), $lines);
        });

        return redirect()
            ->route('admin.service-product-templates.index')
            ->with('success', 'Service berhasil diperbarui.');
    }
}
