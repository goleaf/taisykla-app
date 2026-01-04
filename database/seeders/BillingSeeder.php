<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BillingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $completedWOs = \App\Models\WorkOrder::whereIn('status', ['completed', 'closed'])->get();

        foreach ($completedWOs as $wo) {
            // 80% chance to have an invoice
            if (fake()->boolean(80)) {
                $invoice = \App\Models\Invoice::factory()->create([
                    'organization_id' => $wo->organization_id,
                    'work_order_id' => $wo->id,
                    'status' => fake()->randomElement(['sent', 'paid']),
                ]);

                // If paid, create payment
                if ($invoice->status === 'paid') {
                    \App\Models\Payment::factory()->create([
                        'invoice_id' => $invoice->id,
                        'amount' => $invoice->total,
                        'paid_at' => $invoice->issued_at->addDays(rand(1, 5)),
                    ]);
                }
            }
        }
    }
}
