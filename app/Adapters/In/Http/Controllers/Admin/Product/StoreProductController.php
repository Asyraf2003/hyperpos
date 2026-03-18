<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Product;

use App\Adapters\In\Http\Requests\ProductCatalog\CreateProductRequest;
use App\Application\ProductCatalog\UseCases\CreateProductHandler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

final class StoreProductController extends Controller
{
    public function __invoke(
        CreateProductRequest $request,
        CreateProductHandler $useCase,
    ): RedirectResponse {
        $data = $request->validated();

        $result = $useCase->handle(
            isset($data['kode_barang']) ? (string) $data['kode_barang'] : null,
            (string) $data['nama_barang'],
            (string) $data['merek'],
            isset($data['ukuran']) ? (int) $data['ukuran'] : null,
            (int) $data['harga_jual'],
        );

        if ($result->isFailure()) {
            return back()
                ->withErrors([
                    'product' => $result->message() ?? 'Product master gagal dibuat.',
                ])
                ->withInput();
        }

        return redirect()
            ->route('admin.products.index')
            ->with('success', $result->message() ?? 'Product master berhasil dibuat.');
    }
}
