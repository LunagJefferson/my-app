<?php

use App\Http\Controllers\NoteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MessageController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| AUTH PROTECTED NOTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    Route::get('/notes', [NoteController::class, 'index']);
    Route::get('/notes/create', [NoteController::class, 'create']);
    Route::post('/notes', [NoteController::class, 'store']);

    Route::get('/notes/{notepad}', [NoteController::class, 'show'])
        ->middleware('notepad.permission:view');

    Route::get('/notes/{notepad}/edit', [NoteController::class, 'edit'])
        ->middleware('notepad.permission:edit');

    Route::post('/notes/{notepad}/autosave', [NoteController::class, 'autosave'])
        ->middleware('notepad.permission:edit');

    Route::put('/notes/{notepad}', [NoteController::class, 'update'])
        ->middleware('notepad.permission:edit');
    Route::delete('/notes/{notepad}', [NoteController::class, 'destroy']);

    Route::get('/notes/{id}/share/{token}', [NoteController::class, 'share']);
    Route::post('/notes/{notepad}/make-public', [NoteController::class, 'makePublic']);
    Route::post('/notes/{notepad}/share', [NoteController::class, 'shareWithUser']);

    Route::delete('/chat/{user}', [MessageController::class, 'deleteConversation']);
});

/*
|--------------------------------------------------------------------------
| DASHBOARD
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    return redirect('/notes');
})->middleware(['auth', 'verified'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| PROFILE (already protected)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->group(function () {

    Route::get('/chat/{user}', [MessageController::class, 'chat']);
    Route::post('/chat/{user}', [MessageController::class, 'send']);
    Route::get('/messages', [MessageController::class, 'index']);
    Route::post('/messages/send', [MessageController::class, 'sendToUser']);

});

require __DIR__.'/auth.php';