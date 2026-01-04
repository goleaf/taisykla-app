<?php

namespace App\Services\Billing;

use App\Models\Organization;
use App\Models\OrganizationTaxJurisdiction;
use App\Models\TaxExemption;
use App\Models\TaxJurisdiction;
use App\Models\TaxRule;
use Carbon\Carbon;

class TaxEngine
{
    public function resolveTaxRate(Organization $organization, array $item): float
    {
        if ($this->isTaxExempt($organization)) {
            return 0.0;
        }

        $jurisdictionIds = OrganizationTaxJurisdiction::query()
            ->where('organization_id', $organization->id)
            ->orderByDesc('is_default')
            ->orderBy('priority')
            ->pluck('tax_jurisdiction_id')
            ->all();

        $jurisdictions = TaxJurisdiction::query()
            ->where('is_active', true)
            ->when($jurisdictionIds !== [], fn ($query) => $query->whereIn('id', $jurisdictionIds))
            ->orderBy('priority')
            ->get();

        if ($jurisdictions->isEmpty()) {
            return 0.0;
        }

        $appliesTo = $item['item_type'] ?? 'service';
        $serviceType = $item['service_type'] ?? null;
        $partCategoryId = $item['part_category_id'] ?? null;
        $feeType = $item['fee_type'] ?? null;

        $rules = TaxRule::query()
            ->whereIn('tax_jurisdiction_id', $jurisdictions->pluck('id')->all())
            ->where('is_active', true)
            ->where(function ($query) use ($appliesTo) {
                $query->where('applies_to', 'all')->orWhere('applies_to', $appliesTo);
            })
            ->get();

        $rate = 0.0;
        foreach ($jurisdictions as $jurisdiction) {
            $rule = $rules->first(function (TaxRule $rule) use ($jurisdiction, $serviceType, $partCategoryId, $feeType) {
                if ($rule->tax_jurisdiction_id !== $jurisdiction->id) {
                    return false;
                }

                if ($rule->service_type && $serviceType && $rule->service_type !== $serviceType) {
                    return false;
                }

                if ($rule->part_category_id && $partCategoryId && $rule->part_category_id !== $partCategoryId) {
                    return false;
                }

                if ($rule->fee_type && $feeType && $rule->fee_type !== $feeType) {
                    return false;
                }

                return true;
            });

            if ($rule && ! $rule->is_taxable) {
                continue;
            }

            $rate += (float) $jurisdiction->rate;
        }

        return round($rate, 4);
    }

    private function isTaxExempt(Organization $organization): bool
    {
        if ($organization->is_tax_exempt) {
            return true;
        }

        $validExemption = TaxExemption::query()
            ->where('organization_id', $organization->id)
            ->where(function ($query) {
                $query->whereNull('valid_until')
                    ->orWhere('valid_until', '>=', Carbon::today()->toDateString());
            })
            ->exists();

        return $validExemption;
    }
}
