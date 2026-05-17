<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Asset;
use App\Models\Supplier;
use App\Models\User;
use App\Services\AuthService;
use App\Support\View;

final class DashboardController
{
    public function __construct(
        private readonly AuthService $auth,
        private readonly Asset $assets,
        private readonly Supplier $suppliers,
        private readonly User $users
    ) {
    }

    public function index(): void
    {
        $this->auth->requireLogin();

        $user = $this->auth->user();
        $role = $user['role_name'];

        View::render('dashboard/index', [
            'role' => $role,
            'assetTotal' => $this->assets->totalCount(),
            'supplierTotal' => count($this->suppliers->all()),
            'userTotal' => count($this->users->all()),
            'categoryDistribution' => $this->assets->countByCategory(),
            'statusDistribution' => $this->assets->countByStatus(),
        ]);
    }
}
