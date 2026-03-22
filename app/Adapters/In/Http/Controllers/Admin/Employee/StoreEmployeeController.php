<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Employee;

use App\Adapters\In\Http\Requests\EmployeeFinance\RegisterEmployeeRequest;
use App\Application\EmployeeFinance\UseCases\RegisterEmployeeHandler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Throwable;

final class StoreEmployeeController extends Controller
{
    public function __invoke(
        RegisterEmployeeRequest $request,
        RegisterEmployeeHandler $useCase,
    ): RedirectResponse {
        $data = $request->validated();

        try {
            $useCase->handle(
                (string) $data['name'],
                isset($data['phone']) && $data['phone'] !== '' ? (string) $data['phone'] : null,
                (int) $data['base_salary_amount'],
                (string) $data['pay_period_value'],
            );
        } catch (Throwable $e) {
            $message = trim($e->getMessage()) !== '' ? $e->getMessage() : 'Data karyawan gagal dibuat.';

            return back()
                ->withErrors([
                    'employee' => $message,
                ])
                ->withInput();
        }

        return redirect()
            ->route('admin.employees.index')
            ->with('success', 'Data karyawan berhasil dibuat.');
    }
}
