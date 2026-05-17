<?php
$appTitle = 'Inventory Management System';
$user = $_SESSION['user'] ?? null;
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
<body class="bg-light">
<?php if ($user): ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="/index.php?action=dashboard">Inventory</a>
        <div class="collapse navbar-collapse show">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="/index.php?action=dashboard">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="/index.php?action=assets">Assets</a></li>
                <li class="nav-item"><a class="nav-link" href="/index.php?action=suppliers">Suppliers</a></li>
                <?php if ($user['role_name'] === 'System Administrator'): ?>
                    <li class="nav-item"><a class="nav-link" href="/index.php?action=users">Users</a></li>
                    <li class="nav-item"><a class="nav-link" href="/index.php?action=categories">Categories & Specs</a></li>
                <?php endif; ?>
                <li class="nav-item"><a class="nav-link" href="/index.php?action=reports">Reports</a></li>
            </ul>
            <span class="navbar-text text-white me-3"><?= htmlspecialchars($user['full_name']) ?> (<?= htmlspecialchars($user['role_name']) ?>)</span>
            <a class="btn btn-outline-light" href="/index.php?action=logout">Logout</a>
        </div>
    </div>
</nav>
<?php endif; ?>
<main class="container py-4">
    <?php require $templateFile; ?>
</main>
<script src="/assets/js/specifications.js"></script>
</body>
</html>
