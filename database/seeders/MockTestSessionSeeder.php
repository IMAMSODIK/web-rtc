<?php

namespace Database\Seeders;

use App\Models\MockTestSession;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class MockTestSessionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teacher = User::where('role', 'teacher')->first();
        $student = User::where('role', 'student')->first();

        if (!$teacher || !$student) {
            $this->command->info('Please run DatabaseSeeder first to create users.');
            return;
        }

        // Create a pending session
        MockTestSession::create([
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'title' => 'English Speaking Practice',
            'description' => 'I want to practice my speaking skills for IELTS exam.',
            'proposed_time' => Carbon::now()->addDays(2),
            'duration_minutes' => 60,
            'status' => 'pending',
        ]);

        // Create an accepted session that can be started now (for testing)
        MockTestSession::create([
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'title' => 'Math Problem Solving',
            'description' => 'Need help with calculus problems.',
            'proposed_time' => Carbon::now()->subHour(),
            'scheduled_time' => Carbon::now()->subMinutes(10), // Can be started now
            'duration_minutes' => 45,
            'status' => 'accepted',
            'jitsi_room_name' => 'mocktest-test-' . uniqid(),
            'teacher_notes' => 'Please prepare your questions before the session.',
        ]);

        // Create a completed session
        MockTestSession::create([
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'title' => 'Physics Review Session',
            'description' => 'Review of thermodynamics concepts.',
            'proposed_time' => Carbon::now()->subDays(3),
            'scheduled_time' => Carbon::now()->subDays(3),
            'duration_minutes' => 30,
            'status' => 'completed',
            'jitsi_room_name' => 'mocktest-completed-' . uniqid(),
            'teacher_notes' => 'Good session!',
        ]);

        $this->command->info('MockTestSession seeder completed successfully.');
    }
}
