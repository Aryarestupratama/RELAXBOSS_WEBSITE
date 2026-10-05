<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignUuid('conversation_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('turn_number');               // ganjil = pengguna, genap = AI
            $table->string('role', 20);                                // user | assistant
            $table->text('content');                                   // terenkripsi (cast pada model)
            $table->string('detected_intent', 50)->nullable();         // hanya pesan AI
            $table->decimal('detection_confidence', 4, 4)->nullable(); // 0.0000 sampai 0.9999
            $table->string('ai_strategy', 100)->nullable();            // hanya pesan AI
            $table->string('ai_flow_action', 100)->nullable();         // hanya pesan AI
            $table->string('suggestion_type', 50)->nullable();         // escalate_crisis | recommend_professional | recommend_support
            $table->boolean('is_crisis')->default(false);
            $table->timestamp('created_at')->nullable();               // tanpa updated_at

            // Mencegah race condition dan pesan ganda. Tanpa user_id (query lewat pemilik percakapan).
            $table->unique(['conversation_id', 'turn_number']);
            $table->index(['detected_intent', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
