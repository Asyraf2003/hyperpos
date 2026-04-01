<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Cashier\Note;

use App\Application\Note\Services\EditTransactionWorkspacePageDataBuilder;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

final class EditTransactionWorkspacePageController extends Controller
{
    public function __invoke(
        string $noteId,
        EditTransactionWorkspacePageDataBuilder $builder,
    ): View {
        return view('cashier.notes.workspace.create', $builder->build($noteId));
    }
}
