<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tanpa identitas pengguna dan tanpa timestamps. Dipakai memantau kuota (NFR-011).
        Schema::create('ai_usage_daily', function (Blueprint $table): void {
            $table->id();
            $table->date('usage_date');                                // tanggal UTC
            $table->string('model', 80);
            $table->string('feature', 20);                             // chat | assessment
            $table->unsignedInteger('requests')->default(0);
            $table->unsignedInteger('rate_limited')->default(0);       // jumlah 429
            $table->unsignedInteger('errors')->default(0);             // termasuk 401 dan 403
            $table->unsignedBigInteger('prompt_tokens')->default(0);
            $table->unsignedBigInteger('completion_tokens')->default(0);

            $table->unique(['usage_date', 'model', 'feature']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_usage_daily');
    }
};
