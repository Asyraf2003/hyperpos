<?php

declare(strict_types=1);

namespace Tests\Feature\Expense;

use App\Adapters\Out\Expense\DatabaseExpenseCategoryReaderAdapter;
use App\Adapters\Out\Expense\DatabaseExpenseCategoryWriterAdapter;
use App\Application\Expense\UseCases\CreateExpenseCategoryHandler;
use App\Application\Shared\DTO\Result;
use App\Ports\Out\UuidPort;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class CreateExpenseCategoryFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_expense_category_handler_stores_new_category(): void
    {
        $handler = new CreateExpenseCategoryHandler(
            new DatabaseExpenseCategoryReaderAdapter(),
            new DatabaseExpenseCategoryWriterAdapter(),
            new class () implements UuidPort {
                public function generate(): string
                {
                    return 'expense-category-1';
                }
            },
        );

        $result = $handler->handle(
            'LISTRIK',
            'Listrik',
            'Biaya listrik bulanan',
        );

        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->isSuccess());

        $this->assertDatabaseHas('expense_categories', [
            'id' => 'expense-category-1',
            'code' => 'LISTRIK',
            'name' => 'Listrik',
            'description' => 'Biaya listrik bulanan',
            'is_active' => 1,
        ]);
    }

    public function test_create_expense_category_handler_rejects_duplicate_code(): void
    {
        DB::table('expense_categories')->insert([
            'id' => 'expense-category-old',
            'code' => 'LISTRIK',
            'name' => 'Listrik Lama',
            'description' => null,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $handler = new CreateExpenseCategoryHandler(
            new DatabaseExpenseCategoryReaderAdapter(),
            new DatabaseExpenseCategoryWriterAdapter(),
            new class () implements UuidPort {
                public function generate(): string
                {
                    return 'expense-category-1';
                }
            },
        );

        $result = $handler->handle(
            'LISTRIK',
            'Listrik',
            null,
        );

        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->isFailure());
        $this->assertSame(
            ['expense_category' => ['EXPENSE_CATEGORY_CODE_ALREADY_EXISTS']],
            $result->errors(),
        );

        $this->assertDatabaseCount('expense_categories', 1);
    }

    public function test_create_expense_category_handler_rejects_blank_name(): void
    {
        $handler = new CreateExpenseCategoryHandler(
            new DatabaseExpenseCategoryReaderAdapter(),
            new DatabaseExpenseCategoryWriterAdapter(),
            new class () implements UuidPort {
                public function generate(): string
                {
                    return 'expense-category-1';
                }
            },
        );

        $result = $handler->handle(
            'LISTRIK',
            '   ',
            null,
        );

        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->isFailure());
        $this->assertSame(
            ['expense_category' => ['INVALID_EXPENSE_CATEGORY']],
            $result->errors(),
        );

        $this->assertDatabaseCount('expense_categories', 0);
    }
}
