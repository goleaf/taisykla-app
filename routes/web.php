<?php

use App\Livewire\Billing\Index as BillingIndex;
use App\Livewire\Clients\Index as ClientsIndex;
use App\Livewire\Dashboard;
use App\Livewire\Equipment\Index as EquipmentIndex;
use App\Livewire\Inventory\Index as InventoryIndex;
use App\Livewire\KnowledgeBase\Index as KnowledgeBaseIndex;
use App\Livewire\Messages\Index as MessagesIndex;
use App\Livewire\Reports\Index as ReportsIndex;
use App\Livewire\Schedule\Index as ScheduleIndex;
use App\Livewire\Settings\Index as SettingsIndex;
use App\Livewire\SupportTickets\Index as SupportTicketsIndex;
use App\Livewire\WorkOrders\Index as WorkOrdersIndex;
use App\Livewire\WorkOrders\Show as WorkOrdersShow;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', Dashboard::class)->name('dashboard');
    Route::get('work-orders', WorkOrdersIndex::class)->name('work-orders.index');
    Route::get('work-orders/{workOrder}', WorkOrdersShow::class)->name('work-orders.show');
    Route::get('equipment', EquipmentIndex::class)->name('equipment.index');
    Route::get('clients', ClientsIndex::class)->name('clients.index');
    Route::get('schedule', ScheduleIndex::class)->name('schedule.index');
    Route::get('inventory', InventoryIndex::class)->name('inventory.index');
    Route::get('messages', MessagesIndex::class)->name('messages.index');
    Route::get('reports', ReportsIndex::class)->name('reports.index');
    Route::get('billing', BillingIndex::class)->name('billing.index');
    Route::get('knowledge-base', KnowledgeBaseIndex::class)->name('knowledge-base.index');
    Route::get('support-tickets', SupportTicketsIndex::class)->name('support-tickets.index');
    Route::get('settings', SettingsIndex::class)->name('settings.index');

    Route::view('profile', 'profile')->name('profile');
});

require __DIR__.'/auth.php';
