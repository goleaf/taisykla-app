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
        $this->call([
            RbacSeeder::class,
            UserSeeder::class,
            OrganizationSeeder::class,
            InventorySeeder::class, // Seeds Parts, Stock, and Equipment for Organizations
            WorkOrderSeeder::class,
            BillingSeeder::class,
            KnowledgeBaseSeeder::class,
            SystemSeeder::class,
        ]);

        // Settings and specific rules can be kept or moved to SettingsSeeder.
        // For now I'll keep the SystemSetting creation here as a "SystemSeeder" inline block or move it.
        // To be clean, I should move the specific template/settings creation to a SystemSeeder or leave it in DatabaseSeeder *after* the main entities if it refers to them (like admin id).

        // ... (Actually, let's keep the existing System/Automation/Template seeds in a SystemSeeder or just inline them here if they are unique system config)
        // Since I'm replacing the file content, I'll put the System/Config seeds in a new `SystemSeeder` file to call it.
    }
}
