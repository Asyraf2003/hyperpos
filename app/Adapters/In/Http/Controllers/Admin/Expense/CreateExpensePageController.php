<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Expense;

use App\Adapters\Out\Expense\DatabaseExpenseCategoryListPageQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

final class CreateExpensePageController extends Controller
{
    public function __invoke(DatabaseExpenseCategoryListPageQuery $categories): View
    {
        return view('admin.expenses.create', [
            'categoryOptions' => $categories->listActiveOptions(),
        ]);
    }
}
