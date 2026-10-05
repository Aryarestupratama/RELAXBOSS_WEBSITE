<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Sengaja tanpa user_id dan tanpa isi: hanya untuk hitungan (M-5, FR-025).
        Schema::create('crisis_events', function (Blueprint $table): void {
            $table->id();
            $table->string('layer', 20)->default('rule'); // rule (chat) | pfa_rule (jawaban PFA)
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crisis_events');
    }
};
