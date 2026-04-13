<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Chat with {{ $user->name }}</h2>
                <p class="text-sm text-gray-500">Send messages and share note links directly.</p>
            </div>
            <a href="/messages" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">← Conversations</a>
        <form method="POST" action="/chat/{{ $user->id }}" 
            onsubmit="return confirm('Delete this conversation?')">
            @csrf
            @method('DELETE')

            <button class="text-sm text-red-600 hover:text-red-800">
                🗑 Delete Conversation
            </button>
        </form>
        </div>
    </x-slot>

    <div class="lg:flex lg:min-h-[calc(100vh-5rem)]">
        <aside class="lg:w-72 bg-gray-800 text-white p-6">
            <h3 class="text-lg font-semibold mb-5">💬 Chat</h3>
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

        <main class="flex-1 p-6 flex flex-col">
            <div class="flex-1 overflow-y-auto space-y-4" id="messages">
                @foreach($messages as $msg)
                    <div class="max-w-xl rounded-3xl p-4 {{ $msg->sender_id == auth()->id() ? 'ml-auto bg-blue-600 text-white' : 'bg-gray-200 text-gray-900' }}">
                        <div class="mb-2 text-sm font-medium">
                            {{ $msg->sender_id == auth()->id() ? 'You' : $user->name }}
                        </div>
                        <p class="whitespace-pre-line">{{ $msg->message }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 rounded-3xl bg-white p-6 shadow-lg">
                <form method="POST" class="space-y-4" id="chatForm">
                    @csrf
                    <input type="text" name="message" placeholder="Type your message..." required class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:border-blue-500 focus:ring-2 focus:ring-blue-200" />
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-green-600 px-6 py-3 text-white hover:bg-green-700">Send Message</button>
                </form>
            </div>
        </main>
    </div>
    

    <script>
        const messagesContainer = document.getElementById('messages');
        const chatForm = document.getElementById('chatForm');
        const messageInput = chatForm.querySelector('input[name="message"]');
        const currentUserId = @json(auth()->id());
        const otherUserId = @json($user->id);
        const chatId = [Math.min(currentUserId, otherUserId), Math.max(currentUserId, otherUserId)].join('.');

        function scrollMessages() {
            if (!messagesContainer) {
                return;
            }
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        function appendMessage(message, senderName, isOwn) {
            const wrapper = document.createElement('div');
            wrapper.className = `max-w-xl rounded-3xl p-4 ${isOwn ? 'ml-auto bg-blue-600 text-white' : 'bg-gray-200 text-gray-900'}`;

            const label = document.createElement('div');
            label.className = 'mb-2 text-sm font-medium';
            label.textContent = senderName;
            wrapper.appendChild(label);

            const text = document.createElement('p');
            text.className = 'whitespace-pre-line';
            text.innerHTML = message.replace(
                /(https?:\/\/[^\s]+)/g,
                '<a href="$1" target="_blank" class="text-blue-400 underline">$1</a>'
            );
            wrapper.appendChild(text);

            messagesContainer.appendChild(wrapper);
            scrollMessages();
        }

        if (chatForm) {
            chatForm.addEventListener('submit', async function (event) {
                event.preventDefault();

                const message = messageInput.value.trim();
                const notepadLink = null

                if (!message && !notepadLink) {
                    return;
                }

                try {
                    const response = await fetch(chatForm.action, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': @json(csrf_token()),
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            message,
                        }),
                    });

                    if (!response.ok) {
                        return;
                    }

                    const data = await response.json();
                    appendMessage(data.message || message, 'You', true, data.notepad_link);
                    messageInput.value = '';
                } catch (error) {
                    console.error('Message send failed', error);
                }
            });
        }

        scrollMessages();
    </script>
</x-app-layout>