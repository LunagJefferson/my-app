<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Note</h2>
                <p class="text-sm text-gray-500">Update the note content or manage sharing options.</p>
            </div>
            <a href="/notes" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">← Back to Notes</a>
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
            <div class="mx-auto max-w-4xl space-y-6">
<form id="updateForm" method="POST" action="/notes/{{ $notepad->id }}" class="rounded-3xl bg-white p-6 shadow-lg">
    <input type="hidden" name="content" id="content_hidden" />
                    @csrf
                    @method('PUT')

                    <div id="liveStatus" class="mb-6 rounded-3xl border border-indigo-200 bg-indigo-50 p-4 text-sm text-indigo-700">
                        <strong id="liveStatusText">Checking collaborator status...</strong>
                        <p id="liveChangeHint" class="mt-2 hidden text-xs text-indigo-600"></p>
                    </div>

                    <div class="mb-4">
                        <label class="mb-2 block text-sm font-medium text-gray-700">Title</label>
                        <input type="text" name="title" value="{{ $notepad->title }}" placeholder="Note title" required class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:border-green-500 focus:ring-2 focus:ring-green-200" />
                    </div>

                    <div class="mb-6">
                        <label class="mb-2 block text-sm font-medium text-gray-700">Content</label>
                        <div id="editorWrapper" class="relative rounded-xl border border-gray-300 bg-white min-h-[24rem]">
                            <div id="editor" class="min-h-[24rem]"></div>
                        </div>

                    </div>
    <script>
    </script>

                    <div class="flex flex-wrap gap-3">
                        <button type="submit" class="rounded-xl bg-green-600 px-5 py-3 text-white hover:bg-green-700">Update Note</button>
                    </div>
            </form>

            @if($notepad->user_id == auth()->id() && !$notepad->is_public)
            <form method="POST" action="/notes/{{ $notepad->id }}/make-public" class="mx-auto max-w-4xl mt-4 rounded-3xl bg-white p-6 shadow-lg">
                @csrf
                <button type="submit" class="rounded-xl bg-blue-600 px-5 py-3 text-white hover:bg-blue-700">🌍 Make Public</button>
            </form>
            @endif

            @if($notepad->user_id == auth()->id())
            <section class="rounded-3xl bg-white p-6 shadow-lg max-w-4xl mx-auto">
                    <h3 class="text-lg font-semibold mb-4">🔗 Share with User</h3>
                    <form method="POST" action="/notes/{{ $notepad->id }}/share" class="space-y-4">
                        @csrf
                        <input type="email" name="email" placeholder="User email" required class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:border-green-500 focus:ring-2 focus:ring-green-200" />
                        <select name="role" class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:border-green-500 focus:ring-2 focus:ring-green-200">
                            <option value="viewer">Viewer</option>
                            <option value="editor">Editor</option>
                        </select>
                        <button type="submit" class="rounded-xl bg-green-600 px-5 py-3 text-white hover:bg-green-700">Invite User</button>
                    </form>
                </section>
                @endif
            </div>
        </main>
    </div>

    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        #editorWrapper { min-height: 24rem; }
    </style>
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {

            const quill = new Quill('#editor', {
                theme: 'snow',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline'],
                        [{ list: 'ordered' }, { list: 'bullet' }],
                        ['link'],
                        ['clean']
                    ]
                }
            });

            const initialContent = @json(
                $notepad->content
                    ? json_decode($notepad->content, true)
                    : ['ops' => []]
            );

            quill.setContents(initialContent);

            const noteId = @json($notepad->id);
            const currentUserId = @json(auth()->id());
            const form = document.getElementById('updateForm');

            const hiddenContent = document.getElementById('content_hidden');

            form.addEventListener('submit', (e) => {
                hiddenContent.value = JSON.stringify(quill.getContents());
            });

            window.Echo.private(`note.${noteId}`)
                .listen('NoteUpdated', (event) => {

                    if (!event.updated_by || event.updated_by.id === currentUserId) return;

                    if (event.content) {
                        quill.setContents(event.content);
                    }
                });

            let autosaveTimer;

            quill.on('text-change', () => {
                clearTimeout(autosaveTimer);

                autosaveTimer = setTimeout(() => {
                    fetch(`/notes/${noteId}/autosave`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': @json(csrf_token()),
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            content: quill.getContents()
                        }),
                    });
                }, 500);
            });

        });
    </script>
</x-app-layout>