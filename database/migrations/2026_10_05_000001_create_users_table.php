<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100);
            $table->string('email', 255)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password', 255);
            $table->string('institution_name', 150)->nullable();
            $table->string('major', 100)->nullable();
            $table->string('role', 20)->default('user');
            $table->boolean('is_active')->default(true);
            $table->timestamp('ai_consent_at')->nullable();
            $table->unsignedSmallInteger('ai_consent_version')->nullable();
            $table->string('ai_training_consent_choice', 10)->nullable();
            $table->timestamp('ai_training_consent_at')->nullable();
            $table->unsignedSmallInteger('ai_training_consent_version')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->index('role');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
