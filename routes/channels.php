<?php

use App\Models\Notepad;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('note.{notepad}', function ($user, Notepad $notepad) {
    return $notepad->user_id === $user->id || $notepad->userRole($user) === 'editor';
});

Broadcast::channel('note.{noteId}', function ($user, $noteId) {

    $note = Notepad::find($noteId);

    if (!$note) return false;

    // owner
    if ($note->user_id === $user->id) return true;

    // shared users
    return $note->users()->where('user_id', $user->id)->exists();
});