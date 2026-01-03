<?php

namespace App\Support;

use App\Models\CustomStatus;
use App\Models\CustomStatusTransition;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class StatusCatalog
{
    public const CONTEXT_WORK_ORDER = 'work_order';
    public const CONTEXT_EQUIPMENT = 'equipment';

    public static function statuses(string $context): Collection
    {
        $cacheKey = self::cacheKey('statuses', $context);

        return Cache::remember($cacheKey, 3600, function () use ($context) {
            return CustomStatus::forContext($context)
                ->active()
                ->orderBy('sort_order')
                ->orderBy('label')
                ->get();
        });
    }

    public static function statusOptions(string $context): array
    {
        return self::meta($context)
            ->mapWithKeys(fn ($status) => [$status['key'] => $status['label']])
            ->all();
    }

    public static function meta(string $context): Collection
    {
        $cacheKey = self::cacheKey('meta', $context);

        return Cache::remember($cacheKey, 3600, function () use ($context) {
            $statuses = self::statuses($context);
            if ($statuses->isEmpty()) {
                return collect(self::fallbackStatuses($context));
            }

            return $statuses->map(function (CustomStatus $status) {
                return [
                    'id' => $status->id,
                    'key' => $status->key,
                    'label' => $status->label,
                    'state' => $status->state,
                    'color' => $status->color,
                    'text_color' => $status->text_color,
                    'icon' => $status->icon,
                    'icon_svg' => self::iconSvg($status->icon),
                    'is_default' => $status->is_default,
                    'is_terminal' => $status->is_terminal,
                    'sort_order' => $status->sort_order,
                ];
            });
        });
    }

    public static function defaultKey(string $context): ?string
    {
        $default = self::meta($context)->firstWhere('is_default', true);
        if ($default) {
            return $default['key'];
        }

        return self::meta($context)->first()['key'] ?? null;
    }

    public static function stateFor(string $context, ?string $statusKey): ?string
    {
        if (! $statusKey) {
            return null;
        }

        $status = self::meta($context)->firstWhere('key', $statusKey);

        return $status['state'] ?? null;
    }

    public static function transitions(string $context): array
    {
        $cacheKey = self::cacheKey('transitions', $context);

        return Cache::remember($cacheKey, 3600, function () use ($context) {
            $statuses = self::statuses($context);
            if ($statuses->isEmpty()) {
                return self::fallbackTransitions($context);
            }

            $statusIds = $statuses->pluck('id', 'key');
            if ($statusIds->isEmpty()) {
                return [];
            }

            $rows = CustomStatusTransition::query()
                ->whereIn('from_status_id', $statusIds->values())
                ->get();

            $map = [];
            foreach ($rows as $row) {
                $fromKey = $statuses->firstWhere('id', $row->from_status_id)?->key;
                $toKey = $statuses->firstWhere('id', $row->to_status_id)?->key;
                if (! $fromKey || ! $toKey) {
                    continue;
                }
                $map[$fromKey][] = $toKey;
            }

            return $map;
        });
    }

    public static function allowedTransitions(string $context, ?string $statusKey): array
    {
        $options = array_keys(self::statusOptions($context));
        if (! $options) {
            return [];
        }

        $transitions = self::transitions($context);
        if (! $transitions) {
            return $options;
        }

        if (! $statusKey) {
            return $options;
        }

        $allowed = $transitions[$statusKey] ?? [];
        $allowed[] = $statusKey;
        $allowed = array_unique($allowed);

        return array_values(array_intersect($options, $allowed));
    }

    public static function clearCache(): void
    {
        foreach ([self::CONTEXT_WORK_ORDER, self::CONTEXT_EQUIPMENT] as $context) {
            Cache::forget(self::cacheKey('statuses', $context));
            Cache::forget(self::cacheKey('meta', $context));
            Cache::forget(self::cacheKey('transitions', $context));
        }
    }

    public static function iconSvg(?string $iconKey): ?string
    {
        if (! $iconKey) {
            return null;
        }

        $icons = self::iconMap();

        return $icons[$iconKey] ?? null;
    }

    private static function cacheKey(string $prefix, string $context): string
    {
        return 'customization.' . $prefix . '.' . $context;
    }

    private static function fallbackStatuses(string $context): array
    {
        if ($context === self::CONTEXT_EQUIPMENT) {
            return [
                [
                    'key' => 'operational',
                    'label' => 'Operational',
                    'state' => 'operational',
                    'color' => '#DCFCE7',
                    'text_color' => '#166534',
                    'icon' => 'check',
                    'icon_svg' => self::iconSvg('check'),
                    'is_default' => true,
                    'is_terminal' => false,
                    'sort_order' => 10,
                ],
                [
                    'key' => 'needs_attention',
                    'label' => 'Needs Attention',
                    'state' => 'needs_attention',
                    'color' => '#FEF9C3',
                    'text_color' => '#A16207',
                    'icon' => 'alert',
                    'icon_svg' => self::iconSvg('alert'),
                    'is_default' => false,
                    'is_terminal' => false,
                    'sort_order' => 20,
                ],
                [
                    'key' => 'in_repair',
                    'label' => 'In Repair',
                    'state' => 'in_repair',
                    'color' => '#FFEDD5',
                    'text_color' => '#9A3412',
                    'icon' => 'tool',
                    'icon_svg' => self::iconSvg('tool'),
                    'is_default' => false,
                    'is_terminal' => false,
                    'sort_order' => 30,
                ],
                [
                    'key' => 'retired',
                    'label' => 'Retired',
                    'state' => 'retired',
                    'color' => '#F3F4F6',
                    'text_color' => '#4B5563',
                    'icon' => 'archive',
                    'icon_svg' => self::iconSvg('archive'),
                    'is_default' => false,
                    'is_terminal' => true,
                    'sort_order' => 40,
                ],
            ];
        }

        return [
            [
                'key' => 'submitted',
                'label' => 'Submitted',
                'state' => 'submitted',
                'color' => '#F3F4F6',
                'text_color' => '#374151',
                'icon' => 'clipboard',
                'icon_svg' => self::iconSvg('clipboard'),
                'is_default' => true,
                'is_terminal' => false,
                'sort_order' => 10,
            ],
            [
                'key' => 'assigned',
                'label' => 'Assigned',
                'state' => 'assigned',
                'color' => '#DBEAFE',
                'text_color' => '#1D4ED8',
                'icon' => 'user-check',
                'icon_svg' => self::iconSvg('user-check'),
                'is_default' => false,
                'is_terminal' => false,
                'sort_order' => 20,
            ],
            [
                'key' => 'in_progress',
                'label' => 'In Progress',
                'state' => 'in_progress',
                'color' => '#E0E7FF',
                'text_color' => '#4338CA',
                'icon' => 'progress',
                'icon_svg' => self::iconSvg('progress'),
                'is_default' => false,
                'is_terminal' => false,
                'sort_order' => 30,
            ],
            [
                'key' => 'on_hold',
                'label' => 'On Hold',
                'state' => 'on_hold',
                'color' => '#FEF9C3',
                'text_color' => '#A16207',
                'icon' => 'pause',
                'icon_svg' => self::iconSvg('pause'),
                'is_default' => false,
                'is_terminal' => false,
                'sort_order' => 40,
            ],
            [
                'key' => 'completed',
                'label' => 'Completed',
                'state' => 'completed',
                'color' => '#DCFCE7',
                'text_color' => '#166534',
                'icon' => 'check-circle',
                'icon_svg' => self::iconSvg('check-circle'),
                'is_default' => false,
                'is_terminal' => true,
                'sort_order' => 50,
            ],
            [
                'key' => 'closed',
                'label' => 'Closed',
                'state' => 'closed',
                'color' => '#DCFCE7',
                'text_color' => '#166534',
                'icon' => 'lock',
                'icon_svg' => self::iconSvg('lock'),
                'is_default' => false,
                'is_terminal' => true,
                'sort_order' => 60,
            ],
            [
                'key' => 'canceled',
                'label' => 'Canceled',
                'state' => 'canceled',
                'color' => '#FEE2E2',
                'text_color' => '#991B1B',
                'icon' => 'x-circle',
                'icon_svg' => self::iconSvg('x-circle'),
                'is_default' => false,
                'is_terminal' => true,
                'sort_order' => 70,
            ],
        ];
    }

    private static function fallbackTransitions(string $context): array
    {
        if ($context === self::CONTEXT_EQUIPMENT) {
            return [
                'operational' => ['needs_attention', 'in_repair', 'retired'],
                'needs_attention' => ['in_repair', 'operational', 'retired'],
                'in_repair' => ['operational', 'retired'],
            ];
        }

        return [
            'submitted' => ['assigned', 'canceled'],
            'assigned' => ['in_progress', 'on_hold', 'canceled'],
            'in_progress' => ['on_hold', 'completed', 'canceled'],
            'on_hold' => ['in_progress', 'canceled'],
            'completed' => ['closed'],
        ];
    }

    private static function iconMap(): array
    {
        return [
            'clipboard' => '<svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M6 2.75A1.75 1.75 0 0 1 7.75 1h4.5A1.75 1.75 0 0 1 14 2.75V4h1.25A1.75 1.75 0 0 1 17 5.75v10.5A1.75 1.75 0 0 1 15.25 18H4.75A1.75 1.75 0 0 1 3 16.25V5.75A1.75 1.75 0 0 1 4.75 4H6V2.75Zm1.5 1.25h5V2.75a.25.25 0 0 0-.25-.25h-4.5a.25.25 0 0 0-.25.25V4Z"/></svg>',
            'user-check' => '<svg class="h-3 w-3" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="7" cy="6" r="3"/><path d="M2.5 16c.7-3 3-4.5 5.5-4.5S12.8 13 13.5 16"/><path d="M12.5 9.5l1.5 1.5 3-3"/></svg>',
            'progress' => '<svg class="h-3 w-3" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M7 6l4 4-4 4"/></svg>',
            'pause' => '<svg class="h-3 w-3" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M7 6v8M13 6v8"/></svg>',
            'check-circle' => '<svg class="h-3 w-3" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="10" cy="10" r="7"/><path d="M7 10.5l2 2 4-4"/></svg>',
            'lock' => '<svg class="h-3 w-3" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><rect x="5" y="9" width="10" height="7" rx="1.5"/><path d="M7 9V7a3 3 0 0 1 6 0v2"/></svg>',
            'x-circle' => '<svg class="h-3 w-3" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="10" cy="10" r="7"/><path d="M7.5 7.5l5 5M12.5 7.5l-5 5"/></svg>',
            'check' => '<svg class="h-3 w-3" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M6 10l2.5 2.5L14 7"/></svg>',
            'alert' => '<svg class="h-3 w-3" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M10 4l6 11H4L10 4Z"/><path d="M10 8v3M10 14h.01"/></svg>',
            'tool' => '<svg class="h-3 w-3" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M12 5a3 3 0 0 0-4 4l-4 4 2 2 4-4a3 3 0 0 0 4-4Z"/></svg>',
            'archive' => '<svg class="h-3 w-3" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><rect x="4" y="6" width="12" height="9" rx="1.5"/><path d="M7 6V4h6v2M8 10h4"/></svg>',
        ];
    }
}
