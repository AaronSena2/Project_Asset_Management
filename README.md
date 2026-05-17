# Project_Asset_Management

Simple inventory-management scaffold for tracking fixed assets, suppliers, and users without any financial fields.

## Included deliverables

- `schema.sql` – PostgreSQL-flavored DDL for roles, users, suppliers, assets, dynamic category specifications, and reporting views.
- `inventory_management.py` – core backend models/classes for RBAC, category specification validation, asset age calculation, and event-driven email notifications.
- `tests/test_inventory_management.py` – focused unit tests for the RBAC, age, and notification behavior.

## API endpoint breakdown

### Users and role management

| Method | Path | Description | Allowed roles |
| --- | --- | --- | --- |
| `POST` | `/api/users` | Create a user profile and assign the initial role | System Administrator |
| `PATCH` | `/api/users/{userId}/role` | Update a user's role assignment | System Administrator |
| `GET` | `/api/users` | List users and current roles | System Administrator |

### Asset category and specification management

| Method | Path | Description | Allowed roles |
| --- | --- | --- | --- |
| `GET` | `/api/categories` | List asset categories and spec definitions | System Administrator, Office Administrator, IT Manager, Finance Manager |
| `POST` | `/api/categories` | Create a category | System Administrator |
| `POST` | `/api/categories/{categoryKey}/spec-fields` | Add or update dynamic specification fields for a category | System Administrator |

### Supplier management

| Method | Path | Description | Allowed roles |
| --- | --- | --- | --- |
| `GET` | `/api/suppliers` | List suppliers | System Administrator, Office Administrator, IT Manager, Finance Manager |
| `POST` | `/api/suppliers` | Create a supplier | Office Administrator, IT Manager |
| `PATCH` | `/api/suppliers/{supplierId}` | Update a supplier | Office Administrator, IT Manager |

### Asset management

| Method | Path | Description | Allowed roles |
| --- | --- | --- | --- |
| `GET` | `/api/assets` | List assets with calculated age | System Administrator, Office Administrator, IT Manager, Finance Manager |
| `POST` | `/api/assets` | Create a new asset | Office Administrator (furniture), IT Manager (technical assets) |
| `PATCH` | `/api/assets/{assetId}` | Update an asset | Office Administrator (furniture), IT Manager (technical assets) |
| `PATCH` | `/api/assets/{assetId}/assign` | Assign a technical asset to a user/location | IT Manager |

### Reports

| Method | Path | Description | Allowed roles |
| --- | --- | --- | --- |
| `GET` | `/api/reports/asset-age` | Asset age report grouped by category/status | System Administrator, Finance Manager |
| `GET` | `/api/reports/asset-distribution` | Asset distribution report by category/status/supplier | System Administrator, Finance Manager |

## Notes

- Asset age is calculated dynamically from `date_of_purchase` and the current date.
- Notifications are event-driven and email the Office Administrator whenever a new asset, supplier, or user is created.
- No price, depreciation, cost, or other financial fields are included anywhere in the scaffold.
