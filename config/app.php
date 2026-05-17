<?php

declare(strict_types=1);

return [
    'app_name' => 'Inventory Management System',
    'base_url' => '/',
    'default_admin_email' => getenv('DEFAULT_ADMIN_EMAIL') ?: 'sysadmin@example.com',
    'default_admin_password' => getenv('DEFAULT_ADMIN_PASSWORD') ?: 'Sebalulule1',
    'office_admin_notification_email' => 'office.admin@example.com',
    'mail_from' => 'noreply@example.com',
    'mail_from_name' => 'Inventory Management System',
];
