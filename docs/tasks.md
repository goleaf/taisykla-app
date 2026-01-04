I'll analyze the code structure by fetching key files from the repository to provide detailed, actionable prompts.Based on my analysis of the Taisykla App repository structure and the Laravel application architecture, I'll provide you with detailed, actionable prompts that you can use directly to improve the codebase. These are organized by priority and can be implemented incrementally.

---

## **🎯 DETAILED IMPLEMENTATION PROMPTS**

### **1. DATABASE & MODELS ARCHITECTURE**

#### **Prompt 1.1: Create Comprehensive Service Request Model**
```
Create a Laravel Eloquent model for ServiceRequest with the following specifications:

Requirements:
- Model name: ServiceRequest
- Table: service_requests
- Enable soft deletes (use SoftDeletes trait)
- Add fillable fields: customer_id, equipment_id, technician_id, priority, status, description, scheduled_at, completed_at, estimated_cost, actual_cost, approval_status, approved_by, approved_at
- Add casts: scheduled_at and completed_at as datetime, estimated_cost and actual_cost as decimal:2
- Define relationships:
  * belongsTo: Customer, Equipment, Technician (User model), ApprovedBy (User model)
  * hasMany: ServiceRequestItems, ServiceRequestNotes, ServiceRequestAttachments
  * morphMany: ActivityLog (polymorphic for audit trail)
- Add scopes: scopeActive, scopePending, scopeInProgress, scopeCompleted, scopeHighPriority
- Add accessor for status_label that returns human-readable status
- Add mutator for priority that validates enum values
- Implement observer for automatically logging status changes
- Add constants for STATUS_PENDING, STATUS_ASSIGNED, STATUS_IN_PROGRESS, STATUS_COMPLETED, STATUS_CANCELLED
- Add constants for PRIORITY_LOW, PRIORITY_MEDIUM, PRIORITY_HIGH, PRIORITY_URGENT

Include proper PHPDoc blocks for all methods and properties.
```

#### **Prompt 1.2: Create Equipment Model with Maintenance History**
```
Create a comprehensive Equipment model with maintenance tracking:

Model requirements:
- Model name: Equipment
- Table: equipment
- Enable soft deletes
- Fillable: customer_id, equipment_type_id, serial_number, manufacturer, model, purchase_date, warranty_expiry, status, location, notes
- Relationships:
  * belongsTo: Customer, EquipmentType
  * hasMany: ServiceRequest, MaintenanceSchedule, EquipmentDocument
  * morphMany: ActivityLog
- Add scope for active equipment: scopeActive
- Add scope for equipment needing maintenance: scopeNeedsMaintenance (checks last service date)
- Add accessor: days_since_last_service
- Add accessor: warranty_status (returns 'active', 'expired', or 'expiring_soon')
- Add method: scheduleNextMaintenance() that creates a maintenance reminder
- Add constants for equipment status: STATUS_OPERATIONAL, STATUS_NEEDS_REPAIR, STATUS_OUT_OF_SERVICE, STATUS_RETIRED
- Implement automatic notification when warranty is expiring (within 30 days)

Include validation rules as static method validateRules() for use in form requests.
```

#### **Prompt 1.3: Create Migration for Service Requests with Indexes**
```
Create a Laravel migration for the service_requests table with proper indexes and foreign keys:

Migration specifications:
- Table name: service_requests
- Columns:
  * id (bigIncrements, primary key)
  * customer_id (unsignedBigInteger, foreign key to users)
  * equipment_id (unsignedBigInteger, foreign key to equipment, nullable)
  * technician_id (unsignedBigInteger, foreign key to users, nullable)
  * priority (enum: 'low', 'medium', 'high', 'urgent', default 'medium')
  * status (enum: 'pending', 'assigned', 'in_progress', 'completed', 'cancelled', default 'pending')
  * description (text)
  * scheduled_at (timestamp, nullable)
  * started_at (timestamp, nullable)
  * completed_at (timestamp, nullable)
  * estimated_hours (decimal 8,2, nullable)
  * actual_hours (decimal 8,2, nullable)
  * estimated_cost (decimal 10,2, nullable)
  * actual_cost (decimal 10,2, nullable)
  * approval_status (enum: 'pending', 'approved', 'rejected', nullable)
  * approved_by (unsignedBigInteger, foreign key to users, nullable)
  * approved_at (timestamp, nullable)
  * rejection_reason (text, nullable)
  * customer_notes (text, nullable)
  * technician_notes (text, nullable)
  * internal_notes (text, nullable)
  * timestamps (created_at, updated_at)
  * softDeletes (deleted_at)

- Add indexes on:
  * customer_id
  * equipment_id
  * technician_id
  * status
  * priority
  * scheduled_at
  * created_at
  * composite index on (status, priority, scheduled_at) for efficient filtering

- Add foreign key constraints with onDelete('cascade') for customer_id and onDelete('set null') for technician_id

Include down() method that properly drops foreign keys and indexes before dropping the table.
```

---

### **2. CONTROLLERS & BUSINESS LOGIC**

#### **Prompt 2.1: Create Service Request Controller with Repository Pattern**
```
Create a ServiceRequestController using repository pattern and best practices:

Controller structure:
- Name: ServiceRequestController
- Inject ServiceRequestRepository in constructor
- Use Form Request validation classes (StoreServiceRequestRequest, UpdateServiceRequestRequest)
- Implement methods:
  * index(): Display paginated list with filters (status, priority, customer, technician, date range)
  * create(): Show form with customer and equipment selection
  * store(): Create new service request with validation, send notifications
  * show($id): Display detailed view with timeline, notes, attachments
  * edit($id): Show edit form with authorization check
  * update($id): Update request, log changes, send notifications if status changed
  * destroy($id): Soft delete with authorization
  * assign(AssignTechnicianRequest $request, $id): Assign technician to request
  * updateStatus(UpdateStatusRequest $request, $id): Change status, log activity
  * approve($id): Approve request (manager only)
  * reject(RejectRequestRequest $request, $id): Reject with reason

Features to implement:
- Use Laravel policies for authorization (ServiceRequestPolicy)
- Add middleware for role-based access
- Return JSON for AJAX requests, views for regular requests
- Implement eager loading to prevent N+1 queries
- Add try-catch blocks with proper error handling
- Log all important actions using Laravel's logging
- Send event when status changes (ServiceRequestStatusChanged event)
- Return with flash messages for user feedback
- Add pagination (15 items per page)
- Add sorting by multiple columns

Include proper PHPDoc blocks and type hints for all methods.
```

#### **Prompt 2.2: Create Repository for Service Requests**
```
Create a ServiceRequestRepository class following repository pattern:

Repository specifications:
- Interface: ServiceRequestRepositoryInterface
- Implementation: ServiceRequestRepository
- Inject ServiceRequest model in constructor

Methods to implement:
- all($filters = []): Get all with optional filters (status, priority, customer_id, technician_id, date_from, date_to)
- find($id): Find by ID with relationships loaded
- create(array $data): Create new service request
- update($id, array $data): Update existing request
- delete($id): Soft delete
- restore($id): Restore soft deleted
- forceDelete($id): Permanently delete
- getWithRelations($id, array $relations): Get with specific relations
- getPendingForTechnician($technicianId): Get pending requests for specific technician
- getOverdueRequests(): Get requests past scheduled_at that aren't completed
- getByCustomer($customerId, $paginate = true): Get all requests for customer
- getStatistics($dateFrom = null, $dateTo = null): Return array with counts by status and priority
- searchByKeyword($keyword): Full-text search in description and notes
- getUpcoming($days = 7): Get requests scheduled in next X days
- getCompletedInDateRange($from, $to): Get completed requests in date range
- updateStatus($id, $status, $userId): Update status and log change
- assignTechnician($id, $technicianId): Assign technician to request

Features:
- Use query builder for complex queries
- Implement caching for statistics (cache for 5 minutes)
- Add query scopes for common filters
- Return paginated results where appropriate
- Include proper error handling
- Log database queries in debug mode

Bind interface to implementation in AppServiceProvider.
```

#### **Prompt 2.3: Create Form Request Validation Classes**
```
Create comprehensive Form Request classes for ServiceRequest:

1. StoreServiceRequestRequest:
- Validation rules:
  * customer_id: required, exists:users,id (with role check for customer)
  * equipment_id: nullable, exists:equipment,id (must belong to customer)
  * priority: required, in:low,medium,high,urgent
  * description: required, string, min:10, max:5000
  * scheduled_at: nullable, date, after:now
  * estimated_hours: nullable, numeric, min:0.5, max:100
  * estimated_cost: nullable, numeric, min:0
  * customer_notes: nullable, string, max:2000

- Custom validation:
  * Ensure scheduled_at is during business hours (Mon-Fri, 8am-6pm)
  * Validate equipment belongs to selected customer
  * Check technician availability if technician_id provided

- Authorization: User must have 'create-service-requests' permission

2. UpdateServiceRequestRequest:
- Same rules as Store but make customer_id optional (can't change customer)
- Add rule for status transitions (can't go from completed to pending)
- Authorization: User must own the request or be admin/manager

3. AssignTechnicianRequest:
- technician_id: required, exists:users,id (must have technician role)
- scheduled_at: required, date, after:now
- estimated_hours: required, numeric, min:0.5

4. UpdateStatusRequest:
- status: required, in:pending,assigned,in_progress,completed,cancelled
- notes: required_if:status,cancelled|required_if:status,completed
- actual_hours: required_if:status,completed
- actual_cost: required_if:status,completed

Include custom error messages for better UX.
```

---

### **3. FRONTEND & USER INTERFACE**

#### **Prompt 3.1: Create Service Request Index View with Filters**
```
Create a Blade view for service requests listing (resources/views/service-requests/index.blade.php):

Requirements:
- Extend main layout: @extends('layouts.app')
- Page title: "Service Requests"
- Breadcrumbs component showing: Dashboard > Service Requests

Components to include:
1. Filter section (collapsible card):
   - Status filter (dropdown with all statuses)
   - Priority filter (dropdown)
   - Customer search (autocomplete if admin/manager)
   - Technician filter (dropdown if admin/manager)
   - Date range picker (from/to)
   - Search box for keyword search
   - Apply Filters and Clear Filters buttons

2. Actions section:
   - "Create New Request" button (check permission)
   - Export to CSV button
   - Bulk actions dropdown (for selected items)

3. Results table:
   - Columns: ID, Customer, Equipment, Priority (badge with colors), Status (badge), Scheduled Date, Technician, Actions
   - Make priority badges: red for urgent, orange for high, yellow for medium, green for low
   - Make status badges: gray for pending, blue for assigned, yellow for in_progress, green for completed, red for cancelled
   - Each row should be clickable to view details
   - Action buttons: View, Edit, Delete (check permissions for each)

4. Pagination:
   - Use Laravel's pagination links
   - Show total count of results
   - Per page selector (10, 25, 50, 100)

Features:
- Use Alpine.js for interactive filters without page reload
- Add sorting by clicking column headers
- Highlight overdue requests in light red background
- Add loading spinner when filtering
- Show empty state with illustration if no results
- Make responsive: stack on mobile, table on desktop
- Add tooltips for truncated text

Use Tailwind CSS for styling with modern design.
```

#### **Prompt 3.2: Create Service Request Detail View**
```
Create a comprehensive service request detail view (resources/views/service-requests/show.blade.php):

Layout sections:
1. Header:
   - Request ID and status badge
   - Priority badge
   - Edit and Delete buttons (if authorized)
   - Back to list button

2. Main Information Card:
   - Customer details (name, email, phone) with link to customer profile
   - Equipment information (type, manufacturer, model, serial)
   - Description (formatted text)
   - Scheduled date and time
   - Estimated vs actual hours and cost comparison

3. Status Timeline (visual):
   - Show all status changes with dates and who made the change
   - Use vertical timeline component with icons
   - Highlight current status

4. Assignment Section:
   - Current technician info (photo, name, contact)
   - Assign/Reassign button (if manager)
   - Show technician workload indicator

5. Notes Section (tabbed):
   - Customer Notes tab
   - Technician Notes tab
   - Internal Notes tab (admin only)
   - Add note form for each type

6. Attachments Section:
   - Grid of uploaded images/documents
   - Download and delete options
   - Upload new attachment button with drag-drop

7. Parts Used Section (if completed):
   - Table of parts with quantity and cost
   - Total parts cost

8. Activity Log:
   - Chronological list of all changes
   - Show who did what and when
   - Filterable by action type

9. Action Buttons (context-aware):
   - If pending: "Approve" and "Reject" buttons (manager)
   - If assigned: "Start Work" button (technician)
   - If in_progress: "Complete" button (technician)
   - If completed: "Rate Service" button (customer)

Features:
- Real-time updates using Laravel Echo for status changes
- Confirmation modals for destructive actions
- Loading states for async operations
- Print-friendly CSS
- Share button to generate public link
- Export as PDF button

Use responsive design with mobile-first approach.
```

#### **Prompt 3.3: Create Dashboard with Analytics**
```
Create an analytics dashboard (resources/views/dashboard.blade.php):

Dashboard widgets (use grid layout):
1. Statistics Cards (top row):
   - Total Active Requests (with trend indicator)
   - Requests This Week
   - Average Response Time
   - Customer Satisfaction Score
   - Revenue This Month
   - Pending Approvals (clickable)

2. Charts Section:
   - Requests by Status (doughnut chart)
   - Requests by Priority (bar chart)
   - Requests Over Time (line chart, last 30 days)
   - Revenue Trend (area chart)

3. Quick Actions Panel:
   - Create New Request
   - Schedule Maintenance
   - View Reports
   - Manage Inventory

4. Recent Requests Table:
   - Last 10 requests with quick view
   - Click to see details

5. Technician Performance (manager view):
   - Table showing each technician with:
     * Active jobs count
     * Completed this week
     * Average completion time
     * Customer rating
   - Click technician to see their schedule

6. Overdue Requests Alert:
   - Red alert box if any overdue
   - List of overdue with action buttons

7. Upcoming Schedule (calendar view):
   - This week's scheduled requests
   - Drag-and-drop to reschedule

8. Low Stock Alerts (if inventory module):
   - Parts below reorder level
   - Link to inventory management

Implementation details:
- Use Chart.js for all charts
- Make charts responsive and interactive
- Add date range selector for filtering data
- Implement real-time updates for statistics
- Add loading skeletons while data loads
- Make widgets draggable/reorderable (save preference)
- Add export options for each widget
- Use Alpine.js for interactivity
- Fetch data via AJAX from dedicated API endpoints

Create corresponding controller method: DashboardController@index
```

---

### **4. API DEVELOPMENT**

#### **Prompt 4.1: Create RESTful API for Service Requests**
```
Create a comprehensive RESTful API for ServiceRequests:

API Controller: Api\ServiceRequestController

Endpoints to implement:

GET /api/v1/service-requests
- List all service requests with pagination
- Query parameters: page, per_page, status, priority, customer_id, technician_id, search, sort, order
- Return paginated JSON with meta information
- Include related data: customer, equipment, technician

GET /api/v1/service-requests/{id}
- Get single service request with all relations
- Include: customer, equipment, technician, notes, attachments, activity_log
- Return 404 if not found

POST /api/v1/service-requests
- Create new service request
- Validate using API Form Request
- Return 201 with created resource
- Send notification to customer and dispatch team

PUT /api/v1/service-requests/{id}
- Update existing request
- Return 200 with updated resource
- Log changes in activity log

DELETE /api/v1/service-requests/{id}
- Soft delete request
- Return 204 No Content

POST /api/v1/service-requests/{id}/assign
- Assign technician to request
- Body: { technician_id, scheduled_at }
- Send notification to technician

PATCH /api/v1/service-requests/{id}/status
- Update status
- Body: { status, notes }
- Validate status transitions

GET /api/v1/service-requests/{id}/timeline
- Get complete timeline of status changes

POST /api/v1/service-requests/{id}/notes
- Add note to request
- Body: { note_type, content }

GET /api/v1/service-requests/statistics
- Get dashboard statistics
- Query params: date_from, date_to
- Return counts and aggregates

API Features:
- Use Laravel Sanctum for authentication
- Implement API versioning (v1)
- Add rate limiting (60 requests per minute)
- Use API Resources for consistent response format
- Implement proper HTTP status codes
- Add CORS configuration
- Include ETag headers for caching
- Implement sorting and filtering
- Add comprehensive error responses
- Include API documentation in code (use Swagger annotations)
- Log all API requests
- Implement request validation
- Add response compression

Create API Resource classes:
- ServiceRequestResource
- ServiceRequestCollection
- CustomerResource
- TechnicianResource
```

#### **Prompt 4.2: Create API Authentication with Sanctum**
```
Implement Laravel Sanctum for API authentication:

Setup requirements:
1. Install Laravel Sanctum
2. Publish configuration
3. Run migrations for personal_access_tokens table

Create Auth API Controller (Api\AuthController):

POST /api/v1/register
- Register new user
- Validate: name, email, password, password_confirmation, role
- Create user with hashed password
- Return user and access token
- Token abilities based on role

POST /api/v1/login
- Authenticate user
- Validate: email, password, device_name
- Check credentials
- Create token with device name
- Return user and token

POST /api/v1/logout
- Revoke current token
- Return 204 No Content

GET /api/v1/user
- Get authenticated user info
- Require valid token

POST /api/v1/refresh
- Refresh token (delete old, create new)

Token Management:
- Set token expiration (24 hours for mobile apps)
- Different abilities for different roles:
  * admin: *
  * manager: service-requests:*, customers:read, technicians:read
  * technician: service-requests:read, service-requests:update-status
  * customer: service-requests:read, service-requests:create

Middleware setup:
- Create custom middleware: CheckTokenAbility
- Apply middleware to routes based on required abilities

Security features:
- Implement failed login attempt throttling
- Hash tokens properly
- Add token name/device tracking
- Implement token revocation
- Add IP address logging
- Two-factor authentication option

Create API requests:
- RegisterRequest
- LoginRequest
- RefreshTokenRequest

Error responses:
- 401 for unauthenticated
- 403 for unauthorized (wrong abilities)
- 422 for validation errors
- Include helpful error messages
```

---

### **5. REAL-TIME FEATURES**

#### **Prompt 5.1: Implement WebSocket for Real-time Updates**
```
Set up Laravel WebSockets for real-time functionality:

Installation:
1. Install beyondcode/laravel-websockets package
2. Install pusher/pusher-php-server
3. Configure broadcasting in config/broadcasting.php
4. Publish WebSocket configuration

Events to broadcast:
1. ServiceRequestCreated
   - Channel: service-requests
   - Data: request ID, customer name, priority, created_at
   - Listeners: DispatchDashboard, NotifyManagers

2. ServiceRequestStatusChanged
   - Channel: service-request.{id}
   - Data: request ID, old status, new status, changed_by
   - Listeners: NotifyCustomer, NotifyTechnician, UpdateDashboard

3. TechnicianAssigned
   - Channel: technician.{technician_id}
   - Data: request ID, customer name, scheduled_at
   - Listeners: SendTechnicianNotification

4. MessageSent
   - Channel: service-request.{id}.chat
   - Data: message, sender, timestamp
   - Private channel for customer and technician

Create Event Classes with ShouldBroadcast interface:
- Include channel authorization
- Define broadcast data
- Set queue for event broadcasting

Frontend Integration (resources/js/echo.js):
- Configure Laravel Echo with Pusher
- Listen to channels:
  ```javascript
  Echo.channel('service-requests')
      .listen('ServiceRequestCreated', (e) => {
          // Update dashboard
      });
  
  Echo.private('service-request.' + requestId)
      .listen('ServiceRequestStatusChanged', (e) => {
          // Update status display
      });
  ```

Channel Authorization (routes/channels.php):
- Authorize private channels
- Check user permissions
- Return user data if authorized

Create notification toasts when events received:
- Use Alpine.js for toast notifications
- Show: "New service request created", "Status updated", etc.
- Auto-dismiss after 5 seconds

Dashboard real-time updates:
- Update statistics counters
- Refresh recent requests list
- Update technician status indicators
- Flash row when new item added

WebSocket Dashboard (only for admins):
- Monitor active connections
- View channel statistics
- Debug events in real-time
```

#### **Prompt 5.2: Implement Notification System**
```
Create comprehensive Laravel notification system:

Notification Types (create Notification classes):

1. ServiceRequestCreatedNotification
   - To: Customer, Dispatch managers
   - Channels: database, mail, SMS (optional)
   - Content: Request details, confirmation number
   - Action: View request button

2. TechnicianAssignedNotification
   - To: Technician, Customer
   - Channels: database, mail, push
   - Content: Assignment details, customer info, scheduled time
   - Action: View schedule, Contact customer

3. ServiceStartedNotification
   - To: Customer
   - Channels: database, SMS, mail
   - Content: Technician info, estimated completion
   - Action: Track progress

4. ServiceCompletedNotification
   - To: Customer, Managers
   - Channels: database, mail
   - Content: Completion details, invoice
   - Action: Rate service, View invoice, Pay online

5. ServiceRequestApprovedNotification
   - To: Customer, Assigned technician
   - Channels: database, mail
   - Content: Approval confirmation
   - Action: View details

6. ServiceRequestRejectedNotification
   - To: Customer
   - Channels: database, mail, SMS
   - Content: Rejection reason
   - Action: Contact support, Modify request

7. UpcomingServiceReminderNotification
   - To: Customer, Technician
   - Channels: database, mail, SMS
   - Content: Reminder 24h before scheduled service
   - Action: Reschedule, Confirm

8. OverdueServiceAlertNotification
   - To: Managers, Technicians
   - Channels: database, mail
   - Content: Overdue request details
   - Action: Reassign, Contact customer

Implementation:
- Use database notifications table for in-app notifications
- Create mail templates for each notification (Markdown mailable)
- Implement SMS via Twilio (create TwilioChannel)
- Add push notifications for mobile (FCM)

Notification Preferences:
- Create user preferences table
- Allow users to opt in/out of notification types
- Respect notification channels preference

Notification Center UI:
- Bell icon with unread count badge
- Dropdown with recent notifications
- Mark as read functionality
- Clear all button
- Link to full notification history page

Scheduled Notifications:
- Queue notification jobs
- Send reminders using Laravel's task scheduler
- Example: Check for upcoming services daily at 9 AM

Notification Templates (Blade):
- Create in resources/views/notifications/
- Make responsive email templates
- Include company branding
- Add clear call-to-action buttons

Create Notification Helper Service:
- NotificationService class with methods to send each type
- Handle user preferences checking
- Log notification sending
- Retry failed notifications
```

---

### **6. TESTING**

#### **Prompt 6.1: Create Feature Tests for Service Requests**
```
Create comprehensive PHPUnit feature tests for ServiceRequest functionality:

Test file: tests/Feature/ServiceRequestTest.php

Tests to implement:

1. User Can Create Service Request:
   - test_customer_can_create_service_request()
   - Assert: database has new record
   - Assert: customer receives notification
   - Assert: redirected to request details page

2. User Cannot Create Invalid Request:
   - test_service_request_validation_fails_without_description()
   - test_service_request_validation_fails_with_invalid_priority()
   - Assert: validation errors present

3. Manager Can Assign Technician:
   - test_manager_can_assign_technician_to_request()
   - Assert: technician_id updated
   - Assert: scheduled_at updated
   - Assert: technician receives notification
   - Assert: status changed to 'assigned'

4. Technician Cannot Assign Themselves:
   - test_technician_cannot_assign_themselves()
   - Assert: 403 forbidden

5. Status Updates:
   - test_technician_can_start_assigned_request()
   - test_technician_can_complete_in_progress_request()
   - test_cannot_skip_status_workflow()
   - Assert: status changes correctly
   - Assert: timestamps updated
   - Assert: activity logged

6. Authorization Tests:
   - test_customer_can_only_view_own_requests()
   - test_customer_cannot_edit_others_requests()
   - test_admin_can_view_all_requests()

7. Soft Delete:
   - test_request_can_be_soft_deleted()
   - test_deleted_request_not_in_index()
   - test_deleted_request_can_be_restored()

8. Filtering:
   - test_requests_can_be_filtered_by_status()
   - test_requests_can_be_filtered_by_priority()
   - test_requests_can_be_searched_by_keyword()

9. Pagination:
   - test_requests_are_paginated()
   - test_pagination_respects_per_page_parameter()

10. Relationships:
    - test_service_request_belongs_to_customer()
    - test_service_request_belongs_to_equipment()
    - test_service_request_has_many_notes()

Setup and Helpers:
- Use RefreshDatabase trait
- Create factories for models
- Use setUp() to create test users with roles
- Use faker for test data
- Create helper methods: createServiceRequest(), assignTechnician()

Assertions to use:
- assertDatabaseHas
- assertDatabaseMissing
- assertRedirect
- assertStatus (200, 201, 403, 404, 422)
- assertSessionHas (flash messages)
- assertJsonStructure (for API tests)
- assertNotificationSent

Run tests with: php artisan test --filter ServiceRequestTest
```

#### **Prompt 6.2: Create Unit Tests for Models**
```
Create unit tests for ServiceRequest model:

Test file: tests/Unit/ServiceRequestTest.php

Tests to implement:

1. Model Configuration:
   - test_service_request_uses_soft_deletes()
   - test_service_request_has_correct_fillable_fields()
   - test_service_request_has_correct_casts()

2. Relationships:
   - test_service_request_belongs_to_customer()
   - test_service_request_belongs_to_equipment()
   - test_service_request_belongs_to_technician()
   - test_service_request_has_many_notes()
   - test_service_request_has_many_attachments()

3. Scopes:
   - test_active_scope_returns_only_non_deleted()
   - test_pending_scope_returns_only_pending()
   - test_high_priority_scope_returns_correct_requests()
   - test_overdue_scope_returns_overdue_requests()

4. Accessors:
   - test_status_label_accessor_returns_human_readable()
   - test_priority_badge_accessor_returns_correct_html()
   - test_is_overdue_accessor_returns_boolean()

5. Mutators:
   - test_priority_mutator_validates_enum()
   - test_status_mutator_validates_enum()

6. Methods:
   - test_can_be_assigned_returns_true_when_pending()
   - test_can_be_assigned_returns_false_when_completed()
   - test_is_assigned_to_method_works_correctly()
   - test_get_duration_method_calculates_correctly()

7. Constants:
   - test_status_constants_exist()
   - test_priority_constants_exist()

8. Business Logic:
   - test_cannot_change_status_from_completed_to_pending()
   - test_completion_requires_actual_hours_and_cost()

Test Setup:
- Don't use RefreshDatabase for unit tests
- Mock dependencies where appropriate
- Test model in isolation
- Use setUp() to create test instances

Example test structure:
```php
public function test_service_request_belongs_to_customer()
{
    $serviceRequest = new ServiceRequest();
    $relation = $serviceRequest->customer();
    
    $this->assertInstanceOf(BelongsTo::class, $relation);
    $this->assertEquals('customer_id', $relation->getForeignKeyName());
}
```

Run with: php artisan test --filter ServiceRequestTest --testsuite=Unit
```

---

### **7. SECURITY & PERMISSIONS**

#### **Prompt 7.1: Implement Role-Based Access Control with Spatie**
```
Implement comprehensive RBAC using spatie/laravel-permission:

Installation:
1. Install package: composer require spatie/laravel-permission
2. Publish config and migrations
3. Run migrations

Define Roles (create seeder: RolesAndPermissionsSeeder):
- super_admin: Full system access
- manager: Manage all service operations
- dispatcher: Assign and schedule requests
- technician: View and update assigned requests
- customer: Create and view own requests
- customer_business: Customer role + manage company users

Define Permissions:
Service Requests:
- view-service-requests
- view-own-service-requests
- create-service-requests
- edit-service-requests
- edit-own-service-requests
- delete-service-requests
- assign-service-requests
- approve-service-requests

Customers:
- view-customers
- create-customers
- edit-customers
- delete-customers
- impersonate-customers

Technicians:
- view-technicians
- create-technicians
- edit-technicians
- delete-technicians
- view-technician-schedule

Equipment:
- view-equipment
- create-equipment
- edit-equipment
- delete-equipment

Reports:
- view-reports
- view-financial-reports
- export-reports

Assign permissions to roles:
```php
$manager->givePermissionTo([
    'view-service-requests',
    'create-service-requests',
    'edit-service-requests',
    'assign-service-requests',
    'approve-service-requests',
    'view-customers',
    'view-technicians',
    'view-reports',
]);

$technician->givePermissionTo([
    'view-own-service-requests',
    'edit-own-service-requests',
]);

$customer->givePermissionTo([
    'view-own-service-requests',
    'create-service-requests',
]);
```

Create Policy: ServiceRequestPolicy
```php
public function view(User $user, ServiceRequest $request)
{
    return $user->can('view-service-requests') 
        || ($user->can('view-own-service-requests') && $request->customer_id === $user->id)
        || ($user->can('view-own-service-requests') && $request->technician_id === $user->id);
}

public function update(User $user, ServiceRequest $request)
{
    if ($user->can('edit-service-requests')) {
        return true;
    }
    
    if ($user->can('edit-own-service-requests')) {
        return $request->technician_id === $user->id && $request->status !== 'completed';
    }
    
    return false;
}
```

Middleware:
- Use permission middleware: 'permission:view-reports'
- Use role middleware: 'role:manager|admin'
- Create custom middleware: EnsureUserOwnsResource

Blade Directives:
```blade
@can('create-service-requests')
    <button>Create Request</button>
@endcan

@role('manager')
    <a href="{{ route('reports') }}">Reports</a>
@endrole

@hasanyrole('manager|admin')
    <!-- Admin panel -->
@endhasanyrole
```

Create Permission Management UI:
- Page for admins to manage roles and permissions
- Assign/revoke permissions from roles
- Assign/revoke roles from users
- View permission matrix (role vs permission grid)
```

#### **Prompt 7.2: Implement Two-Factor Authentication**
```
Add two-factor authentication (2FA) for enhanced security:

Use: pragmarx/google2fa-laravel package

Implementation steps:

1. Migration for 2FA fields on users table:
   - google2fa_secret (string, nullable)
   - google2fa_enabled (boolean, default false)
   - backup_codes (json, nullable)

2. Create TwoFactorController:
   - showEnableForm(): Display QR code and setup instructions
   - enable(): Store secret and show backup codes
   - disable(): Remove 2FA and backup codes
   - verifyCode(): Verify 2FA code during login

3. Modify Login Flow:
   - After successful password verification
   - Check if user has 2FA enabled
   - If yes, redirect to 2FA verification page
   - Store partial authentication in session
   - Verify 2FA code before completing login

4. Generate QR Code:
   ```php
   $qrCodeUrl = $google2fa->getQRCodeUrl(
       config('app.name'),
       $user->email,
       $user->google2fa_secret
   );
   ```

5. Backup Codes:
   - Generate 8 random 8-character codes
   - Store hashed in database
   - Show once during setup
   - Allow usage if 2FA code unavailable
   - Regenerate after use

6. Middleware: TwoFactorAuthentication
   - Check if user has completed 2FA
   - Allow access to verification routes
   - Redirect others to 2FA verification

7. UI Components:
   - Enable 2FA page with QR code
   - 2FA code input during login (6-digit)
   - Backup codes display
   - Disable 2FA button with password confirmation
   - Trusted devices option (remember for 30 days)

8. Security Features:
   - Rate limit 2FA verification attempts
   - Lock account after 5 failed attempts
   - Log all 2FA events
   - Email notification when 2FA is enabled/disabled
   - Require password confirmation before setup

9. Recovery Options:
   - Backup codes
   - Email recovery link (sends temp code)
   - Admin can disable 2FA for locked accounts

10. Make 2FA mandatory for:
    - Admin users
    - Manager users
    - Users with financial access

Routes:
- /2fa/enable (GET, POST)
- /2fa/verify (GET, POST)
- /2fa/disable (POST)
- /2fa/backup-codes (GET)
```

---

### **8. PERFORMANCE OPTIMIZATION**

#### **Prompt 8.1: Implement Caching Strategy**
```
Create comprehensive caching strategy for the application:

Cache Configuration:
- Driver: Redis (update .env)
- Set up Redis connection in config/database.php
- Install predis/predis package

Areas to Cache:

1. Service Request Statistics:
```php
// In DashboardController
public function getStatistics()
{
    return Cache::remember('dashboard.statistics', 300, function () {
        return [
            'total_active' => ServiceRequest::active()->count(),
            'pending' => ServiceRequest::where('status', 'pending')->count(),
            'in_progress' => ServiceRequest::where('status', 'in_progress')->count(),
            'completed_today' => ServiceRequest::completedToday()->count(),
            'avg_response_time' => ServiceRequest::getAverageResponseTime(),
        ];
    });
}
```

2. Customer Details:
```php
// Cache customer data that doesn't change often
$customer = Cache::remember("customer.{$customerId}", 3600, function () use ($customerId) {
    return Customer::with('equipment', 'serviceRequests')->find($customerId);
});
```

3. Equipment Types and Catalogs:
```php
// Cache reference data
$equipmentTypes = Cache::rememberForever('equipment.types', function () {
    return EquipmentType::all();
});
```

4. User Permissions:
```php
// Cache user permissions after login
Cache::remember("user.{$userId}.permissions", 3600, function () use ($user) {
    return $user->getAllPermissions()->pluck('name');
});
```

5. Reports and Analytics:
```php
// Cache expensive report queries
$monthlyReport = Cache::remember('reports.monthly.' . $month, 86400, function () use ($month) {
    return ServiceRequest::generateMonthlyReport($month);
});
```

Cache Invalidation:

1. Model Events (in ServiceRequest model):
```php
protected static function booted()
{
    static::saved(function ($request) {
        Cache::tags(['service-requests', "customer.{$request->customer_id}"])
             ->flush();
    });
    
    static::deleted(function ($request) {
        Cache::tags(['service-requests', "customer.{$request->customer_id}"])
             ->flush();
    });
}
```

2. Manual Cache Clearing:
```php
// When status changes
Cache::forget('dashboard.statistics');
Cache::tags('service-requests')->flush();

// When customer updated
Cache::forget("customer.{$customerId}");
```

Cache Tags:
- Use tags for related cache items
- 'service-requests': All service request data
- 'customers': Customer data
- 'reports': Report data
- 'dashboard': Dashboard statistics

Create CacheService:
```php
class CacheService
{
    public function rememberServiceRequest($id, $ttl = 3600)
    {
        return Cache::tags('service-requests')
            ->remember("service-request.{$id}", $ttl, function () use ($id) {
                return ServiceRequest::with('customer', 'equipment', 'technician')->find($id);
            });
    }
    
    public function forgetServiceRequest($id)
    {
        Cache::tags('service-requests')->forget("service-request.{$id}");
    }
    
    public function flushServiceRequests()
    {
        Cache::tags('service-requests')->flush();
    }
}
```

Query Result Caching:
- Cache expensive database queries
- Use query builder cache() method for simple queries
- Set appropriate TTL based on data volatility

Cache Warming:
- Create command to pre-populate cache
- Run during deployment
```php
// app/Console/Commands/WarmCache.php
public function handle()
{
    Cache::remember('equipment.types', now()->addDay(), function () {
        return EquipmentType::all();
    });
    
    // Warm other critical caches
}
```

Monitoring:
- Log cache hits/misses
- Monitor cache size
- Track cache performance with Laravel Telescope
```

#### **Prompt 8.2: Optimize Database Queries**
```
Optimize database performance across the application:

1. Add Database Indexes (create migration):
```php
public function up()
{
    Schema::table('service_requests', function (Blueprint $table) {
        // Single column indexes
        $table->index('customer_id');
        $table->index('technician_id');
        $table->index('equipment_id');
        $table->index('status');
        $table->index('priority');
        $table->index('scheduled_at');
        $table->index('created_at');
        
        // Composite indexes for common queries
        $table->index(['status', 'priority']);
        $table->index(['customer_id', 'status']);
        $table->index(['technician_id', 'status']);
        $table->index(['scheduled_at', 'status']);
        
        // Full-text index for search
        $table->fullText(['description', 'customer_notes']);
    });
    
    Schema::table('equipment', function (Blueprint $table) {
        $table->index('customer_id');
        $table->index('equipment_type_id');
        $table->index(['customer_id', 'status']);
        $table->index('serial_number');
    });
}
```

2. Prevent N+1 Queries - Update Controllers:
```php
// BAD - N+1 Query
$requests = ServiceRequest::all();
foreach ($requests as $request) {
    echo $request->customer->name; // Additional query for each request
}

// GOOD - Eager Loading
$requests = ServiceRequest::with(['customer', 'equipment', 'technician'])->get();
foreach ($requests as $request) {
    echo $request->customer->name; // No additional queries
}
```

3. Optimize ServiceRequest Index Query:
```php
public function index(Request $request)
{
    $query = ServiceRequest::query()
        ->with(['customer:id,name,email', 'equipment:id,model,serial_number', 'technician:id,name'])
        ->select('id', 'customer_id', 'equipment_id', 'technician_id', 'priority', 'status', 'scheduled_at', 'created_at');
    
    // Apply filters
    when($request->status, fn($q, $status) => $q->where('status', $status));
    
    // Use chunk for large datasets
    if ($request->export) {
        return $query->chunk(1000, function ($requests) {
            // Process in chunks
        });
    }
    
    return $query->paginate(15);
}
```

4. Use Query Scopes Efficiently:
```php
// In ServiceRequest model
public function scopeWithRelations($query)
{
    return $query->with([
        'customer' => fn($q) => $q->select('id', 'name', 'email'),
        'equipment' => fn($q) => $q->select('id', 'model', 'serial_number'),
        'technician' => fn($q) => $q->select('id', 'name', 'avatar'),
    ]);
}

// Usage
$requests = ServiceRequest::withRelations()->paginate();
```

5. Optimize Counting Queries:
```php
// BAD
$pendingCount = ServiceRequest::where('status', 'pending')->get()->count();

// GOOD
$pendingCount = ServiceRequest::where('status', 'pending')->count();

// BETTER - Use cache for frequent counts
$pendingCount = Cache::remember('requests.pending.count', 300, function () {
    return ServiceRequest::where('status', 'pending')->count();
});
```

6. Use Lazy Collections for Large Datasets:
```php
// For exports or batch processing
ServiceRequest::where('status', 'completed')
    ->cursor() // Returns lazy collection
    ->each(function ($request) {
        // Process without loading all into memory
    });
```

7. Optimize Exists Checks:
```php
// BAD
if (ServiceRequest::where('customer_id', $id)->count() > 0) { }

// GOOD
if (ServiceRequest::where('customer_id', $id)->exists()) { }
```

8. Database Connection Pooling:
```php
// config/database.php
'connections' => [
    'mysql' => [
        'options' => [
            PDO::ATTR_PERSISTENT => true, // Connection pooling
        ],
    ],
],
```

9. Select Only Needed Columns:
```php
// BAD - Loads all columns
$customers = Customer::all();

// GOOD - Only needed columns
$customers = Customer::select('id', 'name', 'email')->get();
```

10. Create DatabaseOptimizationService:
```php
class DatabaseOptimizationService
{
    public function analyzeSlowQueries()
    {
        // Enable query logging
        DB::enableQueryLog();
        
        // Execute operation
        
        // Get queries
        $queries = DB::getQueryLog();
        
        // Identify slow queries (>100ms)
        return collect($queries)->filter(fn($q) => $q['time'] > 100);
    }
    
    public function optimizeTable($table)
    {
        DB::statement("OPTIMIZE TABLE {$table}");
    }
}
```

Monitor with Laravel Telescope:
- Install telescope for development
- Identify slow queries
- Track duplicate queries
- Monitor query count per request
```

---

### **9. DEPLOYMENT & CI/CD**

#### **Prompt 9.1: Create Docker Configuration**
```
Create Docker setup for the Taisykla application:

Create Dockerfile:
```dockerfile
FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nginx \
    supervisor \
    redis-tools

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Node.js and npm
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - && \
    apt-get install -y nodejs

# Set working directory
WORKDIR /var/www

# Copy application files
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader && \
    npm install && npm run build

# Set permissions
RUN chown -R www-data:www-data /var/www && \
    chmod -R 755 /var/www/storage

# Copy configuration files
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisor.conf /etc/supervisor/conf.d/supervisor.conf

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisor.conf"]
```

Create docker-compose.yml:
```yaml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: taisykla-app
    restart: unless-stopped
    working_dir: /var/www
    volumes:
      - ./:/var/www
      - ./docker/php.ini:/usr/local/etc/php/php.ini
    networks:
      - taisykla-network
    depends_on:
      - db
      - redis

  db:
    image: mysql:8.0
    container_name: taisykla-db
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: ${DB_DATABASE}
      MYSQL_ROOT_PASSWORD: ${DB_PASSWORD}
      MYSQL_PASSWORD: ${DB_PASSWORD}
      MYSQL_USER: ${DB_USERNAME}
    volumes:
      - dbdata:/var/lib/mysql
    networks:
      - taisykla-network
    ports:
      - "3306:3306"

  redis:
    image: redis:alpine
    container_name: taisykla-redis
    restart: unless-stopped
    networks:
      - taisykla-network
    ports:
      - "6379:6379"

  nginx:
    image: nginx:alpine
    container_name: taisykla-nginx
    restart: unless-stopped
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./:/var/www
      - ./docker/nginx/conf.d/:/etc/nginx/conf.d/
    networks:
      - taisykla-network
    depends_on:
      - app

  queue:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: taisykla-queue
    restart: unless-stopped
    command: php artisan queue:work --tries=3
    working_dir: /var/www
    volumes:
      - ./:/var/www
    networks:
      - taisykla-network
    depends_on:
      - db
      - redis

  scheduler:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: taisykla-scheduler
    restart: unless-stopped
    command: while true; do php artisan schedule:run --verbose --no-interaction & sleep 60; done
    working_dir: /var/www
    volumes:
      - ./:/var/www
    networks:
      - taisykla-network
    depends_on:
      - db
      - redis

networks:
  taisykla-network:
    driver: bridge

volumes:
  dbdata:
    driver: local
```

Create docker/nginx/conf.d/app.conf:
```nginx
server {
    listen 80;
    server_name localhost;
    root /var/www/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

Create docker/supervisor.conf:
```ini
[supervisord]
nodaemon=true

[program:nginx]
command=nginx -g 'daemon off;'
autostart=true
autorestart=true
stdout_logfile=/var/log/nginx/access.log
stderr_logfile=/var/log/nginx/error.log

[program:php-fpm]
command=php-fpm
autostart=true
autorestart=true
stdout_logfile=/var/log/php-fpm.log
stderr_logfile=/var/log/php-fpm-error.log

[program:laravel-worker]
command=php /var/www/artisan queue:work --tries=3
autostart=true
autorestart=true
numprocs=2
stdout_logfile=/var/log/worker.log
stderr_logfile=/var/log/worker-error.log
```

Create .dockerignore:
```
node_modules/
vendor/
.git/
.env
storage/logs/*
storage/framework/cache/*
storage/framework/sessions/*
storage/framework/views/*
```

Setup commands in README.md:
```bash
# Build and start containers
docker-compose up -d --build

# Run migrations
docker-compose exec app php artisan migrate --seed

# Generate app key
docker-compose exec app php artisan key:generate

# Create storage link
docker-compose exec app php artisan storage:link

# View logs
docker-compose logs -f app

# Stop containers
docker-compose down
```
```

#### **Prompt 9.2: Create GitHub Actions CI/CD Pipeline**
```
Create comprehensive GitHub Actions workflow for CI/CD:

Create .github/workflows/laravel.yml:
```yaml
name: Laravel CI/CD

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main, develop ]

jobs:
  tests:
    name: Tests (PHP ${{ matrix.php-versions }})
    runs-on: ubuntu-latest
    
    strategy:
      matrix:
        php-versions: ['8.1', '8.2']
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: password
          MYSQL_DATABASE: testing
        ports:
          - 3306:3306
        options: --health-cmd="mysqladmin ping" --health-interval=10s --health-timeout=5s --health-retries=3
      
      redis:
        image: redis:alpine
        ports:
          - 6379:6379
        options: --health-cmd="redis-cli ping" --health-interval=10s --health-timeout=5s --health-retries=3

    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php-versions }}
          extensions: mbstring, dom, fileinfo, mysql, redis
          coverage: xdebug
      
      - name: Get Composer Cache Directory
        id: composer-cache
        run: echo "dir=$(composer config cache-files-dir)" >> $GITHUB_OUTPUT
      
      - name: Cache Composer dependencies
        uses: actions/cache@v3
        with:
          path: ${{ steps.composer-cache.outputs.dir }}
          key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
          restore-keys: ${{ runner.os }}-composer-
      
      - name: Install Composer Dependencies
        run: composer install --prefer-dist --no-interaction --no-progress
      
      - name: Setup Node.js
        uses: actions/setup-node@v3
        with:
          node-version: '18'
          cache: 'npm'
      
      - name: Install NPM Dependencies
        run: npm ci
      
      - name: Build Assets
        run: npm run build
      
      - name: Copy Environment File
        run: cp .env.example .env
      
      - name: Generate Application Key
        run: php artisan key:generate
      
      - name: Directory Permissions
        run: chmod -R 777 storage bootstrap/cache
      
      - name: Run Migrations
        run: php artisan migrate --force
        env:
          DB_CONNECTION: mysql
          DB_HOST: 127.0.0.1
          DB_PORT: 3306
          DB_DATABASE: testing
          DB_USERNAME: root
          DB_PASSWORD: password
      
      - name: Execute PHPUnit Tests
        run: vendor/bin/phpunit --coverage-text --coverage-clover=coverage.xml
        env:
          DB_CONNECTION: mysql
          DB_HOST: 127.0.0.1
          DB_PORT: 3306
          DB_DATABASE: testing
          DB_USERNAME: root
          DB_PASSWORD: password
          REDIS_HOST: 127.0.0.1
          REDIS_PORT: 6379
      
      - name: Upload Coverage to Codecov
        uses: codecov/codecov-action@v3
        with:
          files: ./coverage.xml
          fail_ci_if_error: true

  code-quality:
    name: Code Quality
    runs-on: ubuntu-latest
    
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          tools: phpcs, phpstan
      
      - name: Install Dependencies
        run: composer install --prefer-dist --no-interaction
      
      - name: Run PHP CS Fixer (Dry Run)
        run: vendor/bin/pint --test
      
      - name: Run PHPStan
        run: vendor/bin/phpstan analyse --memory-limit=2G
      
      - name: Run Larastan
        run: vendor/bin/phpstan analyse --configuration=phpstan.neon --memory-limit=2G

  security:
    name: Security Check
    runs-on: ubuntu-latest
    
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
      
      - name: Install Dependencies
        run: composer install --prefer-dist --no-interaction
      
      - name: Security Check
        run: |
          composer require --dev enlightn/security-checker
          php vendor/bin/security-checker security:check composer.lock

  deploy-staging:
    name: Deploy to Staging
    runs-on: ubuntu-latest
    needs: [tests, code-quality, security]
    if: github.ref == 'refs/heads/develop'
    
    steps:
      - uses: actions/checkout@v3
      
      - name: Deploy to Staging Server
        uses: appleboy/ssh-action@master
        with:
          host: ${{ secrets.STAGING_HOST }}
          username: ${{ secrets.STAGING_USERNAME }}
          key: ${{ secrets.STAGING_SSH_KEY }}
          script: |
            cd /var/www/taisykla-staging
            git pull origin develop
            composer install --no-dev --optimize-autoloader
            npm install && npm run build
            php artisan migrate --force
            php artisan config:cache
            php artisan route:cache
            php artisan view:cache
            php artisan queue:restart
            php artisan octane:reload || true

  deploy-production:
    name: Deploy to Production
    runs-on: ubuntu-latest
    needs: [tests, code-quality, security]
    if: github.ref == 'refs/heads/main'
    environment:
      name: production
      url: https://taisykla.com
    
    steps:
      - uses: actions/checkout@v3
      
      - name: Create Deployment
        uses: chrnorm/deployment-action@v2
        with:
          token: ${{ github.token }}
          environment: production
      
      - name: Deploy to Production Server
        uses: appleboy/ssh-action@master
        with:
          host: ${{ secrets.PROD_HOST }}
          username: ${{ secrets.PROD_USERNAME }}
          key: ${{ secrets.PROD_SSH_KEY }}
          script: |
            cd /var/www/taisykla-production
            
            # Backup database
            php artisan backup:run --only-db
            
            # Enable maintenance mode
            php artisan down --retry=60
            
            # Pull latest code
            git pull origin main
            
            # Install dependencies
            composer install --no-dev --optimize-autoloader
            npm install && npm run build
            
            # Run migrations
            php artisan migrate --force
            
            # Clear and cache
            php artisan config:cache
            php artisan route:cache
            php artisan view:cache
            php artisan event:cache
            
            # Restart services
            php artisan queue:restart
            php artisan octane:reload || true
            
            # Disable maintenance mode
            php artisan up
      
      - name: Deployment Success Notification
        if: success()
        uses: 8398a7/action-slack@v3
        with:
          status: custom
          custom_payload: |
            {
              text: "🚀 Production deployment successful!",
              attachments: [{
                color: 'good',
                text: `Deployed ${process.env.AS_COMMIT} to production`
              }]
            }
        env:
          SLACK_WEBHOOK_URL: ${{ secrets.SLACK_WEBHOOK }}
```

Create phpstan.neon for static analysis:
```neon
includes:
    - ./vendor/nunomaduro/larastan/extension.neon

parameters:
    paths:
        - app
    level: 5
    ignoreErrors:
        - '#Unsafe usage of new static#'
    excludePaths:
        - ./*/*/FileToBeExcluded.php
```

Create deployment scripts in scripts/deploy.sh:
```bash
#!/bin/bash
set -e

echo "Starting deployment..."

# Backup
php artisan backup:run

# Maintenance mode
php artisan down

# Update code
git pull origin main

# Dependencies
composer install --no-dev --optimize-autoloader
npm ci && npm run build

# Database
php artisan migrate --force

# Cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Queue
php artisan queue:restart

# Up
php artisan up

echo "Deployment complete!"
```
```
