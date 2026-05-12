<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Api\V1\Procurement;

use App\Adapters\In\Http\Controllers\Api\V1\Procurement\Support\MobileSupplierPaymentProofUploadRequest;
use App\Adapters\In\Http\Controllers\Api\V1\Support\MobileApiAdminAccess;
use App\Application\MobileApi\Auth\DTO\MobileApiActor;
use App\Application\Procurement\UseCases\AttachSupplierPaymentProofHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class UploadMobileApiSupplierPaymentProofController extends Controller
{
    public function __construct(
        private readonly AttachSupplierPaymentProofHandler $proofs,
        private readonly MobileApiAdminAccess $access,
        private readonly MobileSupplierPaymentProofUploadRequest $uploadRequest,
    ) {
    }

    public function __invoke(Request $request, string $supplierPaymentId): JsonResponse
    {
        $actor = $this->access->actorOrError(
            $request,
            'Akses bukti pembayaran supplier mobile hanya untuk admin.'
        );

        if (! $actor instanceof MobileApiActor) {
            return $actor;
        }

        $validationError = $this->uploadRequest->validate($request);

        if ($validationError !== null) {
            return $validationError;
        }

        $result = $this->proofs->handle(
            $supplierPaymentId,
            $this->uploadRequest->uploadedFiles($request),
            $actor->id
        );

        if ($result->isFailure()) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $result->message(),
                'errors' => $result->errors(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data' => $result->data(),
            'message' => $result->message(),
            'errors' => null,
        ]);
    }
}
