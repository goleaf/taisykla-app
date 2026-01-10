<?php

use App\Support\PermissionCatalog;
use Livewire\Volt\Component;

new class extends Component {
    public function with(): array
    {
        $user = auth()->user();

        $mainLinks = [
            [
                'label' => 'Dashboard',
                'route' => 'dashboard',
                'icon' => 'home',
                'show' => $user->can(PermissionCatalog::DASHBOARD_VIEW),
            ],
            [
                'label' => 'Work Orders',
                'route' => 'work-orders.index',
                'icon' => 'clipboard-list',
                'show' => $user->can(PermissionCatalog::WORK_ORDERS_VIEW),
            ],
            [
                'label' => 'Equipment',
                'route' => 'equipment.index',
                'icon' => 'server',
                'show' => $user->can(PermissionCatalog::EQUIPMENT_VIEW),
            ],
            [
                'label' => 'Schedule',
                'route' => 'schedule.index',
                'icon' => 'calendar',
                'show' => $user->can(PermissionCatalog::SCHEDULE_VIEW),
            ],
        ];

        $operationsLinks = [
            [
                'label' => 'Inventory',
                'route' => 'inventory.index',
                'icon' => 'cube',
                'show' => $user->can(PermissionCatalog::INVENTORY_VIEW),
            ],
            [
                'label' => 'Clients',
                'route' => 'clients.index',
                'icon' => 'users',
                'show' => $user->can(PermissionCatalog::CLIENTS_VIEW),
            ],
            [
                'label' => 'Messages',
                'route' => 'messages.index',
                'icon' => 'chat',
                'show' => $user->can(PermissionCatalog::MESSAGES_VIEW),
            ],
        ];

        $insightsLinks = [
            [
                'label' => 'Reports',
                'route' => 'reports.index',
                'icon' => 'chart-bar',
                'show' => $user->can(PermissionCatalog::REPORTS_VIEW),
            ],
            [
                'label' => 'Billing',
                'route' => 'billing.index',
                'icon' => 'credit-card',
                'show' => $user->can(PermissionCatalog::BILLING_VIEW),
            ],
        ];

        $resourcesLinks = [
            [
                'label' => 'Knowledge Base',
                'route' => 'knowledge-base.index',
                'icon' => 'book-open',
                'show' => $user->can(PermissionCatalog::KNOWLEDGE_BASE_VIEW),
            ],
            [
                'label' => 'Support Tickets',
                'route' => 'support-tickets.index',
                'icon' => 'ticket',
                'show' => $user->can(PermissionCatalog::SUPPORT_VIEW),
            ],
            [
                'label' => 'Settings',
                'route' => 'settings.index',
                'icon' => 'cog',
                'show' => $user->can(PermissionCatalog::SETTINGS_VIEW),
            ],
        ];

        return [
            'mainLinks' => array_filter($mainLinks, fn($l) => $l['show']),
            'operationsLinks' => array_filter($operationsLinks, fn($l) => $l['show']),
            'insightsLinks' => array_filter($insightsLinks, fn($l) => $l['show']),
            'resourcesLinks' => array_filter($resourcesLinks, fn($l) => $l['show']),
        ];
    }
}; ?>

<nav class="sidebar-nav">
    @if (count($mainLinks) > 0)
        <div class="nav-section">
            <div class="nav-section-title">Main</div>
            @foreach ($mainLinks as $link)
                <a href="{{ route($link['route']) }}" class="nav-link {{ request()->routeIs($link['route']) ? 'active' : '' }}"
                    wire:navigate>
                    @include('components.icons.' . $link['icon'])
                    <span>{{ $link['label'] }}</span>
                </a>
            @endforeach
        </div>
    @endif

    @if (count($operationsLinks) > 0)
        <div class="nav-section">
            <div class="nav-section-title">Operations</div>
            @foreach ($operationsLinks as $link)
                <a href="{{ route($link['route']) }}" class="nav-link {{ request()->routeIs($link['route']) ? 'active' : '' }}"
                    wire:navigate>
                    @include('components.icons.' . $link['icon'])
                    <span>{{ $link['label'] }}</span>
                </a>
            @endforeach
        </div>
    @endif

    @if (count($insightsLinks) > 0)
        <div class="nav-section">
            <div class="nav-section-title">Insights</div>
            @foreach ($insightsLinks as $link)
                <a href="{{ route($link['route']) }}" class="nav-link {{ request()->routeIs($link['route']) ? 'active' : '' }}"
                    wire:navigate>
                    @include('components.icons.' . $link['icon'])
                    <span>{{ $link['label'] }}</span>
                </a>
            @endforeach
        </div>
    @endif

    @if (count($resourcesLinks) > 0)
        <div class="nav-section">
            <div class="nav-section-title">Resources</div>
            @foreach ($resourcesLinks as $link)
                <a href="{{ route($link['route']) }}" class="nav-link {{ request()->routeIs($link['route']) ? 'active' : '' }}"
                    wire:navigate>
                    @include('components.icons.' . $link['icon'])
                    <span>{{ $link['label'] }}</span>
                </a>
            @endforeach
        </div>
    @endif
</nav>