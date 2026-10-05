<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessments', function (Blueprint $table): void {
            $table->id();
            $table->string('slug', 80)->unique();
            $table->string('name', 150);
            $table->string('display_name', 150)->nullable();
            $table->text('description');
            $table->text('instructions')->nullable();
            $table->unsignedTinyInteger('estimated_minutes');
            $table->json('options');
            $table->decimal('score_multiplier', 4, 2)->default(1.00);
            $table->string('source_reference', 255)->nullable();
            $table->string('creator_name', 150)->nullable();
            $table->string('creator_institution', 150)->nullable();
            $table->string('validator_name', 150)->nullable();
            $table->string('validator_credential', 150)->nullable();
            $table->date('validated_at')->nullable();
            $table->boolean('is_active')->default(false);
            $table->smallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
