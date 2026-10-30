<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        // Demo data and demo accounts are for local development only.
        // Never seed these in production, where they'd create known passwords.
        if (! app()->isProduction()) {
            $this->seedDemo();
        }
    }

    private function seedDemo(): void
    {
        $this->call(SetupDemoSeeder::class);

        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin User', 'password' => 'password'],
        )->assignRole('admin');

        User::firstOrCreate(
            ['email' => 'teacher@example.com'],
            ['name' => 'Teacher User', 'password' => 'password'],
        )->assignRole('teacher');

        User::firstOrCreate(
            ['email' => 'guardian@example.com'],
            ['name' => 'Guardian User', 'password' => 'password'],
        )->assignRole('guardian');

        User::firstOrCreate(
            ['email' => 'student@example.com'],
            ['name' => 'Student User', 'password' => 'password'],
        )->assignRole('student');
    }
}
