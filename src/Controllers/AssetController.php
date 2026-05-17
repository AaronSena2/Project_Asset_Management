<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Asset;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\User;
use App\Services\AuthService;
use App\Services\NotificationService;
use App\Support\View;

final class AssetController
{
    public function __construct(
        private readonly AuthService $auth,
        private readonly Asset $assets,
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

        $user = $this->auth->user();
        $categories = $this->categories->all();
        $manageableCategoryIds = [];
        foreach ($categories as $category) {
            if ($this->canManageCategory($user['role_name'], (int) $category['id'])) {
                $manageableCategoryIds[] = (int) $category['id'];
            }
        }

        View::render('assets/index', [
            'assets' => $this->assets->all(),
            'suppliers' => $this->suppliers->all(),
            'categories' => $categories,
            'statuses' => $this->assets->statuses(),
            'users' => $this->users->all(),
            'manageableCategoryIds' => $manageableCategoryIds,
            'canManageFurniture' => $this->auth->hasRole([
                User::ROLE_SYSTEM_ADMINISTRATOR,
                User::ROLE_OFFICE_ADMINISTRATOR,
            ]),
            'canManageTechnical' => $this->auth->hasRole([
                User::ROLE_SYSTEM_ADMINISTRATOR,
                User::ROLE_IT_MANAGER,
            ]),
        ]);
    }

    public function create(array $post): void
    {
        $this->auth->requireRole([
            User::ROLE_SYSTEM_ADMINISTRATOR,
            User::ROLE_OFFICE_ADMINISTRATOR,
            User::ROLE_IT_MANAGER,
        ]);

        $creator = $this->auth->user();
        $categoryId = (int) ($post['category_id'] ?? 0);
        if (!$this->canManageCategory($creator['role_name'], $categoryId)) {
            http_response_code(403);
            echo 'Forbidden';
            exit;
        }

        $assetId = $this->assets->create($post, (int) $creator['id']);

        $officeAdminEmail = $this->users->officeAdministratorEmail() ?? $this->fallbackOfficeEmail;
        $this->notifications->notifyEntityCreated($officeAdminEmail, 'Asset', [
            'Asset ID' => (string) $assetId,
            'Serial Number' => $post['serial_number'] ?? '',
            'Category ID' => (string) ($post['category_id'] ?? ''),
        ], $creator['full_name']);

        header('Location: /index.php?action=assets');
        exit;
    }

    public function specificationsByCategory(int $categoryId): void
    {
        $this->auth->requireRole([
            User::ROLE_SYSTEM_ADMINISTRATOR,
            User::ROLE_OFFICE_ADMINISTRATOR,
            User::ROLE_IT_MANAGER,
        ]);

        header('Content-Type: application/json');
        echo json_encode($this->categories->specificationDefinitionsByCategoryId($categoryId), JSON_THROW_ON_ERROR);
        exit;
    }

    private function canManageCategory(string $roleName, int $categoryId): bool
    {
        if ($roleName === User::ROLE_SYSTEM_ADMINISTRATOR) {
            return true;
        }

        $categoryNamesById = [];
        foreach ($this->categories->all() as $category) {
            $categoryNamesById[(int) $category['id']] = $category['name'];
        }

        $categoryName = $categoryNamesById[$categoryId] ?? '';
        if ($roleName === User::ROLE_OFFICE_ADMINISTRATOR) {
            return $categoryName === 'Furniture';
        }

        if ($roleName === User::ROLE_IT_MANAGER) {
            return in_array($categoryName, ['Computers', 'Electrical Equipment'], true);
        }

        return false;
    }
}
