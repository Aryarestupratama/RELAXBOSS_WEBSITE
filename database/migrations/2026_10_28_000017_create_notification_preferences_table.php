<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_preferences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('mood_reminder_enabled')->default(false);
            $table->unsignedTinyInteger('mood_reminder_hour')->default(20);
            $table->boolean('nudges_enabled')->default(false);
            $table->timestamp('announcements_seen_at')->nullable();
            $table->json('nudge_dismissals')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
