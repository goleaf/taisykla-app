<?php

use App\Livewire\Billing\Index as BillingIndex;
use App\Livewire\Clients\Index as ClientsIndex;
use App\Livewire\Dashboard;
use App\Livewire\Equipment\Index as EquipmentIndex;
use App\Livewire\Equipment\Show as EquipmentShow;
use App\Livewire\Inventory\Index as InventoryIndex;
use App\Livewire\KnowledgeBase\Home as KnowledgeBaseHome;
use App\Livewire\KnowledgeBase\ArticleList as KnowledgeBaseSearch;
use App\Livewire\KnowledgeBase\Show as KnowledgeBaseShow;
use App\Livewire\KnowledgeBase\Index as KnowledgeBaseManage; // Keep old index as admin/legacy for now, or just don't map it. Let's map it to /manage
use App\Livewire\Messages\Index as MessagesIndex;
use App\Livewire\Reports\Index as ReportsIndex;
use App\Livewire\Schedule\Index as ScheduleIndex;
use App\Livewire\Settings\Index as SettingsIndex;
use App\Livewire\SupportTickets\Index as SupportTicketsIndex;
use App\Livewire\WorkOrders\Index as WorkOrdersIndex;
use App\Livewire\WorkOrders\Show as WorkOrdersShow;
use App\Http\Middleware\EnsureAccountSetup;
use App\Http\Controllers\ReportExportController;
use App\Http\Controllers\ReportExportDownloadController;
use App\Http\Controllers\Messaging\InboundEmailController;
use App\Http\Controllers\Messaging\InboundSmsController;
use App\Support\PermissionCatalog;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');
Route::view('/pricing', 'pages.pricing')->name('pricing');
Route::view('/privacy', 'pages.privacy')->name('privacy');
Route::view('/terms', 'pages.terms')->name('terms');
Route::view('/support', 'pages.support')->name('support');

Route::post('webhooks/messages/email', InboundEmailController::class)
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
    ->middleware('throttle:60,1')
    ->name('webhooks.messages.email');
Route::post('webhooks/messages/sms', InboundSmsController::class)
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
    ->middleware('throttle:60,1')
    ->name('webhooks.messages.sms');

// Two-Factor Authentication Routes (verification during login)
Route::middleware(['web'])->group(function () {
    Route::get('2fa/verify', [\App\Http\Controllers\TwoFactorController::class, 'showVerifyForm'])
        ->name('2fa.verify');
    Route::post('2fa/verify', [\App\Http\Controllers\TwoFactorController::class, 'verify']);
});

// Two-Factor Authentication Routes (enable/disable - requires auth)
Route::middleware(['auth'])->prefix('2fa')->name('2fa.')->group(function () {
    Route::get('enable', [\App\Http\Controllers\TwoFactorController::class, 'showEnableForm'])->name('enable.show');
    Route::post('enable', [\App\Http\Controllers\TwoFactorController::class, 'enable'])->name('enable');
    Route::post('disable', [\App\Http\Controllers\TwoFactorController::class, 'disable'])->name('disable');
    Route::get('backup-codes', [\App\Http\Controllers\TwoFactorController::class, 'regenerateBackupCodes'])->name('backup-codes');
    Route::post('backup-codes', [\App\Http\Controllers\TwoFactorController::class, 'regenerateBackupCodes'])->name('backup-codes.regenerate');
});

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
    Route::get('equipment-topology', \App\Livewire\Equipment\Topology::class)
        ->middleware('can:' . PermissionCatalog::EQUIPMENT_VIEW)
        ->name('equipment.topology');
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
    Route::get('reports/exports/{export}', ReportExportDownloadController::class)
        ->middleware('can:' . PermissionCatalog::REPORTS_EXPORT)
        ->name('reports.exports.download');
    Route::get('billing', BillingIndex::class)
        ->middleware('can:' . PermissionCatalog::BILLING_VIEW)
        ->name('billing.index');
    Route::get('billing/{invoice}/pay', \App\Livewire\Billing\Checkout::class)
        ->middleware('can:' . PermissionCatalog::BILLING_VIEW)
        ->name('billing.checkout');
    Route::get('billing/{invoice}/success', function (\App\Models\Invoice $invoice) {
        return view('billing.success', compact('invoice'));
    })->middleware('can:' . PermissionCatalog::BILLING_VIEW)->name('billing.payment-success');
    Route::get('knowledge-base', KnowledgeBaseHome::class) // New Home Page
        ->middleware('can:' . PermissionCatalog::KNOWLEDGE_BASE_VIEW)
        ->name('knowledge-base.index');
    Route::get('knowledge-base/search', KnowledgeBaseSearch::class) // New Search/List Page
        ->middleware('can:' . PermissionCatalog::KNOWLEDGE_BASE_VIEW)
        ->name('knowledge-base.search');
    Route::get('knowledge-base/manage', KnowledgeBaseManage::class) // Old Index moved to Manage
        ->middleware('can:' . PermissionCatalog::KNOWLEDGE_BASE_MANAGE)
        ->name('knowledge-base.manage');
    Route::get('knowledge-base/{article:slug}', KnowledgeBaseShow::class)
        ->middleware('can:' . PermissionCatalog::KNOWLEDGE_BASE_VIEW)
        ->name('knowledge-base.show');
    Route::get('support-tickets', SupportTicketsIndex::class)
        ->middleware('can:' . PermissionCatalog::SUPPORT_VIEW)
        ->name('support-tickets.index');
    Route::get('settings', SettingsIndex::class)
        ->middleware('can:' . PermissionCatalog::SETTINGS_VIEW)
        ->name('settings.index');

    // Admin Permission Management
    Route::prefix('admin/permissions')
        ->name('admin.permissions.')
        ->middleware('can:' . PermissionCatalog::USERS_MANAGE)
        ->controller(\App\Http\Controllers\Admin\PermissionManagementController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::put('/roles/{role}', 'updateRolePermissions')->name('roles.update');
            Route::get('/users', 'users')->name('users');
            Route::put('/users/{user}/roles', 'updateUserRoles')->name('users.roles.update');
            Route::post('/sync', 'syncPermissions')->name('sync');
        });

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

    // Service Requests
    Route::controller(\App\Http\Controllers\ServiceRequestController::class)->group(function () {
        Route::post('service-requests/{id}/assign', 'assign')->name('service-requests.assign');
        Route::post('service-requests/{id}/status', 'updateStatus')->name('service-requests.update-status');
        Route::post('service-requests/{id}/approve', 'approve')->name('service-requests.approve');
        Route::post('service-requests/{id}/reject', 'reject')->name('service-requests.reject');
    });
    Route::resource('service-requests', \App\Http\Controllers\ServiceRequestController::class);

    // Service Request Scheduling
    Route::controller(\App\Http\Controllers\ServiceRequestSchedulingController::class)->group(function () {
        Route::get('service-requests/{serviceRequest}/schedule', 'showAvailability')->name('service-requests.schedule.availability');
        Route::post('service-requests/{serviceRequest}/schedule', 'schedule')->name('service-requests.schedule.store');
        Route::get('service-requests/{serviceRequest}/reschedule', 'showReschedule')->name('service-requests.reschedule.show');
        Route::put('service-requests/{serviceRequest}/reschedule', 'reschedule')->name('service-requests.reschedule.store');
    });
    Route::prefix('admin/technicians/{technician}/schedule')
        ->name('admin.technicians.schedule.')
        ->middleware('can:' . PermissionCatalog::SCHEDULE_MANAGE)
        ->controller(\App\Http\Controllers\Admin\TechnicianScheduleController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{schedule}/edit', 'edit')->name('edit');
            Route::put('/{schedule}', 'update')->name('update');
            Route::delete('/{schedule}', 'destroy')->name('destroy');
            Route::post('/default-hours', 'setDefaultHours')->name('default-hours');
            Route::post('/block-time', 'blockTimeOff')->name('block-time');
        });

    // Technician Calendar
    Route::get('technicians/{technician}/calendar', [\App\Http\Controllers\TechnicianCalendarController::class, 'show'])
        ->middleware('can:' . PermissionCatalog::SCHEDULE_VIEW)
        ->name('technicians.calendar');
    Route::get('technicians/{technician}/calendar/events', [\App\Http\Controllers\TechnicianCalendarController::class, 'events'])
        ->middleware('can:' . PermissionCatalog::SCHEDULE_VIEW)
        ->name('technicians.calendar.events');

    Route::view('profile', 'profile')->name('profile');
});

require __DIR__ . '/auth.php';
