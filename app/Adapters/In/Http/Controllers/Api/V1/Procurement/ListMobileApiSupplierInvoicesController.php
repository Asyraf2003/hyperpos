<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Api\V1\Procurement;

use App\Application\IdentityAccess\Services\LoginActorAccessDecision;
use App\Application\MobileApi\Auth\DTO\MobileApiActor;
use App\Application\Procurement\DTO\ProcurementInvoiceTableQuery;
use App\Application\Procurement\UseCases\GetProcurementInvoiceTableHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class ListMobileApiSupplierInvoicesController extends Controller
{
    public function __construct(private readonly GetProcurementInvoiceTableHandler $invoices)
    {
    }

    public function __invoke(Request $request): JsonResponse
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

        $result = $this->invoices->handle(ProcurementInvoiceTableQuery::fromValidated([
            'q' => $this->nullableQueryString($request, 'q'),
            'payment_status' => $this->paymentStatus($request),
            'page' => $this->positiveIntQuery($request, 'page', 1),
            'per_page' => 10,
            'sort_by' => $this->sortBy($request),
            'sort_dir' => $this->sortDir($request),
            'shipment_date_from' => $this->nullableQueryString($request, 'shipment_date_from'),
            'shipment_date_to' => $this->nullableQueryString($request, 'shipment_date_to'),
        ]));

        /** @var array{rows:list<array<string, mixed>>, meta:array<string, mixed>} $payload */
        $payload = $result->data();

        return response()->json([
            'success' => true,
            'data' => [
                'rows' => $payload['rows'],
            ],
            'meta' => $payload['meta'],
            'errors' => null,
        ]);
    }

    private function nullableQueryString(Request $request, string $key): ?string
    {
        $value = $request->query($key);

        if (! is_string($value)) {
            return null;
        }

        $normalized = trim($value);

        return $normalized === '' ? null : $normalized;
    }

    private function paymentStatus(Request $request): string
    {
        $value = $this->nullableQueryString($request, 'payment_status') ?? 'all';

        return in_array($value, ['active', 'outstanding', 'paid', 'all', 'voided'], true)
            ? $value
            : 'all';
    }

    private function positiveIntQuery(Request $request, string $key, int $default): int
    {
        $value = $request->query($key);

        if (! is_numeric($value)) {
            return $default;
        }

        return max(1, (int) $value);
    }

    private function sortBy(Request $request): string
    {
        $value = $this->nullableQueryString($request, 'sort_by') ?? 'shipment_date';

        return in_array($value, [
            'shipment_date',
            'due_date',
            'nama_pt_pengirim',
            'grand_total_rupiah',
            'total_paid_rupiah',
            'outstanding_rupiah',
            'receipt_count',
            'total_received_qty',
        ], true) ? $value : 'shipment_date';
    }

    private function sortDir(Request $request): string
    {
        $value = $this->nullableQueryString($request, 'sort_dir') ?? 'desc';

        return in_array($value, ['asc', 'desc'], true) ? $value : 'desc';
    }
}
