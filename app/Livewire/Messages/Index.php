<?php

namespace App\Livewire\Messages;

use App\Models\Message;
use App\Models\MessageThread;
use App\Models\MessageThreadParticipant;
use App\Models\User;
use App\Models\WorkOrder;
use App\Support\PermissionCatalog;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class Index extends Component
{
    public ?int $activeThreadId = null;
    public string $replyBody = '';
    public array $composer = [];
    public bool $showComposer = false;
    public string $threadSearch = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can(PermissionCatalog::MESSAGES_VIEW), 403);

        $this->resetComposer();
    }

    public function resetComposer(): void
    {
        $this->composer = [
            'subject' => '',
            'recipient_id' => null,
            'work_order_id' => null,
            'message' => '',
        ];
    }

    public function updatedThreadSearch(): void
    {
        $this->activeThreadId = null;
    }

    public function selectThread(int $threadId): void
    {
        $userId = auth()->id();

        if (! $userId || ! $this->threadExistsForUser($threadId, $userId)) {
            return;
        }

        $this->activeThreadId = $threadId;
        $this->replyBody = '';
        $this->markThreadRead($threadId, $userId);
    }

    public function startComposer(): void
    {
        if (! auth()->user()?->can(PermissionCatalog::MESSAGES_SEND)) {
            return;
        }

        $this->resetComposer();
        $this->showComposer = true;
    }

    public function cancelComposer(): void
    {
        $this->resetComposer();
        $this->showComposer = false;
    }

    protected function rules(): array
    {
        return [
            'composer.recipient_id' => ['required', 'exists:users,id'],
            'composer.subject' => ['nullable', 'string', 'max:255'],
            'composer.work_order_id' => ['nullable', 'exists:work_orders,id'],
            'composer.message' => ['required', 'string'],
        ];
    }

    public function createThread(): void
    {
        if (! auth()->user()?->can(PermissionCatalog::MESSAGES_SEND)) {
            return;
        }

        $this->validate();

        $user = auth()->user();
        if (! $user) {
            return;
        }

        $recipientId = (int) $this->composer['recipient_id'];
        if ($recipientId === $user->id) {
            $this->addError('composer.recipient_id', 'Choose another recipient.');
            return;
        }

        $subject = trim($this->composer['subject']);

        $thread = MessageThread::create([
            'subject' => $subject !== '' ? $subject : null,
            'organization_id' => $user->organization_id,
            'work_order_id' => $this->normalizeId($this->composer['work_order_id']),
            'created_by_user_id' => $user->id,
        ]);

        MessageThreadParticipant::create([
            'thread_id' => $thread->id,
            'user_id' => $user->id,
            'last_read_at' => now(),
        ]);

        MessageThreadParticipant::create([
            'thread_id' => $thread->id,
            'user_id' => $recipientId,
        ]);

        Message::create([
            'thread_id' => $thread->id,
            'user_id' => $user->id,
            'body' => $this->composer['message'],
        ]);

        $thread->touch();

        $this->activeThreadId = $thread->id;
        $this->resetComposer();
        $this->showComposer = false;
        session()->flash('status', 'Message sent.');
    }

    public function sendReply(): void
    {
        if (! auth()->user()?->can(PermissionCatalog::MESSAGES_SEND)) {
            return;
        }

        if (! $this->activeThreadId) {
            return;
        }

        $userId = auth()->id();
        if (! $userId || ! $this->threadExistsForUser($this->activeThreadId, $userId)) {
            return;
        }

        $this->validate([
            'replyBody' => ['required', 'string'],
        ]);

        Message::create([
            'thread_id' => $this->activeThreadId,
            'user_id' => $userId,
            'body' => $this->replyBody,
        ]);

        MessageThread::whereKey($this->activeThreadId)->update(['updated_at' => now()]);
        $this->markThreadRead($this->activeThreadId, $userId);
        $this->replyBody = '';
    }

    public function render()
    {
        $user = auth()->user();
        if (! $user) {
            return view('livewire.messages.index', [
                'threads' => collect(),
                'activeThread' => null,
                'activeMessages' => collect(),
                'recipients' => collect(),
                'workOrders' => collect(),
                'user' => null,
                'unreadCount' => 0,
            ]);
        }

        $threadsQuery = $this->threadBaseQuery($user->id);
        $this->applyThreadSearch($threadsQuery, $this->threadSearch);

        $threads = $threadsQuery
            ->with([
                'workOrder',
                'participants.user',
                'messages' => function ($builder) {
                    $builder->latest()->limit(1)->with('user');
                },
            ])
            ->withCount('messages')
            ->orderByDesc('updated_at')
            ->get();

        if ($this->activeThreadId === null && $threads->isNotEmpty()) {
            $this->activeThreadId = $threads->first()->id;
        }

        $activeThread = $this->activeThreadId
            ? $threads->firstWhere('id', $this->activeThreadId)
            : null;

        $activeMessages = $activeThread
            ? Message::query()
                ->where('thread_id', $activeThread->id)
                ->with('user')
                ->orderBy('created_at')
                ->get()
            : collect();

        $recipients = $this->canSend
            ? User::query()
                ->where('id', '!=', $user->id)
                ->orderBy('name')
                ->get()
            : collect();
        $workOrders = $this->canSend
            ? WorkOrder::orderByDesc('created_at')->take(50)->get()
            : collect();
        $unreadCount = $threads
            ->filter(fn ($thread) => $this->threadIsUnread($thread, $user->id))
            ->count();

        return view('livewire.messages.index', [
            'threads' => $threads,
            'activeThread' => $activeThread,
            'activeMessages' => $activeMessages,
            'recipients' => $recipients,
            'workOrders' => $workOrders,
            'user' => $user,
            'unreadCount' => $unreadCount,
            'canSend' => $this->canSend,
        ]);
    }

    public function getCanSendProperty(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->can(PermissionCatalog::MESSAGES_SEND);
    }

    private function threadBaseQuery(int $userId): Builder
    {
        return MessageThread::query()->whereHas('participants', function ($builder) use ($userId) {
            $builder->where('user_id', $userId);
        });
    }

    private function applyThreadSearch(Builder $query, string $search): void
    {
        $search = trim($search);
        if ($search === '') {
            return;
        }

        $searchLike = '%' . $search . '%';

        $query->where(function (Builder $builder) use ($search, $searchLike) {
            $builder->where('subject', 'like', $searchLike)
                ->orWhereHas('workOrder', function (Builder $workOrderBuilder) use ($searchLike) {
                    $workOrderBuilder->where('subject', 'like', $searchLike);
                })
                ->orWhereHas('messages', function (Builder $messageBuilder) use ($searchLike) {
                    $messageBuilder->where('body', 'like', $searchLike);
                })
                ->orWhereHas('participants.user', function (Builder $userBuilder) use ($searchLike) {
                    $userBuilder->where('name', 'like', $searchLike);
                });

            if (is_numeric($search)) {
                $builder->orWhere('id', (int) $search)
                    ->orWhere('work_order_id', (int) $search);
            }
        });
    }

    private function threadExistsForUser(int $threadId, int $userId): bool
    {
        return $this->threadBaseQuery($userId)->whereKey($threadId)->exists();
    }

    private function markThreadRead(int $threadId, int $userId): void
    {
        MessageThreadParticipant::query()
            ->where('thread_id', $threadId)
            ->where('user_id', $userId)
            ->update(['last_read_at' => now()]);
    }

    private function normalizeId(mixed $value): ?int
    {
        if ($value === '' || $value === null) {
            return null;
        }

        return (int) $value;
    }

    private function threadIsUnread(MessageThread $thread, int $userId): bool
    {
        $lastMessage = $thread->messages->first();
        if (! $lastMessage) {
            return false;
        }

        $participant = $thread->participants->firstWhere('user_id', $userId);
        $lastReadAt = $participant?->last_read_at;

        if (! $lastReadAt) {
            return true;
        }

        return $lastMessage->created_at?->gt($lastReadAt) ?? false;
    }
}
