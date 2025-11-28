<?php

use App\Http\Controllers\MockTestController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/mock-test', [MockTestController::class, 'index'])->name('mock-test.index');
    Route::get('/mock-test/create', [MockTestController::class, 'create'])->name('mock-test.create');
    Route::post('/mock-test', [MockTestController::class, 'store'])->name('mock-test.store');
    Route::get('/mock-test/{mockTest}', [MockTestController::class, 'show'])->name('mock-test.show');
    
    // Teacher routes
    Route::post('/mock-test/{mockTest}/accept', [MockTestController::class, 'accept'])->name('mock-test.accept');
    Route::post('/mock-test/{mockTest}/reject', [MockTestController::class, 'reject'])->name('mock-test.reject');
    Route::post('/mock-test/{mockTest}/end', [MockTestController::class, 'endSession'])->name('mock-test.end');
    
    // Video call routes
    Route::get('/mock-test/{mockTest}/start', [MockTestController::class, 'startSession'])->name('mock-test.start');
    Route::post('/mock-test/{mockTest}/recording', [MockTestController::class, 'saveRecording'])->name('mock-test.save-recording');
    Route::post('/mock-test/{mockTest}/screen-sharing', [MockTestController::class, 'saveScreenSharing'])->name('mock-test.save-screen-sharing');

    Route::get('/mock-test/{mockTest}', [MockTestController::class, 'show'])->name('mock-test.show');
});

Route::get('/', function () {
    $users = User::all();
    return view('quick-login', compact('users'));
});

Route::post('/quick-login', function (\Illuminate\Http\Request $r) {
    Auth::loginUsingId($r->user_id);

    return redirect('/dashboard');
})->name('quick.login');

Route::get('/dashboard', function () {
    return "Logged in as: ".auth()->user()->name;
})->middleware('auth');
