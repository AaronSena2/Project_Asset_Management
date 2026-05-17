from datetime import date
import unittest

from inventory_management import (
    Asset,
    AssetCategory,
    AssetDomain,
    AssetStatus,
    AuthorizationError,
    CategorySpecificationField,
    CreationEvent,
    EventBus,
    InventoryAccessControl,
    OfficeAdminNotificationHandler,
    Role,
    Supplier,
    User,
    ValidationError,
    calculate_asset_age,
)


class FakeEmailGateway:
    def __init__(self) -> None:
        self.messages = []

    def send(self, *, recipient: str, subject: str, body: str) -> None:
        self.messages.append({"recipient": recipient, "subject": subject, "body": body})


class InventoryManagementTests(unittest.TestCase):
    def setUp(self) -> None:
        self.system_admin = User("u1", "Sys Admin", "sys@example.com", Role.SYSTEM_ADMINISTRATOR)
        self.office_admin = User("u2", "Office Admin", "office@example.com", Role.OFFICE_ADMINISTRATOR)
        self.it_manager = User("u3", "IT Manager", "it@example.com", Role.IT_MANAGER)
        self.finance_manager = User("u4", "Finance Manager", "finance@example.com", Role.FINANCE_MANAGER)
        self.furniture_category = AssetCategory(
            category_key="desk",
            display_name="Desk",
            asset_domain=AssetDomain.FURNITURE,
            specification_fields=(CategorySpecificationField("material", "Material"),),
        )
        self.computer_category = AssetCategory(
            category_key="laptop",
            display_name="Laptop",
            asset_domain=AssetDomain.COMPUTER,
            specification_fields=(
                CategorySpecificationField("ram", "RAM"),
                CategorySpecificationField("storage", "Storage"),
            ),
        )

    def test_calculate_asset_age_uses_purchase_anniversary(self) -> None:
        self.assertEqual(calculate_asset_age(date(2020, 6, 1), today=date(2026, 5, 31)), 5)
        self.assertEqual(calculate_asset_age(date(2020, 6, 1), today=date(2026, 6, 1)), 6)

    def test_calculate_asset_age_rejects_future_dates(self) -> None:
        with self.assertRaises(ValidationError):
            calculate_asset_age(date(2030, 1, 1), today=date(2026, 1, 1))

    def test_only_system_admin_can_create_users(self) -> None:
        acl = InventoryAccessControl()
        new_user = User("u5", "New User", "new@example.com", Role.IT_MANAGER)

        with self.assertRaises(AuthorizationError):
            acl.create_user(self.office_admin, new_user)

        self.assertEqual(acl.create_user(self.system_admin, new_user), new_user)

    def test_office_admin_can_manage_furniture_assets_but_not_technical_ones(self) -> None:
        acl = InventoryAccessControl()
        furniture_asset = Asset(
            asset_id="a1",
            unique_serial_number="F-100",
            category=self.furniture_category,
            status=AssetStatus.AVAILABLE,
            date_of_purchase=date(2024, 1, 15),
            supplier_id="s1",
            specifications={"material": "Wood"},
            created_by=self.office_admin.user_id,
        )
        technical_asset = Asset(
            asset_id="a2",
            unique_serial_number="T-100",
            category=self.computer_category,
            status=AssetStatus.DEPLOYED,
            date_of_purchase=date(2024, 1, 15),
            supplier_id="s1",
            specifications={"ram": "16GB", "storage": "512GB"},
            created_by=self.it_manager.user_id,
        )

        self.assertEqual(acl.save_asset(self.office_admin, furniture_asset), furniture_asset)
        with self.assertRaises(AuthorizationError):
            acl.save_asset(self.office_admin, technical_asset)

    def test_finance_manager_is_read_only_for_suppliers(self) -> None:
        acl = InventoryAccessControl()
        supplier = Supplier(
            supplier_id="s1",
            company_name="Acme Supplies",
            contact_person="Pat Doe",
            email="pat@example.com",
            phone="+1000000000",
            specialization_category_key="laptop",
            created_by=self.it_manager.user_id,
        )

        with self.assertRaises(AuthorizationError):
            acl.save_supplier(self.finance_manager, supplier)

    def test_category_validation_requires_defined_specifications(self) -> None:
        invalid_asset = Asset(
            asset_id="a3",
            unique_serial_number="T-200",
            category=self.computer_category,
            status=AssetStatus.AVAILABLE,
            date_of_purchase=date(2024, 1, 15),
            supplier_id="s1",
            specifications={"ram": "8GB"},
            created_by=self.it_manager.user_id,
        )

        with self.assertRaises(ValidationError):
            invalid_asset.validate()

    def test_creation_event_sends_email_to_office_admin(self) -> None:
        email_gateway = FakeEmailGateway()
        event_bus = EventBus()
        handler = OfficeAdminNotificationHandler([self.office_admin], email_gateway)
        event_bus.subscribe("entity.created", handler)

        event_bus.publish(
            "entity.created",
            CreationEvent(
                entity_type="Asset",
                entity_id="a1",
                entity_name="Laptop A",
                created_by=self.it_manager.full_name,
                summary={"category": "Laptop", "status": "AVAILABLE"},
            ),
        )

        self.assertEqual(len(email_gateway.messages), 1)
        self.assertEqual(email_gateway.messages[0]["recipient"], self.office_admin.email)
        self.assertIn("New Asset created", email_gateway.messages[0]["subject"])
        self.assertIn("Created By: IT Manager", email_gateway.messages[0]["body"])


if __name__ == "__main__":
    unittest.main()
