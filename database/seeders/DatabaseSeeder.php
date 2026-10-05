<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(AdminSeeder::class);
        $this->call(DemoAccountSeeder::class); // melewati dirinya sendiri di luar local
        $this->call(DemoAssessmentSeeder::class); // melewati dirinya sendiri di luar local
        $this->call(DemoMoodSeeder::class); // melewati dirinya sendiri di luar local
        $this->call(DemoConversationSeeder::class); // melewati dirinya sendiri di luar local
    }
}
