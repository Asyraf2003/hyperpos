<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Procurement;

use App\Application\Procurement\UseCases\RecordSupplierPaymentHandler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class RecordSupplierPaymentController extends Controller
{
    public function __invoke(
        Request $request,
        RecordSupplierPaymentHandler $useCase,
        string $supplierInvoiceId,
    ): RedirectResponse {
        $data = $request->validate([
            'payment_date' => ['required', 'date_format:Y-m-d'],
            'amount' => ['required', 'integer', 'min:1'],
        ]);

        $user = $request->user();
        $actorId = $user?->getAuthIdentifier();

        $result = $useCase->handle(
            $supplierInvoiceId,
            (int) $data['amount'],
            (string) $data['payment_date'],
            $actorId !== null ? (string) $actorId : '',
        );

        if ($result->isFailure()) {
            return back()
                ->withErrors([
                    'supplier_payment' => $result->message() ?? 'Pembayaran supplier gagal dicatat.',
                ])
                ->withInput();
        }

        return back()->with('success', $result->message() ?? 'Pembayaran supplier berhasil dicatat.');
    }
}
