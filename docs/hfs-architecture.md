# HFS Technical Blueprint

## Core stack

- Backend: Laravel 11, PHP 8.3+, MySQL-ready schema
- Frontend: Tailwind CSS + Livewire
- Realtime: Laravel Reverb prepared through broadcasting install
- Offline-first direction: PWA shell prepared with service worker registration and web manifest

## Domain modules prepared

### 1. Dynamic RBAC

- `spatie/laravel-permission` installed
- Permission catalog centralized in [`config/hfs.php`](/Applications/MAMP/htdocs/HFS EMAS/config/hfs.php)
- Sidebar/menu rendering filtered by user permission through [`app/Support/HfsNavigation.php`](/Applications/MAMP/htdocs/HFS EMAS/app/Support/HfsNavigation.php)
- Owner role seeded with full access

### 2. Fleet Management

- `units` stores master unit and `standard_fuel_ratio`
- `hour_meter_logs` stores HM, fuel, photo, GPS, sync metadata
- `service_logs` stores preventive maintenance history
- HM sequence validation and LPH anomaly calculation live in [`app/Models/HourMeterLog.php`](/Applications/MAMP/htdocs/HFS EMAS/app/Models/HourMeterLog.php)
- Mobile HM camera flow uses native device capture with JavaScript button trigger before client-side watermarking and compression.

### 3. Procurement & Inventory

- PR, PO, DO, inventory item, and inventory movement tables created
- Audit columns (`created_by`, `updated_by`) included in transactional tables
- Inventory receiving automation from DO to stock is the next service-layer implementation

### 4. Executive Dashboard

- Livewire dashboard shell created in [`app/Livewire/Dashboard/ExecutiveDashboard.php`](/Applications/MAMP/htdocs/HFS EMAS/app/Livewire/Dashboard/ExecutiveDashboard.php)
- Fuel discrepancy and service warning sections already read from actual domain tables

## Recommended next implementation slices

1. Authentication UI with Breeze or custom login screen.
2. User & Role Management CRUD backed by dynamic permission matrix.
3. HM input form with WebRTC camera, GPS lock, canvas watermark, and WebP compression.
4. IndexedDB queue for offline capture and background sync to Laravel endpoint.
5. Reverb event broadcasting for live executive dashboard cards, tables, and map markers.
6. Receiving workflow that posts DO receipt into `inventory_movements` and updates `inventory_items.current_stock`.
