<?php
$canManageAssets = $canManageFurniture || $canManageTechnical;
$isOfficeAdministrator = ($role ?? ($_SESSION['user']['role_name'] ?? '')) === 'Office Administrator';
$isSystemAdministrator = ($role ?? ($_SESSION['user']['role_name'] ?? '')) === 'System Administrator';
$totalAssets = count($assets);

$statusBadgeMap = [
    'available' => 'badge-success',
    'in use' => 'badge-success',
    'active' => 'badge-success',
    'assigned' => 'badge-primary',
    'maintenance' => 'badge-warning',
    'in repair' => 'badge-warning',
    'repair' => 'badge-warning',
    'disposed' => 'badge-danger',
    'retired' => 'badge-danger',
];
?>

<section class="analytics-dashboard dashboard-hero mb-4">
    <div class="analytics-dashboard__header dashboard-hero__header">
        <div>
            <p class="analytics-dashboard__eyebrow dashboard-hero__eyebrow">Asset register</p>
            <h1>Manage and monitor every asset from one place</h1>
            <p>
                Review asset lifecycle details, onboard new records, bulk import inventory,
                and keep operational statuses current with a cleaner register workflow.
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2 align-items-center">
            <span class="badge badge-primary"><?= (int) $totalAssets ?> assets tracked</span>
            <?php if ($isSystemAdministrator): ?>
                <a href="#asset-import-panel" class="btn btn-primary">Import Assets</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="metrics-grid">
        <article class="metric-card">
            <div class="metric-card__label">
                <span>Total Assets</span>
                <span class="metric-card__icon" aria-hidden="true">📦</span>
            </div>
            <p class="metric-card__value"><?= (int) $totalAssets ?></p>
            <span class="metric-card__meta trend trend-up">Register is up to date</span>
        </article>

        <article class="metric-card">
            <div class="metric-card__label">
                <span>Categories</span>
                <span class="metric-card__icon" aria-hidden="true">🗃️</span>
            </div>
            <p class="metric-card__value"><?= count($categories) ?></p>
            <span class="metric-card__meta trend trend-neutral">Structured inventory groups</span>
        </article>

        <article class="metric-card">
            <div class="metric-card__label">
                <span>Suppliers</span>
                <span class="metric-card__icon" aria-hidden="true">🚚</span>
            </div>
            <p class="metric-card__value"><?= count($suppliers) ?></p>
            <span class="metric-card__meta trend trend-up">Procurement sources available</span>
        </article>

        <article class="metric-card">
            <div class="metric-card__label">
                <span>Status Types</span>
                <span class="metric-card__icon" aria-hidden="true">🏷️</span>
            </div>
            <p class="metric-card__value"><?= count($statuses) ?></p>
            <span class="metric-card__meta trend trend-neutral">Operational visibility enabled</span>
        </article>
    </div>
</section>

<?php if ($isSystemAdministrator): ?>
<section id="asset-import-panel" class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h2 class="h5 mb-0">Admin bulk asset import</h2>
                <p class="text-muted mb-0">Upload a CSV file to quickly seed the asset register.</p>
            </div>
            <span class="badge badge-info">System Admin workflow</span>
        </div>

        <?php if (isset($_GET['import_success'])): ?>
            <div class="alert alert-success" role="alert"><?= htmlspecialchars((string) $_GET['import_success']) ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['import_error'])): ?>
            <div class="alert alert-danger" role="alert"><?= htmlspecialchars((string) $_GET['import_error']) ?></div>
        <?php endif; ?>

        <form method="post" action="/index.php?action=assets.import" enctype="multipart/form-data" class="import-dropzone upload-dropzone">
            <div class="metric-card__icon" aria-hidden="true">⬆️</div>
            <h3 class="h6 mb-1">Upload asset CSV</h3>
            <p class="text-muted mb-2">Required columns: serial_number, category_id, status_id, date_of_purchase, supplier_id.</p>
            <div class="d-flex flex-column flex-md-row justify-content-center gap-2 align-items-center">
                <input class="form-control" type="file" name="asset_csv" accept=".csv,text/csv" required style="max-width: 420px;">
                <button type="submit" class="btn btn-primary">Import CSV</button>
            </div>
        </form>
    </div>
</section>
<?php endif; ?>

<div class="row">
    <?php if ($canManageAssets): ?>
    <div class="col-lg-5">
        <section class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h2 class="h5 mb-0">Add asset</h2>
                        <p class="text-muted mb-0">Create a new asset record with clean, structured metadata.</p>
                    </div>
                    <span class="badge badge-primary">Create</span>
                </div>

                <form method="post" action="/index.php?action=assets.create">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label" for="asset_serial_number">Serial Number</label>
                            <input id="asset_serial_number" class="form-control" name="serial_number" required>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label" for="assetCategory">Category</label>
                            <select class="form-select" id="assetCategory" name="category_id" required>
                                <option value="">Select category</option>
                                <?php foreach ($categories as $category): ?>
                                    <?php if (!in_array((int) $category['id'], $manageableCategoryIds, true)): ?>
                                        <?php continue; ?>
                                    <?php endif; ?>
                                    <option value="<?= (int) $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="asset_status_id">Status</label>
                            <select id="asset_status_id" class="form-select" name="status_id" required>
                                <?php foreach ($statuses as $status): ?>
                                    <option value="<?= (int) $status['id'] ?>"><?= htmlspecialchars($status['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="asset_date_of_purchase">Date of Purchase</label>
                            <input id="asset_date_of_purchase" class="form-control" type="date" name="date_of_purchase" required>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label" for="asset_supplier_id">Supplier</label>
                            <select id="asset_supplier_id" class="form-select" name="supplier_id" required>
                                <?php foreach ($suppliers as $supplier): ?>
                                    <option value="<?= (int) $supplier['id'] ?>"><?= htmlspecialchars($supplier['company_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label" for="asset_assigned_to_user_id">Assigned User (optional)</label>
                            <select id="asset_assigned_to_user_id" class="form-select" name="assigned_to_user_id">
                                <option value="">Unassigned</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?= (int) $user['id'] ?>"><?= htmlspecialchars($user['full_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mt-3" id="dynamicSpecifications"></div>

                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <button class="btn btn-primary" type="submit">Save Asset</button>
                        <button class="btn btn-outline-primary" type="reset">Clear Form</button>
                    </div>
                </form>
            </div>
        </section>
    </div>

    <div class="col-lg-7">
        <section class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <div>
                        <h2 class="h5 mb-0">Asset category configuration</h2>
                        <p class="text-muted mb-0">Quick access to category structure used across the register.</p>
                    </div>
                    <a class="btn btn-outline-primary" href="/index.php?action=categories">Manage Categories</a>
                </div>

                <div class="category-grid">
                    <?php foreach (array_slice($categories, 0, 6) as $category): ?>
                        <article class="category-card">
                            <span class="badge badge-primary mb-2">Category</span>
                            <h3 class="h6 mb-2"><?= htmlspecialchars($category['name']) ?></h3>
                            <p class="text-muted mb-0">Use this category to organize and standardize related asset records.</p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    </div>
    <?php else: ?>
    <div class="col-12">
        <section class="card mb-4">
            <div class="card-body">
                <h2 class="h5 mb-2">Read-only asset register</h2>
                <p class="text-muted mb-0">You can review current asset records and statuses, but you do not have permission to create new assets.</p>
            </div>
        </section>
    </div>
    <?php endif; ?>
</div>

<section class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h2 class="h5 mb-0">Asset register table</h2>
                <p class="text-muted mb-0">Minimal, high-clarity asset inventory view with quick status visibility.</p>
            </div>
            <?php if ($isSystemAdministrator): ?>
                <a href="#asset-import-panel" class="btn btn-primary">Import Assets</a>
            <?php endif; ?>
        </div>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Serial</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Date of Purchase</th>
                        <th>Age</th>
                        <th>Supplier</th>
                        <th>Assigned To</th>
                        <?php if ($isOfficeAdministrator): ?>
                            <th>Edit Status</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($assets as $asset): ?>
                    <?php
                    $statusName = trim((string) ($asset['status_name'] ?? 'Unknown'));
                    $statusKey = strtolower($statusName);
                    $badgeClass = $statusBadgeMap[$statusKey] ?? 'badge-primary';
                    ?>
                    <tr>
                        <td><?= (int) $asset['id'] ?></td>
                        <td><?= htmlspecialchars($asset['serial_number']) ?></td>
                        <td><?= htmlspecialchars($asset['category_name']) ?></td>
                        <td>
                            <span class="badge <?= $badgeClass ?>">
                                <?= htmlspecialchars($statusName) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($asset['date_of_purchase']) ?></td>
                        <td><?= htmlspecialchars($asset['age']) ?></td>
                        <td><?= htmlspecialchars($asset['supplier_name']) ?></td>
                        <td><?= htmlspecialchars($asset['assigned_to'] ?? 'Unassigned') ?></td>
                        <?php if ($isOfficeAdministrator): ?>
                            <td>
                                <div class="inline-status-editor status-control">
                                    <label class="visually-hidden" for="status_asset_<?= (int) $asset['id'] ?>">Edit status for asset <?= (int) $asset['id'] ?></label>
                                    <select
                                        id="status_asset_<?= (int) $asset['id'] ?>"
                                        class="form-select"
                                        aria-label="Edit status for asset <?= (int) $asset['id'] ?>"
                                    >
                                        <?php foreach ($statuses as $status): ?>
                                            <option
                                                value="<?= (int) $status['id'] ?>"
                                                <?= $statusName === $status['name'] ? 'selected' : '' ?>
                                            >
                                                <?= htmlspecialchars($status['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="button" class="btn btn-outline-primary">Update</button>
                                </div>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
