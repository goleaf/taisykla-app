<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\AutomationRule;
use App\Models\CommunicationTemplate;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\IntegrationSetting;
use App\Models\InventoryItem;
use App\Models\InventoryLocation;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\KnowledgeArticle;
use App\Models\KnowledgeArticleResource;
use App\Models\KnowledgeCategory;
use App\Models\KnowledgeTag;
use App\Models\KnowledgeTemplate;
use App\Models\Message;
use App\Models\MessageAutomation;
use App\Models\MessageThread;
use App\Models\MessageThreadParticipant;
use App\Models\Organization;
use App\Models\Part;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\Report;
use App\Models\ServiceAgreement;
use App\Models\SupportTicket;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\Warranty;
use App\Models\WarrantyClaim;
use App\Models\WorkOrder;
use App\Models\WorkOrderCategory;
use App\Models\WorkOrderFeedback;
use App\Models\WorkOrderPart;
use App\Support\RoleCatalog;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RbacSeeder::class);

        $serviceAgreement = ServiceAgreement::firstOrCreate(
            ['name' => 'Comprehensive Support'],
            [
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
            ]
        );

        $organization = Organization::firstOrCreate(
            ['name' => 'Acme Corporation'],
            [
                'type' => 'business',
                'status' => 'active',
                'primary_contact_name' => 'Jamie Client',
                'primary_contact_email' => 'client@example.com',
                'primary_contact_phone' => '+1 555-0100',
                'billing_email' => 'billing@example.com',
                'billing_address' => '100 Main Street, Suite 200',
                'service_agreement_id' => $serviceAgreement->id,
                'notes' => 'Priority business account.',
            ]
        );

        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $admin->assignRole(RoleCatalog::ADMIN);

        $dispatch = User::firstOrCreate(
            ['email' => 'dispatch@example.com'],
            [
                'name' => 'Dispatch Manager',
                'password' => Hash::make('password'),
                'job_title' => 'Dispatch Manager',
                'is_active' => true,
            ]
        );
        $dispatch->assignRole(RoleCatalog::DISPATCH);

        $operations = User::firstOrCreate(
            ['email' => 'ops@example.com'],
            [
                'name' => 'Operations Manager',
                'password' => Hash::make('password'),
                'job_title' => 'Operations Manager',
                'is_active' => true,
            ]
        );
        $operations->assignRole(RoleCatalog::OPERATIONS_MANAGER);

        $technician = User::firstOrCreate(
            ['email' => 'tech@example.com'],
            [
                'name' => 'Alex Technician',
                'password' => Hash::make('password'),
                'job_title' => 'Field Technician',
                'phone' => '+1 555-0123',
                'is_active' => true,
            ]
        );
        $technician->assignRole(RoleCatalog::TECHNICIAN);

        $inventory = User::firstOrCreate(
            ['email' => 'inventory@example.com'],
            [
                'name' => 'Pat Inventory',
                'password' => Hash::make('password'),
                'job_title' => 'Inventory Specialist',
                'is_active' => true,
            ]
        );
        $inventory->assignRole(RoleCatalog::INVENTORY_SPECIALIST);

        $quality = User::firstOrCreate(
            ['email' => 'qa@example.com'],
            [
                'name' => 'Quinn Quality',
                'password' => Hash::make('password'),
                'job_title' => 'Quality Assurance Manager',
                'is_active' => true,
            ]
        );
        $quality->assignRole(RoleCatalog::QA_MANAGER);

        $billing = User::firstOrCreate(
            ['email' => 'billing@example.com'],
            [
                'name' => 'Finley Billing',
                'password' => Hash::make('password'),
                'job_title' => 'Billing Specialist',
                'is_active' => true,
            ]
        );
        $billing->assignRole(RoleCatalog::BILLING_SPECIALIST);

        $support = User::firstOrCreate(
            ['email' => 'support@example.com'],
            [
                'name' => 'Support Manager',
                'password' => Hash::make('password'),
                'job_title' => 'Support Manager',
                'is_active' => true,
            ]
        );
        $support->assignRole(RoleCatalog::SUPPORT);

        $clientOwner = User::firstOrCreate(
            ['email' => 'client@example.com'],
            [
                'name' => 'Jamie Client',
                'password' => Hash::make('password'),
                'organization_id' => $organization->id,
                'job_title' => 'Office Manager',
                'phone' => '+1 555-0199',
                'is_active' => true,
            ]
        );
        $clientOwner->assignRole(RoleCatalog::CLIENT);

        $businessAdmin = User::firstOrCreate(
            ['email' => 'bizadmin@example.com'],
            [
                'name' => 'Business Admin',
                'password' => Hash::make('password'),
                'organization_id' => $organization->id,
                'job_title' => 'Operations Lead',
                'phone' => '+1 555-0188',
                'is_active' => true,
            ]
        );
        $businessAdmin->assignRole(RoleCatalog::BUSINESS_ADMIN);

        $businessUser = User::firstOrCreate(
            ['email' => 'bizuser@example.com'],
            [
                'name' => 'Business User',
                'password' => Hash::make('password'),
                'organization_id' => $organization->id,
                'job_title' => 'Coordinator',
                'phone' => '+1 555-0187',
                'is_active' => true,
            ]
        );
        $businessUser->assignRole(RoleCatalog::BUSINESS_USER);

        $consumer = User::firstOrCreate(
            ['email' => 'consumer@example.com'],
            [
                'name' => 'Individual Consumer',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $consumer->assignRole(RoleCatalog::CONSUMER);

        $guest = User::firstOrCreate(
            ['email' => 'guest@example.com'],
            [
                'name' => 'Guest User',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $guest->assignRole(RoleCatalog::GUEST);

        $categories = [
            ['name' => 'Hardware Repair', 'default_estimated_minutes' => 180],
            ['name' => 'Software Installation', 'default_estimated_minutes' => 90],
            ['name' => 'Networking', 'default_estimated_minutes' => 120],
            ['name' => 'Printer Setup', 'default_estimated_minutes' => 60],
        ];

        foreach ($categories as $category) {
            WorkOrderCategory::firstOrCreate(['name' => $category['name']], $category);
        }

        $equipmentCategoryRows = [
            ['name' => 'Printers', 'description' => 'Office printers, copiers, and multifunction devices.'],
            ['name' => 'Desktops', 'description' => 'Workstation and desktop computer assets.'],
            ['name' => 'Laptops', 'description' => 'Portable laptops and notebooks.'],
            ['name' => 'Network', 'description' => 'Routers, switches, and network appliances.'],
        ];

        foreach ($equipmentCategoryRows as $row) {
            EquipmentCategory::firstOrCreate(['name' => $row['name']], $row);
        }

        $printerCategory = EquipmentCategory::where('name', 'Printers')->first();

        $equipment = Equipment::firstOrCreate(
            ['name' => 'Conference Room Printer', 'organization_id' => $organization->id],
            [
                'equipment_category_id' => $printerCategory?->id,
                'type' => 'Printer',
                'manufacturer' => 'HP',
                'model' => 'LaserJet Pro',
                'serial_number' => 'HP-PRN-002',
                'status' => 'needs_attention',
                'location_name' => 'Conference Room',
                'location_address' => '100 Main Street, Suite 200',
                'assigned_user_id' => $clientOwner->id,
                'notes' => 'Paper jams frequently.',
            ]
        );

        $warranty = Warranty::firstOrCreate(
            ['equipment_id' => $equipment->id, 'provider_name' => 'HP Care'],
            [
                'coverage_type' => 'labor_included',
                'coverage_details' => 'Covers parts and labor for mechanical failure.',
                'starts_at' => now()->subYear()->toDateString(),
                'ends_at' => now()->addYear()->toDateString(),
            ]
        );

        $workOrder = WorkOrder::firstOrCreate(
            ['subject' => 'Printer keeps jamming', 'organization_id' => $organization->id],
            [
                'equipment_id' => $equipment->id,
                'requested_by_user_id' => $clientOwner->id,
                'assigned_to_user_id' => $technician->id,
                'assigned_at' => now()->subHours(1),
                'category_id' => WorkOrderCategory::first()->id,
                'priority' => 'high',
                'status' => 'assigned',
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
            ]
        );

        Appointment::firstOrCreate(
            ['work_order_id' => $workOrder->id, 'scheduled_start_at' => now()->addHours(1)],
            [
                'assigned_to_user_id' => $technician->id,
                'scheduled_end_at' => now()->addHours(2),
                'time_window' => '08:00-12:00',
                'status' => 'scheduled',
            ]
        );

        $part = Part::firstOrCreate(
            ['sku' => 'PRN-ROLLER-01'],
            [
                'name' => 'Printer Roller Kit',
                'description' => 'Replacement roller kit for LaserJet.',
                'unit_cost' => 25.00,
                'unit_price' => 55.00,
                'vendor' => 'HP',
                'reorder_level' => 5,
            ]
        );

        $location = InventoryLocation::firstOrCreate(
            ['name' => 'Main Warehouse'],
            ['address' => '200 Supply Road']
        );

        InventoryItem::updateOrCreate(
            ['part_id' => $part->id, 'location_id' => $location->id],
            ['quantity' => 12, 'reserved_quantity' => 1]
        );

        WorkOrderPart::firstOrCreate(
            ['work_order_id' => $workOrder->id, 'part_id' => $part->id],
            [
                'quantity' => 1,
                'unit_cost' => 25.00,
                'unit_price' => 55.00,
            ]
        );

        $quote = Quote::firstOrCreate(
            ['work_order_id' => $workOrder->id],
            [
                'organization_id' => $organization->id,
                'status' => 'approved',
                'subtotal' => 55.00,
                'tax' => 0,
                'total' => 55.00,
                'approved_at' => now(),
            ]
        );

        QuoteItem::firstOrCreate(
            ['quote_id' => $quote->id, 'description' => 'Printer Roller Kit'],
            [
                'quantity' => 1,
                'unit_price' => 55.00,
                'total' => 55.00,
            ]
        );

        $invoice = Invoice::firstOrCreate(
            ['work_order_id' => $workOrder->id],
            [
                'organization_id' => $organization->id,
                'status' => 'sent',
                'subtotal' => 55.00,
                'tax' => 0,
                'total' => 55.00,
                'due_date' => now()->addDays(30)->toDateString(),
                'sent_at' => now(),
            ]
        );

        InvoiceItem::firstOrCreate(
            ['invoice_id' => $invoice->id, 'description' => 'Printer Roller Kit'],
            [
                'quantity' => 1,
                'unit_price' => 55.00,
                'total' => 55.00,
            ]
        );

        WarrantyClaim::firstOrCreate(
            ['work_order_id' => $workOrder->id, 'warranty_id' => $warranty->id],
            [
                'equipment_id' => $equipment->id,
                'status' => 'submitted',
                'submitted_at' => now(),
                'details' => 'Claim submitted for roller replacement.',
            ]
        );

        WorkOrderFeedback::firstOrCreate(
            ['work_order_id' => $workOrder->id],
            [
                'user_id' => $clientOwner->id,
                'rating' => 5,
                'comments' => 'Quick response and helpful technician.',
            ]
        );

        $thread = MessageThread::firstOrCreate(
            ['work_order_id' => $workOrder->id, 'subject' => 'Printer Service Updates'],
            [
                'organization_id' => $organization->id,
                'created_by_user_id' => $dispatch->id,
            ]
        );

        MessageThreadParticipant::firstOrCreate([
            'thread_id' => $thread->id,
            'user_id' => $clientOwner->id,
        ]);

        MessageThreadParticipant::firstOrCreate([
            'thread_id' => $thread->id,
            'user_id' => $technician->id,
        ]);

        Message::firstOrCreate([
            'thread_id' => $thread->id,
            'user_id' => $technician->id,
            'body' => 'I will arrive in about 30 minutes.',
        ]);

        SupportTicket::firstOrCreate(
            ['subject' => 'Follow-up on printer service'],
            [
                'organization_id' => $organization->id,
                'work_order_id' => $workOrder->id,
                'submitted_by_user_id' => $clientOwner->id,
                'assigned_to_user_id' => $support->id,
                'status' => 'open',
                'priority' => 'standard',
                'description' => 'Please confirm if warranty covers the roller kit.',
            ]
        );

        $kbRoot = KnowledgeCategory::firstOrCreate(
            ['slug' => 'hardware'],
            [
                'name' => 'Hardware',
                'description' => 'Physical devices and components.',
                'icon' => 'cpu',
                'sort_order' => 1,
            ]
        );
        $kbPrinters = KnowledgeCategory::firstOrCreate(
            ['slug' => 'hardware-printers'],
            [
                'name' => 'Printers',
                'description' => 'Printer diagnostics and maintenance.',
                'icon' => 'printer',
                'parent_id' => $kbRoot->id,
                'sort_order' => 2,
            ]
        );
        $kbTroubleshooting = KnowledgeCategory::firstOrCreate(
            ['slug' => 'hardware-printers-troubleshooting'],
            [
                'name' => 'Troubleshooting',
                'description' => 'Resolve common device issues.',
                'icon' => 'wrench',
                'parent_id' => $kbPrinters->id,
                'sort_order' => 1,
            ]
        );

        $tagJam = KnowledgeTag::firstOrCreate(['slug' => 'paper-jam'], ['name' => 'Paper Jam']);
        $tagRoller = KnowledgeTag::firstOrCreate(['slug' => 'rollers'], ['name' => 'Rollers']);
        $tagMaintenance = KnowledgeTag::firstOrCreate(['slug' => 'maintenance'], ['name' => 'Maintenance']);

        $template = KnowledgeTemplate::firstOrCreate(
            ['name' => 'Troubleshooting Template'],
            [
                'content_type' => 'troubleshooting',
                'description' => 'Problem, symptoms, root cause, and resolution checklist.',
                'sections' => [
                    'Problem summary',
                    'Symptoms',
                    'Likely causes',
                    'Resolution steps',
                    'Prevention tips',
                ],
                'body' => "## Problem\\nDescribe the issue.\\n\\n## Symptoms\\n- Symptom 1\\n- Symptom 2\\n\\n## Resolution\\n1. Step one\\n2. Step two\\n",
                'is_active' => true,
            ]
        );

        $article = KnowledgeArticle::firstOrCreate(
            ['slug' => 'troubleshooting-printer-jams'],
            [
                'title' => 'Troubleshooting Printer Jams',
                'summary' => 'Steps for clearing jams, inspecting rollers, and preventing repeat issues.',
                'category' => 'Printers',
                'category_id' => $kbTroubleshooting->id,
                'content' => "## Quick fix\\n1. Power down the printer.\\n2. Remove jammed paper carefully.\\n3. Inspect rollers for debris.\\n\\n## Prevention\\nClean the feed path weekly and store paper flat.",
                'content_type' => 'troubleshooting',
                'content_format' => 'markdown',
                'visibility' => 'public',
                'status' => 'published',
                'is_published' => true,
                'published_at' => now(),
                'author_name' => $support->name,
                'author_title' => $support->job_title ?? 'Support Specialist',
                'template_key' => $template->name,
                'allow_comments' => true,
                'reading_time_minutes' => 3,
                'created_by_user_id' => $support->id,
                'updated_by_user_id' => $support->id,
            ]
        );

        $article->tags()->sync([$tagJam->id, $tagRoller->id, $tagMaintenance->id]);

        KnowledgeArticleResource::firstOrCreate(
            [
                'knowledge_article_id' => $article->id,
                'label' => 'Printer Jam Checklist',
            ],
            [
                'resource_type' => 'quick_reference',
                'url' => 'https://example.com/resources/printer-jam-checklist.pdf',
                'file_type' => 'pdf',
                'is_downloadable' => true,
                'meta' => ['pages' => 2],
            ]
        );

        $templateSeeds = [
            'Service Request Received' => [
                'category' => 'General',
                'channel' => 'email',
                'subject' => 'We received your service request',
                'body' => 'Thanks for reaching out. We will respond within the SLA window.',
                'merge_fields' => ['customer_name', 'work_order_id'],
            ],
            'Appointment Confirmation' => [
                'category' => 'Appointments',
                'channel' => 'email',
                'subject' => 'Appointment confirmed for {{appointment_time}}',
                'body' => 'Hi {{customer_name}}, your appointment is confirmed for {{appointment_time}} with {{technician_name}}.',
                'merge_fields' => ['customer_name', 'appointment_time', 'technician_name'],
            ],
            'Running Late Notification' => [
                'category' => 'Appointments',
                'channel' => 'sms',
                'subject' => 'Running late',
                'body' => 'Hi {{customer_name}}, your technician is running about 15 minutes late. We are on the way.',
                'merge_fields' => ['customer_name'],
            ],
            'Work Completed' => [
                'category' => 'Work Orders',
                'channel' => 'email',
                'subject' => 'Work completed for WO #{{work_order_id}}',
                'body' => 'We completed the work order. Summary: {{work_order_subject}}.',
                'merge_fields' => ['work_order_id', 'work_order_subject'],
            ],
            'Quote Provided' => [
                'category' => 'Quotes',
                'channel' => 'email',
                'subject' => 'Quote ready for your review',
                'body' => 'Hi {{customer_name}}, we prepared a quote for {{work_order_subject}}. Please review when ready.',
                'merge_fields' => ['customer_name', 'work_order_subject'],
            ],
            'Payment Reminder' => [
                'category' => 'Billing',
                'channel' => 'email',
                'subject' => 'Payment reminder for invoice {{work_order_id}}',
                'body' => 'This is a friendly reminder that payment is due. Please contact us if you need help.',
                'merge_fields' => ['work_order_id', 'customer_name'],
            ],
            'Follow-up Message' => [
                'category' => 'Follow-up',
                'channel' => 'email',
                'subject' => 'Checking in after your recent service',
                'body' => 'Hi {{customer_name}}, just checking in to make sure everything is working well.',
                'merge_fields' => ['customer_name'],
            ],
        ];

        $templateModels = [];
        foreach ($templateSeeds as $name => $data) {
            $templateModels[$name] = CommunicationTemplate::firstOrCreate(
                ['name' => $name],
                array_merge($data, [
                    'is_active' => true,
                    'is_shared' => true,
                    'created_by_user_id' => $admin->id,
                ])
            );
        }

        MessageAutomation::firstOrCreate(
            ['trigger' => 'appointment_upcoming_24h'],
            [
                'name' => 'Appointment Reminder (24h)',
                'channels' => ['email', 'sms'],
                'template_id' => $templateModels['Appointment Confirmation']->id ?? null,
                'schedule_offset_minutes' => 1440,
            ]
        );
        MessageAutomation::firstOrCreate(
            ['trigger' => 'technician_en_route'],
            [
                'name' => 'Technician En Route',
                'channels' => ['sms'],
                'template_id' => $templateModels['Running Late Notification']->id ?? null,
                'schedule_offset_minutes' => 30,
            ]
        );
        MessageAutomation::firstOrCreate(
            ['trigger' => 'work_completed'],
            [
                'name' => 'Work Completed',
                'channels' => ['email'],
                'template_id' => $templateModels['Work Completed']->id ?? null,
                'schedule_offset_minutes' => 0,
            ]
        );
        MessageAutomation::firstOrCreate(
            ['trigger' => 'invoice_generated'],
            [
                'name' => 'Invoice Generated',
                'channels' => ['email'],
                'template_id' => $templateModels['Payment Reminder']->id ?? null,
                'schedule_offset_minutes' => 0,
            ]
        );
        MessageAutomation::firstOrCreate(
            ['trigger' => 'satisfaction_survey'],
            [
                'name' => 'Satisfaction Survey',
                'channels' => ['email'],
                'template_id' => $templateModels['Follow-up Message']->id ?? null,
                'schedule_offset_minutes' => 60,
            ]
        );

        $settings = [
            ['group' => 'company', 'key' => 'name', 'value' => 'Maintenance Manager'],
            ['group' => 'company', 'key' => 'address', 'value' => '100 Main Street, Suite 200'],
            ['group' => 'company', 'key' => 'support_email', 'value' => 'support@example.com'],
            ['group' => 'company', 'key' => 'support_phone', 'value' => '+1 555-0101'],
            ['group' => 'company', 'key' => 'website', 'value' => 'https://example.com'],
            ['group' => 'company', 'key' => 'hours', 'value' => 'Mon-Fri 08:00-18:00'],
            ['group' => 'company', 'key' => 'logo_url', 'value' => ''],
            ['group' => 'company', 'key' => 'primary_color', 'value' => '#4F46E5'],
            ['group' => 'sla', 'key' => 'standard_response_minutes', 'value' => 240],
            ['group' => 'sla', 'key' => 'high_response_minutes', 'value' => 180],
            ['group' => 'sla', 'key' => 'urgent_response_minutes', 'value' => 60],
            ['group' => 'billing', 'key' => 'default_terms', 'value' => 'Net 30'],
            ['group' => 'billing', 'key' => 'tax_rate', 'value' => 0],
            ['group' => 'mobile', 'key' => 'offline_mode', 'value' => true],
            ['group' => 'audit', 'key' => 'track_sensitive_changes', 'value' => true],
            ['group' => 'backup', 'key' => 'last_run_at', 'value' => now()->subDay()->toDateTimeString()],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(
                ['group' => $setting['group'], 'key' => $setting['key']],
                ['value' => $setting['value']]
            );
        }

        AutomationRule::firstOrCreate(
            ['name' => 'Urgent Work Order Alert'],
            [
                'trigger' => 'work_order_priority_urgent',
                'conditions' => [
                    ['field' => 'priority', 'operator' => '=', 'value' => 'urgent'],
                ],
                'actions' => [
                    ['type' => 'notify', 'channel' => 'email', 'recipient' => 'dispatch@example.com'],
                ],
                'is_active' => true,
            ]
        );

        IntegrationSetting::firstOrCreate(
            ['provider' => 'stripe'],
            [
                'name' => 'Stripe Payments',
                'config' => [
                    'mode' => 'test',
                    'currency' => 'USD',
                ],
                'is_active' => false,
            ]
        );

        Report::firstOrCreate(
            ['name' => 'Weekly Productivity'],
            [
                'report_type' => 'weekly_productivity',
                'description' => 'Weekly technician output and time tracking.',
                'is_public' => true,
                'created_by_user_id' => $admin->id,
            ]
        );

        Report::firstOrCreate(
            ['name' => 'Revenue Trends'],
            [
                'report_type' => 'revenue',
                'description' => 'Monthly revenue totals for completed invoices.',
                'is_public' => true,
                'created_by_user_id' => $admin->id,
            ]
        );

        Report::firstOrCreate(
            ['name' => 'Custom Work Order Status'],
            [
                'report_type' => 'custom',
                'data_source' => 'work_orders',
                'description' => 'Group work orders by status.',
                'definition' => ['fields' => ['status']],
                'group_by' => ['status'],
                'is_public' => false,
                'created_by_user_id' => $admin->id,
            ]
        );

        Report::firstOrCreate(
            ['name' => 'SLA Compliance'],
            [
                'report_type' => 'sla_compliance',
                'description' => 'SLA response compliance by priority.',
                'is_public' => true,
                'created_by_user_id' => $admin->id,
            ]
        );
    }
}
