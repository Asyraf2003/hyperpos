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
        Schema::table('employees', function (Blueprint $table): void {
            $table->string('employee_name')->nullable()->after('id');
            $table->string('salary_basis_type', 20)->nullable()->after('phone');
            $table->bigInteger('default_salary_amount')->nullable()->after('salary_basis_type');
            $table->string('employment_status', 20)->nullable()->after('default_salary_amount');
            $table->date('started_at')->nullable()->after('employment_status');
            $table->date('ended_at')->nullable()->after('started_at');
        });

        DB::table('employees')->update([
            'employee_name' => DB::raw('name'),
            'salary_basis_type' => DB::raw('pay_period'),
            'default_salary_amount' => DB::raw('base_salary'),
            'employment_status' => DB::raw('status'),
        ]);

        Schema::table('employees', function (Blueprint $table): void {
            $table->string('employee_name')->nullable(false)->change();
            $table->string('salary_basis_type', 20)->nullable(false)->change();
            $table->string('employment_status', 20)->nullable(false)->change();
        });

        Schema::table('employees', function (Blueprint $table): void {
            $table->dropColumn([
                'name',
                'base_salary',
                'pay_period',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table): void {
            $table->string('name')->nullable()->after('id');
            $table->bigInteger('base_salary')->nullable()->after('phone');
            $table->string('pay_period', 20)->nullable()->after('base_salary');
            $table->string('status', 20)->nullable()->after('pay_period');
        });

        DB::table('employees')->update([
            'name' => DB::raw('employee_name'),
            'base_salary' => DB::raw('COALESCE(default_salary_amount, 0)'),
            'pay_period' => DB::raw("
                CASE
                    WHEN salary_basis_type IN ('daily', 'weekly', 'monthly') THEN salary_basis_type
                    ELSE 'monthly'
                END
            "),
            'status' => DB::raw('employment_status'),
        ]);

        Schema::table('employees', function (Blueprint $table): void {
            $table->string('name')->nullable(false)->change();
            $table->bigInteger('base_salary')->nullable(false)->change();
            $table->string('pay_period', 20)->nullable(false)->change();
            $table->string('status', 20)->nullable(false)->change();
        });

        Schema::table('employees', function (Blueprint $table): void {
            $table->dropColumn([
                'employee_name',
                'salary_basis_type',
                'default_salary_amount',
                'employment_status',
                'started_at',
                'ended_at',
            ]);
        });
    }
};
