<?php

declare(strict_types=1);

namespace Tests\Feature\Foundation;

use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

final class ErrorPageHtmlFeatureTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware('web')->get('/__test/error-403', static function (): never {
            abort(403);
        });

        Route::middleware('web')->get('/__test/error-419', static function (): never {
            throw new TokenMismatchException('expired');
        });

        Route::middleware('web')->get('/__test/error-429', static function (): never {
            throw new HttpException(429);
        });

        Route::middleware('web')->get('/__test/error-500', static function (): never {
            throw new \RuntimeException('boom');
        });

        Route::middleware('web')->get('/__test/error-503', static function (): never {
            throw new HttpException(503);
        });
    }

    public function test_404_uses_custom_error_page(): void
    {
        $this->get('/__launch-audit-404__')
            ->assertStatus(404)
            ->assertSee('Halaman Tidak Ditemukan')
            ->assertSee('Ke Beranda')
            ->assertSee('error-404.svg', false);
    }

    public function test_403_uses_custom_error_page(): void
    {
        $this->get('/__test/error-403')
            ->assertStatus(403)
            ->assertSee('Akses Ditolak')
            ->assertSee('error-403.svg', false);
    }

    public function test_419_uses_custom_error_page(): void
    {
        $this->get('/__test/error-419')
            ->assertStatus(419)
            ->assertSee('Sesi Anda Sudah Berakhir')
            ->assertSee('Muat Ulang Halaman');
    }

    public function test_429_uses_custom_error_page(): void
    {
        $this->get('/__test/error-429')
            ->assertStatus(429)
            ->assertSee('Permintaan Terlalu Sering');
    }

    public function test_500_uses_custom_error_page(): void
    {
        $this->get('/__test/error-500')
            ->assertStatus(500)
            ->assertSee('Terjadi Gangguan pada Sistem')
            ->assertSee('error-500.svg', false);
    }

    public function test_503_uses_custom_error_page(): void
    {
        $this->get('/__test/error-503')
            ->assertStatus(503)
            ->assertSee('Layanan Sedang Tidak Tersedia');
    }
}
