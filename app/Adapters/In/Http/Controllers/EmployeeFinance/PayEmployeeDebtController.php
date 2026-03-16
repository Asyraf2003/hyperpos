<?php
declare(strict_types=1);
namespace App\Adapters\In\Http\Controllers\EmployeeFinance;
use App\Adapters\In\Http\Requests\EmployeeFinance\PayEmployeeDebtRequest;
use App\Application\EmployeeFinance\UseCases\PayEmployeeDebtHandler;
use Illuminate\Http\JsonResponse;

final class PayEmployeeDebtController {
    public function __invoke(string $debtId, PayEmployeeDebtRequest $request, PayEmployeeDebtHandler $handler): JsonResponse {
        $paymentId = $handler->handle(
            $debtId,
            (int) $request->validated('payment_amount'),
            $request->validated('notes')
        );
        return response()->json(['data' => ['payment_id' => $paymentId]], 201);
    }
}
