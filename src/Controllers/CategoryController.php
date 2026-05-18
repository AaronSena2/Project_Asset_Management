<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Category;
use App\Models\User;
use App\Services\AuthService;
use App\Support\View;

final class CategoryController
{
    public function __construct(
        private readonly AuthService $auth,
        private readonly Category $categories
    ) {
    }

    public function index(): void
    {
        $this->auth->requireRole([User::ROLE_SYSTEM_ADMINISTRATOR]);

        View::render('categories/index', [
            'categories' => $this->categories->all(),
            'definitions' => $this->categories->allSpecificationDefinitions(),
        ]);
    }

    public function createDefinition(array $post): void
    {
        $this->auth->requireRole([User::ROLE_SYSTEM_ADMINISTRATOR]);

        $creator = $this->auth->user();
        $this->categories->createSpecificationDefinition($post, (int) $creator['id']);

        header('Location: /index.php?action=categories');
        exit;
    }
}
