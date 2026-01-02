# Project Tasks

## Task 1: Technician Availability Check-In
- Add availability tracking for staff with a new `availability_status` and `availability_updated_at` on the users table.
- Provide a Dashboard control for technicians/dispatchers to mark themselves Available, Unavailable, or Offline.
- Persist availability changes to the user record, update `last_seen_at`, and record an audit log entry for each change.

## Task 2: On-Site Arrival Tracking
- Add `arrived_at` to work orders and expose a "Mark Arrived" action on the Work Order detail view for technicians.
- When marked arrived, set `arrived_at`, set `started_at` if empty, update status to `in_progress` if needed, and log a work order event plus audit entry.
- Keep automation hooks consistent by emitting a status-change automation trigger when the arrival changes status.

## Task 3: Inventory Low-Stock Alerts
- Add a low-stock panel to the Inventory view that shows parts where total on-hand quantity is at or below the part’s `reorder_level`.
- Display part name, on-hand quantity, and reorder level, sorted by lowest quantity first.

## Task 4: Knowledge Base Search
- Add a search input and category filter to the Knowledge Base page.
- Filter articles by keyword (title/content) and optional category selection, and show results in the existing list.
