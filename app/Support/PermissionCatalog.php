<?php

namespace App\Support;

final class PermissionCatalog
{
    public const DASHBOARD_VIEW = 'dashboard.view';

    public const WORK_ORDERS_VIEW = 'work_orders.view';
    public const WORK_ORDERS_VIEW_ALL = 'work_orders.view_all';
    public const WORK_ORDERS_VIEW_ASSIGNED = 'work_orders.view_assigned';
    public const WORK_ORDERS_VIEW_ORG = 'work_orders.view_org';
    public const WORK_ORDERS_VIEW_OWN = 'work_orders.view_own';
    public const WORK_ORDERS_CREATE = 'work_orders.create';
    public const WORK_ORDERS_UPDATE = 'work_orders.update';
    public const WORK_ORDERS_ASSIGN = 'work_orders.assign';
    public const WORK_ORDERS_REPORT = 'work_orders.manage_report';
    public const WORK_ORDERS_ARRIVE = 'work_orders.mark_arrived';
    public const WORK_ORDERS_NOTE = 'work_orders.add_note';
    public const WORK_ORDERS_SIGNOFF = 'work_orders.signoff';
    public const WORK_ORDERS_FEEDBACK = 'work_orders.feedback';

    public const EQUIPMENT_VIEW = 'equipment.view';
    public const EQUIPMENT_VIEW_ALL = 'equipment.view_all';
    public const EQUIPMENT_VIEW_ORG = 'equipment.view_org';
    public const EQUIPMENT_VIEW_OWN = 'equipment.view_own';
    public const EQUIPMENT_MANAGE = 'equipment.manage';

    public const SCHEDULE_VIEW = 'schedule.view';
    public const SCHEDULE_VIEW_ALL = 'schedule.view_all';
    public const SCHEDULE_VIEW_ASSIGNED = 'schedule.view_assigned';
    public const SCHEDULE_VIEW_ORG = 'schedule.view_org';
    public const SCHEDULE_VIEW_OWN = 'schedule.view_own';
    public const SCHEDULE_MANAGE = 'schedule.manage';

    public const INVENTORY_VIEW = 'inventory.view';
    public const INVENTORY_MANAGE = 'inventory.manage';

    public const CLIENTS_VIEW = 'clients.view';
    public const CLIENTS_MANAGE = 'clients.manage';

    public const MESSAGES_VIEW = 'messages.view';
    public const MESSAGES_SEND = 'messages.send';

    public const REPORTS_VIEW = 'reports.view';
    public const REPORTS_MANAGE = 'reports.manage';
    public const REPORTS_EXPORT = 'reports.export';

    public const BILLING_VIEW = 'billing.view';
    public const BILLING_VIEW_ALL = 'billing.view_all';
    public const BILLING_VIEW_ORG = 'billing.view_org';
    public const BILLING_VIEW_OWN = 'billing.view_own';
    public const BILLING_MANAGE = 'billing.manage';

    public const KNOWLEDGE_BASE_VIEW = 'knowledge_base.view';
    public const KNOWLEDGE_BASE_MANAGE = 'knowledge_base.manage';

    public const SUPPORT_VIEW = 'support.view';
    public const SUPPORT_VIEW_ALL = 'support.view_all';
    public const SUPPORT_VIEW_ORG = 'support.view_org';
    public const SUPPORT_VIEW_OWN = 'support.view_own';
    public const SUPPORT_CREATE = 'support.create';
    public const SUPPORT_MANAGE = 'support.manage';
    public const SUPPORT_ASSIGN = 'support.assign';

    // Service Requests
    public const SERVICE_REQUESTS_VIEW = 'service_requests.view';
    public const SERVICE_REQUESTS_VIEW_ALL = 'service_requests.view_all';
    public const SERVICE_REQUESTS_VIEW_OWN = 'service_requests.view_own';
    public const SERVICE_REQUESTS_VIEW_ORG = 'service_requests.view_org';
    public const SERVICE_REQUESTS_CREATE = 'service_requests.create';
    public const SERVICE_REQUESTS_UPDATE = 'service_requests.update';
    public const SERVICE_REQUESTS_UPDATE_OWN = 'service_requests.update_own';
    public const SERVICE_REQUESTS_DELETE = 'service_requests.delete';
    public const SERVICE_REQUESTS_ASSIGN = 'service_requests.assign';
    public const SERVICE_REQUESTS_APPROVE = 'service_requests.approve';

    // Technicians
    public const TECHNICIANS_VIEW = 'technicians.view';
    public const TECHNICIANS_CREATE = 'technicians.create';
    public const TECHNICIANS_UPDATE = 'technicians.update';
    public const TECHNICIANS_DELETE = 'technicians.delete';
    public const TECHNICIANS_VIEW_SCHEDULE = 'technicians.view_schedule';
    public const TECHNICIANS_IMPERSONATE = 'technicians.impersonate';

    // Customers
    public const CUSTOMERS_VIEW = 'customers.view';
    public const CUSTOMERS_CREATE = 'customers.create';
    public const CUSTOMERS_UPDATE = 'customers.update';
    public const CUSTOMERS_DELETE = 'customers.delete';
    public const CUSTOMERS_IMPERSONATE = 'customers.impersonate';

    public const SETTINGS_VIEW = 'settings.view';
    public const SETTINGS_MANAGE = 'settings.manage';
    public const USERS_MANAGE = 'users.manage';

    public static function all(): array
    {
        return array_values(array_unique(array_merge(
            self::modulePermissions(),
            self::workOrderPermissions(),
            self::workOrderVisibilityPermissions(),
            self::equipmentPermissions(),
            self::equipmentVisibilityPermissions(),
            self::schedulePermissions(),
            self::scheduleVisibilityPermissions(),
            self::inventoryPermissions(),
            self::clientPermissions(),
            self::messagePermissions(),
            self::reportPermissions(),
            self::billingPermissions(),
            self::billingVisibilityPermissions(),
            self::knowledgeBasePermissions(),
            self::supportPermissions(),
            self::supportVisibilityPermissions(),
            self::serviceRequestPermissions(),
            self::serviceRequestVisibilityPermissions(),
            self::technicianPermissions(),
            self::customerPermissions(),
            self::settingsPermissions(),
        )));
    }

    public static function rolePermissions(): array
    {
        return [
            RoleCatalog::ADMIN => self::all(),
            RoleCatalog::OPERATIONS_MANAGER => [
                self::INVENTORY_MANAGE,
                self::CLIENTS_MANAGE,
                self::EQUIPMENT_MANAGE,
                self::WORK_ORDERS_REPORT,
                self::WORK_ORDERS_ARRIVE,
                self::REPORTS_MANAGE,
                self::REPORTS_EXPORT,
                self::BILLING_VIEW,
                self::BILLING_VIEW_ALL,
                self::BILLING_MANAGE,
                self::KNOWLEDGE_BASE_MANAGE,
                    // Service Requests
                self::SERVICE_REQUESTS_VIEW,
                self::SERVICE_REQUESTS_VIEW_ALL,
                self::SERVICE_REQUESTS_CREATE,
                self::SERVICE_REQUESTS_UPDATE,
                self::SERVICE_REQUESTS_ASSIGN,
                self::SERVICE_REQUESTS_APPROVE,
                self::SERVICE_REQUESTS_DELETE,
                    // Technicians & Customers
                self::TECHNICIANS_VIEW,
                self::TECHNICIANS_VIEW_SCHEDULE,
                self::CUSTOMERS_VIEW,
            ],
            RoleCatalog::DISPATCH => [
                self::DASHBOARD_VIEW,
                self::WORK_ORDERS_VIEW,
                self::WORK_ORDERS_VIEW_ALL,
                self::WORK_ORDERS_CREATE,
                self::WORK_ORDERS_UPDATE,
                self::WORK_ORDERS_ASSIGN,
                self::WORK_ORDERS_NOTE,
                self::EQUIPMENT_VIEW,
                self::EQUIPMENT_VIEW_ALL,
                self::SCHEDULE_VIEW,
                self::SCHEDULE_VIEW_ALL,
                self::SCHEDULE_MANAGE,
                self::INVENTORY_VIEW,
                self::CLIENTS_VIEW,
                self::MESSAGES_VIEW,
                self::MESSAGES_SEND,
                self::REPORTS_VIEW,
                self::SUPPORT_VIEW,
                self::SUPPORT_VIEW_ALL,
                self::SUPPORT_MANAGE,
                self::SUPPORT_ASSIGN,
                self::SUPPORT_CREATE,
                self::KNOWLEDGE_BASE_VIEW,
                    // Service Requests
                self::SERVICE_REQUESTS_VIEW,
                self::SERVICE_REQUESTS_VIEW_ALL,
                self::SERVICE_REQUESTS_CREATE,
                self::SERVICE_REQUESTS_UPDATE,
                self::SERVICE_REQUESTS_ASSIGN,
                    // Technicians
                self::TECHNICIANS_VIEW,
                self::TECHNICIANS_VIEW_SCHEDULE,
            ],
            RoleCatalog::TECHNICIAN => [
                self::DASHBOARD_VIEW,
                self::WORK_ORDERS_VIEW,
                self::WORK_ORDERS_VIEW_ASSIGNED,
                self::WORK_ORDERS_UPDATE,
                self::WORK_ORDERS_REPORT,
                self::WORK_ORDERS_NOTE,
                self::WORK_ORDERS_ARRIVE,
                self::EQUIPMENT_VIEW,
                self::EQUIPMENT_VIEW_ALL,
                self::SCHEDULE_VIEW,
                self::SCHEDULE_VIEW_ASSIGNED,
                self::INVENTORY_VIEW,
                self::MESSAGES_VIEW,
                self::MESSAGES_SEND,
                self::SUPPORT_VIEW,
                self::SUPPORT_VIEW_OWN,
                self::SUPPORT_CREATE,
                self::KNOWLEDGE_BASE_VIEW,
                    // Service Requests
                self::SERVICE_REQUESTS_VIEW,
                self::SERVICE_REQUESTS_VIEW_OWN,
                self::SERVICE_REQUESTS_UPDATE_OWN,
            ],
            RoleCatalog::INVENTORY_SPECIALIST => [
                self::DASHBOARD_VIEW,
                self::INVENTORY_VIEW,
                self::INVENTORY_MANAGE,
                self::WORK_ORDERS_VIEW,
                self::WORK_ORDERS_VIEW_ALL,
                self::WORK_ORDERS_NOTE,
                self::EQUIPMENT_VIEW,
                self::EQUIPMENT_VIEW_ALL,
                self::MESSAGES_VIEW,
                self::MESSAGES_SEND,
                self::REPORTS_VIEW,
                self::KNOWLEDGE_BASE_VIEW,
            ],
            RoleCatalog::QA_MANAGER => [
                self::REPORTS_MANAGE,
            ],
            RoleCatalog::BILLING_SPECIALIST => [
                self::DASHBOARD_VIEW,
                self::BILLING_VIEW,
                self::BILLING_VIEW_ALL,
                self::BILLING_MANAGE,
                self::REPORTS_VIEW,
                self::REPORTS_EXPORT,
                self::WORK_ORDERS_VIEW,
                self::WORK_ORDERS_VIEW_ALL,
                self::CLIENTS_VIEW,
                self::MESSAGES_VIEW,
                self::MESSAGES_SEND,
                self::KNOWLEDGE_BASE_VIEW,
            ],
            RoleCatalog::SUPPORT => [
                self::DASHBOARD_VIEW,
                self::SUPPORT_VIEW,
                self::SUPPORT_VIEW_ALL,
                self::SUPPORT_MANAGE,
                self::SUPPORT_ASSIGN,
                self::SUPPORT_CREATE,
                self::WORK_ORDERS_VIEW,
                self::WORK_ORDERS_VIEW_ALL,
                self::WORK_ORDERS_NOTE,
                self::CLIENTS_VIEW,
                self::EQUIPMENT_VIEW,
                self::EQUIPMENT_VIEW_ALL,
                self::MESSAGES_VIEW,
                self::MESSAGES_SEND,
                self::KNOWLEDGE_BASE_VIEW,
            ],
            RoleCatalog::BUSINESS_ADMIN => [
                self::BILLING_VIEW,
                self::BILLING_VIEW_ORG,
            ],
            RoleCatalog::BUSINESS_USER => [],
            RoleCatalog::CLIENT => [
                self::DASHBOARD_VIEW,
                self::WORK_ORDERS_VIEW,
                self::WORK_ORDERS_VIEW_ORG,
                self::WORK_ORDERS_CREATE,
                self::WORK_ORDERS_SIGNOFF,
                self::WORK_ORDERS_FEEDBACK,
                self::EQUIPMENT_VIEW,
                self::EQUIPMENT_VIEW_ORG,
                self::SCHEDULE_VIEW,
                self::SCHEDULE_VIEW_ORG,
                self::SUPPORT_VIEW,
                self::SUPPORT_VIEW_ORG,
                self::SUPPORT_CREATE,
                self::MESSAGES_VIEW,
                self::MESSAGES_SEND,
                self::KNOWLEDGE_BASE_VIEW,
            ],
            RoleCatalog::CONSUMER => [
                self::DASHBOARD_VIEW,
                self::WORK_ORDERS_VIEW,
                self::WORK_ORDERS_VIEW_OWN,
                self::WORK_ORDERS_CREATE,
                self::WORK_ORDERS_SIGNOFF,
                self::WORK_ORDERS_FEEDBACK,
                self::EQUIPMENT_VIEW,
                self::EQUIPMENT_VIEW_OWN,
                self::SCHEDULE_VIEW,
                self::SCHEDULE_VIEW_OWN,
                self::BILLING_VIEW,
                self::BILLING_VIEW_OWN,
                self::SUPPORT_VIEW,
                self::SUPPORT_VIEW_OWN,
                self::SUPPORT_CREATE,
                self::MESSAGES_VIEW,
                self::MESSAGES_SEND,
                self::KNOWLEDGE_BASE_VIEW,
            ],
            RoleCatalog::GUEST => [
                self::DASHBOARD_VIEW,
                self::KNOWLEDGE_BASE_VIEW,
            ],
        ];
    }

    public static function roleInheritance(): array
    {
        return [
            RoleCatalog::OPERATIONS_MANAGER => [RoleCatalog::DISPATCH],
            RoleCatalog::QA_MANAGER => [RoleCatalog::SUPPORT],
            RoleCatalog::BUSINESS_ADMIN => [RoleCatalog::BUSINESS_USER],
            RoleCatalog::BUSINESS_USER => [RoleCatalog::CLIENT],
        ];
    }

    public static function permissionsForRole(string $role): array
    {
        $visited = [];

        return array_values(array_unique(self::resolveRolePermissions($role, $visited)));
    }

    private static function resolveRolePermissions(string $role, array &$visited): array
    {
        if (isset($visited[$role])) {
            return [];
        }

        $visited[$role] = true;
        $permissions = self::rolePermissions()[$role] ?? [];

        foreach (self::roleInheritance()[$role] ?? [] as $parentRole) {
            $permissions = array_merge($permissions, self::resolveRolePermissions($parentRole, $visited));
        }

        return $permissions;
    }

    private static function modulePermissions(): array
    {
        return [
            self::DASHBOARD_VIEW,
            self::WORK_ORDERS_VIEW,
            self::EQUIPMENT_VIEW,
            self::SCHEDULE_VIEW,
            self::INVENTORY_VIEW,
            self::CLIENTS_VIEW,
            self::MESSAGES_VIEW,
            self::REPORTS_VIEW,
            self::BILLING_VIEW,
            self::KNOWLEDGE_BASE_VIEW,
            self::SUPPORT_VIEW,
            self::SETTINGS_VIEW,
        ];
    }

    private static function workOrderPermissions(): array
    {
        return [
            self::WORK_ORDERS_CREATE,
            self::WORK_ORDERS_UPDATE,
            self::WORK_ORDERS_ASSIGN,
            self::WORK_ORDERS_REPORT,
            self::WORK_ORDERS_ARRIVE,
            self::WORK_ORDERS_NOTE,
            self::WORK_ORDERS_SIGNOFF,
            self::WORK_ORDERS_FEEDBACK,
        ];
    }

    private static function workOrderVisibilityPermissions(): array
    {
        return [
            self::WORK_ORDERS_VIEW_ALL,
            self::WORK_ORDERS_VIEW_ASSIGNED,
            self::WORK_ORDERS_VIEW_ORG,
            self::WORK_ORDERS_VIEW_OWN,
        ];
    }

    private static function equipmentPermissions(): array
    {
        return [self::EQUIPMENT_MANAGE];
    }

    private static function equipmentVisibilityPermissions(): array
    {
        return [
            self::EQUIPMENT_VIEW_ALL,
            self::EQUIPMENT_VIEW_ORG,
            self::EQUIPMENT_VIEW_OWN,
        ];
    }

    private static function schedulePermissions(): array
    {
        return [self::SCHEDULE_MANAGE];
    }

    private static function scheduleVisibilityPermissions(): array
    {
        return [
            self::SCHEDULE_VIEW_ALL,
            self::SCHEDULE_VIEW_ASSIGNED,
            self::SCHEDULE_VIEW_ORG,
            self::SCHEDULE_VIEW_OWN,
        ];
    }

    private static function inventoryPermissions(): array
    {
        return [self::INVENTORY_MANAGE];
    }

    private static function clientPermissions(): array
    {
        return [self::CLIENTS_MANAGE];
    }

    private static function messagePermissions(): array
    {
        return [self::MESSAGES_SEND];
    }

    private static function reportPermissions(): array
    {
        return [self::REPORTS_MANAGE, self::REPORTS_EXPORT];
    }

    private static function billingPermissions(): array
    {
        return [self::BILLING_MANAGE];
    }

    private static function billingVisibilityPermissions(): array
    {
        return [
            self::BILLING_VIEW_ALL,
            self::BILLING_VIEW_ORG,
            self::BILLING_VIEW_OWN,
        ];
    }

    private static function knowledgeBasePermissions(): array
    {
        return [self::KNOWLEDGE_BASE_MANAGE];
    }

    private static function supportPermissions(): array
    {
        return [self::SUPPORT_CREATE, self::SUPPORT_MANAGE, self::SUPPORT_ASSIGN];
    }

    private static function supportVisibilityPermissions(): array
    {
        return [
            self::SUPPORT_VIEW_ALL,
            self::SUPPORT_VIEW_ORG,
            self::SUPPORT_VIEW_OWN,
        ];
    }

    private static function settingsPermissions(): array
    {
        return [
            self::SETTINGS_MANAGE,
            self::USERS_MANAGE,
        ];
    }

    private static function serviceRequestPermissions(): array
    {
        return [
            self::SERVICE_REQUESTS_VIEW,
            self::SERVICE_REQUESTS_CREATE,
            self::SERVICE_REQUESTS_UPDATE,
            self::SERVICE_REQUESTS_UPDATE_OWN,
            self::SERVICE_REQUESTS_DELETE,
            self::SERVICE_REQUESTS_ASSIGN,
            self::SERVICE_REQUESTS_APPROVE,
        ];
    }

    private static function serviceRequestVisibilityPermissions(): array
    {
        return [
            self::SERVICE_REQUESTS_VIEW_ALL,
            self::SERVICE_REQUESTS_VIEW_OWN,
            self::SERVICE_REQUESTS_VIEW_ORG,
        ];
    }

    private static function technicianPermissions(): array
    {
        return [
            self::TECHNICIANS_VIEW,
            self::TECHNICIANS_CREATE,
            self::TECHNICIANS_UPDATE,
            self::TECHNICIANS_DELETE,
            self::TECHNICIANS_VIEW_SCHEDULE,
            self::TECHNICIANS_IMPERSONATE,
        ];
    }

    private static function customerPermissions(): array
    {
        return [
            self::CUSTOMERS_VIEW,
            self::CUSTOMERS_CREATE,
            self::CUSTOMERS_UPDATE,
            self::CUSTOMERS_DELETE,
            self::CUSTOMERS_IMPERSONATE,
        ];
    }
}
