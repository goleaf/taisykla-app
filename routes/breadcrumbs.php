<?php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;
use App\Models\ServiceRequest;
use App\Models\Equipment;
use App\Models\WorkOrder;
use App\Models\Invoice;
use App\Models\KnowledgeArticle;

// Home
Breadcrumbs::for('home', function (BreadcrumbTrail $trail) {
    $trail->push('Home', route('dashboard'));
});

// Dashboard
Breadcrumbs::for('dashboard', function (BreadcrumbTrail $trail) {
    $trail->push('Dashboard', route('dashboard'));
});

// Technician Dashboard
Breadcrumbs::for('technician.dashboard', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Technician Dashboard', route('technician.dashboard'));
});

// Dispatch Dashboard
Breadcrumbs::for('dispatch.dashboard', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Dispatch', route('dispatch.dashboard'));
});

// Admin Dashboard
Breadcrumbs::for('admin.dashboard', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Admin', route('admin.dashboard'));
});

// --- Service Requests ---
Breadcrumbs::for('service-requests.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Service Requests', route('service-requests.index'));
});

Breadcrumbs::for('service-requests.create', function (BreadcrumbTrail $trail) {
    $trail->parent('service-requests.index');
    $trail->push('Create', route('service-requests.create'));
});

Breadcrumbs::for('service-requests.show', function (BreadcrumbTrail $trail, ServiceRequest $serviceRequest) {
    $trail->parent('service-requests.index');
    $trail->push("Request #{$serviceRequest->id}", route('service-requests.show', $serviceRequest));
});

Breadcrumbs::for('service-requests.edit', function (BreadcrumbTrail $trail, ServiceRequest $serviceRequest) {
    $trail->parent('service-requests.show', $serviceRequest);
    $trail->push('Edit', route('service-requests.edit', $serviceRequest));
});

// --- Work Orders ---
Breadcrumbs::for('work-orders.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Work Orders', route('work-orders.index'));
});

Breadcrumbs::for('work-orders.create', function (BreadcrumbTrail $trail) {
    $trail->parent('work-orders.index');
    $trail->push('Create', route('work-orders.create'));
});

Breadcrumbs::for('work-orders.show', function (BreadcrumbTrail $trail, WorkOrder $workOrder) {
    $trail->parent('work-orders.index');
    $title = $workOrder->subject ? "WO #{$workOrder->id}: " . \Illuminate\Support\Str::limit($workOrder->subject, 30) : "WO #{$workOrder->id}";
    $trail->push($title, route('work-orders.show', $workOrder));
});

// --- Equipment ---
Breadcrumbs::for('equipment.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Equipment', route('equipment.index'));
});

Breadcrumbs::for('equipment.show', function (BreadcrumbTrail $trail, Equipment $equipment) {
    $trail->parent('equipment.index');
    $title = $equipment->name ?? ($equipment->manufacturer . ' ' . $equipment->model) ?? $equipment->serial_number;
    $trail->push($title, route('equipment.show', $equipment));
});

Breadcrumbs::for('equipment.topology', function (BreadcrumbTrail $trail) {
    $trail->parent('equipment.index');
    $trail->push('Topology', route('equipment.topology'));
});

// --- Clients/Customers ---
Breadcrumbs::for('clients.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Clients', route('clients.index'));
});

// --- Schedule ---
Breadcrumbs::for('schedule.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Schedule', route('schedule.index'));
});

Breadcrumbs::for('schedule.intelligent', function (BreadcrumbTrail $trail) {
    $trail->parent('schedule.index');
    $trail->push('Intelligent Scheduler', route('schedule.intelligent'));
});

// --- Inventory ---
Breadcrumbs::for('inventory.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Inventory', route('inventory.index'));
});

// --- Messages ---
Breadcrumbs::for('messages.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Messages', route('messages.index'));
});

// --- Reports ---
Breadcrumbs::for('reports.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Reports', route('reports.index'));
});

// --- Billing ---
Breadcrumbs::for('billing.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Billing', route('billing.index'));
});

Breadcrumbs::for('billing.checkout', function (BreadcrumbTrail $trail, Invoice $invoice) {
    $trail->parent('billing.index');
    $trail->push("Invoice #{$invoice->number}", route('billing.checkout', $invoice));
    $trail->push('Checkout');
});

Breadcrumbs::for('billing.payment-success', function (BreadcrumbTrail $trail, Invoice $invoice) {
    $trail->parent('billing.index');
    $trail->push("Invoice #{$invoice->number}", route('billing.checkout', $invoice));
    $trail->push('Payment Success');
});

// --- Knowledge Base ---
Breadcrumbs::for('knowledge-base.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Knowledge Base', route('knowledge-base.index'));
});

Breadcrumbs::for('knowledge-base.search', function (BreadcrumbTrail $trail) {
    $trail->parent('knowledge-base.index');
    $trail->push('Search', route('knowledge-base.search'));
});

Breadcrumbs::for('knowledge-base.manage', function (BreadcrumbTrail $trail) {
    $trail->parent('knowledge-base.index');
    $trail->push('Manage', route('knowledge-base.manage'));
});

Breadcrumbs::for('knowledge-base.show', function (BreadcrumbTrail $trail, KnowledgeArticle $article) {
    $trail->parent('knowledge-base.index');
    $trail->push($article->title, route('knowledge-base.show', $article));
});

// --- Support Tickets ---
Breadcrumbs::for('support-tickets.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Support Tickets', route('support-tickets.index'));
});

// --- Settings ---
Breadcrumbs::for('settings.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Settings', route('settings.index'));
});

// --- Customer Portal ---
Breadcrumbs::for('customer.portal', function (BreadcrumbTrail $trail) {
    $trail->push('Customer Portal', route('customer.portal'));
});

Breadcrumbs::for('customer.track', function (BreadcrumbTrail $trail, WorkOrder $workOrder) {
    $trail->parent('customer.portal');
    $trail->push('Track Technician', route('customer.track', $workOrder));
});

// --- Mobile ---
Breadcrumbs::for('mobile.field-tech', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Field Technician', route('mobile.field-tech'));
});

// --- Profile ---
Breadcrumbs::for('profile', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Profile', route('profile'));
});
