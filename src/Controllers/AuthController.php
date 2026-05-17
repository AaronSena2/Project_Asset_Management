<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuthService;
use App\Support\View;

final class AuthController
{
    public function __construct(private readonly AuthService $auth)
    {
    }

    public function loginForm(?string $error = null): void
    {
        View::render('auth/login', ['error' => $error]);
    }

    public function login(array $post): void
    {
        if ($this->auth->attemptLogin(trim($post['email'] ?? ''), $post['password'] ?? '')) {
            header('Location: /index.php?action=dashboard');
            exit;
        }

        $this->loginForm('Invalid credentials.');
    }

    public function logout(): void
    {
        $this->auth->logout();
        header('Location: /index.php?action=login');
        exit;
    }
}
