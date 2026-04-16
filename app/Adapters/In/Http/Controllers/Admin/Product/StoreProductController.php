<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Product;

use App\Adapters\In\Http\Requests\ProductCatalog\CreateProductRequest;
use App\Application\ProductCatalog\Context\ProductChangeContext;
use App\Application\ProductCatalog\UseCases\CreateProductHandler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

final class StoreProductController extends Controller
{
    public function __invoke(
        CreateProductRequest $request,
        CreateProductHandler $useCase,
        ProductChangeContext $changeContext,
    ): RedirectResponse {
        $data = $request->validated();

        $user = $request->user();
        $actorId = $user?->getAuthIdentifier();

        $changeContext->set(
            $actorId !== null ? (string) $actorId : null,
            null,
            'web_admin',
            null,
        );

        $result = $useCase->handle(
            isset($data['kode_barang']) ? (string) $data['kode_barang'] : null,
            (string) $data['nama_barang'],
            (string) $data['merek'],
            isset($data['ukuran']) ? (int) $data['ukuran'] : null,
            (int) $data['harga_jual'],
            isset($data['reorder_point_qty']) ? (int) $data['reorder_point_qty'] : null,
            isset($data['critical_threshold_qty']) ? (int) $data['critical_threshold_qty'] : null,
        );

        if ($result->isFailure()) {
            return back()
                ->withErrors([
                    'product' => $result->message() ?? 'Product master gagal dibuat.',
                ])
                ->withInput();
        }

        $returnTo = $this->resolveReturnTo($request->input('return_to'));
        $successMessage = $result->message() ?? 'Product master berhasil dibuat.';

        if ($returnTo !== null) {
            return redirect()
                ->to($returnTo)
                ->with('success', $successMessage . ' Silakan lanjutkan nota Anda.');
        }

        return redirect()
            ->route('admin.products.index')
            ->with('success', $successMessage);
    }

    private function resolveReturnTo(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        if ($trimmed === '') {
            return null;
        }

        $allowedAbsolute = route('admin.procurement.supplier-invoices.create');
        $allowedRelative = route('admin.procurement.supplier-invoices.create', [], false);

        if (str_starts_with($trimmed, $allowedAbsolute) || str_starts_with($trimmed, $allowedRelative)) {
            return $trimmed;
        }

        return null;
    }
}
