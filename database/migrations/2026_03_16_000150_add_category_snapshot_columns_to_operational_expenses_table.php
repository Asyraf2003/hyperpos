<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('operational_expenses', function (Blueprint $table): void {
            $table->string('category_code_snapshot')->nullable()->after('category_id');
            $table->string('category_name_snapshot')->nullable()->after('category_code_snapshot');
        });

        $rows = DB::table('operational_expenses')
            ->join('expense_categories', 'expense_categories.id', '=', 'operational_expenses.category_id')
            ->get([
                'operational_expenses.id as expense_id',
                'expense_categories.code as category_code',
                'expense_categories.name as category_name',
            ]);

        foreach ($rows as $row) {
            DB::table('operational_expenses')
                ->where('id', (string) $row->expense_id)
                ->update([
                    'category_code_snapshot' => (string) $row->category_code,
                    'category_name_snapshot' => (string) $row->category_name,
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('operational_expenses', function (Blueprint $table): void {
            $table->dropColumn([
                'category_code_snapshot',
                'category_name_snapshot',
            ]);
        });
    }
};
