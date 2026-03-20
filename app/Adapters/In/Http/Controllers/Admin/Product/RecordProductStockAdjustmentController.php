<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Product;

use App\Application\Inventory\UseCases\RecordStockAdjustmentHandler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class RecordProductStockAdjustmentController extends Controller
{
    public function __invoke(
        Request $request,
        RecordStockAdjustmentHandler $useCase,
        string $productId,
    ): RedirectResponse {
        $data = $request->validate([
            'adjusted_at' => ['required', 'date_format:Y-m-d'],
            'qty_issue' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string'],
        ]);

        $user = $request->user();
        $actorId = $user?->getAuthIdentifier();

        $result = $useCase->handle(
            $productId,
            (int) $data['qty_issue'],
            (string) $data['adjusted_at'],
            trim((string) $data['reason']),
            $actorId !== null ? (string) $actorId : '',
        );

        if ($result->isFailure()) {
            return back()
                ->withErrors([
                    'stock_adjustment' => $result->message() ?? 'Stock adjustment gagal dicatat.',
                ])
                ->withInput();
        }

        return back()->with('success', $result->message() ?? 'Stock adjustment berhasil dicatat.');
    }
}
