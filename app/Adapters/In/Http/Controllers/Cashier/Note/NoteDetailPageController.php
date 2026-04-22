<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Cashier\Note;

use App\Application\Note\Policies\CashierNoteAccessGuard;
use App\Application\Note\Services\EnsureInitialNoteRevisionExists;
use App\Application\Note\Services\NoteCorrectionUiOptionsBuilder;
use App\Application\Note\Services\NoteDetailPageDataBuilder;
use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\ClockPort;
use App\Ports\Out\Note\NoteReaderPort;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class NoteDetailPageController extends Controller
{
    public function __invoke(
        string $noteId,
        Request $request,
        NoteReaderPort $notes,
        NoteDetailPageDataBuilder $builder,
        NoteCorrectionUiOptionsBuilder $options,
        CashierNoteAccessGuard $guard,
        ClockPort $clock,
        EnsureInitialNoteRevisionExists $ensureInitialRevision,
    ): View {
        $note = $notes->getById($noteId);
        abort_if($note === null, 404);

        try {
            $guard->assertCanView($note, $clock->now());
        } catch (DomainException $e) {
            abort(403, $e->getMessage());
        }

        $user = $request->user();
        $actorId = $user !== null ? (string) $user->getAuthIdentifier() : null;

        try {
            $ensureInitialRevision->handle($noteId, trim($noteId) . '-r001', $actorId);
        } catch (DomainException $e) {
            abort(500, $e->getMessage());
        }

        $data = $builder->build($noteId);
        abort_if($data === null, 404);

        $paymentAction = route('cashier.notes.payments.store', ['noteId' => $noteId]);
        $refundAction = route('cashier.notes.refunds.store', ['noteId' => $noteId]);

        return view('shared.notes.show', $data + [
            'backUrl' => route('cashier.notes.index'),
            'pageIntroEyebrow' => 'Workspace Nota Kasir',
            'pageIntroTitle' => 'Detail Nota',
            'pageIntroSubtitle' => 'Detail operasional nota aktif untuk kerja kasir pada scope 2 hari terakhir.',
            'detailConfig' => [
                'workspace_edit_route' => 'cashier.notes.workspace.edit',
            ],
            'addRowsAction' => route('cashier.notes.rows.store', ['noteId' => $noteId]),
            'oldRows' => array_values(old('rows', [['line_type' => 'service']])),
            'paymentAction' => $paymentAction,
            'paymentDateDefault' => date('Y-m-d'),
            'paymentModalConfig' => [
                'action' => $paymentAction,
                'date_default' => date('Y-m-d'),
            ],
            'refundAction' => $refundAction,
            'refundDateDefault' => date('Y-m-d'),
            'refundModalConfig' => [
                'action' => $refundAction,
                'date_default' => date('Y-m-d'),
            ],
            'statusCorrectionAction' => route('cashier.notes.corrections.status.store', ['noteId' => $noteId]),
            'serviceOnlyCorrectionAction' => route('cashier.notes.corrections.service-only.store', ['noteId' => $noteId]),
        ] + $options->build());
    }
}
