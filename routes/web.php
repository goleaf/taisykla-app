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
    Route::get('technician', \App\Livewire\TechnicianDashboard::class)
        ->middleware('can:' . PermissionCatalog::DASHBOARD_VIEW)
        ->name('technician.dashboard');
    Route::get('dispatch', \App\Livewire\DispatchDashboard::class)
        ->middleware('can:' . PermissionCatalog::SCHEDULE_MANAGE)
        ->name('dispatch.dashboard');
    Route::get('admin', \App\Livewire\AdminDashboard::class)
        ->middleware('can:' . PermissionCatalog::SETTINGS_VIEW)
        ->name('admin.dashboard');
    Route::get('work-orders', WorkOrdersIndex::class)
        ->middleware('can:' . PermissionCatalog::WORK_ORDERS_VIEW)
        ->name('work-orders.index');
    Route::get('work-orders/create', \App\Livewire\WorkOrders\CreateWizard::class)
        ->middleware('can:' . PermissionCatalog::WORK_ORDERS_CREATE)
        ->name('work-orders.create');
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
    Route::get('billing/{invoice}/pay', \App\Livewire\Billing\Checkout::class)
        ->middleware('can:' . PermissionCatalog::BILLING_VIEW)
        ->name('billing.checkout');
    Route::get('billing/{invoice}/success', function (\App\Models\Invoice $invoice) {
        return view('billing.success', compact('invoice'));
    })->middleware('can:' . PermissionCatalog::BILLING_VIEW)->name('billing.payment-success');
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

    // Customer Portal
    Route::get('my-portal', \App\Livewire\Customer\Portal::class)
        ->middleware('can:' . PermissionCatalog::DASHBOARD_VIEW)
        ->name('customer.portal');

    // Live Technician Tracking (for customers)
    Route::get('track/{workOrder}', \App\Livewire\Customer\TrackTechnician::class)
        ->middleware('can:' . PermissionCatalog::WORK_ORDERS_VIEW)
        ->name('customer.track');

    // Mobile Field Technician Interface
    Route::get('mobile/field-tech', \App\Livewire\Mobile\FieldTechnician::class)
        ->middleware('can:' . PermissionCatalog::WORK_ORDERS_VIEW)
        ->name('mobile.field-tech');

    // Intelligent Scheduler
    Route::get('schedule/intelligent', \App\Livewire\IntelligentScheduler::class)
        ->middleware('can:' . PermissionCatalog::SCHEDULE_MANAGE)
        ->name('schedule.intelligent');

    Route::view('profile', 'profile')->name('profile');
});

require __DIR__ . '/auth.php';
