<?php

namespace App\Livewire\SupportTickets;

use App\Models\KnowledgeArticle;
use App\Models\Organization;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\WorkOrder;
use App\Support\PermissionCatalog;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public bool $showCreate = false;
    public array $new = [];
    public array $suggestedArticleIds = [];
    public string $search = '';
    public string $statusFilter = 'all';
    public string $priorityFilter = 'all';
    public string $organizationFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'all'],
        'priorityFilter' => ['except' => 'all'],
        'organizationFilter' => ['except' => ''],
    ];

    protected $paginationTheme = 'tailwind';

    public array $statusOptions = ['open', 'in_review', 'resolved'];

    public function mount(): void
    {
        abort_unless(auth()->user()?->can(PermissionCatalog::SUPPORT_VIEW), 403);

        $this->resetNew();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedPriorityFilter(): void
    {
        $this->resetPage();
    }

    public function updatedOrganizationFilter(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->statusFilter = 'all';
        $this->priorityFilter = 'all';
        $this->organizationFilter = '';
        $this->resetPage();
    }

    public function resetNew(): void
    {
        $this->new = [
            'organization_id' => null,
            'work_order_id' => null,
            'assigned_to_user_id' => null,
            'priority' => 'standard',
            'subject' => '',
            'description' => '',
        ];
        $this->suggestedArticleIds = [];
    }

    public function createTicket(): void
    {
        $user = auth()->user();
        if (! $user || ! $user->can(PermissionCatalog::SUPPORT_CREATE)) {
            return;
        }

        if ($user->isBusinessCustomer() && $user->organization_id) {
            $this->new['organization_id'] = $user->organization_id;
        }

        $this->validate([
            'new.organization_id' => ['nullable', 'exists:organizations,id'],
            'new.work_order_id' => ['nullable', 'exists:work_orders,id'],
            'new.assigned_to_user_id' => ['nullable', 'exists:users,id'],
            'new.priority' => ['required', 'string', 'max:50'],
            'new.subject' => ['required', 'string', 'max:255'],
            'new.description' => ['nullable', 'string'],
        ]);

        $ticket = SupportTicket::create([
            'organization_id' => $this->new['organization_id'],
            'work_order_id' => $this->new['work_order_id'],
            'submitted_by_user_id' => $user->id,
            'assigned_to_user_id' => $user->can(PermissionCatalog::SUPPORT_ASSIGN)
                ? $this->new['assigned_to_user_id']
                : null,
            'priority' => $this->new['priority'],
            'subject' => $this->new['subject'],
            'description' => $this->new['description'],
            'status' => 'open',
        ]);

        foreach ($this->suggestedArticleIds as $articleId) {
            $ticket->knowledgeArticles()->attach($articleId, [
                'context' => 'attached',
                'added_by_user_id' => $user->id,
            ]);
        }

        session()->flash('status', 'Support ticket created.');
        $this->resetNew();
        $this->showCreate = false;
    }

    public function updateStatus(int $ticketId, string $status): void
    {
        if (! auth()->user()?->can(PermissionCatalog::SUPPORT_MANAGE)) {
            return;
        }

        if (! in_array($status, $this->statusOptions, true)) {
            return;
        }

        $ticket = SupportTicket::findOrFail($ticketId);
        $ticket->update([
            'status' => $status,
            'resolved_at' => $status === 'resolved' ? now() : null,
        ]);
    }

    public function render()
    {
        $user = auth()->user();
        $query = $this->ticketQueryFor($user);

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        if ($this->priorityFilter !== 'all') {
            $query->where('priority', $this->priorityFilter);
        }

        if ($this->organizationFilter !== '' && $this->canManage) {
            $query->where('organization_id', $this->organizationFilter);
        }

        if ($this->search !== '') {
            $searchLike = '%' . $this->search . '%';
            $query->where(function ($q) use ($searchLike) {
                $q->where('subject', 'like', $searchLike)
                    ->orWhere('description', 'like', $searchLike);
            });
        }

        $tickets = $query->latest()->paginate(10);
        $organizations = $this->canManage
            ? Organization::orderBy('name')->get()
            : collect();
        $workOrders = $this->workOrderQueryFor($user)->orderBy('subject')->get();
        $supportManagers = User::permission(PermissionCatalog::SUPPORT_MANAGE)
            ->orderBy('name')
            ->get();

        return view('livewire.support-tickets.index', [
            'tickets' => $tickets,
            'organizations' => $organizations,
            'workOrders' => $workOrders,
            'supportManagers' => $supportManagers,
            'suggestedArticles' => $this->suggestedArticles,
            'user' => $user,
            'canCreate' => $this->canCreate,
            'canManage' => $this->canManage,
            'canAssign' => $this->canAssign,
        ]);
    }

    public function getCanCreateProperty(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->canCreateSupportTickets();
    }

    public function getCanManageProperty(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->canManageSupportTickets();
    }

    public function getCanAssignProperty(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->canAssignSupportTickets();
    }

    private function ticketQueryFor(User $user): Builder
    {
        $query = SupportTicket::query()->with(['organization', 'workOrder', 'assignedTo', 'knowledgeArticles']);

        if ($user->can(PermissionCatalog::SUPPORT_VIEW_ALL)) {
            return $query;
        }

        $hasScope = false;
        $query->where(function (Builder $builder) use ($user, &$hasScope) {
            if ($user->can(PermissionCatalog::SUPPORT_VIEW_ORG) && $user->organization_id) {
                $builder->orWhere('organization_id', $user->organization_id);
                $hasScope = true;
            }

            if ($user->can(PermissionCatalog::SUPPORT_VIEW_OWN)) {
                $builder->orWhere('submitted_by_user_id', $user->id);
                $hasScope = true;
            }
        });

        if (! $hasScope) {
            $query->whereRaw('1 = 0');
        }

        return $query;
    }

    private function workOrderQueryFor(User $user): Builder
    {
        $query = WorkOrder::query();

        if ($user->can(PermissionCatalog::WORK_ORDERS_VIEW_ALL)) {
            return $query;
        }

        $hasScope = false;
        $query->where(function (Builder $builder) use ($user, &$hasScope) {
            if ($user->can(PermissionCatalog::WORK_ORDERS_VIEW_ASSIGNED)) {
                $builder->orWhere('assigned_to_user_id', $user->id);
                $hasScope = true;
            }

            if ($user->can(PermissionCatalog::WORK_ORDERS_VIEW_ORG) && $user->organization_id) {
                $builder->orWhere('organization_id', $user->organization_id);
                $hasScope = true;
            }

            if ($user->can(PermissionCatalog::WORK_ORDERS_VIEW_OWN)) {
                $builder->orWhere('requested_by_user_id', $user->id);
                $hasScope = true;
            }
        });

        if (! $hasScope) {
            $query->whereRaw('1 = 0');
        }

        return $query;
    }

    public function getSuggestedArticlesProperty()
    {
        $user = auth()->user();
        $queryText = trim(($this->new['subject'] ?? '') . ' ' . ($this->new['description'] ?? ''));
        if ($queryText === '') {
            return collect();
        }

        $terms = collect(preg_split('/\s+/', $queryText))
            ->filter(fn ($term) => strlen($term) > 3)
            ->take(5);

        if ($terms->isEmpty()) {
            return collect();
        }

        $query = KnowledgeArticle::query()
            ->visibleTo($user)
            ->where('is_published', true);

        $query->where(function ($builder) use ($terms) {
            foreach ($terms as $term) {
                $like = '%' . $term . '%';
                $builder->orWhere('title', 'like', $like)
                    ->orWhere('summary', 'like', $like)
                    ->orWhere('content', 'like', $like);
            }
        });

        return $query->orderByDesc('view_count')->take(5)->get();
    }
}
