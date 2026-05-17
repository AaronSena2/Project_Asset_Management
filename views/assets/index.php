<h1 class="h3 mb-3">Assets</h1>
<?php if ($canManageFurniture || $canManageTechnical): ?>
<div class="card mb-4">
    <div class="card-body">
        <h2 class="h5">Add Asset</h2>
        <form method="post" action="/index.php?action=assets.create">
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label" for="asset_serial_number">Serial Number</label><input id="asset_serial_number" class="form-control" name="serial_number" required></div>
                <div class="col-md-4"><label class="form-label" for="assetCategory">Category</label>
                    <select class="form-select" id="assetCategory" name="category_id" required>
                        <option value="">Select</option>
                        <?php foreach ($categories as $category): ?>
                            <?php if (!in_array((int) $category['id'], $manageableCategoryIds, true)): ?>
                                <?php continue; ?>
                            <?php endif; ?>
                            <option value="<?= (int) $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4"><label class="form-label" for="asset_status_id">Status</label>
                    <select id="asset_status_id" class="form-select" name="status_id" required>
                        <?php foreach ($statuses as $status): ?>
                            <option value="<?= (int) $status['id'] ?>"><?= htmlspecialchars($status['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4"><label class="form-label" for="asset_date_of_purchase">Date of Purchase</label><input id="asset_date_of_purchase" class="form-control" type="date" name="date_of_purchase" required></div>
                <div class="col-md-4"><label class="form-label" for="asset_supplier_id">Supplier</label>
                    <select id="asset_supplier_id" class="form-select" name="supplier_id" required>
                        <?php foreach ($suppliers as $supplier): ?>
                            <option value="<?= (int) $supplier['id'] ?>"><?= htmlspecialchars($supplier['company_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4"><label class="form-label" for="asset_assigned_to_user_id">Assigned User (optional)</label>
                    <select id="asset_assigned_to_user_id" class="form-select" name="assigned_to_user_id">
                        <option value="">Unassigned</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?= (int) $user['id'] ?>"><?= htmlspecialchars($user['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="mt-3" id="dynamicSpecifications"></div>
            <button class="btn btn-primary mt-2" type="submit">Save Asset</button>
        </form>
    </div>
</div>
<?php endif; ?>

<div class="table-responsive">
<table class="table table-striped table-bordered">
    <thead><tr><th>ID</th><th>Serial</th><th>Category</th><th>Status</th><th>Date of Purchase</th><th>Age</th><th>Supplier</th><th>Assigned To</th></tr></thead>
    <tbody>
    <?php foreach ($assets as $asset): ?>
        <tr>
            <td><?= (int) $asset['id'] ?></td>
            <td><?= htmlspecialchars($asset['serial_number']) ?></td>
            <td><?= htmlspecialchars($asset['category_name']) ?></td>
            <td><?= htmlspecialchars($asset['status_name']) ?></td>
            <td><?= htmlspecialchars($asset['date_of_purchase']) ?></td>
            <td><?= htmlspecialchars($asset['age']) ?></td>
            <td><?= htmlspecialchars($asset['supplier_name']) ?></td>
            <td><?= htmlspecialchars($asset['assigned_to'] ?? 'Unassigned') ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
