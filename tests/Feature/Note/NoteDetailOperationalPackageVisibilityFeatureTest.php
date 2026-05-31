<?php

declare(strict_types=1);

namespace Tests\Feature\Note;

use App\Ports\Out\ClockPort;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\NoteDetailOperationalPackageFixture;
use Tests\Support\SeedsMinimalNotePaymentFixture;
use Tests\TestCase;

final class NoteDetailOperationalPackageVisibilityFeatureTest extends TestCase
{
    use RefreshDatabase;
    use SeedsMinimalNotePaymentFixture;
    use NoteDetailOperationalPackageFixture;

    public function test_detail_shows_operational_note_and_store_stock_package_breakdown(): void
    {
        $this->loginAsAuthorizedAdmin();

        $today = $this->app->make(ClockPort::class)->now()->format('Y-m-d');
        $this->seedVisibleStoreStockPackageDetailFixture($today);

        $this->get(route('admin.notes.show', ['noteId' => 'note-detail-package-1']))
            ->assertOk()
            ->assertSee('Keterangan Nota')
            ->assertSee('Keterangan operasional detail package')
            ->assertSee('Paket total')
            ->assertSee('Total sparepart')
            ->assertSee('Sisa jasa')
            ->assertSee('Filter Oli Detail')
            ->assertSee('Busi Iridium Detail')
            ->assertSee('250.000')
            ->assertSee('130.000')
            ->assertSee('120.000');
    }
}
