<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\EmployeeDebt;

use App\Application\EmployeeFinance\Services\EmployeeDebtPrincipalPageDataBuilder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

final class EmployeeDebtPrincipalPageController extends Controller
{
    public function __invoke(
        string $debtId,
        EmployeeDebtPrincipalPageDataBuilder $pageData,
    ): View|RedirectResponse {
        $data = $pageData->build($debtId);

        if ($data === null) {
            return redirect()
                ->route('admin.employee-debts.index')
                ->with('error', 'Data hutang karyawan tidak ditemukan.');
        }

        return view('admin.employee_debts.principal', $data);
    }
}
