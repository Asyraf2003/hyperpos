<?php
declare(strict_types=1);
namespace App\Adapters\In\Http\Controllers\EmployeeFinance;
use App\Adapters\In\Http\Requests\EmployeeFinance\RegisterEmployeeRequest;
use App\Application\EmployeeFinance\UseCases\RegisterEmployeeHandler;
use Illuminate\Http\JsonResponse;

final class RegisterEmployeeController {
    public function __invoke(RegisterEmployeeRequest $request, RegisterEmployeeHandler $handler): JsonResponse {
        $id = $handler->handle(
            $request->validated('name'),
            $request->validated('phone'),
            (int) $request->validated('base_salary_amount'),
            $request->validated('pay_period_value')
        );
        return response()->json(['data' => ['id' => $id]], 201);
    }
}
