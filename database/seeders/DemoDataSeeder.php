<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Organization;
use App\Models\WorkOrder;
use App\Models\WorkOrderPart;
use App\Models\WorkOrderFeedback;
use App\Models\Appointment;
use App\Models\Part;
use App\Models\MessageThread;
use App\Models\Message;
use App\Models\SupportTicket;
use App\Models\KnowledgeArticle;
use App\Models\KnowledgeCategory;
use App\Support\RoleCatalog;
use Illuminate\Support\Facades\DB;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Run Base Seeders
        $this->call([
            RbacSeeder::class,
            UserSeeder::class,
            OrganizationSeeder::class,
            InventorySeeder::class,
            WorkOrderSeeder::class,
            BillingSeeder::class,
            KnowledgeBaseSeeder::class,
            SystemSeeder::class,
        ]);

        $this->command->info('Base seeders completed. Enriching with demo scenarios...');

        $technicians = User::role(RoleCatalog::TECHNICIAN)->get();
        $clients = User::role(RoleCatalog::CLIENT)->get();
        $parts = Part::all();
        $workOrders = WorkOrder::all();

        // 2. Enrich Work Orders with Parts and Labor
        $workOrders->each(function ($wo) use ($parts) {
            // Add 1-4 parts to 70% of work orders
            if (fake()->boolean(70)) {
                $woParts = $parts->random(rand(1, 4));
                foreach ($woParts as $part) {
                    WorkOrderPart::create([
                        'work_order_id' => $wo->id,
                        'part_id' => $part->id,
                        'quantity' => rand(1, 3),
                        'unit_cost' => $part->cost_price ?? rand(10, 100),
                        'unit_price' => $part->retail_price ?? rand(20, 200),
                    ]);
                }
            }

            // Set labor minutes if completed
            if ($wo->status === 'completed' || $wo->status === 'closed') {
                $wo->update([
                    'labor_minutes' => rand(30, 240),
                    'travel_minutes' => rand(15, 60),
                ]);

                // Add Feedback
                if (fake()->boolean(60)) {
                    WorkOrderFeedback::factory()->create([
                        'work_order_id' => $wo->id,
                        'user_id' => $wo->requested_by_user_id ?? $clients->random()->id,
                    ]);
                }
            }
        });

        // 3. Create Realistic Appointments
        $workOrders->whereIn('status', ['assigned', 'in_progress'])->each(function ($wo) use ($technicians) {
            $tech = $wo->assignedTo ?: $technicians->random();
            
            // Current/Future Appointment
            Appointment::factory()->create([
                'work_order_id' => $wo->id,
                'assigned_to_user_id' => $tech->id,
                'scheduled_start_at' => now()->addDays(rand(-1, 5))->setHour(rand(8, 16)),
                'status' => 'confirmed',
            ]);
        });

        // 4. Create Communication History (Messages)
        $workOrders->random(15)->each(function ($wo) use ($technicians, $clients) {
            $tech = $wo->assignedTo ?: $technicians->random();
            $client = $wo->requestedBy ?: $clients->random();

            $thread = MessageThread::create([
                'subject' => "Update regarding WO #{$wo->id}: {$wo->subject}",
                'work_order_id' => $wo->id,
            ]);

            // Add participants
            DB::table('message_thread_participants')->insert([
                ['thread_id' => $thread->id, 'user_id' => $tech->id, 'created_at' => now(), 'updated_at' => now()],
                ['thread_id' => $thread->id, 'user_id' => $client->id, 'created_at' => now(), 'updated_at' => now()],
            ]);

            // Create a small conversation
            Message::create([
                'thread_id' => $thread->id,
                'user_id' => $tech->id,
                'body' => "Hi, I'm just checking in to see if tomorrow at 10 AM works for the repair?",
                'created_at' => now()->subHours(24),
            ]);

            Message::create([
                'thread_id' => $thread->id,
                'user_id' => $client->id,
                'body' => "Yes, that works perfectly. We'll be on site.",
                'created_at' => now()->subHours(22),
            ]);

            Message::create([
                'thread_id' => $thread->id,
                'user_id' => $tech->id,
                'body' => "Great, see you then!",
                'created_at' => now()->subHours(21),
            ]);
        });

        // 5. Support Tickets
        $orgs = Organization::all();
        SupportTicket::factory(20)->make()->each(function ($ticket) use ($orgs, $clients, $technicians) {
            $org = $orgs->random();
            $ticket->organization_id = $org->id;
            $ticket->submitted_by_user_id = $org->users()->role(RoleCatalog::CLIENT)->first()?->id ?? $clients->random()->id;
            
            if (fake()->boolean(70)) {
                $ticket->assigned_to_user_id = User::role(RoleCatalog::SUPPORT)->get()->random()->id ?? $technicians->random()->id;
            }

            $ticket->save();
        });

        // 6. Enrich Knowledge Base with better articles
        $kbCategories = KnowledgeCategory::all();
        $admin = User::role(RoleCatalog::ADMIN)->first();

        $proArticles = [
            ['title' => 'Common Printer Jams and Solutions', 'category' => 'Hardware'],
            ['title' => 'How to Reset Your Enterprise Password', 'category' => 'Security'],
            ['title' => 'Setting up VPN for Remote Work', 'category' => 'Networking'],
            ['title' => 'Updating Laptop Firmware safely', 'category' => 'Software'],
            ['title' => 'Troubleshooting Slow Wi-Fi in Office', 'category' => 'Networking'],
        ];

        foreach ($proArticles as $art) {
            $cat = $kbCategories->firstWhere('name', $art['category']) ?? $kbCategories->random();
            KnowledgeArticle::create([
                'category_id' => $cat->id,
                'created_by_user_id' => $admin->id,
                'title' => $art['title'],
                'slug' => \Illuminate\Support\Str::slug($art['title']),
                'content' => "This is a comprehensive guide on {$art['title']}. <br><br> 
                             Step 1: Verify the connection. <br>
                             Step 2: Check for power issues. <br>
                             Step 3: Consult the manufacturer manual if steps 1-2 fail.",
                'status' => 'published',
                'is_published' => true,
                'visibility' => 'public',
                'view_count' => rand(10, 500),
            ]);
        }

        $this->command->info('Demo data enrichment completed successfully!');
    }
}
