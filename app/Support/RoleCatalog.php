<?php

namespace App\Support;

use Illuminate\Support\Str;

final class RoleCatalog
{
    public const ADMIN = 'admin';
    public const OPERATIONS_MANAGER = 'operations_manager';
    public const DISPATCH = 'dispatch';
    public const TECHNICIAN = 'technician';
    public const INVENTORY_SPECIALIST = 'inventory_specialist';
    public const QA_MANAGER = 'qa_manager';
    public const BILLING_SPECIALIST = 'billing_specialist';
    public const SUPPORT = 'support';
    public const BUSINESS_ADMIN = 'business_admin';
    public const BUSINESS_USER = 'business_user';
    public const CLIENT = 'client';
    public const CONSUMER = 'consumer';
    public const GUEST = 'guest';

    public static function all(): array
    {
        return [
            self::ADMIN,
            self::OPERATIONS_MANAGER,
            self::DISPATCH,
            self::TECHNICIAN,
            self::INVENTORY_SPECIALIST,
            self::QA_MANAGER,
            self::BILLING_SPECIALIST,
            self::SUPPORT,
            self::BUSINESS_ADMIN,
            self::BUSINESS_USER,
            self::CLIENT,
            self::CONSUMER,
            self::GUEST,
        ];
    }

    public static function labels(): array
    {
        return [
            self::ADMIN => 'System Administrator',
            self::OPERATIONS_MANAGER => 'Operations Manager',
            self::DISPATCH => 'Dispatch Coordinator',
            self::TECHNICIAN => 'Field Technician',
            self::INVENTORY_SPECIALIST => 'Parts / Inventory Specialist',
            self::QA_MANAGER => 'Quality Assurance Manager',
            self::BILLING_SPECIALIST => 'Financial / Billing Specialist',
            self::SUPPORT => 'Support Staff',
            self::BUSINESS_ADMIN => 'Business Account Administrator',
            self::BUSINESS_USER => 'Standard Business User',
            self::CLIENT => 'Business User (Legacy)',
            self::CONSUMER => 'Individual Consumer',
            self::GUEST => 'Read-only / Guest',
        ];
    }

    public static function label(string $role): string
    {
        return static::labels()[$role] ?? Str::title(str_replace('_', ' ', $role));
    }

    public static function operationsRoles(): array
    {
        return [
            self::ADMIN,
            self::OPERATIONS_MANAGER,
            self::DISPATCH,
        ];
    }

    public static function staffRoles(): array
    {
        return array_values(array_unique(array_merge(
            self::operationsRoles(),
            [
                self::TECHNICIAN,
                self::INVENTORY_SPECIALIST,
                self::QA_MANAGER,
                self::BILLING_SPECIALIST,
                self::SUPPORT,
            ]
        )));
    }

    public static function businessCustomerRoles(): array
    {
        return [
            self::BUSINESS_ADMIN,
            self::BUSINESS_USER,
            self::CLIENT,
        ];
    }

    public static function customerRoles(): array
    {
        return array_values(array_unique(array_merge(
            self::businessCustomerRoles(),
            [self::CONSUMER]
        )));
    }

    public static function scheduleManagers(): array
    {
        return self::operationsRoles();
    }

    public static function scheduleViewers(): array
    {
        return array_values(array_unique(array_merge(
            self::operationsRoles(),
            [self::TECHNICIAN]
        )));
    }

    public static function inventoryAccessRoles(): array
    {
        return array_values(array_unique(array_merge(
            self::operationsRoles(),
            [
                self::TECHNICIAN,
                self::SUPPORT,
                self::INVENTORY_SPECIALIST,
            ]
        )));
    }

    public static function reportsAccessRoles(): array
    {
        return array_values(array_unique(array_merge(
            self::operationsRoles(),
            [
                self::SUPPORT,
                self::QA_MANAGER,
                self::BILLING_SPECIALIST,
            ]
        )));
    }

    public static function clientAccessRoles(): array
    {
        return array_values(array_unique(array_merge(
            self::operationsRoles(),
            [self::SUPPORT]
        )));
    }

    public static function billingManageRoles(): array
    {
        return array_values(array_unique(array_merge(
            self::operationsRoles(),
            [self::BILLING_SPECIALIST]
        )));
    }

    public static function supportManageRoles(): array
    {
        return array_values(array_unique(array_merge(
            self::operationsRoles(),
            [
                self::SUPPORT,
                self::QA_MANAGER,
            ]
        )));
    }

    public static function knowledgeBaseManageRoles(): array
    {
        return array_values(array_unique(array_merge(
            self::operationsRoles(),
            [
                self::TECHNICIAN,
                self::SUPPORT,
                self::QA_MANAGER,
            ]
        )));
    }

    public static function workOrderUpdateRoles(): array
    {
        return array_values(array_unique(array_merge(
            self::operationsRoles(),
            [self::TECHNICIAN]
        )));
    }

    public static function workOrderAssignRoles(): array
    {
        return self::operationsRoles();
    }

    public static function workOrderViewRoles(): array
    {
        return array_values(array_unique(array_merge(
            self::operationsRoles(),
            [
                self::SUPPORT,
                self::QA_MANAGER,
                self::INVENTORY_SPECIALIST,
                self::BILLING_SPECIALIST,
            ]
        )));
    }
}
