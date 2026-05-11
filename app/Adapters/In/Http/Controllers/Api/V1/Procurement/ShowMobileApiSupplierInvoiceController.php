<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Api\V1\Procurement;

use App\Application\IdentityAccess\Services\LoginActorAccessDecision;
use App\Application\MobileApi\Auth\DTO\MobileApiActor;
use App\Application\Procurement\UseCases\GetProcurementInvoiceDetailHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class ShowMobileApiSupplierInvoiceController extends Controller
{
    public function __construct(private readonly GetProcurementInvoiceDetailHandler $details)
    {
    }

    public function __invoke(Request $request, string $supplierInvoiceId): JsonResponse
    {
        $actor = $request->attributes->get('mobile_api_actor');

        if (! $actor instanceof MobileApiActor) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Autentikasi diperlukan.',
                'errors' => [
                    'token' => ['UNAUTHENTICATED'],
                ],
            ], 401);
        }

        if ($actor->role !== LoginActorAccessDecision::ADMIN) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Akses nota supplier mobile hanya untuk admin.',
                'errors' => [
                    'role' => ['ADMIN_ONLY'],
                ],
            ], 403);
        }

        $result = $this->details->handle($supplierInvoiceId);

        if ($result->isFailure()) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $result->message(),
                'errors' => $result->errors(),
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $result->data(),
            'meta' => null,
            'errors' => null,
        ]);
    }
}
