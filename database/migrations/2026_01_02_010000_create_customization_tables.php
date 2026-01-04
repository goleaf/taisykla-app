<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('custom_fields')) {
            Schema::create('custom_fields', function (Blueprint $table) {
                $table->id();
                $table->string('entity_type');
                $table->string('key');
                $table->string('label');
                $table->string('type');
                $table->boolean('is_required')->default(false);
                $table->string('default_value')->nullable();
                $table->string('validation_rules')->nullable();
                $table->json('options')->nullable();
                $table->unsignedInteger('display_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['entity_type', 'key']);
            });
        }

        if (!Schema::hasTable('custom_statuses')) {
            Schema::create('custom_statuses', function (Blueprint $table) {
                $table->id();
                $table->string('context');
                $table->string('key');
                $table->string('label');
                $table->string('state')->nullable();
                $table->string('color')->nullable();
                $table->string('text_color')->nullable();
                $table->string('icon')->nullable();
                $table->boolean('is_default')->default(false);
                $table->boolean('is_terminal')->default(false);
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['context', 'key']);
            });
        }

        if (!Schema::hasTable('custom_status_transitions')) {
            Schema::create('custom_status_transitions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('from_status_id')->constrained('custom_statuses')->cascadeOnDelete();
                $table->foreignId('to_status_id')->constrained('custom_statuses')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['from_status_id', 'to_status_id']);
            });
        }

        if (!Schema::hasTable('label_overrides')) {
            Schema::create('label_overrides', function (Blueprint $table) {
                $table->id();
                $table->string('key');
                $table->string('locale')->default('en');
                $table->string('value');
                $table->string('group')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();

                $table->unique(['key', 'locale']);
            });
        }

        if (!Schema::hasColumn('work_orders', 'custom_fields')) {
            Schema::table('work_orders', function (Blueprint $table) {
                $table->json('custom_fields')->nullable()->after('description');
            });
        }

        if (!Schema::hasColumn('equipment', 'custom_fields')) {
            Schema::table('equipment', function (Blueprint $table) {
                $table->json('custom_fields')->nullable()->after('notes');
            });
        }

        $now = now();
        $workOrderStatuses = [
            [
                'context' => 'work_order',
                'key' => 'submitted',
                'label' => 'Submitted',
                'state' => 'submitted',
                'color' => '#F3F4F6',
                'text_color' => '#374151',
                'icon' => 'clipboard',
                'is_default' => true,
                'is_terminal' => false,
                'sort_order' => 10,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'context' => 'work_order',
                'key' => 'assigned',
                'label' => 'Assigned',
                'state' => 'assigned',
                'color' => '#DBEAFE',
                'text_color' => '#1D4ED8',
                'icon' => 'user-check',
                'is_default' => false,
                'is_terminal' => false,
                'sort_order' => 20,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'context' => 'work_order',
                'key' => 'in_progress',
                'label' => 'In Progress',
                'state' => 'in_progress',
                'color' => '#E0E7FF',
                'text_color' => '#4338CA',
                'icon' => 'progress',
                'is_default' => false,
                'is_terminal' => false,
                'sort_order' => 30,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'context' => 'work_order',
                'key' => 'on_hold',
                'label' => 'On Hold',
                'state' => 'on_hold',
                'color' => '#FEF9C3',
                'text_color' => '#A16207',
                'icon' => 'pause',
                'is_default' => false,
                'is_terminal' => false,
                'sort_order' => 40,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'context' => 'work_order',
                'key' => 'completed',
                'label' => 'Completed',
                'state' => 'completed',
                'color' => '#DCFCE7',
                'text_color' => '#166534',
                'icon' => 'check-circle',
                'is_default' => false,
                'is_terminal' => true,
                'sort_order' => 50,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'context' => 'work_order',
                'key' => 'closed',
                'label' => 'Closed',
                'state' => 'closed',
                'color' => '#DCFCE7',
                'text_color' => '#166534',
                'icon' => 'lock',
                'is_default' => false,
                'is_terminal' => true,
                'sort_order' => 60,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'context' => 'work_order',
                'key' => 'canceled',
                'label' => 'Canceled',
                'state' => 'canceled',
                'color' => '#FEE2E2',
                'text_color' => '#991B1B',
                'icon' => 'x-circle',
                'is_default' => false,
                'is_terminal' => true,
                'sort_order' => 70,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        $equipmentStatuses = [
            [
                'context' => 'equipment',
                'key' => 'operational',
                'label' => 'Operational',
                'state' => 'operational',
                'color' => '#DCFCE7',
                'text_color' => '#166534',
                'icon' => 'check',
                'is_default' => true,
                'is_terminal' => false,
                'sort_order' => 10,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'context' => 'equipment',
                'key' => 'needs_attention',
                'label' => 'Needs Attention',
                'state' => 'needs_attention',
                'color' => '#FEF9C3',
                'text_color' => '#A16207',
                'icon' => 'alert',
                'is_default' => false,
                'is_terminal' => false,
                'sort_order' => 20,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'context' => 'equipment',
                'key' => 'in_repair',
                'label' => 'In Repair',
                'state' => 'in_repair',
                'color' => '#FFEDD5',
                'text_color' => '#9A3412',
                'icon' => 'tool',
                'is_default' => false,
                'is_terminal' => false,
                'sort_order' => 30,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'context' => 'equipment',
                'key' => 'retired',
                'label' => 'Retired',
                'state' => 'retired',
                'color' => '#F3F4F6',
                'text_color' => '#4B5563',
                'icon' => 'archive',
                'is_default' => false,
                'is_terminal' => true,
                'sort_order' => 40,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('custom_statuses')->insert(array_merge($workOrderStatuses, $equipmentStatuses));

        $statusMap = DB::table('custom_statuses')
            ->get()
            ->keyBy(fn($row) => $row->context . ':' . $row->key);

        $workOrderTransitions = [
            'submitted' => ['assigned', 'canceled'],
            'assigned' => ['in_progress', 'on_hold', 'canceled'],
            'in_progress' => ['on_hold', 'completed', 'canceled'],
            'on_hold' => ['in_progress', 'canceled'],
            'completed' => ['closed'],
        ];

        $equipmentTransitions = [
            'operational' => ['needs_attention', 'in_repair', 'retired'],
            'needs_attention' => ['in_repair', 'operational', 'retired'],
            'in_repair' => ['operational', 'retired'],
        ];

        $transitionRows = [];
        foreach ($workOrderTransitions as $from => $targets) {
            $fromStatus = $statusMap->get('work_order:' . $from);
            if (!$fromStatus) {
                continue;
            }
            foreach ($targets as $target) {
                $toStatus = $statusMap->get('work_order:' . $target);
                if (!$toStatus) {
                    continue;
                }
                $transitionRows[] = [
                    'from_status_id' => $fromStatus->id,
                    'to_status_id' => $toStatus->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach ($equipmentTransitions as $from => $targets) {
            $fromStatus = $statusMap->get('equipment:' . $from);
            if (!$fromStatus) {
                continue;
            }
            foreach ($targets as $target) {
                $toStatus = $statusMap->get('equipment:' . $target);
                if (!$toStatus) {
                    continue;
                }
                $transitionRows[] = [
                    'from_status_id' => $fromStatus->id,
                    'to_status_id' => $toStatus->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if ($transitionRows) {
            DB::table('custom_status_transitions')->insert($transitionRows);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropColumn('custom_fields');
        });

        Schema::table('equipment', function (Blueprint $table) {
            $table->dropColumn('custom_fields');
        });

        Schema::dropIfExists('custom_status_transitions');
        Schema::dropIfExists('custom_statuses');
        Schema::dropIfExists('custom_fields');
        Schema::dropIfExists('label_overrides');
    }
};
