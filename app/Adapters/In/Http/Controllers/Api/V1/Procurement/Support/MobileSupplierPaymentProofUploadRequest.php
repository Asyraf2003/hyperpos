<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Api\V1\Procurement\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;

final class MobileSupplierPaymentProofUploadRequest
{
    public function validate(Request $request): ?JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'proof_files' => ['required', 'array', 'min:1', 'max:3'],
            'proof_files.*' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        if (! $validator->fails()) {
            return null;
        }

        return response()->json([
            'success' => false,
            'data' => null,
            'message' => 'Bukti pembayaran supplier tidak valid.',
            'errors' => [
                'supplier_payment_proof' => ['INVALID_SUPPLIER_PAYMENT_PROOF'],
            ],
        ], 422);
    }

    /**
     * @return list<array{source_path:string,original_filename:string,mime_type:string,file_size_bytes:int}>
     */
    public function uploadedFiles(Request $request): array
    {
        $proofFiles = $request->file('proof_files', []);

        if (! is_array($proofFiles)) {
            return [];
        }

        $uploadedFiles = [];

        foreach ($proofFiles as $file) {
            if ($file instanceof UploadedFile) {
                $this->appendFile($uploadedFiles, $file);
            }
        }

        return $uploadedFiles;
    }

    /** @param list<array{source_path:string,original_filename:string,mime_type:string,file_size_bytes:int}> $files */
    private function appendFile(array &$files, UploadedFile $file): void
    {
        $sourcePath = $file->getRealPath();

        if (! is_string($sourcePath) || $sourcePath === '') {
            return;
        }

        $files[] = [
            'source_path' => $sourcePath,
            'original_filename' => (string) $file->getClientOriginalName(),
            'mime_type' => (string) $file->getClientMimeType(),
            'file_size_bytes' => (int) $file->getSize(),
        ];
    }
}
