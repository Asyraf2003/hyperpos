<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Expense;

use App\Ports\Out\Expense\ExpenseCategoryReaderPort;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

final class EditExpenseCategoryPageController extends Controller
{
    public function __construct(
        private readonly ExpenseCategoryReaderPort $categories,
    ) {
    }

    public function __invoke(string $categoryId): View|RedirectResponse
    {
        $category = $this->categories->findById($categoryId);

        if ($category === null) {
            return redirect()
                ->route('admin.expenses.categories.index')
                ->with('error', 'Expense category tidak ditemukan.');
        }

        return view('admin.expenses.categories.edit', [
            'category' => $category,
        ]);
    }
}
