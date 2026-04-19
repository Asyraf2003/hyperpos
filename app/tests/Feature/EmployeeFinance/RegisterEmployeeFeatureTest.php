<?php

declare(strict_types=1);

namespace Tests\Feature\EmployeeFinance;

use App\Application\EmployeeFinance\UseCases\RegisterEmployeeHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class RegisterEmployeeFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_employee_handler_creates_employee_and_versioned_audit_records(): void
    {
        $handler = app(RegisterEmployeeHandler::class);

        $id = $handler->handle(
            'Asyraf Mubarak',
            '08111222333',
            5000000,
            'monthly',
            '2026-04-01',
            null,
        );

        $this->assertIsString($id);

        $this->assertDatabaseHas('employees', [
            'id' => $id,
            'employee_name' => 'Asyraf Mubarak',
            'phone' => '08111222333',
            'salary_basis_type' => 'monthly',
            'default_salary_amount' => 5000000,
            'employment_status' => 'active',
            'started_at' => '2026-04-01',
            'ended_at' => null,
        ]);

        $this->assertDatabaseHas('employee_versions', [
            'employee_id' => $id,
            'revision_no' => 1,
            'event_name' => 'employee_created',
        ]);

        $this->assertDatabaseHas('audit_events', [
            'aggregate_type' => 'employee',
            'aggregate_id' => $id,
            'event_name' => 'employee_created',
            'bounded_context' => 'employee_finance',
            'source_channel' => 'admin_web',
        ]);

        $auditEventId = (string) DB::table('audit_events')
            ->where('aggregate_type', 'employee')
            ->where('aggregate_id', $id)
            ->where('event_name', 'employee_created')
            ->value('id');

        $this->assertNotSame('', $auditEventId);

        $this->assertDatabaseHas('audit_event_snapshots', [
            'audit_event_id' => $auditEventId,
            'snapshot_kind' => 'after',
        ]);

        $version = DB::table('employee_versions')
            ->where('employee_id', $id)
            ->where('revision_no', 1)
            ->first();

        $this->assertNotNull($version);

        $versionSnapshot = json_decode((string) $version->snapshot_json, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame('Asyraf Mubarak', $versionSnapshot['employee_name']);
        $this->assertSame('08111222333', $versionSnapshot['phone']);
        $this->assertSame('monthly', $versionSnapshot['salary_basis_type']);
        $this->assertSame(5000000, $versionSnapshot['default_salary_amount']);
        $this->assertSame('active', $versionSnapshot['employment_status']);
        $this->assertSame('2026-04-01', $versionSnapshot['started_at']);
        $this->assertNull($versionSnapshot['ended_at']);
    }

    public function test_register_employee_handler_accepts_manual_salary_basis_with_nullable_default_salary(): void
    {
        $handler = app(RegisterEmployeeHandler::class);

        $id = $handler->handle(
            'Budi Manual',
            null,
            null,
            'manual',
            null,
            null,
        );

        $this->assertIsString($id);

        $this->assertDatabaseHas('employees', [
            'id' => $id,
            'employee_name' => 'Budi Manual',
            'phone' => null,
            'salary_basis_type' => 'manual',
            'default_salary_amount' => null,
            'employment_status' => 'active',
            'started_at' => null,
            'ended_at' => null,
        ]);

        $this->assertDatabaseHas('employee_versions', [
            'employee_id' => $id,
            'revision_no' => 1,
            'event_name' => 'employee_created',
        ]);

        $this->assertDatabaseHas('audit_events', [
            'aggregate_type' => 'employee',
            'aggregate_id' => $id,
            'event_name' => 'employee_created',
            'bounded_context' => 'employee_finance',
        ]);
    }
}
