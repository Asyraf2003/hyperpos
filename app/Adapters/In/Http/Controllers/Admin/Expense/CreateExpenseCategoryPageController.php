<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Expense;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

final class CreateExpenseCategoryPageController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.expenses.categories.create');
    }
}
