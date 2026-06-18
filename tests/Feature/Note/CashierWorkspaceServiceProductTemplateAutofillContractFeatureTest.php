<?php

declare(strict_types=1);

namespace Tests\Feature\Note;

use Tests\TestCase;

final class CashierWorkspaceServiceProductTemplateAutofillContractFeatureTest extends TestCase
{
    public function test_product_search_js_requests_service_product_context_only_for_service_store_stock_rows(): void
    {
        $source = $this->readWorkspaceScript('search.js');

        self::assertStringContainsString('row?.dataset?.itemType || ""', $source);
        self::assertStringContainsString('service_store_stock', $source);
        self::assertStringContainsString('params.set("context", "service_product")', $source);
        self::assertStringContainsString('NS.applyServiceProductTemplate?.(row, item.service_product_template || null, scope);', $source);
    }

    public function test_service_catalog_js_applies_template_and_respects_manual_overrides(): void
    {
        $source = $this->readWorkspaceScript('service-catalog.js');

        self::assertStringContainsString('NS.applyServiceProductTemplate = (row, template) =>', $source);
        self::assertStringContainsString('shouldAutofillServiceIdentity', $source);
        self::assertStringContainsString('row.dataset.serviceNameManual !== "1"', $source);
        self::assertStringContainsString('row.dataset.servicePackageAutofilled === "1" || current <= 0', $source);
        self::assertStringContainsString('setPackageTotal(row, templatePackageTotal);', $source);
        self::assertStringContainsString('row.dataset.serviceNameManual = "1";', $source);
        self::assertStringContainsString('row.dataset.serviceTemplateAutofilled = "0";', $source);
    }

    private function readWorkspaceScript(string $filename): string
    {
        $path = public_path('assets/static/js/pages/cashier-note-workspace/' . $filename);

        self::assertFileExists($path);

        $source = file_get_contents($path);

        self::assertIsString($source);

        return $source;
    }
}
