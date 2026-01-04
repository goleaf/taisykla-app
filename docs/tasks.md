# Computer & Equipment Maintenance Management System
## Complete Development Prompts Collection

---

## Phase 1: Core Infrastructure Setup

### Prompt 1: Authentication & User Management System

Create a complete authentication and user management system with the following requirements:

1. Multi-role user system supporting:
   - System Administrators (full access)
   - Operations Managers/Dispatch Coordinators
   - Field Technicians
   - Parts/Inventory Specialists
   - Quality Assurance Managers
   - Financial/Billing Specialists
   - Support Staff
   - Business Account Administrators (customers)
   - Standard Business Users (customers)
   - Individual Consumers
   - Read-only/Guest Users

2. Security features:
   - Secure password requirements (min length, complexity rules)
   - Multi-factor authentication support (SMS, Email, Authenticator App, Hardware Keys)
   - Session management with automatic timeouts
   - Account lockout after failed login attempts
   - Password reset functionality via email

3. First-time login flow:
   - Welcome email with account setup link
   - Forced password change on first login
   - Optional MFA setup during onboarding

4. Role-based access control (RBAC):
   - Define granular permissions for each role
   - Permissions at module, function, and data visibility levels
   - Permission inheritance through role assignments

5. User profile management:
   - Full name, contact information, employee ID
   - Department/team assignment
   - Role designation
   - Preferred language and timezone
   - Notification preferences

Build this with secure practices, encrypted password storage, and audit logging for all authentication events.

---

### Prompt 2: Database Schema Design

Design a comprehensive database schema for a maintenance management system with these entities and relationships:

1. Users table:
   - user_id (PK), username, email, password_hash, role_id (FK)
   - first_name, last_name, phone, employee_id
   - created_at, last_login, is_active, mfa_enabled
   - department, timezone, language_preference

2. Roles & Permissions tables:
   - roles: role_id (PK), role_name, description
   - permissions: permission_id (PK), permission_name, module, action_type
   - role_permissions: role_id (FK), permission_id (FK)

3. Customers table:
   - customer_id (PK), account_number, company_name
   - customer_type (residential, business, enterprise, government)
   - service_level (economy, standard, premium)
   - billing_address, service_addresses (JSON or separate table)
   - contact_person, phone, email
   - account_status, credit_limit, payment_terms
   - created_at, parent_customer_id (for hierarchical relationships)

4. Equipment table:
   - equipment_id (PK), customer_id (FK), equipment_name
   - equipment_type, manufacturer, model_number, serial_number
   - purchase_date, purchase_price, location_address
   - warranty_provider, warranty_type, warranty_start, warranty_end
   - current_status, health_score, primary_user
   - technical_specs (JSON), created_at, last_service_date

5. Work Orders table:
   - work_order_id (PK), ticket_number (unique), customer_id (FK)
   - equipment_id (FK), assigned_technician_id (FK)
   - priority (standard, high, urgent), status (submitted, assigned, in_progress, completed, closed)
   - problem_description, problem_category
   - requested_date, scheduled_date, scheduled_time_window
   - estimated_duration, actual_duration
   - service_location, special_instructions
   - created_at, updated_at, completed_at

6. Work Order History/Notes table:
   - history_id (PK), work_order_id (FK), user_id (FK)
   - action_type (status_change, note_added, parts_used)
   - notes, timestamp, photos (array of URLs)

7. Parts Inventory table:
   - part_id (PK), part_name, part_number, manufacturer
   - category, description, technical_specs
   - quantity_in_stock, minimum_reorder_level, reorder_quantity
   - cost_per_unit, retail_price, markup_percentage
   - storage_location, supplier_info
   - last_reorder_date, usage_last_30_days

8. Parts Usage table:
   - usage_id (PK), work_order_id (FK), part_id (FK)
   - technician_id (FK), quantity_used
   - cost_applied, timestamp

9. Service Agreements table:
   - agreement_id (PK), customer_id (FK)
   - agreement_type (pay_per_service, monthly_maintenance, comprehensive)
   - monthly_fee, included_services, excluded_services
   - response_time_commitment, start_date, end_date
   - auto_renewal, cancellation_terms

10. Invoices table:
    - invoice_id (PK), invoice_number (unique), customer_id (FK)
    - work_order_id (FK), invoice_date, due_date
    - subtotal, tax_amount, total_amount
    - payment_status (unpaid, paid, overdue, disputed)
    - payment_date, payment_method, payment_reference

11. Invoice Line Items table:
    - line_item_id (PK), invoice_id (FK)
    - item_type (labor, parts, travel, fees)
    - description, quantity, unit_price, extended_price

12. Messages table:
    - message_id (PK), sender_id (FK), recipient_id (FK)
    - related_work_order_id (FK), subject, message_body
    - timestamp, is_read, attachments (array)

13. Audit Log table:
    - log_id (PK), user_id (FK), action, entity_type
    - entity_id, changes (JSON), ip_address, timestamp

Include proper indexes, foreign key constraints, and consider partitioning strategies for tables that will grow large (work_orders, audit_log, messages).

---

## Phase 2: Dashboard Development

### Prompt 3: Technician Dashboard

Create a comprehensive field technician dashboard with these features:

1. Today's Work Queue:
   - Display assigned jobs in priority order
   - Color-coded priority indicators (red=urgent, orange=high, blue=normal, green=routine)
   - Each job card shows:
     * Customer name and location
     * Scheduled time window
     * Service type and problem description
     * Estimated duration
     * Equipment details
   - Expandable cards revealing full details:
     * Complete customer contact info
     * Building access codes/parking instructions
     * Problem description with photos
     * Equipment history and specifications
     * Suggested parts list

2. Interactive Route Planning Map:
   - Plot all assignments geographically
   - Show optimal route with numbered sequence
   - Calculate travel time between stops with traffic data
   - Allow drag-and-drop reordering of jobs
   - Highlight scheduling conflicts from reordering
   - Show current location and navigation to next stop

3. Time Tracking:
   - Real-time timer for current job
   - Comparison to estimated duration
   - Daily summary: work time, travel time, breaks, billable hours
   - Visual indicator when running over estimated time

4. Communication Center:
   - Unread message counter
   - Recent messages from dispatch/customers
   - Quick reply functionality
   - Alert panel for urgent notifications

5. Parts & Inventory Widget:
   - Show commonly used parts
   - Current availability status
   - Quick reserve/checkout functionality
   - Low stock warnings

6. Status Controls:
   - Large, easy-to-tap status buttons (Available, Traveling, On Site, Working, On Break, Off Duty)
   - One-click check-in/check-out for jobs
   - Emergency alert button

Make the interface mobile-responsive, optimized for tablets and phones, with large touch targets and minimal text entry requirements.

---

### Prompt 4: Dispatch Manager Dashboard

Build a real-time dispatch coordinator dashboard with:

1. Unassigned Work Request Queue:
   - Prioritized list with urgency scoring
   - Each request shows:
     * Customer name and service level
     * Request age (time waiting)
     * Problem type and urgency
     * Requested timeframe
     * SLA deadline countdown
   - One-click assignment to technician
   - Bulk operations for multiple requests

2. Live Technician Status Board:
   - Grid or card view of all technicians
   - Color-coded status indicators:
     * Green = Available
     * Blue = Traveling
     * Orange = Working
     * Red = Overdue/Late
     * Gray = Off Duty
   - For each technician show:
     * Current location on mini-map
     * Current job details
     * Today's schedule with timeline
     * Utilization percentage
     * Performance metrics (avg job time vs estimate)
     * Capacity for additional work

3. Real-time KPI Metrics:
   - Jobs completed today vs target
   - Jobs in progress
   - Average response time
   - Average completion time
   - Customer satisfaction scores
   - Technician utilization rates
   - Visual comparison to historical averages

4. Geographic Heat Map:
   - Show concentration of pending requests
   - Overlay active technician locations
   - Identify coverage gaps
   - Suggest optimal assignments based on proximity

5. Schedule Timeline View:
   - Horizontal timeline with technicians as rows
   - Color-coded job blocks in time slots
   - Drag-and-drop scheduling
   - Automatic conflict detection
   - Gap identification for optimization

6. Alert & Exception Panel:
   - Jobs running significantly over time
   - Missed check-ins
   - SLA violations or near-violations
   - Parts unavailable for scheduled jobs
   - Customer complaints/escalations
   - Priority notifications with action buttons

7. Assignment Workflow:
   - Intelligent technician suggestion algorithm considering:
     * Skills/certifications
     * Geographic proximity
     * Current workload
     * Customer preferences
     * Historical performance
   - Show impact metrics for each assignment option
   - Route optimization suggestions
   - Automated scheduling with manual override

Include real-time updates via WebSockets or polling, with automatic refresh of status changes.

---

### Prompt 5: Administrator Dashboard

Design a system administrator dashboard featuring:

1. System Health Monitoring:
   - Server uptime percentage with historical trend
   - Current response time metrics
   - Database performance indicators
   - Storage utilization with projections
   - Active user session count
   - API endpoint health status
   - Visual indicators (green/yellow/red) for all metrics

2. Security Monitoring:
   - Failed login attempt counter
   - Geographic access anomalies
   - Unusual data access patterns
   - Security alert flags
   - Recent account lockouts
   - Suspicious activity log with details

3. User Management Summary:
   - Total active accounts by role type
   - Recent account creations
   - Recently deactivated accounts
   - Pending password resets
   - Accounts requiring attention:
     * Inactive for 90+ days
     * Locked accounts
     * Access request queue

4. Business Intelligence Overview:
   - Current month revenue vs projection vs last month
   - Job volume trends (graph)
   - Customer acquisition/churn metrics
   - Average profitability per job
   - Service type breakdown (pie chart)

5. Compliance Dashboard:
   - Data backup status with last backup time
   - Security patch status
   - Audit log integrity verification
   - Data privacy compliance indicators
   - Regulatory requirement checklist

6. System Configuration Quick Access:
   - Shortcut buttons for:
     * User account creation
     * System settings adjustment
     * Database maintenance
     * Data export/backup
     * System logs review
     * Integration management

7. Alert Center:
   - Critical system notifications
   - Maintenance schedules
   - License expiration warnings
   - Storage capacity warnings
   - Integration failures

Include comprehensive filtering, date range selection, and export capabilities for all data views.

---

## Phase 3: Work Order Management

### Prompt 6: Work Order Creation & Management

Build a comprehensive work order management system with:

1. Work Order Creation Wizard (multi-step):
   
   Step 1 - Customer Selection:
   - Searchable customer dropdown
   - Display customer service level and status
   - Quick "Add New Customer" option
   - Show customer's recent service history
   
   Step 2 - Equipment Selection:
   - Display customer's equipment inventory
   - Filter by location, type, status
   - Show equipment health score and last service
   - "Add New Equipment" option
   
   Step 3 - Problem Description:
   - Large text area with character counter
   - Helper text with good description examples
   - Problem category dropdown
   - Upload photos/videos (drag & drop)
   - Template selection for common issues
   
   Step 4 - Priority & Scheduling:
   - Priority level selection with cost implications
   - Calendar date picker with availability indicators
   - Time window selection (morning/afternoon/specific)
   - Special instructions field
   - Access requirements checklist
   
   Step 5 - Assignment (for employees):
   - Suggested technician recommendation
   - Manual technician selection
   - Skills match indicator
   - Availability verification
   - Route optimization preview
   
   Step 6 - Review & Submit:
   - Complete summary display
   - Cost estimate if applicable
   - Edit buttons for each section
   - Terms acknowledgment
   - Submit button

2. Work Order List Views:
   
   Table View:
   - Sortable columns: ticket#, customer, status, priority, date, technician
   - Multi-criteria filtering
   - Bulk actions (assign, change priority, export)
   - Status indicators with color coding
   - Quick actions menu per row
   
   Calendar View:
   - Month/week/day views
   - Color-coded by priority or status
   - Drag-to-reschedule
   - Conflict warnings
   - Capacity indicators
   
   Kanban Board View:
   - Columns: Submitted → Assigned → In Progress → Completed → Closed
   - Drag between columns to update status
   - Card preview with key info
   - Filter by technician, priority, customer
   
   Map View:
   - Geographic plotting of work orders
   - Filter by status, date range
   - Cluster markers for dense areas
   - Click marker for details
   - Route planning overlay

3. Work Order Detail Page:
   
   Header Section:
   - Large ticket number
   - Status badge
   - Priority indicator
   - Created/updated timestamps
   
   Customer Information Panel:
   - Name, contact info, service level
   - Location with map
   - Access instructions
   - Communication history
   
   Equipment Information Panel:
   - Equipment name and photo
   - Specifications
   - Warranty status
   - Service history timeline
   
   Problem Details Section:
   - Original description
   - Attached photos/videos
   - Category tags
   
   Assignment & Schedule Section:
   - Assigned technician with photo
   - Scheduled date/time
   - Estimated vs actual duration
   - Status timeline with updates
   
   Parts & Materials Section:
   - Parts used log with costs
   - Materials consumed
   - Availability checker
   - Request additional parts button
   
   Work Notes & Documentation:
   - Chronological activity feed
   - Technician notes entries
   - Status change history
   - Photo uploads during service
   - Time stamped entries
   
   Financial Information:
   - Labor charges breakdown
   - Parts charges
   - Travel/trip fees
   - Tax calculation
   - Total cost
   
   Action Buttons:
   - Edit details
   - Change status
   - Reassign technician
   - Add notes
   - Upload photos
   - Generate invoice
   - Clone work order
   - Print/export

4. Status Management:
   - Clear status workflow definitions
   - Automatic notifications on status changes
   - Required fields per status
   - Status change validation rules
   - Audit trail of all changes

Implement proper validation, error handling, and confirmation dialogs for destructive actions.

---

### Prompt 7: Mobile Work Order Interface for Technicians

Create a mobile-optimized work order interface for field technicians with:

1. Job List Screen:
   - Large, touch-friendly job cards
   - Swipe actions (call customer, navigate, start job)
   - Color-coded priority borders
   - Time-ordered or route-ordered sorting
   - Pull-to-refresh
   - Job counter badge

2. Job Detail Screen:
   
   Top Section:
   - Customer name and location (tappable for maps)
   - One-tap call button
   - One-tap message button
   - Status change dropdown
   
   Problem Information:
   - Clear problem description
   - Photo gallery with pinch-to-zoom
   - Equipment details expandable section
   
   Navigation:
   - "Navigate Here" button (opens Maps app)
   - Estimated travel time
   - Address copy button
   
   Check-in/Status Controls:
   - Large "Arrive at Site" button
   - "Start Work" button
   - "Request Help" button
   - Timer display when working

3. Photo Capture Screen:
   - Camera access with in-app capture
   - Multiple photo support
   - Auto-labeling (Before/During/After)
   - Photo annotation tools
   - Photo deletion option
   - Upload progress indicator

4. Work Notes Screen:
   - Voice-to-text input support
   - Template notes for common scenarios
   - Time-stamped entries
   - Note categories (diagnosis, repair, testing)
   - Attachment support

5. Parts Usage Screen:
   - Barcode scanner for part lookup
   - Quick add from favorites
   - Search parts catalog
   - Quantity selector
   - Running cost total
   - Available inventory indicator

6. Time Tracking Screen:
   - Large start/pause/stop timer
   - Break time logging
   - Activity categorization (diagnosis, repair, travel)
   - Daily time summary
   - Manual time entry option

7. Customer Sign-off Screen:
   - Work summary display
   - Signature pad (smooth drawing)
   - Clear signature button
   - Customer satisfaction quick rating
   - Additional comments field
   - Submit completion button

8. Offline Mode:
   - Queue actions when offline
   - Show offline indicator
   - Auto-sync when connection restored
   - Local storage of job data
   - Conflict resolution on sync

9. Quick Actions Menu:
   - Available from anywhere
   - Emergency support contact
   - Report problem
   - Request parts delivery
   - Check inventory
   - View today's schedule

Design for single-hand use with bottom navigation, large touch targets (minimum 44x44px), high contrast for outdoor visibility, and minimal data usage.

---

## Phase 4: Scheduling & Assignment

### Prompt 8: Intelligent Scheduling System

Build an intelligent scheduling and assignment system with:

1. Assignment Recommendation Engine:
   
   Algorithm should consider:
   - Technician skill levels and certifications matching job requirements
   - Geographic proximity to service location
   - Current workload and availability
   - Travel time from current/previous location
   - Customer preferences for specific technicians
   - Historical performance on similar jobs
   - Work hour constraints and overtime rules
   
   Output should show:
   - Ranked list of suitable technicians
   - Score/match percentage for each
   - Pros/cons for each option
   - Impact on each technician's schedule
   - Estimated start time for customer

2. Route Optimization:
   - Multi-stop route calculation
   - Traffic-aware timing (integration with mapping API)
   - Reorder suggestions to minimize drive time
   - Show time savings from optimization
   - Consider customer time windows as constraints
   - "Accept Optimization" one-click application

3. Schedule Conflict Detection:
   - Real-time validation when assigning
   - Detect overlapping appointments
   - Identify insufficient travel time
   - Flag violations of customer time preferences
   - Warn about overtime/maximum hours
   - Suggest alternatives when conflicts found

4. Capacity Planning:
   - Visual capacity indicators per technician
   - Daily/weekly/monthly utilization metrics
   - Forecast future capacity needs
   - Identify under-utilized resources
   - Alert when approaching capacity limits
   - Suggest hiring needs based on trends

5. Automated Scheduling Rules:
   - Auto-assign based on configurable rules:
     * Customer always gets same technician
     * Specific equipment types → certified technicians
     * Geographic territory assignments
     * Round-robin distribution
     * Skill-based routing
   - Rule priority system
   - Manual override capability
   - Rule audit logging

6. Schedule Adjustment Tools:
   - Drag-and-drop rescheduling
   - Swap appointments between technicians
   - Bulk reschedule operations
   - Schedule compression (fill gaps)
   - Emergency insertion (bump lower priority)
   - Impact analysis before confirming changes

7. Calendar Integration:
   - Sync with Google Calendar, Outlook, Apple Calendar
   - Two-way sync support
   - Respect blocked time in personal calendars
   - Automatic update propagation
   - Conflict resolution preferences

8. Recurring Appointment Management:
   - Create recurring schedules (daily, weekly, monthly, custom)
   - Preventive maintenance automation
   - Series editing (single instance vs all)
   - Skip/postpone individual occurrences
   - End date or occurrence count limits

Implement with transaction safety to prevent double-booking and maintain schedule integrity.

---

## Phase 5: Equipment & Asset Management

### Prompt 9: Equipment Management System

Create a comprehensive equipment tracking and management system with:

1. Equipment Registration:
   
   Input Form:
   - Equipment name/identifier
   - Type/category (hierarchical taxonomy)
   - Manufacturer (searchable dropdown)
   - Model number (with auto-complete from database)
   - Serial number (unique validation)
   - Purchase information (date, price, vendor)
   - Warranty details (provider, type, dates, coverage terms)
   - Physical location (address, building, room)
   - Primary user/owner
   - Photo upload (multiple images)
   - Technical specifications (JSON flexible schema)
   - Custom fields per equipment type
   
   Validation:
   - Duplicate serial number detection
   - Required fields enforcement
   - Date logic validation (purchase before warranty start)
   - Format validation for serial numbers

2. Equipment Inventory Views:
   
   List View:
   - Tabular display with sortable columns
   - Multi-criteria filtering:
     * Customer/owner
     * Equipment type
     * Manufacturer
     * Location
     * Status (operational, needs service, decommissioned)
     * Age range
     * Warranty status
   - Quick search across all fields
   - Export to CSV/Excel
   - Bulk edit capabilities
   
   Card/Grid View:
   - Visual cards with equipment photos
   - Status badges and health indicators
   - Quick action menu per card
   - Pagination or infinite scroll
   
   Location View:
   - Group by physical location
   - Hierarchical display (building → floor → room)
   - Equipment count per location
   - Interactive floor plans (if available)

3. Equipment Detail Page:
   
   Overview Tab:
   - High-quality photo gallery
   - Status and health score
   - Quick stats (age, total service costs, downtime days)
   - QR code for mobile scanning
   - Asset tag printing option
   
   Specifications Tab:
   - Complete technical specifications
   - Capacity/performance metrics
   - Supported features list
   - Network information (IP, MAC address)
   - Physical dimensions and weight
   
   Warranty Tab:
   - Coverage details with visual timeline
   - Terms and conditions document
   - Claim history
   - Expiration countdown
   - Renewal reminders
   - Coverage gap warnings
   
   Service History Tab:
   - Chronological timeline of all service events
   - Each event shows:
     * Date and duration
     * Problem and resolution
     * Technician who performed work
     * Parts replaced
     * Cost incurred
     * Before/after photos
   - Filter by date range or service type
   - Export service history
   
   Documents Tab:
   - User manuals (upload/link)
   - Service manuals
   - Warranty documents
   - Purchase receipts
   - Configuration documents
   - Training materials
   - File organization and versioning
   
   Metrics Tab:
   - Total cost of ownership graph
   - Mean time between failures
   - Average repair time
   - Downtime percentage
   - Maintenance cost trend
   - Comparison to similar equipment

4. Equipment Health Scoring:
   - Algorithm considering:
     * Age vs expected lifespan
     * Service frequency
     * Repair cost trend
     * Downtime incidents
     * Critical component failures
   - Score visualization (0-100 or letter grade)
   - Health trend graph
   - Predictive alerts (equipment likely to fail soon)
   - Replacement recommendations

5. Lifecycle Management:
   - Lifecycle status tracking:
     * New/recently purchased
     * Under warranty
     * In service/operational
     * End of warranty
     * Frequent repairs
     * Recommended for replacement
     * Decommissioned
   - Automated status progression
   - Lifecycle policy configuration
   - Replacement planning reports

6. Equipment Relationships:
   - Parent-child relationships (server → blade servers)
   - Dependencies (UPS → connected equipment)
   - Network topology visualization
   - Impact analysis (if this fails, what else is affected)

7. Preventive Maintenance Scheduling:
   - Define maintenance schedules per equipment type
   - Automatic work order generation
   - Maintenance checklist templates
   - Completion tracking
   - Missed maintenance alerts

8. Equipment Import/Export:
   - Bulk import via CSV upload
   - Field mapping interface
   - Data validation during import
   - Error reporting and correction
   - Export filtered equipment lists

Include barcode/QR code generation for equipment labeling and mobile scanning for quick equipment lookup.

---

## Phase 6: Parts & Inventory Management

### Prompt 10: Inventory Management System

Develop a comprehensive parts and inventory management system with:

1. Parts Catalog:
   
   Part Record Structure:
   - Part ID (auto-generated)
   - Part name/description
   - Manufacturer
   - Manufacturer part number
   - Internal SKU/part number
   - Category/subcategory (hierarchical)
   - Technical specifications
   - Compatibility information (which equipment uses this)
   - Unit of measure
   - Photos from multiple angles
   - Datasheet/documentation links
   - Supplier information (primary and alternatives)
   
   Catalog Management:
   - Add/edit/deactivate parts
   - Bulk import from supplier catalogs
   - Duplicate detection
   - Merge duplicate parts
   - Part substitution/alternative suggestions
   - Obsolescence marking

2. Inventory Tracking:
   
   Stock Levels:
   - Quantity on hand
   - Quantity reserved (assigned to jobs)
   - Quantity on order
   - Available quantity (calculated)
   - Multiple storage locations with quantities per location
   - Bin/shelf location identifiers
   
   Inventory Transactions:
   - Receiving (purchases, returns)
   - Usage (consumed on jobs)
   - Adjustments (corrections, damage, theft)
   - Transfers (between locations)
   - Returns to supplier
   - Each transaction logs:
     * Date/time
     * User who performed it
     * Quantity and reason
     * Associated document (PO, work order)
   
   Reorder Management:
   - Minimum stock level per part
   - Reorder point
   - Reorder quantity
   - Automatic purchase order generation
   - Reorder alerts for below-minimum parts
   - Economic order quantity calculations

3. Financial Tracking:
   
   Cost Information:
   - Unit cost (from supplier)
   - Average cost (FIFO, LIFO, or weighted average)
   - Cost history and trends
   - Quantity discount tiers
   - Retail/customer price
   - Markup percentage
   - Price history
   
   Valuation:
   - Total inventory value calculation
   - Value by category
   - Aging analysis (identify slow-moving stock)
   - Obsolete inventory identification
   - Write-off tracking

4. Usage Analytics:
   
   Reports and Dashboards:
   - Parts usage by time period
   - Most frequently used parts
   - Parts with highest consumption cost
   - Usage by technician
   - Usage by customer
   - Usage by equipment type
   - Seasonal patterns
   - Trend analysis and forecasting
   
   Visualizations:
   - Bar charts for top parts
   - Line graphs for usage trends
   - Heat maps for seasonal patterns
   - Pie charts for cost distribution

5. Supplier Management:
   
   Supplier Records:
   - Supplier name and contact info
   - Payment terms
   - Lead times
   - Minimum order quantities
   - Shipping costs
   - Performance ratings
   - Preferred supplier flags
   
   Purchase Order System:
   - PO creation from reorder suggestions
   - Manual PO creation
   - PO approval workflow (if over threshold)
   - Send PO to supplier (email/EDI)
   - PO tracking and status
   - Receiving against PO
   - Partial receipt handling
   - Discrepancy resolution

6. Technician Inventory Management:
   
   Vehicle Stock Tracking:
   - Parts assigned to each technician
   - Check-out/check-in system
   - Restocking alerts
   - Return unused parts
   - Transfer between technicians
   
   Usage Recording:
   - Scan barcode to log usage
   - Manual entry with part lookup
   - Quantity used
   - Automatic work order association
   - Automatic cost application

7. Barcode/RFID Support:
   - Generate barcode labels for parts
   - Print barcode sheets
   - Barcode scanning for receiving
   - Barcode scanning for usage
   - RFID tag support for tracking

8. Integration Capabilities:
   - Sync with accounting system for financial data
   - Import supplier catalogs
   - Export for external analysis
   - API for third-party connections

9. Cycle Counting:
   - Schedule regular inventory counts
   - Generate count sheets
   - Mobile app for counting
   - Variance reporting
   - Adjustment workflow

Build with real-time updates, transaction integrity, and comprehensive audit trails.

---

## Phase 7: Customer Portal

### Prompt 11: Customer Self-Service Portal

Create a customer-facing web portal with:

1. Account Dashboard:
   
   Overview Section:
   - Welcome message with customer name
   - Account summary card showing:
     * Account status (active, suspended)
     * Service agreement type
     * Current balance (with payment due indicator)
     * Next scheduled maintenance
   
   Active Service Requests:
   - Cards for each open request
   - Status badges with progress indicator
   - Technician info with photo
   - Estimated arrival/completion time
   - Quick action buttons (message, cancel, reschedule)
   - Live tracking link when technician en route
   
   Equipment Overview:
   - Total device count
   - Visual health indicators (pie chart: healthy/warning/critical)
   - List of devices needing attention
   - Quick link to equipment inventory
   
   Recent Activity Feed:
   - Last 5-10 service requests with brief status
   - Dates and technicians
   - Cost for completed services
   - Quick link to details
   
   Quick Actions Panel:
   - Large "Request Service" button
   - "View Equipment" button
   - "Pay Invoice" button
   - "Contact Support" button
   - "Knowledge Base" button

2. Service Request Creation:
   
   Step 1: Select Equipment
   - Visual grid of customer's equipment
   - Filters: location, type, status
   - "Equipment Not Listed?" option
   
   Step 2: Describe Problem
   - Large text box with helper text
   - Photo/video upload (drag & drop)
   - Problem category selection
   
   Step 3: Priority & Schedule
   - Priority level with clear cost implications
   - Calendar with available dates
   - Time preferences
   - Special instructions
   
   Step 4: Review & Submit
   - Summary of all information
   - Cost estimate
   - Terms acceptance
   - Submit button
   
   Confirmation Page:
   - Ticket number prominently displayed
   - Summary of request
   - Expected response time
   - Next steps explanation
   - Option to track request

3. Service Request Tracking:
   
   Request Detail Page:
   - Large ticket number
   - Status progress bar
   - Current status description
   - Timeline of all activities
   
   Technician Information (when assigned):
   - Name and photo
   - Contact button (call or message)
   - Customer ratings/reviews
   - Qualifications/certifications
   
   Scheduled Appointment:
   - Date and time window
   - Countdown to appointment
   - Add to calendar button
   - Reschedule button
   
   Live Tracking (when technician en route):
   - Map showing technician location
   - Updated ETA
   - "Technician nearby" notification
   
   Messaging Interface:
   - Chat-style conversation
   - Send messages to technician
   - Receive updates
   - Photo sharing
   - Message history
   
   Work Completion:
   - Detailed service report
   - Before/during/after photos
   - Parts used and costs
   - Technician notes
   - Digital signature option
   - Satisfaction rating prompt

4. Equipment Management:
   
   Equipment List:
   - Grid or list view toggle
   - Each item shows:
     * Photo
     * Name/description
     * Type and manufacturer
     * Status indicator
     * Last service date
     * Quick "Request Service" button
   - Filter and sort options
   - Add new equipment button
   
   Equipment Detail Page:
   - Complete specifications
   - Service history timeline
   - Warranty information
   - Documents and manuals
   - Health score and recommendations
   - Request service button

5. Billing & Payments:
   
   Invoice List:
   - Table view of all invoices
   - Status indicators (paid, unpaid, overdue)
   - Date, amount, description
   - Download PDF button per invoice
   - Filter by status, date range
   
   Invoice Detail:
   - Itemized breakdown
   - Service description
   - Parts and labor costs
   - Tax calculation
   - Total amount
   - Payment history
   - Dispute/question button
   
   Payment Interface:
   - Select invoice(s) to pay
   - Payment method selection
   - Credit card form (PCI compliant)
   - Bank account (ACH) form
   - Digital wallet options
   - Save payment method for future
   - Submit payment button
   - Receipt generation
   
   Auto-Pay Setup:
   - Enroll in automatic payments
   - Select payment method
   - Choose payment date
   - Modify or cancel anytime

6. Communication Center:
   
   Message Inbox:
   - List of conversations
   - Unread message counter
   - Search messages
   - Filter by sender, date, work order
   
   Compose Message:
   - Recipient selection
   - Subject line
   - Message body with formatting
   - File attachments
   - Link to related work order
   
   Notifications:
   - Notification preferences panel
   - Email, SMS, or in-app options
   - Notification types:
     * Service request updates
     * Appointment reminders
     * Invoice notifications
     * Marketing messages
   - Opt in/out per type

7. Knowledge Base:
   
   Browse Articles:
   - Categorized topics
   - Popular articles
   - Recent additions
   - Search functionality
   
   Article View:
   - Step-by-step instructions
   - Embedded images/videos
   - Helpful/not helpful buttons
   - Related articles
   - "Still need help?" contact option

8. Account Settings:
   
   Profile Management:
   - Personal information
   - Contact details
   - Password change
   - MFA setup
   
   Service Addresses:
   - Add/edit service locations
   - Set default location
   - Access instructions per location
   
   Preferences:
   - Communication preferences
   - Preferred service times
   - Preferred technicians
   - Special requirements
   
   Service Agreement:
   - View agreement details
   - Download contract
   - Renewal information
   - Upgrade options

Design with responsive layout, mobile-first approach, accessibility compliance (WCAG 2.1 AA), and intuitive navigation.

---

## Phase 8: Communication & Messaging

### Prompt 12: Integrated Messaging System

Build a comprehensive communication and messaging platform with:

1. Message Infrastructure:
   
   Database Schema:
   - messages table (id, thread_id, sender_id, recipient_id, subject, body, timestamp, is_read, related_work_order_id, parent_message_id)
   - message_attachments table (id, message_id, file_name, file_path, file_type, file_size)
   - message_participants table (for group messages)
   - notification_preferences table (user_id, channel, frequency)
   
   Message Types:
   - Direct messages (user to user)
   - Group messages (multiple participants)
   - System notifications (automated messages)
   - Work order comments/notes
   - Broadcast messages (admin to many users)

2. Inbox Interface:
   
   Layout:
   - Three-column layout (folders, message list, message content)
   - Responsive collapse on mobile
   
   Folders/Categories:
   - Inbox
   - Sent
   - Drafts
   - Archived
   - Starred
   - Work Order Messages (grouped by ticket)
   - Custom folders
   
   Message List:
   - Preview shows: sender, subject, timestamp, snippet
   - Unread indicator (bold text, counter badge)
   - Star/flag capability
   - Attachment indicator icon
   - Related work order indicator
   - Selection checkboxes
   - Pagination or infinite scroll
   
   Bulk Actions:
   - Mark as read/unread
   - Archive
   - Delete
   - Move to folder
   - Forward
   
   Filters and Search:
   - Filter by: sender, date range, has attachments, work order
   - Full-text search across subject and body
   - Advanced search options

3. Message Composition:
   
   Compose Interface:
   - To: field with auto-complete
   - Multiple recipients support
   - CC/BCC options
   - Subject line
   - Rich text editor:
     * Bold, italic, underline
     * Bulleted/numbered lists
     * Links
     * Text color/highlighting
     * Font size
   - File attachments:
     * Drag and drop upload
     * Multiple files support
     * File size limits
     * Progress indicators
     * Preview thumbnails
   - Link to work order
   - Save as draft
   - Send button with confirmation

4. Message Templates:
   
   Template Management:
   - Create templates for common scenarios:
     * Appointment confirmation
     * Running late notification
     * Work completed
     * Quote provided
     * Payment reminder
     * Follow-up message
   
   Template Features:
   - Merge fields (customer name, appointment time, technician name, etc.)
   - Subject and body templates
   - Category organization
   - Personal and shared templates
   - Edit and delete templates
   
   Using Templates:
   - Select from dropdown when composing
   - Merge fields auto-populate
   - Customize before sending
   - Save customized version as new template

5. Email Integration:
   
   Outbound Email:
   - Send messages via SMTP
   - Branded email templates
   - Track email opens (optional)
   - Track link clicks (optional)
   - Bounce handling
   
   Inbound Email:
   - Parse incoming emails
   - Create messages in system
   - Attachment extraction
   - Thread matching (reply to existing conversation)
   - Auto-response for after-hours

6. SMS/Text Messaging:
   
   SMS Sending:
   - Integration with SMS gateway (Twilio, AWS SNS)
   - Character limit indicator
   - Cost per message display
   - Opt-in verification
   - Unsubscribe handling
   
   SMS Receiving:
   - Parse incoming texts
   - Create messages in system
   - Associate with customer account
   - Auto-response capability
   - Keywords for automated actions

7. Push Notifications:
   
   Mobile App Notifications:
   - New message alerts
   - Work order updates
   - Appointment reminders
   - Payment due reminders
   
   Web Browser Notifications:
   - Desktop notifications when app is closed
   - Permission request workflow
   - Notification settings per user

8. Real-Time Chat:
   
   Chat Interface:
   - Live chat for immediate communication
   - Typing indicators
   - Message delivery/read receipts
   - Online/offline status
   - Quick emoji reactions
   
   Implementation:
   - WebSocket connection
   - Fallback to polling
   - Message queuing when offline
   - Auto-reconnect logic

9. Automated Messaging:
   
   Trigger-Based Messages:
   - Work order status changes
   - Appointment upcoming (24hrs, 2hrs)
   - Technician en route
   - Work completed
   - Invoice generated
   - Payment received
   - Payment overdue
   - Satisfaction survey invitation
   
   Configuration:
   - Enable/disable each message type
   - Customize message content
   - Set delivery channels (email, SMS, push)
   - Define trigger conditions
   - Schedule delivery times

10. Message Threading:
    - Group related messages together
    - Show full conversation history
    - Quote previous messages in replies
    - Collapse old messages
    - Expand to see all

11. Notification Preferences:
    
    Per User Settings:
    - Delivery channels: email, SMS, push, in-app
    - Frequency: immediate, hourly digest, daily digest
    - Message types: which notifications to receive
    - Quiet hours: no notifications during specified times
    - VIP senders: always notify for certain senders

12. Analytics Dashboard:
    - Message volume over time
    - Response time metrics
    - Most active users
    - Message templates usage
    - Email open rates
    - SMS delivery rates

Include proper spam protection, rate limiting, and compliance with communication regulations (CAN-SPAM, TCPA, GDPR).

---

## Phase 9: Reporting & Analytics

### Prompt 13: Comprehensive Reporting System

Create an advanced reporting and analytics system with:

1. Report Categories:

   A. Operational Reports:
   
   Daily Work Summary:
   - Total jobs completed
   - Jobs by status (in progress, completed, cancelled)
   - Jobs by priority level
   - Average completion time
   - Technician productivity
   - Filter by: date range, technician, customer, service type
   
   Technician Performance:
   - Jobs completed per technician
   - Average job duration vs estimates
   - First-time fix rate
   - Customer satisfaction scores
   - On-time arrival percentage
   - Overtime hours
   - Billable vs non-billable hours
   - Compare technicians side-by-side
   
   Schedule Adherence:
   - On-time vs late arrivals
   - Jobs completed within scheduled window
   - Schedule deviation reasons
   - Average delay duration
   - Day-by-day breakdown
   
   Response Time Analysis:
   - Time from request to assignment
   - Time from assignment to start
   - Time from start to completion
   - By priority level
   - By customer service tier
   - SLA compliance percentage
   
   B. Financial Reports:
   
   Revenue Summary:
   - Total revenue by period (day/week/month/quarter/year)
   - Revenue by service type
   - Revenue by customer segment
   - Revenue by geographic region
   - Revenue trend graph
   - Comparison to previous period
   - Comparison to budget/forecast
   
   Cost Analysis:
   - Labor costs
   - Parts costs
   - Material costs
   - Travel costs
   - Overhead allocation
   - Cost per job
   - Cost trends over time
   
   Profitability Report:
   - Revenue vs costs
   - Gross profit by service type
   - Gross profit by customer
   - Profit margin percentages
   - Loss-making jobs identification
   - Break-even analysis
   
   Accounts Receivable Aging:
   - Current (0-30 days)
   - 31-60 days past due
   - 61-90 days past due
   - Over 90 days past due
   - Total outstanding by customer
   - Collection effectiveness
   
   Invoice History:
   - All invoices by date range
   - Invoice status breakdown
   - Average days to payment
   - Payment methods used
   - Discount/adjustment analysis
   
   C. Customer Reports:
   
   Customer Satisfaction:
   - Average rating by period
   - Rating distribution (1-5 stars)
   - Satisfaction by technician
   - Satisfaction by service type
   - Trend over time
   - Low-rating analysis
   - Comments and feedback summary
   
   Customer Activity:
   - Service requests per customer
   - Revenue per customer
   - Frequency of service
   - Customer lifetime value
   - Customer acquisition date
   - Churn risk scoring
   
   Service Level Agreement Compliance:
   - SLA adherence percentage
   - Violations by customer
   - Violations by SLA metric
   - Near-miss analysis
   - Root cause of violations
   
   D. Equipment Reports:
   
   Equipment Reliability:
   - Failure rate by equipment type
   - Failure rate by manufacturer
   - Failure rate by age
   - Mean time between failures
   - Most problematic equipment
   
   Maintenance Frequency:
   - Service calls per equipment
   - Service costs per equipment
   - Equipment with most downtime
   - Preventive vs reactive maintenance ratio
   
   Parts Usage:
   - Most frequently used parts
   - Parts cost analysis
   - Parts by equipment type
   - Parts inventory turnover
   - Parts with highest consumption
   
   Lifecycle Analysis:
   - Equipment age distribution
   - Equipment near end-of-life
   - Replacement recommendations
   - Total cost of ownership by equipment

2. Report Builder:
   
   Custom Report Creation:
   - Select data source (work orders, invoices, equipment, etc.)
   - Choose fields to include
   - Apply filters (date range, customer, status, etc.)
   - Group by dimensions
   - Sort results
   - Add calculated fields
   - Define formulas
   
   Visualization Options:
   - Table/grid view
   - Bar charts (vertical/horizontal)
   - Line graphs
   - Pie charts
   - Area charts
   - Scatter plots
   - Heat maps
   - Gauges
   
   Save and Share:
   - Save report definition
   - Schedule automatic generation
   - Email distribution lists
   - Export formats (PDF, Excel, CSV)
   - Public link generation
   - Embed in dashboards

3. Interactive Dashboards:
   
   Dashboard Types:
   - Executive dashboard (high-level KPIs)
   - Operations dashboard (daily metrics)
   - Technician dashboard (individual performance)
   - Customer success dashboard
   - Financial dashboard
   
   Dashboard Features:
   - Drag-and-drop widget arrangement
   - Resize widgets
   - Multiple dashboard support
   - Set as default dashboard
   - Real-time data updates
   - Drill-down capabilities (click for details)
   - Date range selector
   - Filter panel
   - Refresh button
   
   Widget Types:
   - KPI cards (single number with trend)
   - Charts and graphs
   - Tables/grids
   - Progress bars
   - Sparklines
   - Maps
   - Lists
   - Text/notes

4. Scheduled Reports:
   
   Scheduling Options:
   - Frequency: daily, weekly, monthly, quarterly, annually
   - Day of week/month
   - Time of day
   - Time zone consideration
   
   Distribution:
   - Email recipients list
   - Attach as file or link
   - Email subject and body template
   - Success/failure notifications
   
   Parameters:
   - Date range (last 7 days, last month, etc.)
   - Dynamic filters
   - Conditional generation (only if certain criteria met)

5. Data Export:
   
   Export Formats:
   - PDF (formatted for printing)
   - Excel (XLSX) with formatting
   - CSV (comma-separated values)
   - JSON (for integrations)
   - XML
   
   Export Options:
   - Include/exclude charts
   - Page layout (portrait/landscape)
   - Paper size
   - Header/footer customization
   - Logo inclusion

6. Report Permissions:
   
   Access Control:
   - Who can view each report
   - Who can edit report definitions
   - Who can create new reports
   - Data filtering based on role
   - Field-level permissions

7. Benchmarking:
   
   Compare Performance:
   - Current vs previous period
   - Actual vs budget
   - Actual vs industry standard
   - Team vs individual
   - Location vs location
   
   Variance Analysis:
   - Highlight significant differences
   - Calculate variance percentage
   - Trend indicators (up/down/flat)

8. Predictive Analytics:
   
   Forecasting:
   - Revenue projection
   - Workload forecast
   - Parts consumption prediction
   - Equipment failure prediction
   - Customer churn probability
   
   Trend Identification:
   - Growth/decline patterns
   - Seasonal variations
   - Anomaly detection
   - Statistical significance

Implement with efficient query optimization, caching for frequently-run reports, and export queue management for large reports.

---

## Phase 10: Billing & Financial Management

### Prompt 14: Complete Billing System

Develop a comprehensive billing and financial management system with:

1. Pricing Configuration:
   
   Labor Rates:
   - Multiple rate tiers (standard, senior, specialist)
   - Service type rates (installation, repair, maintenance, consultation)
   - Time-based rates (regular hours, after-hours, weekend, holiday)
   - Emergency/urgent service premiums
   - Volume discounts
   - Customer-specific rates (contract pricing)
   
   Parts Pricing:
   - Cost basis (last cost, average cost, standard cost)
   - Markup methods (percentage, fixed amount)
   - Markup by part category
   - Customer-specific pricing
   - Quantity discounts
   - Bundle pricing
   
   Service Fees:
   - Trip/service call charges
   - Diagnostic fees
   - Mileage charges (per mile or tiered)
   - Expedite fees
   - Fuel surcharges
   - Environmental fees
   
   Tax Configuration:
   - Tax rates by jurisdiction
   - Tax applicability (which services/parts are taxable)
   - Multiple tax support (state, county, city)
   - Tax-exempt customer handling
   - Tax override capability

2. Quote/Estimate Generation:
   
   Quote Creation:
   - Work order association
   - Customer selection with pricing rules
   - Line item entry:
     * Description
     * Quantity
     * Unit price
     * Extended price
     * Tax status
   - Labor estimate (hours × rate)
   - Parts list with costs
   - Additional fees
   - Subtotal, tax, total calculation
   - Validity period (quote expires on date)
   - Terms and conditions
   - Notes/special instructions
   
   Quote Templates:
   - Pre-defined service packages
   - Common repair scenarios
   - Preventive maintenance bundles
   - Quick quote generation
   
   Quote Approval Workflow:
   - Send to customer for approval
   - Customer can view in portal
   - Approve/decline actions
   - Digital signature capture
   - Negotiation/revision tracking
   - Convert approved quote to work order

3. Invoice Generation:
   
   Automatic Invoice Creation:
   - Trigger on work order completion
   - Pull data from work order:
     * Labor time × rate
     * Parts used × price
     * Fees applied
     * Tax calculated
   - Generate unique invoice number
   - Set due date based on payment terms
   
   Manual Invoice Creation:
   - For services outside work orders
   - For recurring charges
   - For one-time fees
   - Flexible line item entry
   
   Invoice Components:
   - Header: invoice #, date, due date, customer info
   - Line items: detailed breakdown
   - Subtotals by category
   - Tax calculation
   - Discounts/adjustments
   - Total amount due
   - Payment instructions
   - Terms and conditions
   - Company branding (logo, contact info)
   
   Invoice Types:
   - Standard invoice
   - Progress invoice (partial billing)
   - Final invoice
   - Credit memo (refund)
   - Debit memo (additional charges)

4. Payment Processing:
   
   Payment Methods:
   - Credit/debit card
     * Integration with payment gateway (Stripe, Square, Authorize.Net)
     * PCI compliance
     * Card tokenization for security
     * CVV verification
   - ACH/bank transfer
     * Bank account verification
     * Processing time disclosure
     * Failed payment retry logic
   - Cash
     * Receipt generation
     * Reconciliation tracking
   - Check
     * Check number recording
     * Deposit date tracking
     * Cleared/bounced status
   - Digital wallets (PayPal, Apple Pay, Google Pay)
   - Store credit
   
   Payment Recording:
   - Manual payment entry
   - Automatic recording from gateway
   - Payment amount
   - Payment date
   - Payment method
   - Reference number
   - Apply to specific invoice(s)
   - Partial payment handling
   - Overpayment handling (credit balance)
   
   Payment Plans:
   - Installment setup
   - Payment schedule definition
   - Automatic recurring billing
   - Payment reminder automation
   - Late payment handling

5. Recurring Billing:
   
   Subscription Management:
   - Service agreement billing
   - Monthly maintenance contracts
   - Annual support packages
   
   Recurring Invoice Configuration:
   - Billing frequency (monthly, quarterly, annually)
   - Bill date (first of month, anniversary date, etc.)
   - Amount
   - Auto-charge vs send invoice
   - Duration (ongoing or fixed term)
   
   Automatic Processing:
   - Generate invoice on schedule
   - Charge payment method on file
   - Email invoice to customer
   - Handle payment failures
   - Retry logic
   - Dunning process

6. Accounts Receivable Management:
   
   Aging Reports:
   - Current (0-30 days)
   - 31-60 days past due
   - 61-90 days past due
   - 90+ days past due
   - Total outstanding per customer
   
   Collections Management:
   - Overdue invoice tracking
   - Automated reminder emails
   - Collection call scheduling
   - Payment plan negotiation
   - Collection agency referral
   - Write-off handling
   
   Payment Reminders:
   - Automatic reminder schedule:
     * 7 days before due date
     * On due date
     * 7 days after due date
     * 14 days after due date
     * 30 days after due date
   - Customizable message templates
   - Escalating tone/urgency
   - Include payment link

7. Credit Management:
   
   Credit Limits:
   - Set credit limit per customer
   - Credit check before service
   - Block service if over limit
   - Credit increase requests
   
   Credit Memos:
   - Issue for refunds
   - Issue for service failures
   - Issue for goodwill gestures
   - Apply to future invoices or refund

8. Financial Reporting:
   
   Revenue Reports:
   - Revenue by period
   - Revenue by service type
   - Revenue by customer
   - Revenue by technician
   
   Payment Reports:
   - Payments received
   - Payment methods used
   - Average days to payment
   - Payment success rates
   
   Outstanding Balance Reports:
   - Total AR
   - By customer
   - By age
   - At risk amounts

9. Integration with Accounting:
   
   Export to Accounting Software:
   - QuickBooks integration
   - Xero integration
   - Sage integration
   - General ledger export
   
   Data Synchronization:
   - Customers/vendors
   - Invoices
   - Payments
   - Chart of accounts mapping
   - Tax codes
   - Two-way sync support

10. Audit Trail:
    - All financial transactions logged
    - User who created/modified
    - Timestamp
    - Before/after values
    - Reason for changes
    - Tamper-proof logging

Implement with strong security, transaction integrity, and compliance with financial regulations (PCI-DSS for payment card data).

---

## Phase 11: Knowledge Base & Self-Service

### Prompt 15: Knowledge Base System

Create a comprehensive knowledge base and self-service support system with:

1. Content Management:
   
   Article Structure:
   - Title (clear, descriptive)
   - Summary (brief overview)
   - Category/subcategory assignment
   - Tags/keywords
   - Body content (rich text)
   - Author information
   - Creation date
   - Last updated date
   - Version history
   - Visibility (public, customer-only, internal-only)
   - Featured/promoted flag
   
   Content Types:
   - How-to guides (step-by-step instructions)
   - Troubleshooting articles (problem → solution)
   - FAQs (question → answer format)
   - Product documentation (specifications, features)
   - Best practices
   - Video tutorials
   - Downloadable resources (PDFs, templates)
   - Quick reference cards
   
   Rich Content Editor:
   - WYSIWYG editing
   - Text formatting (bold, italic, headings, lists)
   - Insert images with captions
   - Embed videos (YouTube, Vimeo, uploaded)
   - Code snippets with syntax highlighting
   - Tables
   - Callout boxes (note, tip, warning)
   - Collapsible sections
   - Internal links to other articles
   - External links
   - File attachments

2. Taxonomy & Organization:
   
   Category Hierarchy:
   - Multi-level categories (3-4 levels deep)
   - Examples:
     * Hardware > Computers > Desktops > Won't Start
     * Software > Operating Systems > Windows > Installation
     * Network > WiFi > Connection Issues
   - Drag-and-drop category organization
   - Category descriptions
   - Category icons
   
   Tagging System:
   - Free-form tags
   - Tag suggestions based on content
   - Popular tags
   - Tag management (merge, rename, delete)
   - Filter articles by tag
   
   Article Relationships:
   - Related articles (manually selected)
   - Automatic suggestions based on tags
   - "See also" links
   - Prerequisites (read this article first)
   - Series/sequence (part 1, part 2, etc.)

3. Search Functionality:
   
   Search Implementation:
   - Full-text search across title, summary, body
   - Search within specific categories
   - Filter by content type
   - Filter by author
   - Filter by date
   - Sort results by relevance, date, popularity
   
   Search Features:
   - Auto-complete suggestions as typing
   - "Did you mean...?" spell correction
   - Highlight search terms in results
   - Search result snippets with context
   - Advanced search operators (AND, OR, quotes)
   
   Search Analytics:
   - Track search queries
   - Identify searches with no results
   - Popular searches
   - Failed searches (clicking back without viewing article)
   - Use data to improve content

4. User Experience:
   
   Article Display:
   - Clean, readable typography
   - Table of contents for long articles
   - Progress indicator (reading position)
   - Estimated reading time
   - Print-friendly version
   - Download as PDF option
   - Share via email, social media
   - Breadcrumb navigation
   
   Navigation:
   - Browse by category (expandable tree)
   - Popular articles
   - Recently updated
   - Most viewed
   - Trending articles
   - Featured articles carousel
   - "Staff picks" section
   
   Feedback & Rating:
   - "Was this article helpful?" (yes/no)
   - 5-star rating system
   - Comment/feedback form
   - Report inaccuracy button
   - Request update button
   - Aggregate ratings displayed
   
   Related Content:
   - "Customers who read this also read..."
   - Related videos
   - Related forum discussions
   - Related support tickets

5. Multi-Language Support:
   
   Translation Management:
   - Create article versions in multiple languages
   - Language selector
   - Track translation status (draft, in review, published)
   - Translation workflow
   - Machine translation integration (Google Translate API)
   - Professional translation service integration
   
   Language Fallback:
   - Display in user's preferred language if available
   - Fall back to default language if translation missing
   - Indicate when viewing translated vs original

6. Access Control:
   
   Visibility Levels:
   - Public (anyone can view)
   - Customer-only (requires login)
   - Internal-only (employees only)
   - Role-based (specific roles can view)
   
   Draft and Published States:
   - Draft articles (visible to authors only)
   - Review state (visible to reviewers)
   - Published (visible per permissions)
   - Archived (searchable but not browsable)
   
   Approval Workflow:
   - Submit for review
   - Reviewer assignment
   - Approve/reject with comments
   - Revision requests
   - Publication scheduling

7. Analytics & Insights:
   
   Article Metrics:
   - View count
   - Unique visitors
   - Average time on page
   - Bounce rate
   - Helpful votes
   - Unhelpful votes
   - Comments/feedback count
   
   Content Performance:
   - Top viewed articles
   - Lowest performing articles
   - Articles with most feedback
   - Articles needing updates (old, poor ratings)
   
   User Behavior:
   - Most common search paths
   - Entry points (how users find KB)
   - Exit points
   - Click-through rates
   - Self-service resolution rate

8. Integration with Support System:
   
   Contextual Help:
   - Suggest relevant KB articles based on:
     * Equipment type
     * Problem description
     * Error codes
   - Display in support ticket interface
   - "Before you submit, check these articles"
   
   Ticket Deflection:
   - Encourage self-service before ticket submission
   - Track how many potential tickets avoided
   - Measure cost savings
   
   Link from Tickets:
   - Support agents can attach KB articles to tickets
   - Send article links to customers
   - Create KB articles from ticket resolutions

9. Community Features:
   
   Comments/Discussion:
   - Allow comments on articles
   - Upvote/downvote comments
   - Mark comments as helpful
   - Moderation queue
   - Report inappropriate comments
   
   User Contributions:
   - Allow customers to submit articles
   - Community review and voting
   - Author recognition/badges
   - Contribution leaderboard

10. Mobile Experience:
    
    Responsive Design:
    - Mobile-optimized article layout
    - Touch-friendly navigation
    - Collapsible sections for space
    - Offline article access (download for later)
    
    Mobile App Integration:
    - Deep links to specific articles
    - Push notifications for new/updated articles
    - In-app search

11. Video Content:
    
    Video Library:
    - Host video tutorials
    - Organized by category
    - Video player with controls
    - Transcripts for accessibility
    - Download option
    
    Video Features:
    - Chapters/timestamps
    - Playback speed control
    - Quality selection
    - Closed captions/subtitles
    - Related videos

12. Maintenance & Management:
    
    Content Lifecycle:
    - Review schedule (annual review of all articles)
    - Expiration dates (flag outdated content)
    - Automated alerts for stale content
    - Bulk operations (update, categorize, archive)
    
    Content Templates:
    - Pre-formatted templates for consistency
    - Templates per content type
    - Include standard sections
    - Style guide enforcement

Build with fast page load times, SEO optimization, and accessibility standards compliance (WCAG 2.1 AA).

---

## Phase 12: System Administration & Configuration

### Prompt 16: Admin Control Panel

Create a comprehensive system administration and configuration interface with:

1. Company Profile Management:
   
   Basic Information:
   - Company name
   - Legal entity name
   - Tax ID/EIN
   - Business registration numbers
   - Industry classification
   - Year established
   
   Contact Information:
   - Primary business address
   - Mailing address (if different)
   - Phone numbers (main, support, billing)
   - Email addresses (general, support, billing, sales)
   - Website URL
   - Social media links
   
   Branding:
   - Company logo upload (multiple sizes/formats)
   - Color scheme (primary, secondary, accent colors)
   - Font selection
   - Email header/footer templates
   - Letterhead template
   - Document watermarks
   
   Operating Hours:
   - Standard business hours by day of week
   - Closed days/holidays
   - After-hours service availability
   - Emergency service hours
   - Time zone

2. Service Configuration:
   
   Service Types:
   - Define service categories
   - Create subcategories
   - Set default durations
   - Assign skill requirements
   - Set pricing
   - Enable/disable service offerings
   
   Equipment Types:
   - Define equipment categories
   - Create equipment subtypes
   - Define custom fields per type
   - Set maintenance schedules
   - Define compatibility rules
   
   Priority Levels:
   - Define priority levels (names, descriptions)
   - Set SLA targets per priority
   - Configure cost multipliers
   - Set visual indicators (colors, icons)
   - Define escalation rules

3. SLA (Service Level Agreement) Configuration:
   
   Customer Tiers:
   - Economy tier settings
   - Standard tier settings
   - Premium tier settings
   - Enterprise tier settings
   - Custom tiers
   
   SLA Metrics:
   - Response time targets (time to assign technician)
   - Resolution time targets (time to complete)
   - First-time fix rate targets
   - Uptime guarantees
   - Communication frequency requirements
   
   SLA Actions:
   - Automatic escalation when approaching deadline
   - Notification recipients for violations
   - Reporting on compliance
   - Penalty/credit calculations for violations

4. Workflow Automation:
   
   Automation Rules:
   - If-then condition builder:
     * Trigger: when work order created/updated/completed
     * Conditions: if priority = urgent AND customer tier = premium
     * Actions: send notification, auto-assign, set flag
   
   Common Automations:
   - Auto-assign to specific technician based on criteria
   - Escalate unassigned requests after X minutes
   - Send customer notifications at status changes
   - Create follow-up tasks automatically
   - Apply discounts based on conditions
   - Flag potential issues (overdue, over budget)
   
   Scheduling Rules:
   - Auto-schedule preventive maintenance
   - Generate recurring work orders
   - Assign based on geographic territory
   - Balance workload across technicians
   - Prefer customer's favorite technician
   
   Rule Management:
   - Enable/disable rules
   - Set rule priority/order
   - Test rules with sample data
   - View rule execution history
   - Export/import rules

5. User & Permission Management:
   
   Role Management:
   - View all roles
   - Create new roles
   - Edit role permissions
   - Delete/deactivate roles
   - Clone roles
   
   Permission Granularity:
   - Module access (can access work orders module?)
   - Action permissions (can create, read, update, delete?)
   - Field-level permissions (can see customer pricing?)
   - Data scope (can see all customers or only assigned?)
   - Administrative functions (can manage users, change settings?)
   
   Permission Matrix View:
   - Grid showing roles × permissions
   - Quick enable/disable checkboxes
   - Visual permission comparison
   - Identify permission overlaps
   
   User Management:
   - View all users
   - Create new accounts
   - Edit user details
   - Reset passwords
   - Enable/disable accounts
   - Deactivate terminated employees
   - Bulk user operations
   - Import users from CSV
   
   Session Management:
   - View active sessions
   - Force logout specific users
   - Set session timeout duration
   - Require re-authentication for sensitive actions
   - IP whitelist/blacklist

6. Email & Communication Settings:
   
   Email Configuration:
   - SMTP server settings
   - Sender name and email address
   - Reply-to address
   - Test email functionality
   - Email signature
   
   Email Templates:
   - List all templates
   - Edit template content
   - Preview templates
   - Use merge fields
   - HTML and plain text versions
   - Template categories
   
   Notification Settings:
   - Configure which events trigger emails
   - Set recipient rules
   - Enable/disable specific notifications
   - Batch notifications (digest mode)
   - Notification frequency limits (don't spam)
   
   SMS Configuration:
   - SMS gateway integration
   - Sender ID/number
   - Character limit warnings
   - Cost per message
   - Opt-in/opt-out management

7. Integration Settings:
   
   Accounting Software:
   - QuickBooks connection
   - Xero connection
   - Sync frequency
   - Field mapping
   - Sync direction (one-way or two-way)
   - Error handling and logging
   
   Payment Gateways:
   - Stripe configuration
   - Square configuration
   - Authorize.Net configuration
   - Test mode vs live mode
   - Webhook setup
   - Supported payment methods
   
   Calendar Integration:
   - Google Calendar API setup
   - Microsoft 365/Outlook setup
   - Sync settings
   - Calendar selection
   - Conflict handling
   
   Mapping Services:
   - Google Maps API key
   - Usage limits
   - Geocoding settings
   - Route optimization preferences
   
   Third-Party APIs:
   - API keys management
   - Rate limit monitoring
   - Usage statistics
   - Error logs
   - Test connections

8. Security Settings:
   
   Password Policies:
   - Minimum length
   - Complexity requirements
   - Password expiration (days)
   - Password history (prevent reuse)
   - Account lockout (after X failed attempts)
   - Lockout duration
   
   Multi-Factor Authentication:
   - Enforce MFA for all users / specific roles
   - Allowed MFA methods
   - Backup codes
   - Remember device settings
   
   Session Security:
   - Session timeout duration
   - Concurrent session limits
   - Require re-login for sensitive actions
   - Log all login attempts
   
   IP Restrictions:
   - Allowed IP addresses
   - Blocked IP addresses
   - Geographic restrictions
   
   Audit Logging:
   - What actions to log
   - Log retention period
   - Tamper-proof logging
   - Log export functionality

9. Data Management:
   
   Backup Settings:
   - Automated backup schedule
   - Backup retention policy
   - Backup storage location
   - Incremental vs full backups
   - Backup encryption
   - Test restore functionality
   
   Data Archival:
   - Archive old records rules
   - Archived data location
   - Archive access permissions
   - Restore from archive process
   
   Data Export:
   - Export entire database
   - Export specific data sets
   - Export format selection
   - Schedule automated exports
   - Export encryption
   
   Data Import:
   - Import from CSV/Excel
   - Field mapping interface
   - Validation during import
   - Error handling
   - Preview before commit
   - Import history/logs

10. System Maintenance:
    
    System Health:
    - Server status monitoring
    - Database health checks
    - Storage usage
    - Memory usage
    - CPU usage
    - Network connectivity
    - Background job queue status
    
    Maintenance Tasks:
    - Database optimization
    - Index rebuilding
    - Cache clearing
    - Log file rotation
    - Temporary file cleanup
    - Schedule maintenance windows
    
    Version & Updates:
    - Current system version
    - Available updates
    - Update history
    - Release notes
    - Schedule updates
    - Rollback capability

11. Compliance & Legal:
    
    Privacy Settings:
    - GDPR compliance mode
    - CCPA compliance mode
    - Cookie consent management
    - Privacy policy display
    - Terms of service display
    
    Data Subject Requests:
    - View personal data (export)
    - Delete personal data
    - Anonymize personal data
    - Request tracking
    - Fulfillment deadlines
    
    Consent Management:
    - Track consent types
    - Record consent timestamps
    - Consent withdrawal
    - Consent audit trail

12. Customization:
    
    Custom Fields:
    - Add custom fields to entities
    - Field types (text, number, date, dropdown, etc.)
    - Required/optional
    - Default values
    - Validation rules
    - Display order
    
    Custom Statuses:
    - Define custom work order statuses
    - Define custom equipment statuses
    - Status colors and icons
    - Status transitions (which statuses can follow which)
    
    Labels & Terminology:
    - Customize field labels
    - Customize menu names
    - Customize button text
    - Multi-language label support

Build with comprehensive validation, confirm dialogs for destructive actions, and clear documentation of each setting's impact.

---

## END OF PROMPTS

---

## Usage Instructions

1. **Sequential Implementation**: These prompts are designed to be implemented in order, as later phases build upon earlier ones.

2. **Technology Stack Flexibility**: While these prompts don't specify particular technologies, they can be implemented using:
   - Frontend: React, Vue.js, Angular, or similar
   - Backend: Node.js, Python (Django/Flask), Ruby on Rails, Java Spring, or similar
   - Database: PostgreSQL, MySQL, MongoDB, or similar
   - Mobile: React Native, Flutter, or native iOS/Android

3. **Customization**: Each prompt can be customized based on:
   - Specific business requirements
   - Target market (B2B vs B2C)
   - Scale of operations
   - Budget constraints
   - Technical expertise available

4. **Iterative Development**: Consider implementing features in iterations:
   - Phase 1: MVP (Core authentication, basic work orders, simple scheduling)
   - Phase 2: Enhanced features (Equipment management, parts inventory)
   - Phase 3: Advanced features (Analytics, automation, integrations)
   - Phase 4: Optimization (Performance tuning, advanced analytics)

5. **Testing Strategy**: For each component, implement:
   - Unit tests for business logic
   - Integration tests for API endpoints
   - End-to-end tests for critical user flows
   - Performance testing for high-load scenarios
   - Security testing for authentication and data access

6. **Documentation**: Maintain comprehensive documentation including:
   - API documentation
   - Database schema documentation
   - User guides and training materials
   - System administration manuals
   - Deployment and maintenance procedures

---

## Total System Components Summary

**Database Tables**: 13+ core tables with proper relationships
**User Roles**: 11 distinct role types
**Major Modules**: 12 functional areas
**Dashboard Types**: 3 specialized dashboards (Technician, Dispatcher, Admin)
**Integration Points**: 6+ external system integrations
**Report Categories**: 4 main categories with 15+ report types
**Mobile Interfaces**: 2 apps (Technician field app, Customer app)

This comprehensive system provides enterprise-grade maintenance management capabilities suitable for organizations ranging from small service businesses to large enterprise operations.