<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mood_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('mood');                 // 1 sampai 5, tidak dienkripsi demi agregasi
            $table->string('arousal_input', 10)->nullable();     // energized | tired
            $table->text('note')->nullable();                    // terenkripsi (cast pada model), maks. 500 karakter
            $table->date('entry_date');                          // tanggal WIB (Support/Wib)
            $table->timestamp('logged_at');                      // UTC
            $table->timestamps();

            // Tanpa indeks unik: banyak entri per hari diperbolehkan (dibatasi aplikasi).
            $table->index(['user_id', 'logged_at']);
            $table->index(['user_id', 'entry_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mood_entries');
    }
};
