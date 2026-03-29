<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Cashier\Note;

use App\Adapters\In\Http\Presenters\JsonPresenter;
use App\Adapters\In\Http\Requests\Note\CashierNoteTableQueryRequest;
use App\Application\Shared\DTO\Result;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class NoteHistoryTableDataController extends Controller
{
    public function __invoke(
        CashierNoteTableQueryRequest $request,
        JsonPresenter $presenter,
    ): JsonResponse {
        $filters = $request->validated();

        return $presenter->success(Result::success([
            'filters' => [
                'date' => $filters['date'] ?? date('Y-m-d'),
                'search' => $filters['search'] ?? '',
                'payment_status' => $filters['payment_status'] ?? '',
                'work_status' => $filters['work_status'] ?? '',
            ],
            'items' => [],
            'pagination' => [
                'page' => (int) ($filters['page'] ?? 1),
                'per_page' => (int) ($filters['per_page'] ?? 10),
                'total' => 0,
                'last_page' => 1,
            ],
            'summary' => [
                'label' => 'Riwayat kasir placeholder belum terhubung ke query database.',
            ],
        ]));
    }
}
