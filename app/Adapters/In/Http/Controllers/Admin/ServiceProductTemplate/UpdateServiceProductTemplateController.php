<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\ServiceProductTemplate;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

final class UpdateServiceProductTemplateController extends Controller
{
    public function __invoke(Request $request, string $templateId): RedirectResponse
    {
        $template = DB::table('service_product_templates')
            ->where('id', trim($templateId))
            ->first();

        if ($template === null) {
            return redirect()
                ->route('admin.service-product-templates.index')
                ->with('error', 'Template jasa + produk tidak ditemukan.');
        }

        $data = $this->validated($request);

        if ((bool) $template->is_active && $this->activeTemplateExists((string) $data['product_id'], trim($templateId))) {
            return back()
                ->withErrors(['product_id' => 'Produk ini sudah punya template aktif lain. Nonaktifkan template lama dulu.'])
                ->withInput();
        }

        DB::table('service_product_templates')
            ->where('id', trim($templateId))
            ->update([
                'product_id' => (string) $data['product_id'],
                'service_catalog_item_id' => (string) $data['service_catalog_item_id'],
                'default_service_price_rupiah' => (int) $data['default_service_price_rupiah'],
                'default_package_total_rupiah' => isset($data['default_package_total_rupiah'])
                    ? (int) $data['default_package_total_rupiah']
                    : null,
                'sort_order' => (int) ($data['sort_order'] ?? 0),
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('admin.service-product-templates.index')
            ->with('success', 'Template jasa + produk berhasil diperbarui.');
    }

    /**
     * @return array<string, mixed>
     */
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
            'default_service_price_rupiah' => ['required', 'integer', 'min:1'],
            'default_package_total_rupiah' => ['nullable', 'integer', 'min:1'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
    }

    private function activeTemplateExists(string $productId, string $exceptTemplateId): bool
    {
        return DB::table('service_product_templates')
            ->where('product_id', trim($productId))
            ->where('is_active', true)
            ->where('id', '!=', trim($exceptTemplateId))
            ->exists();
    }
}
