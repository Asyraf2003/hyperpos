<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Cashier\Note;

use App\Application\Note\Services\NoteCorrectionUiOptionsBuilder;
use App\Application\Note\Services\NoteDetailPageDataBuilder;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

final class NoteDetailPageController extends Controller
{
    public function __invoke(
        string $noteId,
        NoteDetailPageDataBuilder $builder,
        NoteCorrectionUiOptionsBuilder $options,
    ): View {
        $data = $builder->build($noteId);
        abort_if($data === null, 404);

        return view('cashier.notes.show', $data + [
            'addRowsAction' => route('cashier.notes.rows.store', ['noteId' => $noteId]),
            'oldRows' => array_values(old('rows', [['line_type' => 'service']])),
            'paymentAction' => route('cashier.notes.payments.store', ['noteId' => $noteId]),
            'paymentDateDefault' => date('Y-m-d'),
            'statusCorrectionAction' => route('cashier.notes.corrections.status.store', ['noteId' => $noteId]),
            'serviceOnlyCorrectionAction' => route('cashier.notes.corrections.service-only.store', ['noteId' => $noteId]),
        ] + $options->build());
    }
}
