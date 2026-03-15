<?php

declare(strict_types=1);

namespace Tests\Feature\Reporting;

use Tests\TestCase;

final class ReportingReadModelContractFeatureTest extends TestCase
{
    public function test_reporting_adr_locks_reporting_as_read_model_boundary(): void
    {
        $path = base_path('docs/adr/0009-reporting-as-read-model.md');

        $this->assertFileExists($path);

        $content = file_get_contents($path);

        $this->assertNotFalse($content);
        $this->assertStringContainsString(
            '**reporting diposisikan sebagai read model atas data domain final**',
            $content,
        );
        $this->assertStringContainsString(
            'reporting bukan sumber kebenaran utama domain',
            $content,
        );
    }

    public function test_workflow_places_reporting_read_models_in_step_12(): void
    {
        $path = base_path('docs/workflow/workflow_v1.md');

        $this->assertFileExists($path);

        $content = file_get_contents($path);

        $this->assertNotFalse($content);
        $this->assertStringContainsString(
            '## Step 12 — Reporting read models',
            $content,
        );
    }
}
