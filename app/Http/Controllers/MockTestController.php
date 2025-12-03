<?php

namespace App\Http\Controllers;

use App\Models\MockTestSession;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MockTestController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $user = Auth::user();

        if ($user->isStudent()) {
            $sessions = $user->studentSessions()->with('teacher')->latest()->get();
            return view('mock-test.student-index', compact('sessions'));
        } else {
            $pendingSessions = MockTestSession::pending()->with('student')->get();
            $upcomingSessions = $user->teacherSessions()->accepted()->upcoming()->with('student')->get();
            return view('mock-test.teacher-index', compact('pendingSessions', 'upcomingSessions'));
        }
    }

    public function create()
    {
        $teachers = User::where('role', 'teacher')->get();
        return view('mock-test.create', compact('teachers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'proposed_time' => 'required|date|after:now',
            'duration_minutes' => 'required|integer|min:15|max:180',
        ]);

        $proposedTime = \Carbon\Carbon::parse($request->proposed_time)->timezone(config('app.timezone'));

        $session = MockTestSession::create([
            'student_id' => Auth::id(),
            'teacher_id' => $request->teacher_id,
            'title' => $request->title,
            'description' => $request->description,
            'proposed_time' => $proposedTime,
            'duration_minutes' => $request->duration_minutes,
            'status' => 'pending',
        ]);

        return redirect()->route('mock-test.index')->with('success', 'Mock test session requested successfully!');
    }

    public function show(MockTestSession $mockTest)
    {
        if (Auth::id() !== $mockTest->student_id && Auth::id() !== $mockTest->teacher_id) {
            abort(403, 'Unauthorized action.');
        }
        return view('mock-test.show', compact('mockTest'));
    }

    public function destroy(MockTestSession $mockTest)
    {
        $this->authorize('delete', $mockTest);

        $mockTest->delete();

        return redirect()->route('mock-test.index')->with('success', 'Mock test session deleted successfully!');
    }

    public function accept(Request $request, MockTestSession $mockTest)
    {
        $this->authorize('update', $mockTest);

        $request->validate([
            'scheduled_time' => 'required|date|after:now',
            'teacher_notes' => 'nullable|string',
        ]);

        // Generate room name when accepting
        $roomName = 'mocktest-' . $mockTest->id . '-' . uniqid();

        $mockTest->update([
            'status' => 'accepted',
            'scheduled_time' => $request->scheduled_time,
            'teacher_notes' => $request->teacher_notes,
            'jitsi_room_name' => $roomName,
        ]);

        return redirect()->route('mock-test.index')->with('success', 'Mock test session accepted!');
    }

    public function reject(Request $request, MockTestSession $mockTest)
    {
        $this->authorize('update', $mockTest);

        $request->validate([
            'rejection_reason' => 'required|string',
        ]);

        $mockTest->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);

        return redirect()->route('mock-test.index')->with('success', 'Mock test session rejected.');
    }

    public function startSession(MockTestSession $mockTest)
    {
        $this->authorize('view', $mockTest);

        if (!$mockTest->canStart()) {
            return redirect()->back()->with('error', 'Session cannot be started yet. Please wait until the scheduled time.');
        }

        // Ensure room name exists
        if (empty($mockTest->jitsi_room_name)) {
            $mockTest->update([
                'jitsi_room_name' => 'mocktest-' . $mockTest->id . '-' . uniqid()
            ]);
            $mockTest->refresh();
        }

        return view('mock-test.video-call', compact('mockTest'));
    }

    public function endSession(MockTestSession $mockTest)
    {
        // Both teacher and student can end session
        $this->authorize('endSession', $mockTest);

        $mockTest->update([
            'status' => 'completed',
            'ended_at' => now(),
        ]);

        return redirect()->route('mock-test.index')->with('success', 'Mock test session completed!');
    }

    public function saveRecording(Request $request, MockTestSession $mockTest)
    {
        $this->authorize('view', $mockTest);

        $request->validate([
            'recording_url' => 'required|url',
        ]);

        $mockTest->update([
            'recording_url' => $request->recording_url,
        ]);

        return response()->json(['success' => true]);
    }

    public function saveScreenSharing(Request $request, MockTestSession $mockTest)
    {
        $this->authorize('view', $mockTest);

        $request->validate([
            'screen_data' => 'required|array',
        ]);

        // Merge with existing data
        $existingData = $mockTest->screen_sharing_data ?? [];
        $newData = array_merge($existingData, $request->screen_data);

        $mockTest->update([
            'screen_sharing_data' => $newData,
        ]);

        return response()->json(['success' => true]);
    }
}
