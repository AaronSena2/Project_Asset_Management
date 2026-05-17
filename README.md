# Inventory Management System (PHP + MySQL + Bootstrap)

This repository is scaffolded as a simple fixed-asset Inventory Management System focused on assets, suppliers, users, asset age, and distribution.

## Tech Stack

- Backend: Object-Oriented PHP 8+ with PDO and native sessions
- Database: MySQL/InnoDB with relational constraints and EAV dynamic specifications
- UI: Bootstrap 5
- Frontend logic: Vanilla JavaScript with Fetch API for dynamic specification fields

## Project Structure

- `/config` - app and database configuration
- `/database` - `schema.sql` and `seed.sql`
- `/src/Database` - PDO connection factory
- `/src/Models` - domain models (`Asset`, `User`, `Supplier`, `Category`)
- `/src/Services` - authentication and notifications (`NotificationService`)
- `/src/Controllers` - request handlers and RBAC-enforced actions
- `/src/Support` - view renderer
- `/views` - Bootstrap templates/pages
- `/public` - front controller and static JS assets

## Roles and RBAC

Supported roles (exactly 4):

1. System Administrator
2. Office Administrator
3. IT Manager
4. Finance Manager

Access model implemented:

- System Administrator: manages users/roles, global categories/specs, and all assets/suppliers
- Office Administrator: manages furniture/general assets and suppliers
- IT Manager: manages technical assets and suppliers
- Finance Manager: read-only views for assets, suppliers, and reports

## Database Setup

1. Create schema/tables:

```bash
mysql -u root -p < database/schema.sql
```

2. Seed sample data:

```bash
mysql -u root -p < database/seed.sql
```

Seed login accounts (all use password `Password123!`):

- `sysadmin@example.com`
- `office.admin@example.com`
- `it.manager@example.com`
- `finance.manager@example.com`

## Run Locally

```bash
php -S 127.0.0.1:8080 -t public
```

Open: `http://127.0.0.1:8080/index.php`

## Key Functional Coverage

- Asset baseline fields: serial number, category, status, purchase date, auto-calculated age, supplier FK, dynamic specifications
- Dynamic category-based specification fields (EAV): admin-defined field definitions + JS dynamic rendering in asset form
- Supplier management: Office Admin and IT Manager create/update suppliers
- User management: only System Administrator creates users and assigns roles
- Notification service: sends email notification to Office Administrator email when Asset, Supplier, or User is newly created

## Notes

- No financial fields, pricing, depreciation, or monetary values are included.
- Asset age is calculated in PHP (`DateTimeImmutable::diff`) at domain/display level.
