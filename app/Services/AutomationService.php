<?php

namespace App\Services;

use App\Models\AutomationRule;
use App\Models\WorkOrder;
use App\Models\WorkOrderEvent;
use App\Services\AuditLogger;

class AutomationService
{
    public function runForWorkOrder(string $trigger, WorkOrder $workOrder, array $context = []): void
    {
        $rules = AutomationRule::query()
            ->where('is_active', true)
            ->where('trigger', $trigger)
            ->get();

        if ($rules->isEmpty()) {
            return;
        }

        foreach ($rules as $rule) {
            if (! $this->conditionsPass($rule->conditions ?? [], $workOrder, $context)) {
                continue;
            }

            $this->applyActions($rule->actions ?? [], $workOrder, $rule->name);
        }
    }

    private function conditionsPass(array $conditions, WorkOrder $workOrder, array $context): bool
    {
        if ($conditions === []) {
            return true;
        }

        foreach ($conditions as $condition) {
            $field = $condition['field'] ?? null;
            $operator = strtolower($condition['operator'] ?? '=');
            $expected = $condition['value'] ?? null;

            if (! $field) {
                continue;
            }

            $actual = data_get($workOrder, $field, $context[$field] ?? null);

            $match = match ($operator) {
                '=', '==' => $actual == $expected,
                '!=' => $actual != $expected,
                '>' => $actual > $expected,
                '>=' => $actual >= $expected,
                '<' => $actual < $expected,
                '<=' => $actual <= $expected,
                'in' => in_array($actual, (array) $expected, true),
                'contains' => is_string($actual) && is_string($expected) && str_contains($actual, $expected),
                default => $actual == $expected,
            };

            if (! $match) {
                return false;
            }
        }

        return true;
    }

    private function applyActions(array $actions, WorkOrder $workOrder, string $ruleName): void
    {
        if ($actions === []) {
            $this->recordAutomationEvent($workOrder, $ruleName, 'Automation rule matched.');
            return;
        }

        foreach ($actions as $action) {
            $type = $action['type'] ?? 'notify';
            $note = $action['message'] ?? $action['note'] ?? 'Automation action executed.';

            $this->recordAutomationEvent($workOrder, $ruleName, $note, [
                'action' => $type,
                'action_meta' => $action,
            ]);
        }
    }

    private function recordAutomationEvent(WorkOrder $workOrder, string $ruleName, string $note, array $meta = []): void
    {
        WorkOrderEvent::create([
            'work_order_id' => $workOrder->id,
            'user_id' => auth()->id(),
            'type' => 'automation',
            'note' => $note,
            'meta' => array_merge(['rule' => $ruleName], $meta),
        ]);

        app(AuditLogger::class)->log(
            'automation.triggered',
            $workOrder,
            "Automation rule triggered: {$ruleName}",
            ['note' => $note, 'rule' => $ruleName]
        );
    }
}
