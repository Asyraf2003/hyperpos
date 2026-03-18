<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Product;

use App\Adapters\In\Http\Requests\ProductCatalog\UpdateProductRequest;
use App\Application\ProductCatalog\UseCases\UpdateProductHandler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

final class UpdateProductController extends Controller
{
    public function __invoke(
        UpdateProductRequest $request,
        UpdateProductHandler $useCase,
        string $productId,
    ): RedirectResponse {
        $data = $request->validated();

        $result = $useCase->handle(
            $productId,
            isset($data['kode_barang']) ? (string) $data['kode_barang'] : null,
            (string) $data['nama_barang'],
            (string) $data['merek'],
            isset($data['ukuran']) ? (int) $data['ukuran'] : null,
            (int) $data['harga_jual'],
        );

        if ($result->isFailure()) {
            if (($result->errors()['product'] ?? []) === ['PRODUCT_NOT_FOUND']) {
                return redirect()
                    ->route('admin.products.index')
                    ->with('error', $result->message() ?? 'Product tidak ditemukan.');
            }

            return back()
                ->withErrors([
                    'product' => $result->message() ?? 'Product master gagal diperbarui.',
                ])
                ->withInput();
        }

        return redirect()
            ->route('admin.products.index')
            ->with('success', $result->message() ?? 'Product master berhasil diperbarui.');
    }
}
