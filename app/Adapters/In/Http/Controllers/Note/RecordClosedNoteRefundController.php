<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Note;

use App\Adapters\In\Http\Controllers\Note\Support\ClosedNoteRefundResponseFactory;
use App\Adapters\In\Http\Controllers\Note\Support\NoteRouteAreaResolver;
use App\Adapters\In\Http\Requests\Note\RecordClosedNoteRefundRequest;
use App\Application\Note\Services\NoteOperationalStatusResolver;
use App\Application\Note\Services\SelectedNoteRowsRefundPlanResolver;
use App\Application\Payment\DTO\SelectedRowsRefundPlan;
use App\Application\Payment\Services\RecordSelectedRowsRefundIdempotencyService;
use App\Application\Payment\Services\RecordSelectedRowsRefundPlanTransaction;
use App\Ports\Out\Note\NoteReaderPort;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

final class RecordClosedNoteRefundController extends Controller
{
    public function __invoke(
        string $noteId,
        RecordClosedNoteRefundRequest $request,
        SelectedNoteRowsRefundPlanResolver $plans,
        RecordSelectedRowsRefundPlanTransaction $transaction,
        NoteRouteAreaResolver $routes,
        NoteReaderPort $notes,
        NoteOperationalStatusResolver $statuses,
        RecordSelectedRowsRefundIdempotencyService $idempotency,
        ClosedNoteRefundResponseFactory $responses,
    ): RedirectResponse {
        $data = $request->validated();
        $actorId = (string) $request->user()->getAuthIdentifier();
        $actorRole = $request->routeIs('admin.notes.*') ? 'admin' : 'kasir';
        $idempotencyPayload = $data + [
            '_actor_id' => $actorId,
            '_note_id' => trim($noteId),
        ];

        $replayed = $idempotency->replay($idempotencyPayload);

        if ($replayed !== null) {
            return $replayed->isFailure()
                ? $responses->failed($replayed->message())
                : $responses->success($request, $routes, $replayed->message());
        }

        $selectedRowIds = is_array($data['selected_row_ids'] ?? null)
            ? array_values($data['selected_row_ids'])
            : [];

        $note = $notes->getById(trim($noteId));

        if ($note === null) {
            return $responses->failed('Nota tidak ditemukan.');
        }

        if (! $statuses->isClose($note)) {
            return $responses->failed('Refund hanya bisa dicatat untuk nota yang sudah close/lunas.');
        }

        $planResult = $plans->resolve($noteId, $selectedRowIds);

        if ($planResult->isFailure()) {
            return $responses->failed($planResult->message());
        }

        $plan = $planResult->data()['plan'] ?? null;

        if (! $plan instanceof SelectedRowsRefundPlan) {
            return $responses->failed('Refund plan tidak valid.');
        }

        $result = $transaction->run(
            $plan,
            (string) $data['refunded_at'],
            (string) $data['reason'],
            $actorId,
            $actorRole,
            $idempotencyPayload,
        );

        return $result->isFailure()
            ? $responses->failed($result->message())
            : $responses->success($request, $routes, $result->message());
    }
}
