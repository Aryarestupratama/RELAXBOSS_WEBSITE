<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_questions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('assessment_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('position');
            $table->string('text', 500);
            $table->string('sub_scale', 50);
            $table->boolean('is_reversed')->default(false);

            $table->unique(['assessment_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_questions');
    }
};
