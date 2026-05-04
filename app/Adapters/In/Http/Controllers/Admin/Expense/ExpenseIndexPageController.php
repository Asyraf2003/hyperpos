<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Expense;

use App\Application\Expense\Services\ExpenseCategoryOptionList;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

final class ExpenseIndexPageController extends Controller
{
    public function __invoke(ExpenseCategoryOptionList $categories): View
    {
        return view('admin.expenses.index', [
            'categoryOptions' => $categories->active(),
        ]);
    }
}
