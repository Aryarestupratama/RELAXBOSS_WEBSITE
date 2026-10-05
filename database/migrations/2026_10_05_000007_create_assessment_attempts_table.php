<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_attempts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Asesmen dinonaktifkan, bukan dihapus (Schema ENT-005).
            $table->foreignId('assessment_id')->constrained()->restrictOnDelete();
            $table->longText('answers');            // terenkripsi (cast pada model)
            $table->json('results');                // salinan per subskala, tidak dienkripsi demi agregasi
            $table->longText('user_context')->nullable();      // terenkripsi
            $table->longText('ai_recommendation')->nullable(); // terenkripsi
            $table->longText('ai_summary')->nullable();        // terenkripsi
            $table->timestamp('completed_at');
            $table->timestamps();

            $table->index(['user_id', 'completed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_attempts');
    }
};
