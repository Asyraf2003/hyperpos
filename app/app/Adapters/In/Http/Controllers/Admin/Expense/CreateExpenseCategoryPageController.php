<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Expense;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class CreateExpenseCategoryPageController extends Controller
{
    public function __invoke(Request $request): View
    {
        $source = trim((string) $request->query('source', ''));
        $keyword = trim((string) $request->query('q', ''));

        return view('admin.expenses.categories.create', [
            'source' => $source,
            'prefillKeyword' => $keyword,
            'backUrl' => $source === 'expense_create'
                ? route('admin.expenses.create')
                : route('admin.expenses.categories.index'),
        ]);
    }
}
