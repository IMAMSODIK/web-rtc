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

        // Mark session as started if not already
        if (!$mockTest->started_at) {
            $mockTest->update(['started_at' => now()]);
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
            'recording' => 'required|file|mimes:webm,mp4,ogg|max:512000', // Max 500MB
        ]);

        // Store the recording file
        $file = $request->file('recording');
        $filename = 'recording_' . $mockTest->id . '_' . time() . '.' . $file->getClientOriginalExtension();

        // Store in recordings folder
        $path = $file->storeAs('recordings/' . $mockTest->id, $filename, 'public');

        $mockTest->update([
            'recording_url' => $path,
            'recording_filename' => $filename,
            'recording_size' => $file->getSize(),
            'recording_duration' => $request->input('duration', 0),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Recording saved successfully!',
            'path' => $path,
            'filename' => $filename,
        ]);
    }

    /**
     * Upload recording chunk (for large files)
     */
    public function uploadRecordingChunk(Request $request, MockTestSession $mockTest)
    {
        $this->authorize('view', $mockTest);

        $request->validate([
            'chunk' => 'required',
            'chunkIndex' => 'required|integer',
            'totalChunks' => 'required|integer',
            'filename' => 'required|string',
        ]);

        $chunkDir = storage_path('app/temp/recordings/' . $mockTest->id);
        if (!file_exists($chunkDir)) {
            mkdir($chunkDir, 0755, true);
        }

        $chunkPath = $chunkDir . '/' . $request->filename . '.part' . $request->chunkIndex;
        file_put_contents($chunkPath, base64_decode($request->chunk));

        // If all chunks uploaded, merge them
        if ($request->chunkIndex == $request->totalChunks - 1) {
            $finalFilename = 'recording_' . $mockTest->id . '_' . time() . '.webm';
            $finalPath = storage_path('app/public/recordings/' . $mockTest->id);

            if (!file_exists($finalPath)) {
                mkdir($finalPath, 0755, true);
            }

            $finalFile = $finalPath . '/' . $finalFilename;
            $out = fopen($finalFile, 'wb');

            for ($i = 0; $i < $request->totalChunks; $i++) {
                $chunkFile = $chunkDir . '/' . $request->filename . '.part' . $i;
                if (file_exists($chunkFile)) {
                    $in = fopen($chunkFile, 'rb');
                    while ($buff = fread($in, 4096)) {
                        fwrite($out, $buff);
                    }
                    fclose($in);
                    unlink($chunkFile);
                }
            }
            fclose($out);

            // Clean up temp directory
            @rmdir($chunkDir);

            // Update database
            $mockTest->update([
                'recording_url' => 'recordings/' . $mockTest->id . '/' . $finalFilename,
                'recording_filename' => $finalFilename,
                'recording_size' => filesize($finalFile),
                'recording_duration' => $request->input('duration', 0),
            ]);

            return response()->json([
                'success' => true,
                'complete' => true,
                'message' => 'Recording uploaded successfully!',
                'filename' => $finalFilename,
            ]);
        }

        return response()->json([
            'success' => true,
            'complete' => false,
            'chunkIndex' => $request->chunkIndex,
        ]);
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

    /**
     * View/Stream recording
     */
    public function viewRecording(MockTestSession $mockTest)
    {
        $this->authorize('view', $mockTest);

        if (!$mockTest->recording_url) {
            abort(404, 'Recording not found');
        }

        $path = storage_path('app/public/' . $mockTest->recording_url);

        if (!file_exists($path)) {
            abort(404, 'Recording file not found');
        }

        // Stream the video file
        return response()->file($path, [
            'Content-Type' => 'video/webm',
            'Content-Disposition' => 'inline; filename="' . $mockTest->recording_filename . '"',
        ]);
    }

    /**
     * Download recording
     */
    public function downloadRecording(MockTestSession $mockTest)
    {
        $this->authorize('view', $mockTest);

        if (!$mockTest->recording_url) {
            abort(404, 'Recording not found');
        }

        $path = storage_path('app/public/' . $mockTest->recording_url);

        if (!file_exists($path)) {
            abort(404, 'Recording file not found');
        }

        return response()->download($path, $mockTest->recording_filename);
    }
}
