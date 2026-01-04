<?php

use App\Livewire\Actions\Logout;
use App\Models\User;
use App\Models\WorkOrder;
use App\Support\PermissionCatalog;
use App\Support\RoleCatalog;
use Livewire\Volt\Component;

new class extends Component
{
    public string $trackTicket = '';
    public ?string $trackError = null;

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }

    public function trackRequest(): void
    {
        $this->trackError = null;
        $ticket = trim($this->trackTicket);

        if ($ticket === '') {
            return;
        }

        if (! ctype_digit($ticket)) {
            $this->trackError = 'Enter a valid ticket number.';
            return;
        }

        $workOrder = WorkOrder::find((int) $ticket);
        if (! $workOrder) {
            $this->trackError = 'Ticket not found.';
            return;
        }

        $user = auth()->user();
        if (! $this->canViewWorkOrder($user, $workOrder)) {
            $this->trackError = 'Access denied.';
            return;
        }

        $this->redirectRoute('work-orders.show', ['workOrder' => $workOrder->id], navigate: true);
    }

    private function canViewWorkOrder(?User $user, WorkOrder $workOrder): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->canViewAllWorkOrders()) {
            return true;
        }

        if ($workOrder->requested_by_user_id === $user->id) {
            return true;
        }

        if ($user->hasRole(RoleCatalog::TECHNICIAN)) {
            return $workOrder->assigned_to_user_id === $user->id;
        }

        if ($user->isBusinessCustomer()) {
            return $user->organization_id && $workOrder->organization_id === $user->organization_id;
        }

        return false;
    }
}; ?>

<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" wire:navigate>
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                @php
                    $user = auth()->user();
                    $links = [
                        ['label' => 'Dashboard', 'route' => 'dashboard', 'show' => $user->can(PermissionCatalog::DASHBOARD_VIEW)],
                        ['label' => 'Work Orders', 'route' => 'work-orders.index', 'show' => $user->can(PermissionCatalog::WORK_ORDERS_VIEW)],
                        ['label' => 'Equipment', 'route' => 'equipment.index', 'show' => $user->can(PermissionCatalog::EQUIPMENT_VIEW)],
                        ['label' => 'Schedule', 'route' => 'schedule.index', 'show' => $user->can(PermissionCatalog::SCHEDULE_VIEW)],
                        ['label' => 'Inventory', 'route' => 'inventory.index', 'show' => $user->can(PermissionCatalog::INVENTORY_VIEW)],
                        ['label' => 'Clients', 'route' => 'clients.index', 'show' => $user->can(PermissionCatalog::CLIENTS_VIEW)],
                        ['label' => 'Messages', 'route' => 'messages.index', 'show' => $user->can(PermissionCatalog::MESSAGES_VIEW)],
                        ['label' => 'Reports', 'route' => 'reports.index', 'show' => $user->can(PermissionCatalog::REPORTS_VIEW)],
                        ['label' => 'Billing', 'route' => 'billing.index', 'show' => $user->can(PermissionCatalog::BILLING_VIEW)],
                        ['label' => 'Knowledge Base', 'route' => 'knowledge-base.index', 'show' => $user->can(PermissionCatalog::KNOWLEDGE_BASE_VIEW)],
                        ['label' => 'Support', 'route' => 'support-tickets.index', 'show' => $user->can(PermissionCatalog::SUPPORT_VIEW)],
                        ['label' => 'Settings', 'route' => 'settings.index', 'show' => $user->can(PermissionCatalog::SETTINGS_VIEW)],
                    ];
                @endphp

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    @foreach ($links as $link)
                        @if ($link['show'])
                            <x-nav-link :href="route($link['route'])" :active="request()->routeIs($link['route'])" wire:navigate>
                                {{ __($link['label']) }}
                            </x-nav-link>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 gap-3">
                <livewire:global-search />
                
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile')" wire:navigate>
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <button wire:click="logout" class="w-full text-start">
                            <x-dropdown-link>
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </button>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <div class="px-4 pb-3">
                <livewire:global-search />
            </div>
            @foreach ($links as $link)
                @if ($link['show'])
                    <x-responsive-nav-link :href="route($link['route'])" :active="request()->routeIs($link['route'])" wire:navigate>
                        {{ __($link['label']) }}
                    </x-responsive-nav-link>
                @endif
            @endforeach
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800" x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>
                <div class="font-medium text-sm text-gray-500">{{ auth()->user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile')" wire:navigate>
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <button wire:click="logout" class="w-full text-start">
                    <x-responsive-nav-link>
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </button>
            </div>
        </div>
    </div>
</nav>
