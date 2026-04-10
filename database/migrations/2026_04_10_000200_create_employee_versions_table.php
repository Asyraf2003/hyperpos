<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_versions', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('employee_id');
            $table->unsignedInteger('revision_no');
            $table->string('event_name');
            $table->string('changed_by_actor_id')->nullable();
            $table->text('change_reason')->nullable();
            $table->timestamp('changed_at');
            $table->json('snapshot_json');

            $table->unique(['employee_id', 'revision_no'], 'employee_versions_employee_revision_unique');
            $table->index(['employee_id', 'changed_at'], 'employee_versions_employee_changed_at_idx');
            $table->index('event_name', 'employee_versions_event_name_idx');

            $table->foreign('employee_id', 'fk_employee_versions_employee')
                ->references('id')
                ->on('employees')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('employee_versions', function (Blueprint $table): void {
            $table->dropForeign('fk_employee_versions_employee');
        });

        Schema::dropIfExists('employee_versions');
    }
};
