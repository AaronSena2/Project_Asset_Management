<?php
$workspaceMessage = match ($role) {
    'System Administrator' => 'Admin workspace: manage users, role assignments, categories, and custom specification fields.',
    'Office Administrator' => 'Office workspace: manage furniture assets, supplier coordination, and operational status updates.',
    'IT Manager' => 'IT workspace: manage technical assets, assignments, utilization, and supplier performance.',
    default => 'Finance workspace: review utilization trends, asset distribution, and register health metrics.',
};

$totalTrackedCategories = count($categoryDistribution);
$totalTrackedStatuses = count($statusDistribution);
$maintenanceTotal = 0;
$disposedTotal = 0;
$activeTotal = 0;

foreach ($statusDistribution as $item) {
    $label = strtolower((string) ($item['label'] ?? ''));
    $total = (int) ($item['total'] ?? 0);

    if (str_contains($label, 'maintenance') || str_contains($label, 'repair')) {
        $maintenanceTotal += $total;
    }

    if (str_contains($label, 'dispose')) {
        $disposedTotal += $total;
    }

    if (str_contains($label, 'active') || str_contains($label, 'use') || str_contains($label, 'assigned') || str_contains($label, 'available')) {
        $activeTotal += $total;
    }
}

$activeTotal = $activeTotal > 0 ? $activeTotal : max(0, (int) $assetTotal - $maintenanceTotal - $disposedTotal);
$disposedTotal = min($disposedTotal, (int) $assetTotal);
$maintenanceTotal = min($maintenanceTotal, max(0, (int) $assetTotal - $disposedTotal));
$trackedTotal = max(1, (int) $assetTotal);
?>

<section class="analytics-dashboard dashboard-hero">
    <div class="analytics-dashboard__header dashboard-hero__header">
        <div>
            <p class="analytics-dashboard__eyebrow dashboard-hero__eyebrow">Operational overview</p>
            <h1>Asset intelligence dashboard</h1>
            <p><?= htmlspecialchars($workspaceMessage) ?></p>
        </div>
        <span class="badge badge-primary"><?= htmlspecialchars($role) ?></span>
    </div>

    <div class="metrics-grid">
        <article class="metric-card">
            <div class="metric-card__label">
                <span>Total Assets</span>
                <span class="metric-card__icon" aria-hidden="true">📦</span>
            </div>
            <p class="metric-card__value"><?= (int) $assetTotal ?></p>
            <span class="metric-card__meta trend trend-up">+4.5% this month</span>
        </article>

        <article class="metric-card">
            <div class="metric-card__label">
                <span>Active</span>
                <span class="metric-card__icon" aria-hidden="true">✅</span>
            </div>
            <p class="metric-card__value"><?= (int) $activeTotal ?></p>
            <span class="metric-card__meta trend trend-up">Operationally healthy</span>
        </article>

        <article class="metric-card">
            <div class="metric-card__label">
                <span>Under Maintenance</span>
                <span class="metric-card__icon" aria-hidden="true">🛠️</span>
            </div>
            <p class="metric-card__value"><?= (int) $maintenanceTotal ?></p>
            <span class="metric-card__meta trend trend-neutral">Requires review</span>
        </article>

        <article class="metric-card">
            <div class="metric-card__label">
                <span>Disposed</span>
                <span class="metric-card__icon" aria-hidden="true">🗑️</span>
            </div>
            <p class="metric-card__value"><?= (int) $disposedTotal ?></p>
            <span class="metric-card__meta trend trend-down">Lifecycle complete</span>
        </article>
    </div>
</section>

<div class="row">
    <div class="col-lg-8">
        <section class="chart-card analytics-chart chart-panel">
            <div class="chart-card__header analytics-chart__header">
                <div>
                    <h2 class="chart-card__title analytics-chart__title">Asset acquisition and utilization snapshot</h2>
                    <p class="chart-card__subtitle analytics-chart__subtitle">A clean, high-level visual based on current register totals and category distribution.</p>
                </div>
                <span class="badge badge-info">Live register summary</span>
            </div>

            <div class="chart-bars analytics-bars bar-chart" aria-label="Asset trend overview">
                <?php
                $chartSeries = [
                    ['label' => 'Assets', 'value' => (int) $assetTotal, 'secondary' => false],
                    ['label' => 'Active', 'value' => (int) $activeTotal, 'secondary' => true],
                    ['label' => 'Maintenance', 'value' => (int) $maintenanceTotal, 'secondary' => false],
                    ['label' => 'Disposed', 'value' => (int) $disposedTotal, 'secondary' => true],
                    ['label' => 'Suppliers', 'value' => (int) $supplierTotal, 'secondary' => false],
                    ['label' => 'Users', 'value' => (int) $userTotal, 'secondary' => true],
                    ['label' => 'Categories', 'value' => (int) $totalTrackedCategories, 'secondary' => false],
                    ['label' => 'Statuses', 'value' => (int) $totalTrackedStatuses, 'secondary' => true],
                ];
                $chartMax = max(array_map(static fn ($seriesItem) => max(1, (int) $seriesItem['value']), $chartSeries));
                ?>
                <?php foreach ($chartSeries as $seriesItem): ?>
                    <?php $height = max(16, (int) round(($seriesItem['value'] / $chartMax) * 100)); ?>
                    <div class="chart-bar analytics-bar bar-chart__item">
                        <span class="chart-bar__value analytics-bar__value bar-chart__value"><?= (int) $seriesItem['value'] ?></span>
                        <div class="chart-bar__track analytics-bar__track bar-chart__track">
                            <div class="chart-bar__fill analytics-bar__fill bar-chart__fill <?= $seriesItem['secondary'] ? 'is-secondary' : '' ?>" style="height: <?= $height ?>%;"></div>
                        </div>
                        <span class="chart-bar__label analytics-bar__label bar-chart__label"><?= htmlspecialchars($seriesItem['label']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>

    <div class="col-lg-4">
        <section class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h2 class="h5 mb-0">Register health</h2>
                        <p class="text-muted mb-0">Quick operational indicators.</p>
                    </div>
                    <span class="badge badge-success">Stable</span>
                </div>

                <ul class="list-group">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Coverage ratio</span>
                        <strong><?= (int) round(($activeTotal / $trackedTotal) * 100) ?>%</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Suppliers onboarded</span>
                        <strong><?= (int) $supplierTotal ?></strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>User accounts</span>
                        <strong><?= (int) $userTotal ?></strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Tracked categories</span>
                        <strong><?= (int) $totalTrackedCategories ?></strong>
                    </li>
                </ul>
            </div>
        </section>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <section class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h2 class="h5 mb-0">Asset distribution by category</h2>
                        <p class="text-muted mb-0">Top-level mix of registered asset groups.</p>
                    </div>
                    <span class="badge badge-primary"><?= (int) $totalTrackedCategories ?> categories</span>
                </div>

                <ul class="list-group">
                    <?php foreach ($categoryDistribution as $item): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><?= htmlspecialchars($item['label']) ?></span>
                            <span class="badge badge-info"><?= (int) $item['total'] ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </section>
    </div>

    <div class="col-lg-6">
        <section class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h2 class="h5 mb-0">Asset distribution by status</h2>
                        <p class="text-muted mb-0">Status visibility for operations and lifecycle tracking.</p>
                    </div>
                    <span class="badge badge-warning"><?= (int) $totalTrackedStatuses ?> statuses</span>
                </div>

                <ul class="list-group">
                    <?php foreach ($statusDistribution as $item): ?>
                        <?php
                        $statusLabel = strtolower((string) $item['label']);
                        $badgeClass = 'badge-primary';
                        if (str_contains($statusLabel, 'maint') || str_contains($statusLabel, 'repair')) {
                            $badgeClass = 'badge-warning';
                        } elseif (str_contains($statusLabel, 'dispose')) {
                            $badgeClass = 'badge-danger';
                        } elseif (str_contains($statusLabel, 'active') || str_contains($statusLabel, 'use') || str_contains($statusLabel, 'available')) {
                            $badgeClass = 'badge-success';
                        }
                        ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><?= htmlspecialchars($item['label']) ?></span>
                            <span class="badge <?= $badgeClass ?>"><?= (int) $item['total'] ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </section>
    </div>
</div>

<?php if ($role === 'System Administrator'): ?>
<section class="card mt-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h2 class="h5 mb-0">Asset category configuration</h2>
                <p class="text-muted mb-0">Manage category structure and custom specifications from the configuration workspace.</p>
            </div>
            <a class="btn btn-primary" href="/index.php?action=categories">Open Configuration</a>
        </div>

        <div class="category-grid">
            <?php foreach (array_slice($categoryDistribution, 0, 6) as $item): ?>
                <article class="category-card">
                    <span class="badge badge-primary mb-2">Category</span>
                    <h3 class="h6 mb-2"><?= htmlspecialchars($item['label']) ?></h3>
                    <p class="text-muted mb-3">Registered assets in this category: <?= (int) $item['total'] ?>.</p>
                    <a href="/index.php?action=categories" class="btn btn-outline-primary w-100">Manage</a>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
