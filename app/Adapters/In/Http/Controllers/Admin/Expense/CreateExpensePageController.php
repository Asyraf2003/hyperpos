<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Expense;

use App\Adapters\Out\Expense\DatabaseExpenseCategoryListPageQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class CreateExpensePageController extends Controller
{
    public function __invoke(
        Request $request,
        DatabaseExpenseCategoryListPageQuery $categories,
    ): View {
        $selectedCategoryId = trim((string) $request->query('category_id', ''));

        if ($selectedCategoryId === '') {
            $oldCategoryId = old('category_id');
            $selectedCategoryId = is_string($oldCategoryId) ? trim($oldCategoryId) : '';
        }

        return view('admin.expenses.create', [
            'categoryOptions' => $categories->listActiveOptions(),
            'selectedCategoryId' => $selectedCategoryId,
            'createCategoryUrl' => route('admin.expenses.categories.create'),
        ]);
    }
}
