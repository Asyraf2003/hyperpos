<?php
declare(strict_types=1);
namespace App\Adapters\In\Http\Controllers\EmployeeFinance;
use App\Adapters\In\Http\Requests\EmployeeFinance\DisbursePayrollRequest;
use App\Application\EmployeeFinance\UseCases\DisbursePayrollHandler;
use Illuminate\Http\JsonResponse;

final class DisbursePayrollController {
    public function __invoke(DisbursePayrollRequest $request, DisbursePayrollHandler $handler): JsonResponse {
        $id = $handler->handle(
            $request->validated('employee_id'),
            (int) $request->validated('amount'),
            $request->validated('disbursement_date_string'),
            $request->validated('mode_value'),
            $request->validated('notes')
        );
        return response()->json(['data' => ['id' => $id]], 201);
    }
}
