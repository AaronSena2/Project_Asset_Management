<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Category;
use App\Models\Supplier;
use App\Models\User;
use App\Services\AuthService;
use App\Services\NotificationService;
use App\Support\View;

final class SupplierController
{
    public function __construct(
        private readonly AuthService $auth,
        private readonly Supplier $suppliers,
        private readonly Category $categories,
        private readonly User $users,
        private readonly NotificationService $notifications,
        private readonly string $fallbackOfficeEmail
    ) {
    }

    public function index(): void
    {
        $this->auth->requireRole([
            User::ROLE_SYSTEM_ADMINISTRATOR,
            User::ROLE_OFFICE_ADMINISTRATOR,
            User::ROLE_IT_MANAGER,
            User::ROLE_FINANCE_MANAGER,
        ]);

        View::render('suppliers/index', [
            'suppliers' => $this->suppliers->all(),
            'categories' => $this->categories->all(),
            'canManage' => $this->auth->hasRole([
                User::ROLE_OFFICE_ADMINISTRATOR,
                User::ROLE_IT_MANAGER,
                User::ROLE_SYSTEM_ADMINISTRATOR,
            ]),
        ]);
    }

    public function create(array $post): void
    {
        $this->auth->requireRole([
            User::ROLE_OFFICE_ADMINISTRATOR,
            User::ROLE_IT_MANAGER,
            User::ROLE_SYSTEM_ADMINISTRATOR,
        ]);

        $creator = $this->auth->user();
        $id = $this->suppliers->create($post, (int) $creator['id']);

        $officeAdminEmail = $this->users->officeAdministratorEmail() ?? $this->fallbackOfficeEmail;
        $this->notifications->notifyEntityCreated($officeAdminEmail, 'Supplier', [
            'Supplier ID' => (string) $id,
            'Company Name' => $post['company_name'] ?? '',
            'Supplier Code' => $post['supplier_code'] ?? '',
        ], $creator['full_name']);

        header('Location: /index.php?action=suppliers');
        exit;
    }

    public function update(array $post): void
    {
        $this->auth->requireRole([
            User::ROLE_OFFICE_ADMINISTRATOR,
            User::ROLE_IT_MANAGER,
            User::ROLE_SYSTEM_ADMINISTRATOR,
        ]);

        $id = (int) ($post['supplier_id'] ?? 0);
        if ($id > 0) {
            $this->suppliers->update($id, $post);
        }

        header('Location: /index.php?action=suppliers');
        exit;
    }
}
