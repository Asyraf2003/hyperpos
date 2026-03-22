<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\EmployeeDebt;

use App\Adapters\Out\EmployeeFinance\DatabaseEmployeeDebtDetailPageQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

final class EmployeeDebtDetailPageController extends Controller
{
    public function __invoke(string $debtId, DatabaseEmployeeDebtDetailPageQuery $query): View|RedirectResponse
    {
        $detail = $query->findById($debtId);

        if ($detail === null) {
            return redirect()
                ->route('admin.employee-debts.index')
                ->with('error', 'Data hutang karyawan tidak ditemukan.');
        }

        return view('admin.employee_debts.show', [
            'detail' => $detail,
        ]);
    }
}
