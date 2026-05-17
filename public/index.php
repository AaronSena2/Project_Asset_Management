<?php

declare(strict_types=1);

use App\Controllers\AssetController;
use App\Controllers\AuthController;
use App\Controllers\CategoryController;
use App\Controllers\DashboardController;
use App\Controllers\ReportController;
use App\Controllers\SupplierController;
use App\Controllers\UserController;
use App\Database\Connection;
use App\Models\Asset;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\User;
use App\Services\AuthService;
use App\Services\BootstrapService;
use App\Services\NativeMailer;
use App\Services\NotificationService;

session_start();

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
    $path = __DIR__ . '/../src/' . $relative . '.php';
    if (file_exists($path)) {
        require $path;
    }
});

$appConfig = require __DIR__ . '/../config/app.php';
$dbConfig = require __DIR__ . '/../config/database.php';
$db = Connection::make($dbConfig);
(new BootstrapService(
    $db,
    $appConfig['default_admin_email'],
    $appConfig['default_admin_password']
))->ensureDefaults();

$users = new User($db);
$categories = new Category($db);
$suppliers = new Supplier($db);
$assets = new Asset($db);

$auth = new AuthService($users);
$notificationService = new NotificationService(new NativeMailer($appConfig['mail_from'], $appConfig['mail_from_name']));

$authController = new AuthController($auth);
$dashboardController = new DashboardController($auth, $assets, $suppliers, $users);
$supplierController = new SupplierController($auth, $suppliers, $categories, $users, $notificationService, $appConfig['office_admin_notification_email']);
$assetController = new AssetController($auth, $assets, $suppliers, $categories, $users, $notificationService, $appConfig['office_admin_notification_email']);
$userController = new UserController($auth, $users, $notificationService, $appConfig['office_admin_notification_email']);
$categoryController = new CategoryController($auth, $categories);
$reportController = new ReportController($auth, $assets, $suppliers);

$action = $_GET['action'] ?? ($auth->user() ? 'dashboard' : 'login');
$method = $_SERVER['REQUEST_METHOD'];

if ($action === 'login' && $method === 'GET') {
    $authController->loginForm();
    return;
}

if ($action === 'login' && $method === 'POST') {
    $authController->login($_POST);
    return;
}

if ($action === 'logout') {
    $authController->logout();
    return;
}

$auth->requireLogin();

if ($action === 'dashboard') {
    $dashboardController->index();
    return;
}

if ($action === 'suppliers' && $method === 'GET') {
    $supplierController->index();
    return;
}

if ($action === 'suppliers.create' && $method === 'POST') {
    $supplierController->create($_POST);
    return;
}

if ($action === 'suppliers.update' && $method === 'POST') {
    $supplierController->update($_POST);
    return;
}

if ($action === 'assets' && $method === 'GET') {
    $assetController->index();
    return;
}

if ($action === 'assets.create' && $method === 'POST') {
    $assetController->create($_POST);
    return;
}

if ($action === 'specifications' && $method === 'GET') {
    $assetController->specificationsByCategory((int) ($_GET['category_id'] ?? 0));
    return;
}

if ($action === 'users' && $method === 'GET') {
    $userController->index();
    return;
}

if ($action === 'users.create' && $method === 'POST') {
    $userController->create($_POST);
    return;
}

if ($action === 'users.update_role' && $method === 'POST') {
    $userController->updateRole($_POST);
    return;
}

if ($action === 'categories' && $method === 'GET') {
    $categoryController->index();
    return;
}

if ($action === 'categories.definition.create' && $method === 'POST') {
    $categoryController->createDefinition($_POST);
    return;
}

if ($action === 'reports' && $method === 'GET') {
    $reportController->index();
    return;
}

http_response_code(404);
echo 'Page not found';
