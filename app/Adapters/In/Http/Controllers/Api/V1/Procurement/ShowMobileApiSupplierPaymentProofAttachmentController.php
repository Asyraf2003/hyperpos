<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Api\V1\Procurement;

use App\Adapters\In\Http\Controllers\Api\V1\Procurement\Support\MobileSupplierPaymentProofAttachmentResponseFactory;
use App\Adapters\In\Http\Controllers\Api\V1\Support\MobileApiAdminAccess;
use App\Application\MobileApi\Auth\DTO\MobileApiActor;
use App\Application\Procurement\UseCases\GetSupplierPaymentProofAttachmentFileHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

final class ShowMobileApiSupplierPaymentProofAttachmentController extends Controller
{
    public function __construct(
        private readonly GetSupplierPaymentProofAttachmentFileHandler $proofs,
        private readonly MobileApiAdminAccess $access,
        private readonly MobileSupplierPaymentProofAttachmentResponseFactory $responses,
    ) {
    }

    public function __invoke(Request $request, string $attachmentId): JsonResponse|Response
    {
        $actor = $this->access->actorOrError(
            $request,
            'Akses bukti pembayaran supplier mobile hanya untuk admin.'
        );

        if (! $actor instanceof MobileApiActor) {
            return $actor;
        }

        $file = $this->proofs->handle($attachmentId);

        if ($file === null) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Bukti pembayaran supplier tidak ditemukan.',
                'errors' => [
                    'supplier_payment_proof' => ['SUPPLIER_PAYMENT_PROOF_NOT_FOUND'],
                ],
            ], 404);
        }

        return $this->responses->make($request, $file);
    }
}
