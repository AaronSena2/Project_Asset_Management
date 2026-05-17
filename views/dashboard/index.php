<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Dashboard</h1>
    <span class="badge bg-primary"><?= htmlspecialchars($role) ?></span>
</div>
<div class="card mb-4">
    <div class="card-body">
        <?php if ($role === 'System Administrator'): ?>
            <p class="mb-0">Admin workspace: manage users, role assignments, categories, and custom specification fields.</p>
        <?php elseif ($role === 'Office Administrator'): ?>
            <p class="mb-0">Office workspace: manage furniture assets and suppliers.</p>
        <?php elseif ($role === 'IT Manager'): ?>
            <p class="mb-0">IT workspace: manage computers/electrical assets, assignments, and suppliers.</p>
        <?php else: ?>
            <p class="mb-0">Finance workspace: read-only access to asset age and distribution insights.</p>
        <?php endif; ?>
    </div>
</div>
<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="card"><div class="card-body"><h2 class="h6">Total Assets</h2><p class="display-6"><?= (int) $assetTotal ?></p></div></div></div>
    <div class="col-md-4"><div class="card"><div class="card-body"><h2 class="h6">Total Suppliers</h2><p class="display-6"><?= (int) $supplierTotal ?></p></div></div></div>
    <div class="col-md-4"><div class="card"><div class="card-body"><h2 class="h6">Total Users</h2><p class="display-6"><?= (int) $userTotal ?></p></div></div></div>
</div>
<div class="row g-3">
    <div class="col-lg-6">
        <div class="card"><div class="card-body">
            <h2 class="h5">Asset Distribution by Category</h2>
            <ul class="list-group list-group-flush">
                <?php foreach ($categoryDistribution as $item): ?>
                    <li class="list-group-item d-flex justify-content-between">
                        <span><?= htmlspecialchars($item['label']) ?></span><span><?= (int) $item['total'] ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div></div>
    </div>
    <div class="col-lg-6">
        <div class="card"><div class="card-body">
            <h2 class="h5">Asset Distribution by Status</h2>
            <ul class="list-group list-group-flush">
                <?php foreach ($statusDistribution as $item): ?>
                    <li class="list-group-item d-flex justify-content-between">
                        <span><?= htmlspecialchars($item['label']) ?></span><span><?= (int) $item['total'] ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div></div>
    </div>
</div>
