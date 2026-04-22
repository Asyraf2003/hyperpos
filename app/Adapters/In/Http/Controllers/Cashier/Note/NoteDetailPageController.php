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
use Illuminate\Routing\Controller;

final class NoteDetailPageController extends Controller
{
    public function __invoke(
        string $noteId,
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

        $actorId = auth()->user()?->getAuthIdentifier();
        $bootstrapRevisionId = trim($noteId) . '-r001';

        try {
            $ensureInitialRevision->handle(
                $noteId,
                $bootstrapRevisionId,
                $actorId !== null ? (string) $actorId : null,
            );
        } catch (DomainException $e) {
            abort(500, $e->getMessage());
        }

        $data = $builder->build($noteId);
        abort_if($data === null, 404);

        $paymentAction = route('cashier.notes.payments.store', ['noteId' => $noteId]);
        $refundAction = route('cashier.notes.refunds.store', ['noteId' => $noteId]);
        $paymentDateDefault = date('Y-m-d');
        $refundDateDefault = date('Y-m-d');

        return view('cashier.notes.show', $data + [
            'addRowsAction' => route('cashier.notes.rows.store', ['noteId' => $noteId]),
            'oldRows' => array_values(old('rows', [['line_type' => 'service']])),
            'paymentAction' => $paymentAction,
            'paymentDateDefault' => $paymentDateDefault,
            'paymentModalConfig' => [
                'action' => $paymentAction,
                'date_default' => $paymentDateDefault,
            ],
            'refundAction' => $refundAction,
            'refundDateDefault' => $refundDateDefault,
            'refundModalConfig' => [
                'action' => $refundAction,
                'date_default' => $refundDateDefault,
            ],
            'statusCorrectionAction' => route('cashier.notes.corrections.status.store', ['noteId' => $noteId]),
            'serviceOnlyCorrectionAction' => route('cashier.notes.corrections.service-only.store', ['noteId' => $noteId]),
        ] + $options->build());
    }
}
