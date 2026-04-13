<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create Note</h2>
                <p class="text-sm text-gray-500">Write a new note and save it instantly.</p>
            </div>
            <a href="/notes" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">← Back to Notes</a>
        </div>
    </x-slot>

    <div class="lg:flex lg:min-h-[calc(100vh-5rem)]">
        <aside class="lg:w-72 bg-gray-800 text-white p-6">
            <h3 class="text-lg font-semibold mb-5">📝 Notepad</h3>
            <nav class="space-y-3">
                <a href="/notes" class="block rounded-lg px-3 py-2 hover:bg-gray-700">📄 All Notes</a>
                <a href="/notes/create" class="block rounded-lg px-3 py-2 bg-gray-700">➕ New Note</a>
                <a href="/messages" class="block rounded-lg px-3 py-2 hover:bg-gray-700">💬 Messages</a>
                <a href="/profile" class="block rounded-lg px-3 py-2 hover:bg-gray-700">⚙️ Profile</a>
            </nav>

            <div class="mt-auto pt-6 border-t border-gray-700">
                <p class="text-sm text-gray-300">Signed in as</p>
                <p class="mt-2 font-medium">{{ auth()->user()->name }}</p>
            </div>
        </aside>

        <main class="flex-1 p-6">
            <form method="POST" action="/notes" class="mx-auto max-w-4xl rounded-3xl bg-white p-6 shadow-lg">
                @csrf
                <div class="mb-4">
                    <label class="mb-2 block text-sm font-medium text-gray-700">Title</label>
                    <input type="text" name="title" placeholder="Note title" required class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:border-green-500 focus:ring-2 focus:ring-green-200" />
                </div>

                <div class="mb-6">
                    <label class="mb-2 block text-sm font-medium text-gray-700">Content</label>
                    <div id="editor" class="h-96 rounded-xl border border-gray-300 bg-white"></div>
                    <input type="hidden" name="content" id="content" class="hidden" aria-hidden="true" />
                </div>

                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-green-600 px-6 py-3 text-white hover:bg-green-700">Save Note</button>
            </form>
        </main>
    </div>

    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    <script>
        var quill = new Quill('#editor', {
            theme: 'snow',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['link'],
                    ['clean']
                ]
            }
        });

        document.querySelector('form').addEventListener('submit', function () {
            document.querySelector('#content').value = quill.root.innerHTML;
        });
    </script>
</x-app-layout>