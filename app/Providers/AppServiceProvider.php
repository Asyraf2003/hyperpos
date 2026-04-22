<?php

// @audit-skip: line-limit

namespace App\Providers;

use App\Adapters\Out\Note\DbNoteRevisionRepository;
use App\Application\IdentityAccess\Request\IdentityAccessRequestStore;
use App\Ports\Out\Note\NoteRevisionReaderPort;
use App\Ports\Out\Note\NoteRevisionWriterPort;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(
            IdentityAccessRequestStore::class,
            fn (): IdentityAccessRequestStore => new IdentityAccessRequestStore()
        );

        $this->app->scoped(
            DbNoteRevisionRepository::class,
            fn (): DbNoteRevisionRepository => new DbNoteRevisionRepository()
        );

        $this->app->scoped(
            NoteRevisionReaderPort::class,
            fn (): NoteRevisionReaderPort => $this->app->make(DbNoteRevisionRepository::class)
        );

        $this->app->scoped(
            NoteRevisionWriterPort::class,
            fn (): NoteRevisionWriterPort => $this->app->make(DbNoteRevisionRepository::class)
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
