<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Note;

use App\Application\Note\Services\EnsureInitialNoteRevisionExists;
use App\Application\Note\Services\NoteCorrectionUiOptionsBuilder;
use App\Application\Note\Services\NoteDetailPageDataBuilder;
use App\Core\Shared\Exceptions\DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class NoteDetailPageController extends Controller
{
    public function __invoke(
        string $noteId,
        Request $request,
        NoteDetailPageDataBuilder $builder,
        NoteCorrectionUiOptionsBuilder $options,
        EnsureInitialNoteRevisionExists $ensureInitialRevision,
    ): View {
        $user = $request->user();
        $actorId = $user !== null ? (string) $user->getAuthIdentifier() : null;

        try {
            $ensureInitialRevision->handle(
                $noteId,
                trim($noteId) . '-r001',
                $actorId,
            );
        } catch (DomainException $e) {
            abort(500, $e->getMessage());
        }

        $data = $builder->build($noteId);
        abort_if($data === null, 404);

        return view('admin.notes.show', $data + [
            'addRowsAction' => route('admin.notes.rows.store', ['noteId' => $noteId]),
            'oldRows' => array_values(old('rows', [['line_type' => 'service']])),
            'paymentAction' => route('admin.notes.payments.store', ['noteId' => $noteId]),
            'paymentDateDefault' => date('Y-m-d'),
            'refundAction' => route('admin.notes.refunds.store', ['noteId' => $noteId]),
            'refundDateDefault' => date('Y-m-d'),
        ] + $options->build());
    }
}
