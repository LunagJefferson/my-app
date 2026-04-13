<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Messages</h2>
                <p class="text-sm text-gray-500">Start a new conversation or continue existing chats.</p>
            </div>
        </div>
    </x-slot>

    <div class="lg:flex lg:min-h-[calc(100vh-5rem)]">
        <aside class="lg:w-72 bg-gray-800 text-white p-6">
            <h3 class="text-lg font-semibold mb-5">💬 Messaging</h3>
            <nav class="space-y-3">
                <a href="/notes" class="block rounded-lg px-3 py-2 hover:bg-gray-700">📄 All Notes</a>
                <a href="/notes/create" class="block rounded-lg px-3 py-2 hover:bg-gray-700">➕ New Note</a>
                <a href="/messages" class="block rounded-lg px-3 py-2 bg-gray-700">💬 Messages</a>
                <a href="/profile" class="block rounded-lg px-3 py-2 hover:bg-gray-700">⚙️ Profile</a>
            </nav>

            <div class="mt-auto pt-6 border-t border-gray-700">
                <p class="text-sm text-gray-300">Signed in as</p>
                <p class="mt-2 font-medium">{{ auth()->user()->name }}</p>
            </div>
        </aside>

        <main class="flex-1 p-6">
            <section class="rounded-3xl bg-white p-6 shadow-lg mb-6">
                <h3 class="text-lg font-semibold mb-4">💬 Start New Conversation</h3>
                @if($errors->any())
                    <div class="mb-4 rounded-xl bg-red-50 p-4 text-sm text-red-700">
                        @foreach($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif
                <form method="POST" action="/messages/send" class="space-y-4">
                    @csrf
                    <input type="email" name="email" placeholder="Recipient email" required class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:border-green-500 focus:ring-2 focus:ring-green-200" />
                    <input type="text" name="message" placeholder="Your message" required class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:border-green-500 focus:ring-2 focus:ring-green-200" />
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-green-600 px-6 py-3 text-white hover:bg-green-700">Send Message</button>
                </form>
            </section>

            <section class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @forelse($users as $user)
                    <article class="rounded-3xl bg-white p-5 shadow-lg">
                        <h3 class="text-lg font-semibold mb-3">{{ $user->name }}</h3>
                        <a href="/chat/{{ $user->id }}" class="inline-flex items-center rounded-xl bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">💬 Chat</a>
                        
                        <form method="POST" action="/chat/{{ $user->id }}" 
                            onsubmit="return confirm('Delete conversation with {{ $user->name }}?')">
                            @csrf
                            @method('DELETE')

                            <button class="mt-2 text-xs text-red-500 hover:text-red-700">
                                Delete
                            </button>
                        </form>
                    </article>
                @empty
                    <div class="rounded-3xl bg-white p-6 shadow-lg text-center text-gray-500">No conversations yet. Start by sending a message above.</div>
                @endforelse
            </section>
        </main>
    </div>
</x-app-layout>