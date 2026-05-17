<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Services\AuthService;
use App\Services\NotificationService;
use App\Support\View;

final class UserController
{
    public function __construct(
        private readonly AuthService $auth,
        private readonly User $users,
        private readonly NotificationService $notifications,
        private readonly string $fallbackOfficeEmail
    ) {
    }

    public function index(): void
    {
        $this->auth->requireRole([User::ROLE_SYSTEM_ADMINISTRATOR]);

        View::render('users/index', [
            'users' => $this->users->all(),
            'roles' => $this->users->roles(),
        ]);
    }

    public function create(array $post): void
    {
        $this->auth->requireRole([User::ROLE_SYSTEM_ADMINISTRATOR]);

        $creator = $this->auth->user();
        $id = $this->users->create($post, (int) $creator['id']);

        $officeAdminEmail = $this->users->officeAdministratorEmail() ?? $this->fallbackOfficeEmail;
        $this->notifications->notifyEntityCreated($officeAdminEmail, 'User', [
            'User ID' => (string) $id,
            'Email' => $post['email'] ?? '',
            'Role ID' => (string) ($post['role_id'] ?? ''),
        ], $creator['full_name']);

        header('Location: /index.php?action=users');
        exit;
    }

    public function updateRole(array $post): void
    {
        $this->auth->requireRole([User::ROLE_SYSTEM_ADMINISTRATOR]);

        $this->users->updateRole((int) $post['user_id'], (int) $post['role_id']);
        header('Location: /index.php?action=users');
        exit;
    }
}
