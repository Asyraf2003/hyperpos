<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Product;

use App\Application\Inventory\UseCases\ReverseStockAdjustmentHandler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class ReverseProductStockAdjustmentController extends Controller
{
    public function __invoke(
        Request $request,
        ReverseStockAdjustmentHandler $useCase,
        string $productId,
        string $adjustmentId,
    ): RedirectResponse {
        $data = $request->validate([
            'reversed_at' => ['required', 'date_format:Y-m-d'],
        ]);

        $user = $request->user();
        $actorId = $user?->getAuthIdentifier();

        $result = $useCase->handle(
            $productId,
            $adjustmentId,
            (string) $data['reversed_at'],
            $actorId !== null ? (string) $actorId : '',
        );

        if ($result->isFailure()) {
            return back()
                ->withErrors([
                    'stock_adjustment_reversal' => $result->message() ?? 'Reverse stock adjustment gagal dicatat.',
                ])
                ->withInput();
        }

        return back()->with('success', $result->message() ?? 'Stock adjustment berhasil direverse.');
    }
}
