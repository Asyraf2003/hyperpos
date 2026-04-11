<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Employee;

use App\Adapters\Out\EmployeeFinance\DatabaseEmployeeDetailPageQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

final class EmployeeDetailPageController extends Controller
{
    public function __invoke(string $employeeId, DatabaseEmployeeDetailPageQuery $query): View|RedirectResponse
    {
        $detail = $query->findById($employeeId);

        if ($detail === null) {
            return redirect()
                ->route('admin.employees.index')
                ->with('error', 'Data karyawan tidak ditemukan.');
        }

        return view('admin.employees.show', [
            'detail' => $detail,
            'page' => $detail['page'],
        ]);
    }
}
