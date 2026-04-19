<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use Tests\TestCase;

final class HealthCheckTest extends TestCase
{
    public function test_health_endpoint_returns_expected_shape(): void
    {
        $response = $this->get('/health');

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'data' => [
                'status',
                'time',
                'request_id',
            ],
            'message',
            'errors',
        ]);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.status', 'ok');
    }
}
