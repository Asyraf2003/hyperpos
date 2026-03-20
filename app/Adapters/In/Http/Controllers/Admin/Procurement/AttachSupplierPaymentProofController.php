<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Procurement;

use App\Application\Procurement\UseCases\AttachSupplierPaymentProofHandler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;

final class AttachSupplierPaymentProofController extends Controller
{
    public function __invoke(
        Request $request,
        AttachSupplierPaymentProofHandler $useCase,
        string $supplierPaymentId,
    ): RedirectResponse {
        $data = $request->validate([
            'proof_file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        $user = $request->user();
        $actorId = $user?->getAuthIdentifier();

        $file = $data['proof_file'];
        $storedPath = Storage::disk('local')->putFileAs(
            'supplier-payment-proofs/' . trim($supplierPaymentId),
            $file,
            $file->hashName(),
        );

        if (! is_string($storedPath) || $storedPath === '') {
            return back()
                ->withErrors([
                    'supplier_payment_proof' => 'Bukti pembayaran supplier gagal diunggah.',
                ])
                ->withInput();
        }

        $result = $useCase->handle(
            $supplierPaymentId,
            $storedPath,
            $actorId !== null ? (string) $actorId : '',
        );

        if ($result->isFailure()) {
            return back()
                ->withErrors([
                    'supplier_payment_proof' => $result->message() ?? 'Bukti pembayaran supplier gagal diunggah.',
                ])
                ->withInput();
        }

        return back()->with('success', $result->message() ?? 'Bukti pembayaran supplier berhasil diunggah.');
    }
}
