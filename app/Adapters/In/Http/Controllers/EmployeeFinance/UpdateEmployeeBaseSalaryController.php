<?php
declare(strict_types=1);
namespace App\Adapters\In\Http\Controllers\EmployeeFinance;
use App\Adapters\In\Http\Requests\EmployeeFinance\UpdateEmployeeBaseSalaryRequest;
use App\Application\EmployeeFinance\UseCases\UpdateEmployeeBaseSalaryHandler;
use Illuminate\Http\JsonResponse;

final class UpdateEmployeeBaseSalaryController {
    public function __invoke(string $employeeId, UpdateEmployeeBaseSalaryRequest $request, UpdateEmployeeBaseSalaryHandler $handler): JsonResponse {
        $handler->handle(
            $employeeId,
            (int) $request->validated('new_salary_amount'),
            $request->validated('reason')
        );
        return response()->json(['message' => 'Salary updated successfully']);
    }
}
