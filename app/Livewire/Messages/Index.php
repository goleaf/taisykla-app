<?php

namespace App\Livewire\Messages;

use App\Models\CommunicationTemplate;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\MessageFolder;
use App\Models\MessageAutomation;
use App\Models\MessageParticipant;
use App\Models\MessageThread;
use App\Models\MessageThreadParticipant;
use App\Models\NotificationPreference;
use App\Models\User;
use App\Models\WorkOrder;
use App\Services\Messaging\MessagingPolicyService;
use App\Services\Messaging\MessagingDispatchService;
use App\Support\PermissionCatalog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Index extends Component
{
    use WithFileUploads;
    use WithPagination;

    public ?int $activeThreadId = null;
    public string $replyBody = '';
    public array $composer = [];
    public array $composerAttachments = [];
    public bool $showComposer = false;
    public string $threadSearch = '';
    public string $activeFolder = 'inbox';
    public array $selectedThreads = [];
    public bool $selectAll = false;
    public string $bulkMoveFolder = '';
    public string $recipientSearch = '';
    public string $ccSearch = '';
    public string $bccSearch = '';
    public ?int $selectedTemplateId = null;
    public bool $showTemplatePanel = false;
    public string $newFolderName = '';
    public array $filters = [
        'sender' => '',
        'date_start' => '',
        'date_end' => '',
        'has_attachments' => false,
        'work_order_id' => '',
        'message_type' => '',
        'channel' => '',
        'unread' => false,
    ];
    public array $notificationPreferences = [];
    public int $perPage = 15;

    protected $queryString = [
        'activeFolder' => ['except' => 'inbox'],
        'threadSearch' => ['except' => ''],
    ];

    public function mount(): void
    {
        abort_unless(auth()->user()?->can(PermissionCatalog::MESSAGES_VIEW), 403);

        $this->resetComposer();
        $this->loadPreferences();
    }

    public function resetComposer(): void
    {
        $this->composer = [
            'subject' => '',
            'recipient_ids' => [],
            'cc_ids' => [],
            'bcc_ids' => [],
            'work_order_id' => null,
            'message' => '',
            'channel' => 'in_app',
            'message_type' => 'direct',
            'broadcast_all' => false,
            'template_id' => null,
        ];

        $this->composerAttachments = [];
        $this->recipientSearch = '';
        $this->ccSearch = '';
        $this->bccSearch = '';
        $this->selectedTemplateId = null;
    }

    public function updatedThreadSearch(): void
    {
        $this->resetPage();
        $this->activeThreadId = null;
    }

    public function updatedActiveFolder(): void
    {
        $this->resetPage();
        $this->selectedThreads = [];
        $this->selectAll = false;
        $this->activeThreadId = null;
    }

    public function updatedFilters(): void
    {
        $this->resetPage();
        $this->activeThreadId = null;
    }

    public function updatedSelectedTemplateId(): void
    {
        if ($this->selectedTemplateId) {
            $this->applyTemplate((int) $this->selectedTemplateId);
        }
    }

    public function selectThread(int $threadId): void
    {
        $userId = auth()->id();

        if (!$userId || !$this->threadExistsForUser($threadId, $userId)) {
            return;
        }

        $this->activeThreadId = $threadId;
        $this->replyBody = '';
        $this->markThreadRead($threadId, $userId);
    }

    public function toggleThreadSelection(int $threadId): void
    {
        if (in_array($threadId, $this->selectedThreads, true)) {
            $this->selectedThreads = array_values(array_diff($this->selectedThreads, [$threadId]));
            $this->selectAll = false;
            return;
        }

        $this->selectedThreads[] = $threadId;
    }

    public function toggleSelectAll(): void
    {
        if ($this->selectAll) {
            $this->selectAll = false;
            $this->selectedThreads = [];
            return;
        }

        $this->selectAll = true;
        $this->selectedThreads = $this->currentThreadIds();
    }

    public function clearSelection(): void
    {
        $this->selectedThreads = [];
        $this->selectAll = false;
    }

    public function markSelectedRead(): void
    {
        $userId = auth()->id();
        if (!$userId || empty($this->selectedThreads)) {
            return;
        }

        MessageThreadParticipant::query()
            ->where('user_id', $userId)
            ->whereIn('thread_id', $this->selectedThreads)
            ->update(['last_read_at' => now()]);
    }

    public function markSelectedUnread(): void
    {
        $userId = auth()->id();
        if (!$userId || empty($this->selectedThreads)) {
            return;
        }

        MessageThreadParticipant::query()
            ->where('user_id', $userId)
            ->whereIn('thread_id', $this->selectedThreads)
            ->update(['last_read_at' => null]);
    }

    public function archiveSelected(): void
    {
        $userId = auth()->id();
        if (!$userId || empty($this->selectedThreads)) {
            return;
        }

        MessageThreadParticipant::query()
            ->where('user_id', $userId)
            ->whereIn('thread_id', $this->selectedThreads)
            ->update([
                'is_archived' => true,
                'folder' => 'archived',
            ]);

        $this->clearSelection();
    }

    public function deleteSelected(): void
    {
        $userId = auth()->id();
        if (!$userId || empty($this->selectedThreads)) {
            return;
        }

        MessageThreadParticipant::query()
            ->where('user_id', $userId)
            ->whereIn('thread_id', $this->selectedThreads)
            ->update(['deleted_at' => now()]);

        $this->clearSelection();
    }

    public function moveSelected(): void
    {
        $folder = trim($this->bulkMoveFolder);
        $userId = auth()->id();
        if (!$userId || $folder === '' || empty($this->selectedThreads)) {
            return;
        }

        MessageThreadParticipant::query()
            ->where('user_id', $userId)
            ->whereIn('thread_id', $this->selectedThreads)
            ->update([
                'folder' => $folder,
                'is_archived' => $folder === 'archived',
            ]);

        $this->clearSelection();
    }

    public function forwardSelected(): void
    {
        if (empty($this->selectedThreads)) {
            return;
        }

        $threadId = $this->selectedThreads[0];
        $thread = MessageThread::with('messages')->find($threadId);
        if (!$thread) {
            return;
        }

        $lastMessage = $thread->messages->sortByDesc('created_at')->first();
        $this->startComposer();
        $this->composer['subject'] = 'Fwd: ' . ($thread->subject ?? 'Conversation');
        $this->composer['message'] = $lastMessage ? "Forwarded message:\n\n" . $lastMessage->body : '';
    }

    public function toggleStar(int $threadId): void
    {
        $userId = auth()->id();
        if (!$userId) {
            return;
        }

        $participant = MessageThreadParticipant::query()
            ->where('thread_id', $threadId)
            ->where('user_id', $userId)
            ->first();

        if (!$participant) {
            return;
        }

        $participant->update(['is_starred' => !$participant->is_starred]);
    }

    public function startComposer(): void
    {
        if (!auth()->user()?->can(PermissionCatalog::MESSAGES_SEND)) {
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

    public function addRecipient(string $type): void
    {
        $searchMap = [
            'to' => 'recipientSearch',
            'cc' => 'ccSearch',
            'bcc' => 'bccSearch',
        ];
        $keyMap = [
            'to' => 'recipient_ids',
            'cc' => 'cc_ids',
            'bcc' => 'bcc_ids',
        ];

        if (!isset($searchMap[$type], $keyMap[$type])) {
            return;
        }

        $value = trim($this->{$searchMap[$type]});
        if ($value === '') {
            return;
        }

        $user = User::query()
            ->where('email', $value)
            ->orWhere('name', $value)
            ->first();

        if (!$user) {
            $this->addError($searchMap[$type], 'User not found.');
            return;
        }

        $key = $keyMap[$type];
        $current = $this->composer[$key] ?? [];
        if (!in_array($user->id, $current, true)) {
            $current[] = $user->id;
            $this->composer[$key] = $current;
        }

        $this->{$searchMap[$type]} = '';
    }

    public function removeRecipient(string $type, int $userId): void
    {
        $keyMap = [
            'recipient' => 'recipient_ids',
            'to' => 'recipient_ids',
            'cc' => 'cc_ids',
            'bcc' => 'bcc_ids',
        ];
        $key = $keyMap[$type] ?? null;
        if (!$key) {
            return;
        }
        $current = $this->composer[$key] ?? [];
        $this->composer[$key] = array_values(array_filter($current, fn($id) => (int) $id !== $userId));
    }

    public function createFolder(): void
    {
        $user = auth()->user();
        $name = trim($this->newFolderName);

        if (!$user || $name === '') {
            return;
        }

        MessageFolder::firstOrCreate([
            'user_id' => $user->id,
            'slug' => Str::slug($name),
        ], [
            'name' => $name,
            'color' => 'slate',
        ]);

        $this->newFolderName = '';
    }

    public function saveNotificationPreferences(): void
    {
        $user = auth()->user();
        if (!$user) {
            return;
        }

        foreach ($this->notificationPreferences as $channel => $data) {
            $vipSenders = array_values(array_filter(array_map('trim', explode(',', (string) ($data['vip_senders'] ?? '')))));
            NotificationPreference::updateOrCreate([
                'user_id' => $user->id,
                'channel' => $channel,
            ], [
                'frequency' => $data['frequency'] ?? 'immediate',
                'message_types' => $data['message_types'] ?? [],
                'vip_senders' => $vipSenders,
                'quiet_hours_start' => $data['quiet_hours_start'] ?? null,
                'quiet_hours_end' => $data['quiet_hours_end'] ?? null,
                'is_enabled' => (bool) ($data['is_enabled'] ?? true),
            ]);
        }

        session()->flash('status', 'Notification preferences updated.');
    }

    public function toggleAutomation(int $automationId): void
    {
        $automation = MessageAutomation::find($automationId);
        if (!$automation) {
            return;
        }

        $automation->update(['is_enabled' => !$automation->is_enabled]);
    }

    protected function rules(): array
    {
        return [
            'composer.recipient_ids' => ['required_without:composer.broadcast_all', 'array'],
            'composer.recipient_ids.*' => ['integer', 'exists:users,id'],
            'composer.cc_ids' => ['array'],
            'composer.cc_ids.*' => ['integer', 'exists:users,id'],
            'composer.bcc_ids' => ['array'],
            'composer.bcc_ids.*' => ['integer', 'exists:users,id'],
            'composer.subject' => ['nullable', 'string', 'max:255'],
            'composer.work_order_id' => ['nullable', 'exists:work_orders,id'],
            'composer.message' => ['required', 'string'],
            'composer.channel' => ['required', 'string', 'max:20'],
            'composer.message_type' => ['required', 'string', 'max:30'],
            'composer.broadcast_all' => ['boolean'],
            'composerAttachments.*' => ['file', 'max:10240'],
        ];
    }

    public function applyTemplate(int $templateId): void
    {
        $template = CommunicationTemplate::find($templateId);
        if (!$template) {
            return;
        }

        $context = $this->mergeContext();
        $this->composer['subject'] = $this->renderTemplate($template->subject, $context);
        $this->composer['message'] = $this->renderTemplate($template->body, $context);
        $this->selectedTemplateId = $templateId;
    }

    public function saveTemplateFromComposer(): void
    {
        $user = auth()->user();
        if (!$user || !$this->canSend) {
            return;
        }

        CommunicationTemplate::create([
            'name' => 'Custom Template ' . now()->format('M d H:i'),
            'category' => 'Custom',
            'channel' => $this->composer['channel'] ?? 'in_app',
            'subject' => $this->composer['subject'] ?? null,
            'body' => $this->composer['message'] ?? '',
            'is_active' => true,
            'is_shared' => false,
            'merge_fields' => array_keys($this->mergeContext()),
            'created_by_user_id' => $user->id,
        ]);
    }

    public function saveDraft(): void
    {
        $user = auth()->user();
        if (!$user || !$this->canSend) {
            return;
        }

        $thread = MessageThread::create([
            'subject' => trim($this->composer['subject']) !== '' ? $this->composer['subject'] : null,
            'organization_id' => $user->organization_id,
            'work_order_id' => $this->normalizeId($this->composer['work_order_id']),
            'created_by_user_id' => $user->id,
            'type' => 'draft',
            'status' => 'draft',
        ]);

        MessageThreadParticipant::create([
            'thread_id' => $thread->id,
            'user_id' => $user->id,
            'folder' => 'drafts',
            'last_read_at' => now(),
        ]);

        Message::create([
            'thread_id' => $thread->id,
            'user_id' => $user->id,
            'sender_id' => $user->id,
            'subject' => $thread->subject,
            'body' => $this->composer['message'] ?? '',
            'timestamp' => now(),
            'message_type' => 'draft',
            'channel' => $this->composer['channel'] ?? 'in_app',
            'metadata' => ['draft' => true],
        ]);

        $this->resetComposer();
        $this->showComposer = false;
        session()->flash('status', 'Draft saved.');
    }

    public function sendMessage(): void
    {
        if (!$this->canSend) {
            return;
        }

        $this->validate();

        $user = auth()->user();
        if (!$user) {
            return;
        }

        if (!$this->checkRateLimit($user->id, 'send')) {
            return;
        }

        $toIds = Arr::wrap($this->composer['recipient_ids'] ?? []);
        $ccIds = Arr::wrap($this->composer['cc_ids'] ?? []);
        $bccIds = Arr::wrap($this->composer['bcc_ids'] ?? []);
        $recipientIds = $this->resolveRecipients($user->id);
        if (empty($recipientIds)) {
            $this->addError('composer.recipient_ids', 'Select at least one recipient.');
            return;
        }

        $messageType = $this->composer['message_type'] ?? 'direct';
        if (!empty($this->composer['broadcast_all'])) {
            $messageType = 'broadcast';
        }
        if (count($recipientIds) > 1 && $messageType === 'direct') {
            $messageType = 'group';
        }
        $threadType = $messageType === 'broadcast'
            ? 'broadcast'
            : (count($recipientIds) > 1 ? 'group' : $messageType);

        $thread = MessageThread::create([
            'subject' => trim($this->composer['subject']) !== '' ? $this->composer['subject'] : null,
            'organization_id' => $user->organization_id,
            'work_order_id' => $this->normalizeId($this->composer['work_order_id']),
            'created_by_user_id' => $user->id,
            'type' => $threadType,
        ]);

        $this->createParticipants($thread, $user->id, $recipientIds);

        $message = Message::create([
            'thread_id' => $thread->id,
            'user_id' => $user->id,
            'sender_id' => $user->id,
            'recipient_id' => count($recipientIds) === 1 ? $recipientIds[0] : null,
            'subject' => $thread->subject,
            'body' => $this->composer['message'],
            'timestamp' => now(),
            'message_type' => $messageType,
            'channel' => $this->composer['channel'] ?? 'in_app',
            'related_work_order_id' => $thread->work_order_id,
        ]);

        $this->storeMessageAttachments($message, $user->id);
        $recipientMap = [
            'to' => !empty($this->composer['broadcast_all']) ? $recipientIds : $toIds,
            'cc' => $ccIds,
            'bcc' => $bccIds,
        ];
        $this->createMessageParticipants($message, $recipientMap, $this->composer['channel'] ?? 'in_app');

        app(MessagingDispatchService::class)->dispatch($message);

        $thread->touch();

        $this->activeThreadId = $thread->id;
        $this->resetComposer();
        $this->showComposer = false;
        session()->flash('status', 'Message sent.');
    }

    public function sendReply(): void
    {
        if (!$this->canSend || !$this->activeThreadId) {
            return;
        }

        $userId = auth()->id();
        if (!$userId || !$this->threadExistsForUser($this->activeThreadId, $userId)) {
            return;
        }

        if (!$this->checkRateLimit($userId, 'reply')) {
            return;
        }

        $this->validate([
            'replyBody' => ['required', 'string'],
        ]);

        $thread = MessageThread::find($this->activeThreadId);
        if (!$thread) {
            return;
        }

        $participantIds = $thread->participants
            ->pluck('user_id')
            ->filter(fn($id) => (int) $id !== $userId)
            ->values()
            ->all();

        $message = Message::create([
            'thread_id' => $thread->id,
            'user_id' => $userId,
            'sender_id' => $userId,
            'recipient_id' => count($participantIds) === 1 ? $participantIds[0] : null,
            'subject' => $thread->subject,
            'body' => $this->replyBody,
            'timestamp' => now(),
            'message_type' => $thread->type ?? 'direct',
            'channel' => 'in_app',
            'related_work_order_id' => $thread->work_order_id,
        ]);

        $this->createMessageParticipants($message, ['to' => $participantIds], 'in_app');

        $thread->update(['updated_at' => now()]);
        $this->markThreadRead($thread->id, $userId);
        $this->replyBody = '';
    }

    public function render()
    {
        $user = auth()->user();
        if (!$user) {
            return view('livewire.messages.index', [
                'threads' => collect(),
                'activeThread' => null,
                'activeMessages' => collect(),
                'recipients' => collect(),
                'workOrders' => collect(),
                'templates' => collect(),
                'folders' => collect(),
                'user' => null,
                'unreadCount' => 0,
                'folderCounts' => [],
                'canSend' => false,
            ]);
        }

        $threadsQuery = $this->threadBaseQuery($user->id);
        $this->applyFolderFilter($threadsQuery, $user->id, $this->activeFolder);
        $this->applyThreadSearch($threadsQuery, $this->threadSearch);
        $this->applyFilters($threadsQuery, $this->filters, $user->id);

        $threads = $threadsQuery
            ->with([
                'workOrder',
                'participants.user',
                'messages' => function ($builder) {
                    $builder->latest()->limit(1)->with(['user', 'messageAttachments', 'attachments']);
                },
            ])
            ->withCount('messages')
            ->orderByDesc('updated_at')
            ->paginate($this->perPage);

        if ($this->activeThreadId === null && $threads->isNotEmpty()) {
            $this->activeThreadId = $threads->first()->id;
        }

        $activeThread = $this->activeThreadId
            ? $threads->firstWhere('id', $this->activeThreadId)
            : null;

        $activeMessages = $activeThread
            ? Message::query()
                ->where('thread_id', $activeThread->id)
                ->with(['user', 'sender', 'messageAttachments', 'attachments'])
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

        $templates = CommunicationTemplate::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $folders = MessageFolder::query()
            ->where('user_id', $user->id)
            ->orderBy('name')
            ->get();

        $automations = MessageAutomation::query()
            ->orderBy('name')
            ->get();

        $unreadCount = $this->unreadThreadCount($user->id);
        $folderCounts = $this->folderCounts($user->id);

        return view('livewire.messages.index', [
            'threads' => $threads,
            'activeThread' => $activeThread,
            'activeMessages' => $activeMessages,
            'recipients' => $recipients,
            'workOrders' => $workOrders,
            'templates' => $templates,
            'folders' => $folders,
            'automations' => $automations,
            'user' => $user,
            'unreadCount' => $unreadCount,
            'folderCounts' => $folderCounts,
            'canSend' => $this->canSend,
            'notificationPreferences' => $this->notificationPreferences,
        ]);
    }

    public function getCanSendProperty(): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        return $user->can(PermissionCatalog::MESSAGES_SEND);
    }

    private function threadBaseQuery(int $userId): Builder
    {
        return MessageThread::query()->whereHas('participants', function ($builder) use ($userId) {
            $builder->where('user_id', $userId)
                ->whereNull('deleted_at');
        });
    }

    private function applyFolderFilter(Builder $query, int $userId, string $folder): void
    {
        $query->whereHas('participants', function ($builder) use ($userId, $folder) {
            $builder->where('user_id', $userId)
                ->whereNull('deleted_at');

            if ($folder === 'archived') {
                $builder->where('is_archived', true);
                return;
            }

            if ($folder === 'starred') {
                $builder->where('is_starred', true);
                return;
            }

            if ($folder === 'drafts') {
                $builder->where('folder', 'drafts');
                return;
            }

            if (!in_array($folder, ['inbox', 'sent', 'work_orders'], true)) {
                $builder->where('folder', $folder);
                return;
            }

            $builder->where('folder', $folder === 'sent' ? 'sent' : 'inbox');
        });

        if ($folder === 'sent') {
            $query->whereHas('messages', function ($builder) use ($userId) {
                $builder->where('user_id', $userId);
            });
        }

        if ($folder === 'work_orders') {
            $query->whereNotNull('work_order_id');
        }
    }

    private function applyThreadSearch(Builder $query, string $search): void
    {
        $search = trim($search);
        if ($search === '') {
            return;
        }

        $searchLike = '%' . $search . '%';

        $query->where(function (Builder $builder) use ($searchLike, $search) {
            $builder->where('subject', 'like', $searchLike)
                ->orWhereHas('workOrder', function (Builder $workOrderBuilder) use ($searchLike) {
                    $workOrderBuilder->where('subject', 'like', $searchLike);
                })
                ->orWhereHas('messages', function (Builder $messageBuilder) use ($searchLike) {
                    $messageBuilder->where('body', 'like', $searchLike)
                        ->orWhere('subject', 'like', $searchLike);
                })
                ->orWhereHas('participants.user', function (Builder $userBuilder) use ($searchLike) {
                    $userBuilder->where('name', 'like', $searchLike)
                        ->orWhere('email', 'like', $searchLike);
                });

            if (is_numeric($search)) {
                $builder->orWhere('id', (int) $search)
                    ->orWhere('work_order_id', (int) $search);
            }
        });
    }

    private function applyFilters(Builder $query, array $filters, int $userId): void
    {
        if (!empty($filters['sender'])) {
            $sender = trim((string) $filters['sender']);
            $query->whereHas('messages.user', function (Builder $builder) use ($sender) {
                $builder->where('name', 'like', '%' . $sender . '%')
                    ->orWhere('email', 'like', '%' . $sender . '%');
            });
        }

        if (!empty($filters['date_start'])) {
            $query->whereDate('updated_at', '>=', $filters['date_start']);
        }

        if (!empty($filters['date_end'])) {
            $query->whereDate('updated_at', '<=', $filters['date_end']);
        }

        if (!empty($filters['work_order_id'])) {
            $query->where('work_order_id', (int) $filters['work_order_id']);
        }

        if (!empty($filters['message_type'])) {
            $query->whereHas('messages', function (Builder $builder) use ($filters) {
                $builder->where('message_type', $filters['message_type']);
            });
        }

        if (!empty($filters['channel'])) {
            $query->whereHas('messages', function (Builder $builder) use ($filters) {
                $builder->where('channel', $filters['channel']);
            });
        }

        if (!empty($filters['has_attachments'])) {
            $query->whereHas('messages', function (Builder $builder) {
                $builder->whereHas('messageAttachments')
                    ->orWhereHas('attachments');
            });
        }

        if (!empty($filters['unread'])) {
            $query->whereHas('participants', function (Builder $builder) use ($userId) {
                $builder->where('user_id', $userId)->whereNull('last_read_at');
            });
        }
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
        if (!$lastMessage) {
            return false;
        }

        $participant = $thread->participants->firstWhere('user_id', $userId);
        $lastReadAt = $participant?->last_read_at;

        if (!$lastReadAt) {
            return true;
        }

        return $lastMessage->created_at?->gt($lastReadAt) ?? false;
    }

    private function unreadThreadCount(int $userId): int
    {
        $threads = $this->threadBaseQuery($userId)
            ->with([
                'participants',
                'messages' => function ($builder) {
                    $builder->latest()->limit(1);
                }
            ])
            ->get();

        return $threads->filter(fn(MessageThread $thread) => $this->threadIsUnread($thread, $userId))->count();
    }

    private function folderCounts(int $userId): array
    {
        $base = MessageThreadParticipant::query()->where('user_id', $userId)->whereNull('deleted_at');

        return [
            'inbox' => (clone $base)->where('folder', 'inbox')->where('is_archived', false)->count(),
            'sent' => (clone $base)->where('folder', 'sent')->count(),
            'drafts' => (clone $base)->where('folder', 'drafts')->count(),
            'archived' => (clone $base)->where('is_archived', true)->count(),
            'starred' => (clone $base)->where('is_starred', true)->count(),
            'work_orders' => MessageThread::query()
                ->whereHas('participants', fn($builder) => $builder->where('user_id', $userId)->whereNull('deleted_at'))
                ->whereNotNull('work_order_id')
                ->count(),
        ];
    }

    private function currentThreadIds(): array
    {
        $userId = auth()->id();
        if (!$userId) {
            return [];
        }

        $threadsQuery = $this->threadBaseQuery($userId);
        $this->applyFolderFilter($threadsQuery, $userId, $this->activeFolder);
        $this->applyThreadSearch($threadsQuery, $this->threadSearch);
        $this->applyFilters($threadsQuery, $this->filters, $userId);

        return $threadsQuery->pluck('id')->all();
    }

    private function resolveRecipients(int $senderId): array
    {
        if (!empty($this->composer['broadcast_all'])) {
            return User::query()
                ->where('id', '!=', $senderId)
                ->pluck('id')
                ->all();
        }

        $to = Arr::wrap($this->composer['recipient_ids'] ?? []);
        $cc = Arr::wrap($this->composer['cc_ids'] ?? []);
        $bcc = Arr::wrap($this->composer['bcc_ids'] ?? []);

        return array_values(array_unique(array_filter(array_merge($to, $cc, $bcc))));
    }

    private function createParticipants(MessageThread $thread, int $senderId, array $recipientIds): void
    {
        MessageThreadParticipant::create([
            'thread_id' => $thread->id,
            'user_id' => $senderId,
            'folder' => 'sent',
            'last_read_at' => now(),
        ]);

        foreach ($recipientIds as $recipientId) {
            MessageThreadParticipant::firstOrCreate([
                'thread_id' => $thread->id,
                'user_id' => $recipientId,
            ], [
                'folder' => 'inbox',
            ]);
        }
    }

    private function createMessageParticipants(Message $message, array $recipientMap, string $channel): void
    {
        $toIds = array_values(array_unique($recipientMap['to'] ?? []));
        $ccIds = array_values(array_unique(array_diff($recipientMap['cc'] ?? [], $toIds)));
        $bccIds = array_values(array_unique(array_diff($recipientMap['bcc'] ?? [], $toIds, $ccIds)));

        $finalMap = [
            'to' => $toIds,
            'cc' => $ccIds,
            'bcc' => $bccIds,
        ];

        foreach (['to', 'cc', 'bcc'] as $role) {
            foreach ($finalMap[$role] ?? [] as $recipientId) {
                MessageParticipant::create([
                    'message_id' => $message->id,
                    'user_id' => $recipientId,
                    'role' => $role,
                    'delivery_status' => 'pending',
                    'channel' => $channel,
                ]);
            }
        }
    }

    private function storeMessageAttachments(Message $message, int $userId): void
    {
        if (empty($this->composerAttachments)) {
            return;
        }

        foreach ($this->composerAttachments as $upload) {
            $path = $upload->store('messages/' . $message->thread_id, 'public');

            MessageAttachment::create([
                'message_id' => $message->id,
                'file_name' => $upload->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $upload->getMimeType(),
                'file_size' => $upload->getSize(),
                'uploaded_by_user_id' => $userId,
            ]);
        }
    }

    private function renderTemplate(?string $template, array $context): ?string
    {
        if ($template === null) {
            return null;
        }

        foreach ($context as $key => $value) {
            $template = str_replace('{{' . $key . '}}', $value, $template);
        }

        return $template;
    }

    private function mergeContext(): array
    {
        $user = auth()->user();
        $workOrder = $this->composer['work_order_id']
            ? WorkOrder::find($this->composer['work_order_id'])
            : null;

        return [
            'technician_name' => $user?->name ?? '',
            'technician_phone' => $user?->phone ?? '',
            'customer_name' => $workOrder?->organization?->name ?? '',
            'appointment_time' => $workOrder?->scheduled_start_at?->format('M d, g:i A') ?? '',
            'work_order_id' => $workOrder?->id ?? '',
        ];
    }

    private function loadPreferences(): void
    {
        $user = auth()->user();
        if (!$user) {
            $this->notificationPreferences = [];
            return;
        }

        $defaults = [
            'in_app' => [
                'frequency' => 'immediate',
                'message_types' => ['direct', 'group', 'system', 'work_order', 'broadcast'],
                'vip_senders' => '',
                'quiet_hours_start' => null,
                'quiet_hours_end' => null,
                'is_enabled' => true,
            ],
            'email' => [
                'frequency' => 'hourly',
                'message_types' => ['direct', 'group', 'work_order', 'broadcast'],
                'vip_senders' => '',
                'quiet_hours_start' => null,
                'quiet_hours_end' => null,
                'is_enabled' => true,
            ],
            'sms' => [
                'frequency' => 'immediate',
                'message_types' => ['urgent', 'work_order'],
                'vip_senders' => '',
                'quiet_hours_start' => null,
                'quiet_hours_end' => null,
                'is_enabled' => false,
            ],
            'push' => [
                'frequency' => 'immediate',
                'message_types' => ['direct', 'group', 'work_order', 'broadcast'],
                'vip_senders' => '',
                'quiet_hours_start' => null,
                'quiet_hours_end' => null,
                'is_enabled' => true,
            ],
        ];

        $existing = NotificationPreference::query()
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('channel');

        $this->notificationPreferences = collect($defaults)->map(function ($default, $channel) use ($existing) {
            $record = $existing->get($channel);
            if (!$record) {
                return $default;
            }

            return array_merge($default, [
                'frequency' => $record->frequency,
                'message_types' => $record->message_types ?? $default['message_types'],
                'vip_senders' => implode(', ', $record->vip_senders ?? []),
                'quiet_hours_start' => $record->quiet_hours_start?->format('H:i'),
                'quiet_hours_end' => $record->quiet_hours_end?->format('H:i'),
                'is_enabled' => $record->is_enabled,
            ]);
        })->toArray();
    }

    private function checkRateLimit(int $userId, string $action): bool
    {
        $channel = $this->composer['channel'] ?? 'in_app';
        $policy = app(MessagingPolicyService::class);

        if (!$policy->checkRateLimit($userId, $channel, $action)) {
            $this->addError('composer.message', 'You are sending messages too quickly. Please wait a moment.');
            return false;
        }

        return true;
    }
}
