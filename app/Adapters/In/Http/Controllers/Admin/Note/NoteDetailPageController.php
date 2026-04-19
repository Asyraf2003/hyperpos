<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Note;

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
