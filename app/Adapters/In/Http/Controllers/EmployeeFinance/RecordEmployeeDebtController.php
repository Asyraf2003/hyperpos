<?php
declare(strict_types=1);
namespace App\Adapters\In\Http\Controllers\EmployeeFinance;
use App\Adapters\In\Http\Requests\EmployeeFinance\RecordEmployeeDebtRequest;
use App\Application\EmployeeFinance\UseCases\RecordEmployeeDebtHandler;
use Illuminate\Http\JsonResponse;

final class RecordEmployeeDebtController {
    public function __invoke(RecordEmployeeDebtRequest $request, RecordEmployeeDebtHandler $handler): JsonResponse {
        $id = $handler->handle(
            $request->validated('employee_id'),
            (int) $request->validated('debt_amount'),
            $request->validated('notes')
        );
        return response()->json(['data' => ['id' => $id]], 201);
    }
}
