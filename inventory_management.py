from __future__ import annotations

from dataclasses import dataclass, field
from datetime import date
from enum import Enum
from typing import Any, Callable, Iterable, Mapping, Protocol


class Role(str, Enum):
    SYSTEM_ADMINISTRATOR = "SYSTEM_ADMINISTRATOR"
    OFFICE_ADMINISTRATOR = "OFFICE_ADMINISTRATOR"
    IT_MANAGER = "IT_MANAGER"
    FINANCE_MANAGER = "FINANCE_MANAGER"


class AssetStatus(str, Enum):
    AVAILABLE = "AVAILABLE"
    DEPLOYED = "DEPLOYED"
    UNDER_MAINTENANCE = "UNDER_MAINTENANCE"
    DISPOSED = "DISPOSED"


class AssetDomain(str, Enum):
    COMPUTER = "COMPUTER"
    FURNITURE = "FURNITURE"
    ELECTRICAL_EQUIPMENT = "ELECTRICAL_EQUIPMENT"


class AuthorizationError(PermissionError):
    pass


class ValidationError(ValueError):
    pass


@dataclass(frozen=True)
class User:
    user_id: str
    full_name: str
    email: str
    role: Role


@dataclass(frozen=True)
class CategorySpecificationField:
    field_key: str
    field_label: str
    data_type: str = "text"
    is_required: bool = True


@dataclass(frozen=True)
class AssetCategory:
    category_key: str
    display_name: str
    asset_domain: AssetDomain
    specification_fields: tuple[CategorySpecificationField, ...] = ()

    def validate_specifications(self, specifications: Mapping[str, Any]) -> None:
        allowed_fields = {field.field_key: field for field in self.specification_fields}
        missing_required = [
            field.field_label
            for field in self.specification_fields
            if field.is_required and field.field_key not in specifications
        ]
        if missing_required:
            raise ValidationError(f"Missing specification fields: {', '.join(missing_required)}")

        unknown_fields = sorted(set(specifications) - set(allowed_fields))
        if unknown_fields:
            raise ValidationError(f"Unknown specification fields: {', '.join(unknown_fields)}")


@dataclass(frozen=True)
class Supplier:
    supplier_id: str
    company_name: str
    contact_person: str
    email: str
    phone: str
    specialization_category_key: str
    created_by: str


def calculate_asset_age(date_of_purchase: date, *, today: date | None = None) -> int:
    current_date = today or date.today()
    if date_of_purchase > current_date:
        raise ValidationError("Date of purchase cannot be in the future")
    return current_date.year - date_of_purchase.year - (
        (current_date.month, current_date.day) < (date_of_purchase.month, date_of_purchase.day)
    )


@dataclass(frozen=True)
class Asset:
    asset_id: str
    unique_serial_number: str
    category: AssetCategory
    status: AssetStatus
    date_of_purchase: date
    supplier_id: str
    specifications: Mapping[str, Any]
    created_by: str
    assigned_to_user_id: str | None = None

    @property
    def age(self) -> int:
        return calculate_asset_age(self.date_of_purchase)

    def validate(self) -> None:
        self.category.validate_specifications(self.specifications)


ROLE_PERMISSIONS: dict[Role, set[str]] = {
    Role.SYSTEM_ADMINISTRATOR: {
        "user:create",
        "user:assign_role",
        "category:create",
        "category:configure_specs",
        "asset:view",
        "supplier:view",
        "report:view",
    },
    Role.OFFICE_ADMINISTRATOR: {
        "asset:view",
        "asset:create:furniture",
        "asset:update:furniture",
        "supplier:view",
        "supplier:create",
        "supplier:update",
    },
    Role.IT_MANAGER: {
        "asset:view",
        "asset:create:technical",
        "asset:update:technical",
        "asset:assign:technical",
        "supplier:view",
        "supplier:create",
        "supplier:update",
    },
    Role.FINANCE_MANAGER: {
        "asset:view",
        "supplier:view",
        "report:view",
    },
}


class InventoryAccessControl:
    def assert_allowed(self, actor: User, permission: str) -> None:
        if permission not in ROLE_PERMISSIONS.get(actor.role, set()):
            raise AuthorizationError(f"{actor.role.value} cannot perform {permission}")

    def create_user(self, actor: User, new_user: User) -> User:
        self.assert_allowed(actor, "user:create")
        return new_user

    def update_user_role(self, actor: User, target_user: User, new_role: Role) -> User:
        self.assert_allowed(actor, "user:assign_role")
        return User(
            user_id=target_user.user_id,
            full_name=target_user.full_name,
            email=target_user.email,
            role=new_role,
        )

    def save_supplier(self, actor: User, supplier: Supplier) -> Supplier:
        if actor.role == Role.SYSTEM_ADMINISTRATOR:
            return supplier
        self.assert_allowed(actor, "supplier:create")
        return supplier

    def save_asset(self, actor: User, asset: Asset) -> Asset:
        asset.validate()
        if asset.category.asset_domain == AssetDomain.FURNITURE:
            permission = "asset:create:furniture"
        else:
            permission = "asset:create:technical"
        self.assert_allowed(actor, permission)
        return asset

    def assign_asset(self, actor: User, asset: Asset, assigned_to_user_id: str) -> Asset:
        self.assert_allowed(actor, "asset:assign:technical")
        if asset.category.asset_domain == AssetDomain.FURNITURE:
            raise AuthorizationError("Furniture assets cannot be assigned through the technical assignment flow")
        return Asset(
            asset_id=asset.asset_id,
            unique_serial_number=asset.unique_serial_number,
            category=asset.category,
            status=asset.status,
            date_of_purchase=asset.date_of_purchase,
            supplier_id=asset.supplier_id,
            specifications=asset.specifications,
            created_by=asset.created_by,
            assigned_to_user_id=assigned_to_user_id,
        )


@dataclass(frozen=True)
class CreationEvent:
    entity_type: str
    entity_id: str
    entity_name: str
    created_by: str
    summary: Mapping[str, Any]


class EmailGateway(Protocol):
    def send(self, *, recipient: str, subject: str, body: str) -> None:
        ...


class EventBus:
    def __init__(self) -> None:
        self._subscribers: dict[str, list[Callable[[CreationEvent], None]]] = {}

    def subscribe(self, event_name: str, handler: Callable[[CreationEvent], None]) -> None:
        self._subscribers.setdefault(event_name, []).append(handler)

    def publish(self, event_name: str, event: CreationEvent) -> None:
        for handler in self._subscribers.get(event_name, []):
            handler(event)


@dataclass
class OfficeAdminNotificationHandler:
    office_admins: Iterable[User]
    email_gateway: EmailGateway

    def __call__(self, event: CreationEvent) -> None:
        subject = f"New {event.entity_type} created"
        body = (
            f"{event.entity_type} created\n"
            f"ID: {event.entity_id}\n"
            f"Name: {event.entity_name}\n"
            f"Created By: {event.created_by}\n"
            f"Summary: {dict(event.summary)}"
        )
        for office_admin in self.office_admins:
            self.email_gateway.send(recipient=office_admin.email, subject=subject, body=body)


API_ENDPOINTS: dict[str, list[dict[str, Any]]] = {
    "users": [
        {
            "method": "POST",
            "path": "/api/users",
            "description": "Create a user and assign the initial role",
            "roles": [Role.SYSTEM_ADMINISTRATOR.value],
        },
        {
            "method": "PATCH",
            "path": "/api/users/{userId}/role",
            "description": "Update a user's role",
            "roles": [Role.SYSTEM_ADMINISTRATOR.value],
        },
    ],
    "categories": [
        {
            "method": "GET",
            "path": "/api/categories",
            "description": "List categories and dynamic specification definitions",
            "roles": [role.value for role in Role],
        },
        {
            "method": "POST",
            "path": "/api/categories",
            "description": "Create a category",
            "roles": [Role.SYSTEM_ADMINISTRATOR.value],
        },
        {
            "method": "POST",
            "path": "/api/categories/{categoryKey}/spec-fields",
            "description": "Create or update category-specific spec fields",
            "roles": [Role.SYSTEM_ADMINISTRATOR.value],
        },
    ],
    "suppliers": [
        {
            "method": "GET",
            "path": "/api/suppliers",
            "description": "List suppliers",
            "roles": [role.value for role in Role],
        },
        {
            "method": "POST",
            "path": "/api/suppliers",
            "description": "Create a supplier",
            "roles": [Role.OFFICE_ADMINISTRATOR.value, Role.IT_MANAGER.value],
        },
        {
            "method": "PATCH",
            "path": "/api/suppliers/{supplierId}",
            "description": "Update a supplier",
            "roles": [Role.OFFICE_ADMINISTRATOR.value, Role.IT_MANAGER.value],
        },
    ],
    "assets": [
        {
            "method": "GET",
            "path": "/api/assets",
            "description": "List assets with calculated age",
            "roles": [role.value for role in Role],
        },
        {
            "method": "POST",
            "path": "/api/assets",
            "description": "Create an asset with dynamic specifications",
            "roles": [Role.OFFICE_ADMINISTRATOR.value, Role.IT_MANAGER.value],
        },
        {
            "method": "PATCH",
            "path": "/api/assets/{assetId}",
            "description": "Update an asset",
            "roles": [Role.OFFICE_ADMINISTRATOR.value, Role.IT_MANAGER.value],
        },
        {
            "method": "PATCH",
            "path": "/api/assets/{assetId}/assign",
            "description": "Assign a technical asset",
            "roles": [Role.IT_MANAGER.value],
        },
    ],
    "reports": [
        {
            "method": "GET",
            "path": "/api/reports/asset-age",
            "description": "Asset age report",
            "roles": [Role.SYSTEM_ADMINISTRATOR.value, Role.FINANCE_MANAGER.value],
        },
        {
            "method": "GET",
            "path": "/api/reports/asset-distribution",
            "description": "Asset distribution report",
            "roles": [Role.SYSTEM_ADMINISTRATOR.value, Role.FINANCE_MANAGER.value],
        },
    ],
}
