<?php

namespace App\Livewire\Messages;

use App\Models\Message;
use App\Models\MessageThread;
use App\Models\MessageThreadParticipant;
use App\Models\User;
use App\Models\WorkOrder;
use Livewire\Component;

class Index extends Component
{
    public ?int $activeThreadId = null;
    public string $replyBody = '';
    public array $newThread = [];

    public function mount(): void
    {
        $this->resetNewThread();
    }

    public function resetNewThread(): void
    {
        $this->newThread = [
            'subject' => '',
            'recipient_id' => null,
            'work_order_id' => null,
            'message' => '',
        ];
    }

    public function selectThread(int $threadId): void
    {
        $this->activeThreadId = $threadId;
    }

    public function startThread(): void
    {
        $this->validate([
            'newThread.recipient_id' => ['required', 'exists:users,id'],
            'newThread.subject' => ['nullable', 'string', 'max:255'],
            'newThread.work_order_id' => ['nullable', 'exists:work_orders,id'],
            'newThread.message' => ['required', 'string'],
        ]);

        $user = auth()->user();

        $thread = MessageThread::create([
            'subject' => $this->newThread['subject'],
            'organization_id' => $user->organization_id,
            'work_order_id' => $this->newThread['work_order_id'],
            'created_by_user_id' => $user->id,
        ]);

        MessageThreadParticipant::create([
            'thread_id' => $thread->id,
            'user_id' => $user->id,
        ]);

        MessageThreadParticipant::create([
            'thread_id' => $thread->id,
            'user_id' => $this->newThread['recipient_id'],
        ]);

        Message::create([
            'thread_id' => $thread->id,
            'user_id' => $user->id,
            'body' => $this->newThread['message'],
        ]);

        $this->activeThreadId = $thread->id;
        $this->resetNewThread();
        session()->flash('status', 'Message sent.');
    }

    public function sendReply(): void
    {
        if (! $this->activeThreadId) {
            return;
        }

        $this->validate([
            'replyBody' => ['required', 'string'],
        ]);

        Message::create([
            'thread_id' => $this->activeThreadId,
            'user_id' => auth()->id(),
            'body' => $this->replyBody,
        ]);

        $this->replyBody = '';
    }

    public function render()
    {
        $user = auth()->user();

        $threads = MessageThread::whereHas('participants', function ($builder) use ($user) {
            $builder->where('user_id', $user->id);
        })
            ->with(['messages.user'])
            ->latest()
            ->get();

        $activeThread = $this->activeThreadId
            ? $threads->firstWhere('id', $this->activeThreadId)
            : $threads->first();

        if ($activeThread && $this->activeThreadId === null) {
            $this->activeThreadId = $activeThread->id;
        }

        $recipients = User::orderBy('name')->get();
        $workOrders = WorkOrder::orderBy('subject')->take(50)->get();

        return view('livewire.messages.index', [
            'threads' => $threads,
            'activeThread' => $activeThread,
            'recipients' => $recipients,
            'workOrders' => $workOrders,
        ]);
    }
}
