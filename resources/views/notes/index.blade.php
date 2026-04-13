<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Notepads</h2>
                <p class="text-sm text-gray-500">Manage your notes, sharing and messaging from one place.</p>
            </div>
        </div>
    </x-slot>

    <div class="lg:flex lg:min-h-[calc(100vh-5rem)]">
        <aside class="lg:w-72 bg-gray-800 text-white p-6">
            <h3 class="text-lg font-semibold mb-5">📝 Notepad</h3>

            <nav class="space-y-3">
                <a href="/notes" class="block rounded-lg px-3 py-2 hover:bg-gray-700">📄 All Notes</a>
                <a href="/notes/create" class="block rounded-lg px-3 py-2 hover:bg-gray-700">➕ New Note</a>
                <a href="/messages" class="block rounded-lg px-3 py-2 hover:bg-gray-700">💬 Messages</a>
                <a href="/profile" class="block rounded-lg px-3 py-2 hover:bg-gray-700">⚙️ Profile</a>
            </nav>

            <div class="mt-auto pt-6 border-t border-gray-700">
                <p class="text-sm text-gray-300">Signed in as</p>
                <p class="mt-2 font-medium">{{ auth()->user()->name }}</p>
            </div>
        </aside>

        <main class="flex-1 p-6">
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <form method="GET" action="/notes" class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <input type="text" name="search" placeholder="Search notes..." value="{{ request('search') }}" class="w-full sm:w-72 rounded-md border border-gray-300 px-3 py-2 focus:border-green-500 focus:ring-2 focus:ring-green-200" />
                    <button class="inline-flex items-center justify-center rounded-md bg-green-600 px-4 py-2 text-white hover:bg-green-700">Search</button>
                </form>
                <a href="/notes/create" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Create Note</a>
            </div>

            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach($notepads as $notepad)
                    @php
                        $role = $notepad->userRole(auth()->user()) ?? ($notepad->user_id == auth()->id() ? 'owner' : null);
                    @endphp
                    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:shadow-md">
                        <div class="mb-3">
                            <h3 class="text-lg font-semibold text-gray-900">{{ $notepad->title }}</h3>
                            <p class="text-sm text-gray-500">Role: {{ ucfirst($role) }}</p>

                            @php
                                $activeEditors = $activeEditors[$notepad->id] ?? [];
                            @endphp

                            @if(count($activeEditors))
                                <div class="mt-3 inline-flex items-center gap-2 rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                    <span class="h-2 w-2 rounded-full bg-green-500"></span>
                                    Editing now by {{ collect($activeEditors)->pluck('name')->unique()->join(', ') }}
                                </div>
                            @endif
                        </div>

                        <div class="flex flex-wrap gap-3">
                            @if($role === 'owner' || $role === 'editor')
                                <a href="/notes/{{ $notepad->id }}/edit" class="rounded-md bg-blue-600 px-3 py-2 text-sm text-white hover:bg-blue-700">Edit</a>
                            @endif

                            @if($role === 'owner')
                                <form method="POST" action="/notes/{{ $notepad->id }}" onsubmit="return confirm('Delete this note?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-md bg-red-600 px-3 py-2 text-sm text-white hover:bg-red-700">Delete</button>
                                </form>
                            @endif

                            <a href="/chat/{{ $notepad->user_id }}" class="rounded-md bg-gray-600 px-3 py-2 text-sm text-white hover:bg-gray-700">💬 Message Owner</a>
                        </div>

                        @if(!$notepad->is_public)
                            <form method="POST" action="/notes/{{ $notepad->id }}/make-public" class="mt-5">
                                @csrf
                                <button type="submit" class="w-full rounded-md bg-emerald-600 px-3 py-2 text-sm text-white hover:bg-emerald-700">🌍 Make Public</button>
                            </form>
                        @endif

                        @if($notepad->is_public)
                            <div class="mt-4 rounded-xl bg-gray-50 p-3 border border-gray-200">
                                <label class="block text-xs uppercase tracking-wide text-gray-500">Public Share Link</label>
                                <div class="mt-2 flex gap-2">
                                    <input type="text" readonly value="{{ url('/notes/' . $notepad->id . '/share/' . $notepad->share_token) }}" class="flex-1 rounded-md border border-gray-300 bg-white px-3 py-2 text-xs text-gray-700" />
                                    <button type="button" class="rounded-md bg-green-600 px-3 py-2 text-sm text-white hover:bg-green-700" onclick="navigator.clipboard.writeText(this.previousElementSibling.value)">📋 Copy</button>
                                </div>
                            </div>
                        @endif
                    </section>
                @endforeach
            </div>
        </main>
    </div>
</x-app-layout>