<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Procurement;

use App\Adapters\In\Http\Requests\Procurement\ReverseSupplierPaymentRequest;
use App\Application\Procurement\UseCases\ReverseSupplierPaymentHandler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

final class ReverseSupplierPaymentController extends Controller
{
    public function __invoke(
        string $supplierPaymentId,
        ReverseSupplierPaymentRequest $request,
        ReverseSupplierPaymentHandler $useCase,
    ): RedirectResponse {
        $data = $request->validated();

        $result = $useCase->handle(
            $supplierPaymentId,
            (string) $data['reason'],
            (string) $request->user()->getAuthIdentifier(),
        );

        if ($result->isFailure()) {
            return back()
                ->withErrors([
                    'supplier_payment_reversal' => $result->message() ?? 'Reversal pembayaran supplier gagal dicatat.',
                ])
                ->withInput();
        }

        return back()->with('success', $result->message() ?? 'Reversal pembayaran supplier berhasil dicatat.');
    }
}
