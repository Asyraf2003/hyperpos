<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('nama_pt_pengirim');
            $table->string('nama_pt_pengirim_normalized');

            $table->index('nama_pt_pengirim_normalized');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
