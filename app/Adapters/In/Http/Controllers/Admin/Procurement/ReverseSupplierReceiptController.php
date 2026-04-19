<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Procurement;

use App\Adapters\In\Http\Requests\Procurement\ReverseSupplierReceiptRequest;
use App\Application\Procurement\UseCases\ReverseSupplierReceiptHandler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

final class ReverseSupplierReceiptController extends Controller
{
    public function __invoke(
        string $supplierReceiptId,
        ReverseSupplierReceiptRequest $request,
        ReverseSupplierReceiptHandler $useCase,
    ): RedirectResponse {
        $data = $request->validated();

        $result = $useCase->handle(
            $supplierReceiptId,
            (string) $data['reversed_at'],
            (string) $data['reason'],
            (string) $request->user()->getAuthIdentifier(),
        );

        if ($result->isFailure()) {
            return back()
                ->withErrors([
                    'supplier_receipt_reversal' => $result->message() ?? 'Reversal penerimaan supplier gagal dicatat.',
                ])
                ->withInput();
        }

        return back()->with('success', $result->message() ?? 'Reversal penerimaan supplier berhasil dicatat.');
    }
}
