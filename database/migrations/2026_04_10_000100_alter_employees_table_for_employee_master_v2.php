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
            $table->string('employee_name')->nullable();
            $table->string('salary_basis_type', 20)->nullable();
            $table->bigInteger('default_salary_amount')->nullable();
            $table->string('employment_status', 20)->nullable();
            $table->date('started_at')->nullable();
            $table->date('ended_at')->nullable();
        });

        DB::table('employees')->update([
            'employee_name' => DB::raw('name'),
            'salary_basis_type' => DB::raw('pay_period'),
            'default_salary_amount' => DB::raw('base_salary'),
            'employment_status' => DB::raw('status'),
        ]);

        $this->setEmployeeMasterColumnsNotNullable();

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
            $table->string('name')->nullable();
            $table->bigInteger('base_salary')->nullable();
            $table->string('pay_period', 20)->nullable();
            $table->string('status', 20)->nullable();
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

        $this->setLegacyEmployeeColumnsNotNullable();

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
    private function setEmployeeMasterColumnsNotNullable(): void
    {
        $this->setColumnsNotNullable('employees', [
            'employee_name' => '`employee_name` varchar(255) NOT NULL',
            'salary_basis_type' => '`salary_basis_type` varchar(20) NOT NULL',
            'employment_status' => '`employment_status` varchar(20) NOT NULL',
        ]);
    }

    private function setLegacyEmployeeColumnsNotNullable(): void
    {
        $this->setColumnsNotNullable('employees', [
            'name' => '`name` varchar(255) NOT NULL',
            'base_salary' => '`base_salary` bigint NOT NULL',
            'pay_period' => '`pay_period` varchar(20) NOT NULL',
            'status' => '`status` varchar(20) NOT NULL',
        ]);
    }

    /**
     * @param array<string, string> $mysqlDefinitions
     */
    private function setColumnsNotNullable(string $table, array $mysqlDefinitions): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            foreach (array_keys($mysqlDefinitions) as $column) {
                DB::statement(sprintf(
                    'ALTER TABLE "%s" ALTER COLUMN "%s" SET NOT NULL',
                    $table,
                    $column
                ));
            }

            return;
        }

        if ($driver === 'mysql' || $driver === 'mariadb') {
            foreach ($mysqlDefinitions as $definition) {
                DB::statement(sprintf(
                    'ALTER TABLE `%s` MODIFY %s',
                    $table,
                    $definition
                ));
            }

            return;
        }

        throw new RuntimeException(sprintf(
            'Unsupported database driver for employee nullability migration: %s',
            $driver
        ));
    }

};
