<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Note;

use App\Adapters\In\Http\Controllers\Note\Support\NoteRouteAreaResolver;
use App\Adapters\In\Http\Requests\Note\RecordClosedNoteRefundRequest;
use App\Application\Note\Services\NoteOperationalStatusResolver;
use App\Application\Note\Services\SelectedNoteRowsRefundPlanResolver;
use App\Application\Payment\DTO\SelectedRowsRefundPlan;
use App\Ports\Out\Note\NoteReaderPort;
use App\Application\Payment\Services\RecordSelectedRowsRefundPlanTransaction;
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
    ): RedirectResponse {
        $data = $request->validated();
        $actorId = (string) $request->user()->getAuthIdentifier();
        $actorRole = $request->routeIs('admin.notes.*') ? 'admin' : 'kasir';
        $selectedRowIds = is_array($data['selected_row_ids'] ?? null)
            ? array_values($data['selected_row_ids'])
            : [];

        $note = $notes->getById(trim($noteId));

        if ($note === null) {
            return back()
                ->withErrors(['refund' => 'Nota tidak ditemukan.'])
                ->withInput();
        }

        if (!$statuses->isClose($note)) {
            return back()
                ->withErrors(['refund' => 'Refund hanya bisa dicatat untuk nota yang sudah close/lunas.'])
                ->withInput();
        }

        $planResult = $plans->resolve($noteId, $selectedRowIds);

        if ($planResult->isFailure()) {
            return back()
                ->withErrors(['refund' => $planResult->message() ?? 'Refund gagal dicatat.'])
                ->withInput();
        }

        $plan = $planResult->data()['plan'] ?? null;

        if (!$plan instanceof SelectedRowsRefundPlan) {
            return back()
                ->withErrors(['refund' => 'Refund plan tidak valid.'])
                ->withInput();
        }

        $result = $transaction->run(
            $plan,
            (string) $data['refunded_at'],
            (string) $data['reason'],
            $actorId,
            $actorRole,
        );

        if ($result->isFailure()) {
            return back()
                ->withErrors(['refund' => $result->message() ?? 'Refund gagal dicatat.'])
                ->withInput();
        }

        return redirect()
            ->route($routes->indexRoute($request))
            ->with('success', $result->message() ?? 'Refund berhasil dicatat.');
    }
}
