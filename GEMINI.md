# Taisykla App

## Project Overview

**Taisykla App** is a comprehensive Computer & Equipment Maintenance Management System designed to orchestrate the complete lifecycle of repair services. It connects service companies, technicians, and customers into a unified workflow, replacing manual processes with real-time digital management.

**Core Features:**
*   **Service Request Management:** Intake, prioritization, and tracking of customer requests.
*   **Dispatch & Scheduling:** Intelligent assignment of technicians and route optimization.
*   **Technician Workflow:** Mobile-friendly tools for diagnostics, parts usage, and documentation.
*   **Inventory Management:** Parts tracking, procurement, and usage analysis.
*   **Billing & Invoicing:** Automated invoicing, payment processing, and financial reporting.
*   **Customer Portal:** Self-service interface for requests, approvals, and history.

**Tech Stack:**
*   **Framework:** Laravel 12.0 (PHP 8.2+)
*   **Frontend/Interactivity:** Livewire 3.6, Livewire Volt 1.7
*   **Styling:** Tailwind CSS (via Vite)
*   **Authentication/Permissions:** Laravel Breeze, Spatie Laravel Permission
*   **Database:** Compatible with standard Laravel drivers (SQLite default for dev)

## Building and Running

The project includes convenient Composer scripts to streamline common tasks.

### Initial Setup

To set up the project from scratch (install dependencies, setup environment, migrate, build assets):

```bash
composer run setup
```

### Development Server

To run the full development stack (Laravel server, Queue worker, Pail logs, and Vite) concurrently:

```bash
composer run dev
```

Alternatively, you can run services individually:
*   **Backend:** `php artisan serve`
*   **Frontend (Vite):** `npm run dev`
*   **Queue:** `php artisan queue:listen`

### Testing

To run the automated test suite:

```bash
composer run test
```

## Key Directories

*   **`app/Livewire/`**: Contains the application's interactive components. This project relies heavily on Livewire for UI logic.
    *   **`Actions/`**: Reusable business logic actions.
    *   **`Forms/`**: Form objects for handling user input.
    *   **`...`**: Feature-specific directories (e.g., `WorkOrders`, `Inventory`, `Schedule`).
*   **`app/Models/`**: Eloquent models representing the database schema (e.g., `WorkOrder`, `Equipment`, `Customer`).
*   **`docs/`**: Detailed project documentation.
    *   `project-description.md`: comprehensive user guide and system manual.
    *   `tasks.md`: Task tracking.
*   **`database/`**: Migrations, factories, and seeders.
*   **`routes/`**: Application route definitions (`web.php`, `auth.php`).

## Development Conventions

*   **Livewire & Volt:** The project uses Livewire for dynamic frontend interactions. Look for Volt functional components for simpler UI elements.
*   **Tailwind CSS:** All styling is utility-first using Tailwind.
*   **Permissions:** Access control is managed via `spatie/laravel-permission`. Ensure roles and permissions are checked in controllers/components.
*   **Service-Oriented:** Complex business logic should reside in Service classes or Actions, keeping Controllers/Components light.
