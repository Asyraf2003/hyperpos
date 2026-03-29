<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Note;

use App\Adapters\In\Http\Presenters\JsonPresenter;
use App\Adapters\In\Http\Requests\Note\AdminNoteTableQueryRequest;
use App\Application\Shared\DTO\Result;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class NoteHistoryTableDataController extends Controller
{
    public function __invoke(
        AdminNoteTableQueryRequest $request,
        JsonPresenter $presenter,
    ): JsonResponse {
        $filters = $request->validated();

        return $presenter->success(Result::success([
            'filters' => [
                'date_from' => $filters['date_from'] ?? date('Y-m-d'),
                'date_to' => $filters['date_to'] ?? date('Y-m-d'),
                'search' => $filters['search'] ?? '',
                'payment_status' => $filters['payment_status'] ?? '',
                'editability' => $filters['editability'] ?? '',
                'work_summary' => $filters['work_summary'] ?? '',
            ],
            'items' => [],
            'pagination' => [
                'page' => (int) ($filters['page'] ?? 1),
                'per_page' => (int) ($filters['per_page'] ?? 10),
                'total' => 0,
                'last_page' => 1,
            ],
            'summary' => [
                'label' => 'Riwayat admin placeholder belum terhubung ke query database.',
            ],
        ]));
    }
}
