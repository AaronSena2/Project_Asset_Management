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
            'currentUserId' => (int) ($this->auth->user()['id'] ?? 0),
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

        $userId = (int) ($post['user_id'] ?? 0);
        $roleId = (int) ($post['role_id'] ?? 0);
        if ($userId <= 0 || $roleId <= 0) {
            header('Location: /index.php?action=users');
            exit;
        }

        $isSystemAdministratorUser = $this->users->isSystemAdministratorUser($userId);
        $isSystemAdministratorRole = $this->users->isSystemAdministratorRole($roleId);
        if (
            $isSystemAdministratorUser
            && !$isSystemAdministratorRole
            && $this->users->activeSystemAdministratorsCount() <= 1
        ) {
            header('Location: /index.php?action=users');
            exit;
        }

        $this->users->updateRole($userId, $roleId);
        $this->syncSessionUserIfNeeded($userId);
        header('Location: /index.php?action=users');
        exit;
    }

    public function update(array $post): void
    {
        $this->auth->requireRole([User::ROLE_SYSTEM_ADMINISTRATOR]);

        $userId = (int) ($post['user_id'] ?? 0);
        $roleId = (int) ($post['role_id'] ?? 0);
        $fullName = trim((string) ($post['full_name'] ?? ''));
        $email = trim((string) ($post['email'] ?? ''));
        if ($userId <= 0 || $roleId <= 0 || $fullName === '' || $email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            header('Location: /index.php?action=users');
            exit;
        }

        $isSystemAdministratorUser = $this->users->isSystemAdministratorUser($userId);
        $isSystemAdministratorRole = $this->users->isSystemAdministratorRole($roleId);
        if (
            $isSystemAdministratorUser
            && !$isSystemAdministratorRole
            && $this->users->activeSystemAdministratorsCount() <= 1
        ) {
            header('Location: /index.php?action=users');
            exit;
        }

        $this->users->update($userId, $post);
        $this->syncSessionUserIfNeeded($userId);
        header('Location: /index.php?action=users');
        exit;
    }

    public function resetPassword(array $post): void
    {
        $this->auth->requireRole([User::ROLE_SYSTEM_ADMINISTRATOR]);

        $userId = (int) ($post['user_id'] ?? 0);
        $newPassword = (string) ($post['new_password'] ?? '');
        if ($userId <= 0 || strlen(trim($newPassword)) < 8) {
            header('Location: /index.php?action=users');
            exit;
        }

        $this->users->resetPassword($userId, $newPassword);
        header('Location: /index.php?action=users');
        exit;
    }

    public function remove(array $post): void
    {
        $this->auth->requireRole([User::ROLE_SYSTEM_ADMINISTRATOR]);

        $currentUserId = (int) ($this->auth->user()['id'] ?? 0);
        $userId = (int) ($post['user_id'] ?? 0);
        if ($userId <= 0 || $userId === $currentUserId) {
            header('Location: /index.php?action=users');
            exit;
        }

        if ($this->users->isSystemAdministratorUser($userId) && $this->users->activeSystemAdministratorsCount() <= 1) {
            header('Location: /index.php?action=users');
            exit;
        }

        $this->users->deactivate($userId);
        header('Location: /index.php?action=users');
        exit;
    }

    private function syncSessionUserIfNeeded(int $userId): void
    {
        $sessionUser = $this->auth->user();
        if ($sessionUser === null || (int) ($sessionUser['id'] ?? 0) !== $userId) {
            return;
        }

        $updated = $this->users->findById($userId);
        if ($updated === null || (int) ($updated['is_active'] ?? 0) !== 1) {
            unset($_SESSION['user']);

            return;
        }

        $_SESSION['user'] = [
            'id' => (int) $updated['id'],
            'full_name' => $updated['full_name'],
            'email' => $updated['email'],
            'role_name' => $updated['role_name'],
        ];
    }
}
