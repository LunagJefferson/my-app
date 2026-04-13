<?php

namespace App\Http\Controllers;

use App\Events\MessageTyping;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index()
    {
        // Get users you've chatted with
        $userIds = Message::where('sender_id', auth()->id())
            ->orWhere('receiver_id', auth()->id())
            ->pluck('sender_id', 'receiver_id')
            ->flatten()
            ->unique()
            ->filter(fn($id) => $id != auth()->id())
            ->values();

        $users = User::whereIn('id', $userIds)->get();

        return view('messages.index', compact('users'));
    }

    public function chat(User $user)
    {
        $messages = Message::where(function ($q) use ($user) {
            $q->where('sender_id', auth()->id())
              ->where('receiver_id', $user->id);
        })->orWhere(function ($q) use ($user) {
            $q->where('sender_id', $user->id)
              ->where('receiver_id', auth()->id());
        })->get();

        return view('chat.index', compact('messages', 'user'));
    }

    public function sendToUser(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'message' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'User not found']);
        }

        if ($user->id == auth()->id()) {
            return back()->withErrors(['email' => 'Cannot send message to yourself']);
        }

        Message::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $user->id,
            'message' => $request->message,
        ]);

        return redirect('/chat/' . $user->id)->with('success', 'Message sent!');
    }

    public function send(Request $request, User $user)
    {
        $request->validate([
            'message' => 'nullable|string',
            'notepad_link' => 'nullable|string'
        ]);

        $message = Message::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $user->id,
            'message' => $request->message,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'id' => $message->id,
                'message' => $message->message,
                'sender' => ['id' => auth()->id(), 'name' => auth()->user()->name],
                'receiver_id' => $message->receiver_id,
                'created_at' => $message->created_at->toDateTimeString(),
            ]);
        }

        return back();
    }

    public function deleteConversation(User $user)
    {
        Message::where(function ($q) use ($user) {
            $q->where('sender_id', auth()->id())
            ->where('receiver_id', $user->id);
        })->orWhere(function ($q) use ($user) {
            $q->where('sender_id', $user->id)
            ->where('receiver_id', auth()->id());
        })->delete();

        return redirect('/messages')->with('success', 'Conversation deleted.');
    }
}
