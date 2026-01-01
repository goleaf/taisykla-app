<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\CommunicationTemplate;
use App\Models\Equipment;
use App\Models\InventoryItem;
use App\Models\InventoryLocation;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\KnowledgeArticle;
use App\Models\Message;
use App\Models\MessageThread;
use App\Models\MessageThreadParticipant;
use App\Models\Organization;
use App\Models\Part;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\ServiceAgreement;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\Warranty;
use App\Models\WarrantyClaim;
use App\Models\WorkOrder;
use App\Models\WorkOrderCategory;
use App\Models\WorkOrderFeedback;
use App\Models\WorkOrderPart;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $roles = [
            'admin',
            'dispatch',
            'technician',
            'support',
            'client',
            'guest',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        $serviceAgreement = ServiceAgreement::create([
            'name' => 'Comprehensive Support',
            'agreement_type' => 'comprehensive',
            'response_time_minutes' => 120,
            'resolution_time_minutes' => 480,
            'included_visits_per_month' => 2,
            'monthly_fee' => 299.00,
            'includes_parts' => true,
            'includes_labor' => true,
            'billing_terms' => 'Net 30',
            'coverage_details' => 'Unlimited service calls, parts and labor included.',
            'is_active' => true,
        ]);

        $organization = Organization::create([
            'name' => 'Acme Corporation',
            'type' => 'business',
            'status' => 'active',
            'primary_contact_name' => 'Jamie Client',
            'primary_contact_email' => 'client@example.com',
            'primary_contact_phone' => '+1 555-0100',
            'billing_email' => 'billing@example.com',
            'billing_address' => '100 Main Street, Suite 200',
            'service_agreement_id' => $serviceAgreement->id,
            'notes' => 'Priority business account.',
        ]);

        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $admin->assignRole('admin');

        $dispatch = User::create([
            'name' => 'Dispatch Manager',
            'email' => 'dispatch@example.com',
            'password' => Hash::make('password'),
            'job_title' => 'Dispatch Manager',
            'is_active' => true,
        ]);
        $dispatch->assignRole('dispatch');

        $technician = User::create([
            'name' => 'Alex Technician',
            'email' => 'tech@example.com',
            'password' => Hash::make('password'),
            'job_title' => 'Field Technician',
            'phone' => '+1 555-0123',
            'is_active' => true,
        ]);
        $technician->assignRole('technician');

        $support = User::create([
            'name' => 'Support Manager',
            'email' => 'support@example.com',
            'password' => Hash::make('password'),
            'job_title' => 'Support Manager',
            'is_active' => true,
        ]);
        $support->assignRole('support');

        $clientOwner = User::create([
            'name' => 'Jamie Client',
            'email' => 'client@example.com',
            'password' => Hash::make('password'),
            'organization_id' => $organization->id,
            'job_title' => 'Office Manager',
            'phone' => '+1 555-0199',
            'is_active' => true,
        ]);
        $clientOwner->assignRole('client');

        $categories = [
            ['name' => 'Hardware Repair', 'default_estimated_minutes' => 180],
            ['name' => 'Software Installation', 'default_estimated_minutes' => 90],
            ['name' => 'Networking', 'default_estimated_minutes' => 120],
            ['name' => 'Printer Setup', 'default_estimated_minutes' => 60],
        ];

        foreach ($categories as $category) {
            WorkOrderCategory::create($category);
        }

        $equipment = Equipment::create([
            'organization_id' => $organization->id,
            'name' => 'Conference Room Printer',
            'type' => 'Printer',
            'manufacturer' => 'HP',
            'model' => 'LaserJet Pro',
            'serial_number' => 'HP-PRN-002',
            'status' => 'needs_attention',
            'location_name' => 'Conference Room',
            'location_address' => '100 Main Street, Suite 200',
            'assigned_user_id' => $clientOwner->id,
            'notes' => 'Paper jams frequently.',
        ]);

        $warranty = Warranty::create([
            'equipment_id' => $equipment->id,
            'provider_name' => 'HP Care',
            'coverage_type' => 'labor_included',
            'coverage_details' => 'Covers parts and labor for mechanical failure.',
            'starts_at' => now()->subYear()->toDateString(),
            'ends_at' => now()->addYear()->toDateString(),
        ]);

        $workOrder = WorkOrder::create([
            'organization_id' => $organization->id,
            'equipment_id' => $equipment->id,
            'requested_by_user_id' => $clientOwner->id,
            'assigned_to_user_id' => $technician->id,
            'category_id' => WorkOrderCategory::first()->id,
            'priority' => 'high',
            'status' => 'assigned',
            'subject' => 'Printer keeps jamming',
            'description' => 'Paper jams every time we print a multi-page job.',
            'location_name' => 'Conference Room',
            'location_address' => '100 Main Street, Suite 200',
            'requested_at' => now()->subHours(2),
            'scheduled_start_at' => now()->addHours(1),
            'scheduled_end_at' => now()->addHours(2),
            'time_window' => 'Morning',
            'estimated_minutes' => 60,
            'total_cost' => 0,
            'is_warranty' => true,
        ]);

        Appointment::create([
            'work_order_id' => $workOrder->id,
            'assigned_to_user_id' => $technician->id,
            'scheduled_start_at' => now()->addHours(1),
            'scheduled_end_at' => now()->addHours(2),
            'time_window' => '08:00-12:00',
            'status' => 'scheduled',
        ]);

        $part = Part::create([
            'sku' => 'PRN-ROLLER-01',
            'name' => 'Printer Roller Kit',
            'description' => 'Replacement roller kit for LaserJet.',
            'unit_cost' => 25.00,
            'unit_price' => 55.00,
            'vendor' => 'HP',
            'reorder_level' => 5,
        ]);

        $location = InventoryLocation::create([
            'name' => 'Main Warehouse',
            'address' => '200 Supply Road',
        ]);

        InventoryItem::create([
            'part_id' => $part->id,
            'location_id' => $location->id,
            'quantity' => 12,
            'reserved_quantity' => 1,
        ]);

        WorkOrderPart::create([
            'work_order_id' => $workOrder->id,
            'part_id' => $part->id,
            'quantity' => 1,
            'unit_cost' => 25.00,
            'unit_price' => 55.00,
        ]);

        $quote = Quote::create([
            'work_order_id' => $workOrder->id,
            'organization_id' => $organization->id,
            'status' => 'approved',
            'subtotal' => 55.00,
            'tax' => 0,
            'total' => 55.00,
            'approved_at' => now(),
        ]);

        QuoteItem::create([
            'quote_id' => $quote->id,
            'description' => 'Printer Roller Kit',
            'quantity' => 1,
            'unit_price' => 55.00,
            'total' => 55.00,
        ]);

        $invoice = Invoice::create([
            'work_order_id' => $workOrder->id,
            'organization_id' => $organization->id,
            'status' => 'sent',
            'subtotal' => 55.00,
            'tax' => 0,
            'total' => 55.00,
            'due_date' => now()->addDays(30)->toDateString(),
            'sent_at' => now(),
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'description' => 'Printer Roller Kit',
            'quantity' => 1,
            'unit_price' => 55.00,
            'total' => 55.00,
        ]);

        WarrantyClaim::create([
            'work_order_id' => $workOrder->id,
            'equipment_id' => $equipment->id,
            'warranty_id' => $warranty->id,
            'status' => 'submitted',
            'submitted_at' => now(),
            'details' => 'Claim submitted for roller replacement.',
        ]);

        WorkOrderFeedback::create([
            'work_order_id' => $workOrder->id,
            'user_id' => $clientOwner->id,
            'rating' => 5,
            'comments' => 'Quick response and helpful technician.',
        ]);

        $thread = MessageThread::create([
            'subject' => 'Printer Service Updates',
            'organization_id' => $organization->id,
            'work_order_id' => $workOrder->id,
            'created_by_user_id' => $dispatch->id,
        ]);

        MessageThreadParticipant::create([
            'thread_id' => $thread->id,
            'user_id' => $clientOwner->id,
        ]);

        MessageThreadParticipant::create([
            'thread_id' => $thread->id,
            'user_id' => $technician->id,
        ]);

        Message::create([
            'thread_id' => $thread->id,
            'user_id' => $technician->id,
            'body' => 'I will arrive in about 30 minutes.',
        ]);

        SupportTicket::create([
            'organization_id' => $organization->id,
            'work_order_id' => $workOrder->id,
            'submitted_by_user_id' => $clientOwner->id,
            'assigned_to_user_id' => $support->id,
            'status' => 'open',
            'priority' => 'standard',
            'subject' => 'Follow-up on printer service',
            'description' => 'Please confirm if warranty covers the roller kit.',
        ]);

        KnowledgeArticle::create([
            'title' => 'Troubleshooting Printer Jams',
            'slug' => 'troubleshooting-printer-jams',
            'category' => 'Printers',
            'content' => "Step 1: Power down the printer.\nStep 2: Remove jammed paper carefully.\nStep 3: Inspect rollers.",
            'is_published' => true,
            'published_at' => now(),
            'created_by_user_id' => $support->id,
        ]);

        CommunicationTemplate::create([
            'name' => 'Service Request Received',
            'channel' => 'email',
            'subject' => 'We received your service request',
            'body' => 'Thanks for reaching out. We will respond within the SLA window.',
            'is_active' => true,
            'created_by_user_id' => $admin->id,
        ]);
    }
}
