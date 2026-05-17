<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;

final class AuthService
{
    public function __construct(private readonly User $users)
    {
    }

    public function attemptLogin(string $email, string $password): bool
    {
        $user = $this->users->findByEmail($email);
        if ($user === null || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        $_SESSION['user'] = [
            'id' => (int) $user['id'],
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'role_name' => $user['role_name'],
        ];

        return true;
    }

    public function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public function requireLogin(): void
    {
        if ($this->user() !== null) {
            return;
        }

        header('Location: /index.php?action=login');
        exit;
    }

    public function hasRole(array $allowedRoles): bool
    {
        $user = $this->user();
        if ($user === null) {
            return false;
        }

        return in_array($user['role_name'], $allowedRoles, true);
    }

    public function requireRole(array $allowedRoles): void
    {
        if ($this->hasRole($allowedRoles)) {
            return;
        }

        http_response_code(403);
        echo 'Forbidden';
        exit;
    }

    public function logout(): void
    {
        unset($_SESSION['user']);
        session_regenerate_id(true);
    }
}
