<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create main teacher
        User::create([
            'name' => 'Teacher John',
            'email' => 'teacher@example.com',
            'password' => Hash::make('password'),
            'role' => 'teacher',
        ]);

        // Create main student
        User::create([
            'name' => 'Student Jane',
            'email' => 'student@example.com',
            'password' => Hash::make('password'),
            'role' => 'student',
        ]);

        // Create more users
        User::factory(3)->create(['role' => 'teacher']);
        User::factory(5)->create(['role' => 'student']);

        // Run MockTestSession seeder
        $this->call(MockTestSessionSeeder::class);
    }
}
