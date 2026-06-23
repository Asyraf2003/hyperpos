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
use Illuminate\Support\Str;

final class StoreServiceProductTemplateController extends Controller
{
    use ValidatesServiceProductTemplateForm;

    public function __construct(
        private readonly ServiceProductTemplateAdminLineInput $lineInput,
        private readonly ServiceProductTemplateLineWriter $lineWriter,
    ) {
    }

    public function __invoke(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $lines = $this->lineInput->fromData($data);

        if ($this->activeTemplateExists($lines[0]['product_id'])) {
            return back()
                ->withErrors(['product_id' => 'Produk ini sudah punya paket aktif. Nonaktifkan paket lama dulu.'])
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

        DB::transaction(function () use ($data, $lines, $packageTotal, $servicePrice): void {
            $templateId = (string) Str::uuid();

            DB::table('service_product_templates')->insert([
                'id' => $templateId,
                'product_id' => $lines[0]['product_id'],
                'service_catalog_item_id' => (string) $data['service_catalog_item_id'],
                'default_service_price_rupiah' => $servicePrice,
                'default_package_total_rupiah' => $packageTotal,
                'is_active' => true,
                'sort_order' => (int) $data['sort_order'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->lineWriter->replace($templateId, $lines);
        });

        return redirect()
            ->route('admin.service-product-templates.index')
            ->with('success', 'Service berhasil dibuat.');
    }
}
