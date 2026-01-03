<?php

use App\Livewire\Billing\Index as BillingIndex;
use App\Livewire\Clients\Index as ClientsIndex;
use App\Livewire\Dashboard;
use App\Livewire\Equipment\Index as EquipmentIndex;
use App\Livewire\Equipment\Show as EquipmentShow;
use App\Livewire\Inventory\Index as InventoryIndex;
use App\Livewire\KnowledgeBase\Index as KnowledgeBaseIndex;
use App\Livewire\KnowledgeBase\Show as KnowledgeBaseShow;
use App\Livewire\Messages\Index as MessagesIndex;
use App\Livewire\Reports\Index as ReportsIndex;
use App\Livewire\Schedule\Index as ScheduleIndex;
use App\Livewire\Settings\Index as SettingsIndex;
use App\Livewire\SupportTickets\Index as SupportTicketsIndex;
use App\Livewire\WorkOrders\Index as WorkOrdersIndex;
use App\Livewire\WorkOrders\Show as WorkOrdersShow;
use App\Http\Middleware\EnsureAccountSetup;
use App\Http\Controllers\ReportExportController;
use App\Support\PermissionCatalog;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::middleware(['auth', EnsureAccountSetup::class])->group(function () {
    Route::get('dashboard', Dashboard::class)
        ->middleware('can:' . PermissionCatalog::DASHBOARD_VIEW)
        ->name('dashboard');
    Route::get('work-orders', WorkOrdersIndex::class)
        ->middleware('can:' . PermissionCatalog::WORK_ORDERS_VIEW)
        ->name('work-orders.index');
    Route::get('work-orders/{workOrder}', WorkOrdersShow::class)
        ->middleware('can:' . PermissionCatalog::WORK_ORDERS_VIEW)
        ->name('work-orders.show');
    Route::get('equipment', EquipmentIndex::class)
        ->middleware('can:' . PermissionCatalog::EQUIPMENT_VIEW)
        ->name('equipment.index');
    Route::get('equipment/{equipment}', EquipmentShow::class)
        ->middleware('can:' . PermissionCatalog::EQUIPMENT_VIEW)
        ->name('equipment.show');
    Route::get('clients', ClientsIndex::class)
        ->middleware('can:' . PermissionCatalog::CLIENTS_VIEW)
        ->name('clients.index');
    Route::get('schedule', ScheduleIndex::class)
        ->middleware('can:' . PermissionCatalog::SCHEDULE_VIEW)
        ->name('schedule.index');
    Route::get('inventory', InventoryIndex::class)
        ->middleware('can:' . PermissionCatalog::INVENTORY_VIEW)
        ->name('inventory.index');
    Route::get('messages', MessagesIndex::class)
        ->middleware('can:' . PermissionCatalog::MESSAGES_VIEW)
        ->name('messages.index');
    Route::get('reports', ReportsIndex::class)
        ->middleware('can:' . PermissionCatalog::REPORTS_VIEW)
        ->name('reports.index');
    Route::get('reports/{report}/export', ReportExportController::class)
        ->middleware('can:' . PermissionCatalog::REPORTS_EXPORT)
        ->name('reports.export');
    Route::get('billing', BillingIndex::class)
        ->middleware('can:' . PermissionCatalog::BILLING_VIEW)
        ->name('billing.index');
    Route::get('knowledge-base', KnowledgeBaseIndex::class)
        ->middleware('can:' . PermissionCatalog::KNOWLEDGE_BASE_VIEW)
        ->name('knowledge-base.index');
    Route::get('knowledge-base/{article:slug}', KnowledgeBaseShow::class)
        ->middleware('can:' . PermissionCatalog::KNOWLEDGE_BASE_VIEW)
        ->name('knowledge-base.show');
    Route::get('support-tickets', SupportTicketsIndex::class)
        ->middleware('can:' . PermissionCatalog::SUPPORT_VIEW)
        ->name('support-tickets.index');
    Route::get('settings', SettingsIndex::class)
        ->middleware('can:' . PermissionCatalog::SETTINGS_VIEW)
        ->name('settings.index');

    Route::view('profile', 'profile')->name('profile');
});

require __DIR__.'/auth.php';
