<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Expense;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

final class ExpenseCategoryIndexPageController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.expenses.categories.index');
    }
}
