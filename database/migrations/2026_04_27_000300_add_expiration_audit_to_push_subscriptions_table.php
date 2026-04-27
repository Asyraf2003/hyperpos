<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('push_subscriptions', function (Blueprint $table): void {
            $table->timestamp('expired_at')->nullable();
            $table->unsignedSmallInteger('last_failure_status')->nullable();
            $table->string('last_failure_reason')->nullable();

            $table->index('expired_at', 'push_subscriptions_expired_at_idx');
        });
    }

    public function down(): void
    {
        Schema::table('push_subscriptions', function (Blueprint $table): void {
            $table->dropIndex('push_subscriptions_expired_at_idx');
            $table->dropColumn([
                'expired_at',
                'last_failure_status',
                'last_failure_reason',
            ]);
        });
    }
};
