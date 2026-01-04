<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = \App\Models\User::role(\App\Support\RoleCatalog::ADMIN)->first();

        // 1. Communication Templates
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
            $templateModels[$name] = \App\Models\CommunicationTemplate::firstOrCreate(
                ['name' => $name],
                array_merge($data, [
                    'is_active' => true,
                    'is_shared' => true,
                    'created_by_user_id' => $admin?->id ?? 1,
                ])
            );
        }

        // 2. Message Automations
        \App\Models\MessageAutomation::firstOrCreate(
            ['trigger' => 'appointment_upcoming_24h'],
            [
                'name' => 'Appointment Reminder (24h)',
                'channels' => ['email', 'sms'],
                'template_id' => $templateModels['Appointment Confirmation']->id ?? null,
                'schedule_offset_minutes' => 1440,
            ]
        );
        \App\Models\MessageAutomation::firstOrCreate(
            ['trigger' => 'technician_en_route'],
            [
                'name' => 'Technician En Route',
                'channels' => ['sms'],
                'template_id' => $templateModels['Running Late Notification']->id ?? null,
                'schedule_offset_minutes' => 30,
            ]
        );
        \App\Models\MessageAutomation::firstOrCreate(
            ['trigger' => 'work_completed'],
            [
                'name' => 'Work Completed',
                'channels' => ['email'],
                'template_id' => $templateModels['Work Completed']->id ?? null,
                'schedule_offset_minutes' => 0,
            ]
        );
        \App\Models\MessageAutomation::firstOrCreate(
            ['trigger' => 'invoice_generated'],
            [
                'name' => 'Invoice Generated',
                'channels' => ['email'],
                'template_id' => $templateModels['Payment Reminder']->id ?? null,
                'schedule_offset_minutes' => 0,
            ]
        );
        \App\Models\MessageAutomation::firstOrCreate(
            ['trigger' => 'satisfaction_survey'],
            [
                'name' => 'Satisfaction Survey',
                'channels' => ['email'],
                'template_id' => $templateModels['Follow-up Message']->id ?? null,
                'schedule_offset_minutes' => 60,
            ]
        );

        // 3. System Settings
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
            \App\Models\SystemSetting::updateOrCreate(
                ['group' => $setting['group'], 'key' => $setting['key']],
                ['value' => $setting['value']]
            );
        }

        // 4. Automation Rules
        \App\Models\AutomationRule::firstOrCreate(
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

        // 5. Integration Settings
        \App\Models\IntegrationSetting::firstOrCreate(
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

        // 6. Reports
        \App\Models\Report::firstOrCreate(
            ['name' => 'Weekly Productivity'],
            [
                'report_type' => 'weekly_productivity',
                'description' => 'Weekly technician output and time tracking.',
                'is_public' => true,
                'created_by_user_id' => $admin?->id ?? 1,
            ]
        );

        \App\Models\Report::firstOrCreate(
            ['name' => 'Revenue Trends'],
            [
                'report_type' => 'revenue',
                'description' => 'Monthly revenue totals for completed invoices.',
                'is_public' => true,
                'created_by_user_id' => $admin?->id ?? 1,
            ]
        );

        \App\Models\Report::firstOrCreate(
            ['name' => 'Custom Work Order Status'],
            [
                'report_type' => 'custom',
                'data_source' => 'work_orders',
                'description' => 'Group work orders by status.',
                'definition' => ['fields' => ['status']],
                'group_by' => ['status'],
                'is_public' => false,
                'created_by_user_id' => $admin?->id ?? 1,
            ]
        );

        \App\Models\Report::firstOrCreate(
            ['name' => 'SLA Compliance'],
            [
                'report_type' => 'sla_compliance',
                'description' => 'SLA response compliance by priority.',
                'is_public' => true,
                'created_by_user_id' => $admin?->id ?? 1,
            ]
        );
    }
}
