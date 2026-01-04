<?php

namespace App\Services\Billing;

use App\Models\LaborRateOverride;
use App\Models\LaborRateRule;
use App\Models\LaborRateTier;
use App\Models\Organization;
use App\Models\Part;
use App\Models\PartPricingRule;
use Carbon\Carbon;

class PricingEngine
{
    public function resolveLaborRate(Organization $organization, ?string $serviceType = null, ?Carbon $when = null, ?string $timeCategory = null): array
    {
        $when = $when ?? now();
        [$resolvedTimeCategory, $dayType] = $this->resolveTimeContext($when, $timeCategory);

        $override = LaborRateOverride::query()
            ->where('organization_id', $organization->id)
            ->where('is_active', true)
            ->when($serviceType, function ($query) use ($serviceType) {
                $query->where(function ($inner) use ($serviceType) {
                    $inner->whereNull('service_type')->orWhere('service_type', $serviceType);
                });
            })
            ->where('time_category', $resolvedTimeCategory)
            ->where(function ($query) use ($when) {
                $query->whereNull('valid_from')->orWhere('valid_from', '<=', $when->toDateString());
            })
            ->where(function ($query) use ($when) {
                $query->whereNull('valid_until')->orWhere('valid_until', '>=', $when->toDateString());
            })
            ->get()
            ->sortByDesc(fn ($item) => $item->service_type !== null)
            ->first();

        $tier = $organization->defaultLaborRateTier
            ?? LaborRateTier::query()->where('is_active', true)->orderBy('id')->first();

        $baseRate = $tier?->base_rate ?? (float) config('billing.default_labor_rate', 100);
        $currency = $tier?->currency ?? ($organization->billing_currency ?? config('billing.default_currency', 'USD'));

        if ($override) {
            $rate = $override->fixed_rate ?? ($baseRate * (float) $override->multiplier);

            return [
                'rate' => round((float) $rate, 2),
                'currency' => $currency,
                'source' => 'override',
                'context' => [
                    'time_category' => $resolvedTimeCategory,
                    'day_type' => $dayType,
                ],
            ];
        }

        $rule = null;
        if ($tier) {
            $rule = LaborRateRule::query()
                ->where('rate_tier_id', $tier->id)
                ->where('is_active', true)
                ->where('time_category', $resolvedTimeCategory)
                ->where(function ($query) use ($serviceType) {
                    $query->whereNull('service_type')
                        ->when($serviceType, fn ($inner) => $inner->orWhere('service_type', $serviceType));
                })
                ->where(function ($query) use ($dayType) {
                    $query->whereNull('day_type')->orWhere('day_type', $dayType);
                })
                ->get()
                ->filter(function ($item) use ($when) {
                    if (! $item->start_time || ! $item->end_time) {
                        return true;
                    }
                    $start = Carbon::parse($item->start_time);
                    $end = Carbon::parse($item->end_time);
                    $current = Carbon::parse($when->format('H:i:s'));

                    if ($start->lessThanOrEqualTo($end)) {
                        return $current->betweenIncluded($start, $end);
                    }

                    return $current->greaterThanOrEqualTo($start) || $current->lessThanOrEqualTo($end);
                })
                ->sortByDesc(fn ($item) => $item->service_type !== null)
                ->sortByDesc(fn ($item) => $item->day_type !== null)
                ->first();
        }

        $rate = $baseRate;
        if ($rule) {
            $rate = $rule->fixed_rate ?? ($baseRate * (float) $rule->multiplier);
            $rate += (float) $rule->emergency_premium;
        }

        return [
            'rate' => round((float) $rate, 2),
            'currency' => $currency,
            'source' => $rule ? 'rule' : 'tier',
            'context' => [
                'time_category' => $resolvedTimeCategory,
                'day_type' => $dayType,
            ],
        ];
    }

    public function resolvePartPrice(Organization $organization, Part $part, int $quantity = 1): array
    {
        $rule = PartPricingRule::query()
            ->where('is_active', true)
            ->where(function ($query) use ($organization) {
                $query->whereNull('organization_id')
                    ->orWhere('organization_id', $organization->id);
            })
            ->where(function ($query) use ($part) {
                $query->whereNull('part_id')
                    ->orWhere('part_id', $part->id);
            })
            ->where(function ($query) use ($part) {
                $query->whereNull('part_category_id')
                    ->orWhere('part_category_id', $part->part_category_id ?? null);
            })
            ->get()
            ->sortByDesc(fn ($item) => $item->part_id !== null)
            ->sortByDesc(fn ($item) => $item->part_category_id !== null)
            ->sortByDesc(fn ($item) => $item->organization_id !== null)
            ->first();

        $baseCost = (float) $part->unit_cost;
        $price = (float) $part->unit_price;

        if ($rule) {
            if ($rule->fixed_price !== null) {
                $price = (float) $rule->fixed_price;
            } elseif ($rule->markup_type === 'fixed') {
                $price = $baseCost + (float) $rule->markup_value;
            } else {
                $price = $baseCost * (1 + ((float) $rule->markup_value / 100));
            }
        }

        if ($price <= 0 && $baseCost > 0) {
            $price = $baseCost * (1 + (float) config('billing.default_parts_markup', 0.25));
        }

        return [
            'unit_price' => round($price, 2),
            'currency' => $organization->billing_currency ?? config('billing.default_currency', 'USD'),
            'rule' => $rule,
            'quantity' => $quantity,
        ];
    }

    private function resolveTimeContext(Carbon $when, ?string $timeCategory = null): array
    {
        if ($timeCategory) {
            return [$timeCategory, $this->dayType($when)];
        }

        $hour = (int) $when->format('G');
        $isAfterHours = $hour < 8 || $hour >= 18;

        if ($this->dayType($when) === 'weekend') {
            return ['weekend', 'weekend'];
        }

        return [$isAfterHours ? 'after_hours' : 'regular', $this->dayType($when)];
    }

    private function dayType(Carbon $when): string
    {
        return $when->isWeekend() ? 'weekend' : 'weekday';
    }
}
