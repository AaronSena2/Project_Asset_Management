CREATE TABLE roles (
    role_code VARCHAR(32) PRIMARY KEY,
    description TEXT NOT NULL
);

INSERT INTO roles (role_code, description) VALUES
    ('SYSTEM_ADMINISTRATOR', 'Manages categories, custom fields, and user role assignments'),
    ('OFFICE_ADMINISTRATOR', 'Manages furniture assets and suppliers'),
    ('IT_MANAGER', 'Manages technical assets and suppliers'),
    ('FINANCE_MANAGER', 'Read-only access to assets, suppliers, and reports');

CREATE TABLE users (
    user_id UUID PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    role_code VARCHAR(32) NOT NULL REFERENCES roles(role_code),
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE asset_categories (
    category_key VARCHAR(64) PRIMARY KEY,
    display_name VARCHAR(120) NOT NULL,
    asset_domain VARCHAR(32) NOT NULL CHECK (asset_domain IN ('COMPUTER', 'FURNITURE', 'ELECTRICAL_EQUIPMENT')),
    created_by UUID NOT NULL REFERENCES users(user_id),
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE category_spec_fields (
    spec_field_id UUID PRIMARY KEY,
    category_key VARCHAR(64) NOT NULL REFERENCES asset_categories(category_key) ON DELETE CASCADE,
    field_key VARCHAR(64) NOT NULL,
    field_label VARCHAR(120) NOT NULL,
    data_type VARCHAR(32) NOT NULL DEFAULT 'text',
    is_required BOOLEAN NOT NULL DEFAULT TRUE,
    configured_by UUID NOT NULL REFERENCES users(user_id),
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    UNIQUE (category_key, field_key)
);

CREATE TABLE suppliers (
    supplier_id UUID PRIMARY KEY,
    company_name VARCHAR(150) NOT NULL,
    contact_person VARCHAR(120) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    specialization_category_key VARCHAR(64) NOT NULL REFERENCES asset_categories(category_key),
    created_by UUID NOT NULL REFERENCES users(user_id),
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE assets (
    asset_id UUID PRIMARY KEY,
    unique_serial_number VARCHAR(120) NOT NULL UNIQUE,
    category_key VARCHAR(64) NOT NULL REFERENCES asset_categories(category_key),
    status VARCHAR(32) NOT NULL CHECK (status IN ('AVAILABLE', 'DEPLOYED', 'UNDER_MAINTENANCE', 'DISPOSED')),
    date_of_purchase DATE NOT NULL,
    supplier_id UUID NOT NULL REFERENCES suppliers(supplier_id),
    specifications JSONB NOT NULL DEFAULT '{}'::jsonb,
    assigned_to_user_id UUID NULL REFERENCES users(user_id),
    created_by UUID NOT NULL REFERENCES users(user_id),
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE VIEW asset_inventory_overview AS
SELECT
    a.asset_id,
    a.unique_serial_number,
    a.category_key,
    a.status,
    a.date_of_purchase,
    EXTRACT(YEAR FROM AGE(CURRENT_DATE, a.date_of_purchase))::INT AS age_years,
    a.supplier_id,
    a.specifications,
    a.assigned_to_user_id
FROM assets a;

CREATE VIEW asset_distribution_report AS
SELECT
    a.category_key,
    a.status,
    a.supplier_id,
    COUNT(*) AS asset_count
FROM assets a
GROUP BY a.category_key, a.status, a.supplier_id;
