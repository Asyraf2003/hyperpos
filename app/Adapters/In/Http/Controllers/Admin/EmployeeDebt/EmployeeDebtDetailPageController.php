<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\EmployeeDebt;

use App\Application\EmployeeFinance\Services\EmployeeDebtDetailPageDataBuilder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

final class EmployeeDebtDetailPageController extends Controller
{
    public function __invoke(
        string $debtId,
        EmployeeDebtDetailPageDataBuilder $pageData,
    ): View|RedirectResponse {
        $data = $pageData->build($debtId);

        if ($data === null) {
            return redirect()
                ->route('admin.employee-debts.index')
                ->with('error', 'Data hutang karyawan tidak ditemukan.');
        }

        return view('admin.employee_debts.show', $data);
    }
}
