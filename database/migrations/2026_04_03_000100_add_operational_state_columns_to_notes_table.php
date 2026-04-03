<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notes', function (Blueprint $table): void {
            $table->string('note_state')->default('open')->after('transaction_date');
            $table->dateTime('closed_at')->nullable()->after('note_state');
            $table->string('closed_by_actor_id')->nullable()->after('closed_at');
            $table->dateTime('reopened_at')->nullable()->after('closed_by_actor_id');
            $table->string('reopened_by_actor_id')->nullable()->after('reopened_at');

            $table->index('note_state');
            $table->index('closed_at');
        });
    }

    public function down(): void
    {
        Schema::table('notes', function (Blueprint $table): void {
            $table->dropIndex(['note_state']);
            $table->dropIndex(['closed_at']);
            $table->dropColumn([
                'note_state',
                'closed_at',
                'closed_by_actor_id',
                'reopened_at',
                'reopened_by_actor_id',
            ]);
        });
    }
};
