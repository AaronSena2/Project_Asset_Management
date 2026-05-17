<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Asset;
use App\Models\Supplier;
use App\Models\User;
use App\Services\AuthService;
use App\Support\View;

final class ReportController
{
    public function __construct(
        private readonly AuthService $auth,
        private readonly Asset $assets,
        private readonly Supplier $suppliers
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

        View::render('reports/index', [
            'assets' => $this->assets->all(),
            'categoryDistribution' => $this->assets->countByCategory(),
            'statusDistribution' => $this->assets->countByStatus(),
            'suppliers' => $this->suppliers->all(),
        ]);
    }
}
