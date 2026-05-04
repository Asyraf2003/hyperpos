<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Note;

use App\Adapters\In\Http\Presenters\JsonPresenter;
use App\Adapters\In\Http\Requests\Note\AdminNoteTableQueryRequest;
use App\Application\Note\Services\AdminNoteHistoryTableData;
use App\Application\Shared\DTO\Result;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class NoteHistoryTableDataController extends Controller
{
    public function __invoke(
        AdminNoteTableQueryRequest $request,
        AdminNoteHistoryTableData $query,
        JsonPresenter $presenter,
    ): JsonResponse {
        return $presenter->success(
            Result::success($query->get($request->validated()))
        );
    }
}
