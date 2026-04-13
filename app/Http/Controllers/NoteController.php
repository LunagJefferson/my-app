<?php

namespace App\Http\Controllers;

use App\Events\NoteUpdated;
use App\Models\Notepad;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class NoteController extends Controller
{
    protected function presenceCacheKey(Notepad $notepad)
    {
        return "note-editing:{$notepad->id}";
    }

    protected function draftCacheKey(Notepad $notepad)
    {
        return "note-draft:{$notepad->id}";
    }

    protected function getActiveEditors(Notepad $notepad)
    {
        $editors = Cache::get($this->presenceCacheKey($notepad), []);

        return collect($editors)
            ->filter(fn ($editor) => now()->timestamp - $editor['updated_at'] < 15)
            ->values()
            ->all();
    }

    public function index(Request $request)
    {
        $query = Notepad::where('user_id', auth()->id())
            ->orWhereHas('users', function ($q) {
                $q->where('user_id', auth()->id());
            });

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $notepads = $query->latest()->get();
        $activeEditors = [];

        foreach ($notepads as $notepad) {
            $activeEditors[$notepad->id] = $this->getActiveEditors($notepad);
        }

        return view('notes.index', compact('notepads', 'activeEditors'));
    }

    public function create()
    {
        return view('notes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
        ]);

        $notepad = Notepad::create([
            'title' => $request->title,
            'content' => $request->content ?: '',
            'user_id' => auth()->id(),
        ]);

        $notepad->users()->attach(auth()->id(), [
            'role' => 'owner'
        ]);

        return redirect('/notes');
    }

    public function edit(Notepad $notepad)
    {
        $role = $notepad->userRole(auth()->user());
        if ($notepad->user_id !== auth()->id() && $role !== 'editor') {
            abort(403);
        }

        $notepad->load('users');
        $notepad->html_content = $notepad->content && is_string($notepad->content) ? $notepad->content : '';
        return view('notes.edit', compact('notepad'));
    }

    public function autosave(Request $request, Notepad $notepad)
    {
        $request->validate([
            'content' => 'required|array',
        ]);

        $content = $request->input('content');

        // Save FULL document (NOT delta merge)
        try {
            $notepad->update([
                'content' => json_encode($content),
            ]);
            Log::info('Note autosaved successfully', [
                'notepad_id' => $notepad->id,
                'user_id' => auth()->id()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to autosave note', [
                'notepad_id' => $notepad->id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['ok' => false, 'error' => 'Autosave failed'], 500);
        }

        event(new NoteUpdated($notepad, [
            'content' => $content,
            'updated_by' => [
                'id' => auth()->id(),
                'name' => auth()->user()->name,
            ],
        ]));

        return response()->json(['ok' => true]);
    }

    public function destroy(Notepad $notepad)
    {
        if ($notepad->user_id !== auth()->id()) {
            abort(403);
        }

        $notepad->delete();

        return redirect('/notes');
    }

    public function share($id, $token)
    {
        $note = Notepad::where('id', $id)
            ->where('share_token', $token)
            ->firstOrFail();

        if (!$note->is_public) {
            abort(403);
        }

        return view('notes.share', compact('note'));
    }

    public function makePublic(Notepad $notepad)
    {
        if ($notepad->user_id !== auth()->id()) {
            abort(403);
        }

        $notepad->is_public = true;
        $notepad->save();

        return back();
    }

    public function shareWithUser(Request $request, Notepad $notepad)
    {
        $request->validate([
            'email' => 'required|email',
            'role' => 'required|in:editor,viewer'
        ]);

        // find user
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'User not found']);
        }

        // attach or update role
        $notepad->users()->syncWithoutDetaching([
            $user->id => ['role' => $request->role]
        ]);

        return back()->with('success', 'User invited successfully');
    }

    public function update(Request $request, Notepad $notepad)
    {
        $role = $notepad->userRole(auth()->user());

        Log::info('Update permission check', ['role' => $role, 'user_id' => auth()->id(), 'notepad_user_id' => $notepad->user_id]);

        if ($notepad->user_id !== auth()->id() && $role !== 'editor') {
            return back()->withErrors(['error' => 'Permission denied: ' . $role]);
        }

        $request->validate([
            'title' => 'required',
            'content' => 'required',
        ]);

        try {
            $notepad->update([
                'title' => $request->title,
                'content' => $request->content,
            ]);
            Log::info('Note updated successfully', [
                'notepad_id' => $notepad->id,
                'title' => $request->title,
                'content' => $request->content,
                'user_id' => auth()->id()
            ]);
            return redirect('/notes')->with('success', 'Note updated successfully');
        } catch (\Exception $e) {
            Log::error('Failed to update note', [
                'notepad_id' => $notepad->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['title' => 'Failed to save note. Check server logs.']);
        }
    }
}
