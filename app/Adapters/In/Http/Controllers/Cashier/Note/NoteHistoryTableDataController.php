<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Cashier\Note;

use App\Adapters\In\Http\Presenters\JsonPresenter;
use App\Adapters\In\Http\Requests\Note\CashierNoteTableQueryRequest;
use App\Adapters\Out\Note\Queries\CashierNoteHistoryTableQuery;
use App\Application\Shared\DTO\Result;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class NoteHistoryTableDataController extends Controller
{
    public function __invoke(
        CashierNoteTableQueryRequest $request,
        CashierNoteHistoryTableQuery $query,
        JsonPresenter $presenter,
    ): JsonResponse {
        return $presenter->success(
            Result::success($query->get($request->validated()))
        );
    }
}
