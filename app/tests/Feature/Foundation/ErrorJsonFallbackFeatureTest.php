<?php

declare(strict_types=1);

namespace Tests\Feature\Foundation;

use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

final class ErrorJsonFallbackFeatureTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware('web')->get('/__test/json-error-419', static function (): never {
            throw new TokenMismatchException('expired');
        });

        Route::middleware('web')->get('/__test/json-error-500', static function (): never {
            throw new \RuntimeException('boom');
        });

        Route::middleware('web')->get('/__test/json-error-503', static function (): never {
            throw new HttpException(503);
        });
    }

    public function test_json_419_uses_safe_payload(): void
    {
        $this->getJson('/__test/json-error-419')
            ->assertStatus(419)
            ->assertJson([
                'message' => 'Sesi Anda sudah berakhir. Silakan muat ulang halaman.',
                'status' => 419,
            ]);
    }

    public function test_json_500_uses_safe_payload(): void
    {
        $this->getJson('/__test/json-error-500')
            ->assertStatus(500)
            ->assertJson([
                'message' => 'Terjadi gangguan pada sistem.',
                'status' => 500,
            ]);
    }

    public function test_json_503_uses_safe_payload(): void
    {
        $this->getJson('/__test/json-error-503')
            ->assertStatus(503)
            ->assertJson([
                'message' => 'Layanan sedang tidak tersedia sementara.',
                'status' => 503,
            ]);
    }
}
