<?php
$appTitle = 'Inventory Management System';
$user = $_SESSION['user'] ?? null;
$currentAction = $_GET['action'] ?? 'dashboard';
$currentSection = explode('.', (string) $currentAction)[0] ?: 'dashboard';
$roleName = $user['role_name'] ?? 'Guest';
$userName = $user['full_name'] ?? 'Guest User';
$userInitials = '';
if ($userName !== '') {
    $nameParts = preg_split('/\s+/', trim($userName)) ?: [];
    foreach (array_slice($nameParts, 0, 2) as $part) {
        $userInitials .= strtoupper(substr($part, 0, 1));
    }
    $userInitials = $userInitials !== '' ? $userInitials : 'GU';
}

$navigationItems = [
    [
        'label' => 'Dashboard',
        'action' => 'dashboard',
        'href' => '/index.php?action=dashboard',
        'icon' => '📊',
        'match' => ['dashboard'],
    ],
    [
        'label' => 'Asset Register',
        'action' => 'assets',
        'href' => '/index.php?action=assets',
        'icon' => '🗂️',
        'match' => ['assets'],
    ],
    [
        'label' => 'Configuration',
        'action' => 'categories',
        'href' => '/index.php?action=categories',
        'icon' => '⚙️',
        'match' => ['categories'],
    ],
];

$pageTitles = [
    'dashboard' => 'Dashboard',
    'assets' => 'Asset Register',
    'categories' => 'Configuration',
    'suppliers' => 'Suppliers',
    'reports' => 'Reports',
    'users' => 'Users',
];

$pageTitle = $pageTitles[$currentSection] ?? 'Workspace';
$pageDescription = match ($currentSection) {
    'dashboard' => 'Track performance, utilization, and operational insights across your assets.',
    'assets' => 'Manage assets, monitor statuses, and keep your register up to date.',
    'categories' => 'Configure categories and custom specification fields for structured asset data.',
    default => 'Manage your asset operations from one clean workspace.',
};

if (!empty($viewData) && is_array($viewData)) {
    foreach ($viewData as $viewKey => $viewValue) {
        if (!isset($$viewKey)) {
            $$viewKey = $viewValue;
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($appTitle) ?></title>
    <link href="/assets/css/bootstrap-fallback.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="<?= $user ? 'dashboard-layout' : 'bg-light' ?>">
<?php if ($user): ?>
<div class="app-shell">
    <aside id="app-sidebar" class="sidebar app-sidebar" aria-label="Primary navigation">
        <div class="sidebar__brand sidebar-brand">
            <span class="sidebar__brand-mark sidebar-brand__mark" aria-hidden="true">AM</span>
            <div>
                <h1 class="sidebar-brand__title">Asset Manager</h1>
                <p class="sidebar-brand__subtitle">Modern operations workspace</p>
            </div>
        </div>

        <p class="sidebar__section-label sidebar-label">Navigation</p>
        <nav class="sidebar-nav sidebar-menu">
            <?php foreach ($navigationItems as $item): ?>
                <?php $isActive = in_array($currentSection, $item['match'], true); ?>
                <a
                    href="<?= htmlspecialchars($item['href']) ?>"
                    class="sidebar-nav__link <?= $isActive ? 'is-active active' : '' ?>"
                    <?= $isActive ? 'aria-current="page"' : '' ?>
                >
                    <span class="sidebar-nav__icon icon" aria-hidden="true"><?= htmlspecialchars($item['icon']) ?></span>
                    <span><?= htmlspecialchars($item['label']) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="card mt-3" style="margin-top:auto; background: rgba(255,255,255,0.06); border-color: rgba(255,255,255,0.08); color: #fff;">
            <div class="card-body">
                <p class="sidebar__section-label" style="padding:0; margin:0 0 0.5rem; color:rgba(255,255,255,0.45);">Signed in as</p>
                <strong style="display:block; font-size:1rem;"><?= htmlspecialchars($userName) ?></strong>
                <span style="display:block; color:rgba(255,255,255,0.68); font-size:0.85rem;"><?= htmlspecialchars($roleName) ?></span>
                <a class="btn btn-outline-light mt-3 w-100" href="/index.php?action=logout">Logout</a>
            </div>
        </div>
    </aside>
    <button type="button" class="sidebar-backdrop" id="sidebarBackdrop" aria-label="Close navigation"></button>

    <main class="main-content app-main">
        <header class="topbar navbar" aria-label="Page header">
            <button
                type="button"
                class="btn btn-outline-primary sidebar-toggle"
                id="sidebarToggle"
                aria-controls="app-sidebar"
                aria-expanded="false"
                aria-label="Open navigation"
            >
                ☰ Menu
            </button>
            <div class="topbar__heading">
                <ul class="breadcrumb topbar__breadcrumb" aria-label="Breadcrumb">
                    <li class="breadcrumb-item"><a href="/index.php?action=dashboard">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($pageTitle) ?></li>
                </ul>
                <h2 class="topbar__title"><?= htmlspecialchars($pageTitle) ?></h2>
                <p><?= htmlspecialchars($pageDescription) ?></p>
            </div>
            <div class="profile-chip user-dropdown" role="button" tabindex="0" aria-label="User profile summary">
                <span class="profile-chip__avatar user-dropdown__avatar"><?= htmlspecialchars($userInitials) ?></span>
                <span>
                    <strong style="display:block;"><?= htmlspecialchars($userName) ?></strong>
                    <span class="text-muted" style="font-size:0.82rem;"><?= htmlspecialchars($roleName) ?></span>
                </span>
            </div>
        </header>

        <div class="container-fluid" style="padding: 0;">
            <?php require $templateFile; ?>
        </div>
    </main>
</div>
<?php else: ?>
<main class="container py-4">
    <?php require $templateFile; ?>
</main>
<?php endif; ?>
<script src="/assets/js/specifications.js"></script>
<?php if ($user): ?>
<script>
    (() => {
        const body = document.body;
        const toggle = document.getElementById('sidebarToggle');
        const backdrop = document.getElementById('sidebarBackdrop');

        if (!toggle || !backdrop) {
            return;
        }

        const closeSidebar = () => {
            body.classList.remove('is-sidebar-open');
            toggle.setAttribute('aria-expanded', 'false');
        };

        toggle.addEventListener('click', () => {
            const isOpen = body.classList.toggle('is-sidebar-open');
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });

        backdrop.addEventListener('click', closeSidebar);

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeSidebar();
            }
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth > 991) {
                closeSidebar();
            }
        });
    })();
</script>
<?php endif; ?>
</body>
</html>
