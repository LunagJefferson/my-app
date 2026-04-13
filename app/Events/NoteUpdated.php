<?php

namespace App\Events;

use App\Models\Notepad;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NoteUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Notepad $notepad, public array $payload)
    {
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel("note.{$this->notepad->id}");
    }

    public function broadcastAs(): string
    {
        return 'NoteUpdated';
    }

    public function broadcastWith(): array
    {
        return $this->payload;
    }
}
