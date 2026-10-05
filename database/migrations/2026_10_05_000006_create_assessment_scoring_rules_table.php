<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_scoring_rules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('assessment_id')->constrained()->cascadeOnDelete();
            $table->string('sub_scale', 50);
            $table->smallInteger('min_score');
            $table->smallInteger('max_score');
            $table->string('interpretation', 100);
            $table->string('severity_level', 20)->default('normal');
            $table->boolean('trigger_pfa')->default(false);
            $table->text('pfa_question')->nullable();
            $table->text('static_recommendation');

            $table->index(['assessment_id', 'sub_scale', 'min_score'], 'scoring_rules_lookup_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_scoring_rules');
    }
};
