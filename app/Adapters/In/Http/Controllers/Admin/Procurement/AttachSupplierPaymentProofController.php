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
            'proof_files' => ['required', 'array', 'min:1', 'max:3'],
            'proof_files.*' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        $user = $request->user();
        $actorId = $user?->getAuthIdentifier();

        $uploadedFiles = [];
        $storedPaths = [];

        foreach ($data['proof_files'] as $file) {
            $storedPath = Storage::disk('local')->putFileAs(
                'supplier-payment-proofs/' . trim($supplierPaymentId),
                $file,
                $file->hashName(),
            );

            if (! is_string($storedPath) || $storedPath === '') {
                if ($storedPaths !== []) {
                    Storage::disk('local')->delete($storedPaths);
                }

                return back()
                    ->withErrors([
                        'supplier_payment_proof' => 'Bukti pembayaran supplier gagal diunggah.',
                    ])
                    ->withInput();
            }

            $storedPaths[] = $storedPath;
            $uploadedFiles[] = [
                'storage_path' => $storedPath,
                'original_filename' => (string) $file->getClientOriginalName(),
                'mime_type' => (string) $file->getClientMimeType(),
                'file_size_bytes' => (int) $file->getSize(),
            ];
        }

        $result = $useCase->handle(
            $supplierPaymentId,
            $uploadedFiles,
            $actorId !== null ? (string) $actorId : '',
        );

        if ($result->isFailure()) {
            if ($storedPaths !== []) {
                Storage::disk('local')->delete($storedPaths);
            }

            return back()
                ->withErrors([
                    'supplier_payment_proof' => $result->message() ?? 'Bukti pembayaran supplier gagal diunggah.',
                ])
                ->withInput();
        }

        return back()->with('success', $result->message() ?? 'Bukti pembayaran supplier berhasil diunggah.');
    }
}
