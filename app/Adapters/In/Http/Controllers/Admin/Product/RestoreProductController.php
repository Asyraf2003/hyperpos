<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Product;

use App\Application\ProductCatalog\UseCases\RestoreProductHandler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class RestoreProductController extends Controller
{
    public function __invoke(
        Request $request,
        RestoreProductHandler $useCase,
        string $productId,
    ): RedirectResponse {
        $user = $request->user();
        $actorId = $user?->getAuthIdentifier();

        $result = $useCase->handle(
            $productId,
            $actorId !== null ? (string) $actorId : null,
        );

        if ($result->isFailure()) {
            return redirect()
                ->route('admin.products.index')
                ->with('error', $result->message() ?? 'Product gagal direstore.');
        }

        return redirect()
            ->route('admin.products.index')
            ->with('success', $result->message() ?? 'Product berhasil direstore.');
    }
}
