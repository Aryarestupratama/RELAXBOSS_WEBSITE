<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            // Penanda ada tidaknya foto profil sekaligus versi untuk cache. Berkasnya ada di disk privat.
            $table->timestamp('avatar_updated_at')->nullable()->after('major');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('avatar_updated_at');
        });
    }
};
