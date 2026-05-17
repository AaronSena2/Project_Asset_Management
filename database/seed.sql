USE inventory_management;

INSERT INTO roles (name) VALUES
('System Administrator'),
('Office Administrator'),
('IT Manager'),
('Finance Manager');

INSERT INTO categories (name) VALUES
('Computers'),
('Furniture'),
('Electrical Equipment');

INSERT INTO asset_statuses (name) VALUES
('Available'),
('Deployed'),
('Under Maintenance'),
('Disposed');

INSERT INTO users (full_name, email, password_hash, role_id, created_by) VALUES
('System Admin', 'sysadmin@example.com', '$2y$10$/Pjzddrm0UN4jwMXhHsvr.wRkyzibTGtZLevXsbcCLJoLqkZKMqOu', 1, NULL),
('Office Admin', 'office.admin@example.com', '$2y$10$/Pjzddrm0UN4jwMXhHsvr.wRkyzibTGtZLevXsbcCLJoLqkZKMqOu', 2, 1),
('IT Manager', 'it.manager@example.com', '$2y$10$/Pjzddrm0UN4jwMXhHsvr.wRkyzibTGtZLevXsbcCLJoLqkZKMqOu', 3, 1),
('Finance Manager', 'finance.manager@example.com', '$2y$10$/Pjzddrm0UN4jwMXhHsvr.wRkyzibTGtZLevXsbcCLJoLqkZKMqOu', 4, 1);

INSERT INTO specification_definitions (category_id, field_key, field_label, field_type, is_required, created_by) VALUES
(1, 'ram', 'RAM', 'text', 1, 1),
(1, 'storage', 'Storage', 'text', 1, 1),
(1, 'processor', 'Processor', 'text', 1, 1),
(2, 'material', 'Material', 'text', 1, 1),
(2, 'dimensions', 'Dimensions', 'text', 0, 1),
(3, 'voltage', 'Voltage', 'text', 1, 1),
(3, 'power_rating', 'Power Rating', 'text', 0, 1);

INSERT INTO suppliers (supplier_code, company_name, contact_person, email, phone, category_specialization_id, created_by) VALUES
('SUP-COMP-001', 'Compute Source Ltd', 'Alex Doe', 'alex@computesource.com', '+1-202-555-0100', 1, 2),
('SUP-FURN-001', 'FurniPro', 'Mina Cole', 'mina@furnipro.com', '+1-202-555-0101', 2, 2),
('SUP-ELEC-001', 'Electra Systems', 'Jay Kim', 'jay@electra.com', '+1-202-555-0102', 3, 3);

INSERT INTO assets (serial_number, category_id, status_id, date_of_purchase, supplier_id, assigned_to_user_id, created_by) VALUES
('AST-COM-1001', 1, 2, '2024-01-12', 1, 3, 3),
('AST-FUR-2001', 2, 1, '2022-06-04', 2, NULL, 2),
('AST-ELE-3001', 3, 3, '2021-03-15', 3, 3, 3);

INSERT INTO asset_specifications (asset_id, specification_definition_id, value_text) VALUES
(1, 1, '16 GB'),
(1, 2, '512 GB SSD'),
(1, 3, 'Intel i7'),
(2, 4, 'Wood'),
(2, 5, '120x60x75 cm'),
(3, 6, '220V'),
(3, 7, '1.5kW');
