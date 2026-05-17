<h1 class="h3 mb-3">Suppliers</h1>
<?php if ($canManage): ?>
<div class="card mb-4"><div class="card-body">
    <h2 class="h5">Add Supplier</h2>
    <form method="post" action="/index.php?action=suppliers.create">
        <div class="row g-3">
            <div class="col-md-3"><label class="form-label">Supplier Code</label><input class="form-control" name="supplier_code" required></div>
            <div class="col-md-3"><label class="form-label">Company Name</label><input class="form-control" name="company_name" required></div>
            <div class="col-md-2"><label class="form-label">Contact Person</label><input class="form-control" name="contact_person" required></div>
            <div class="col-md-2"><label class="form-label">Email</label><input class="form-control" type="email" name="email" required></div>
            <div class="col-md-2"><label class="form-label">Phone</label><input class="form-control" name="phone" required></div>
            <div class="col-md-4"><label class="form-label">Category Specialization</label>
                <select class="form-select" name="category_specialization_id" required>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int) $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <button class="btn btn-primary mt-3" type="submit">Save Supplier</button>
    </form>
</div></div>
<?php endif; ?>

<div class="table-responsive">
<table class="table table-striped table-bordered">
    <thead><tr><th>ID</th><th>Code</th><th>Company</th><th>Contact</th><th>Email</th><th>Phone</th><th>Specialization</th><?php if ($canManage): ?><th>Update</th><?php endif; ?></tr></thead>
    <tbody>
    <?php foreach ($suppliers as $supplier): ?>
        <tr>
            <td><?= (int) $supplier['id'] ?></td>
            <td><?= htmlspecialchars($supplier['supplier_code']) ?></td>
            <td><?= htmlspecialchars($supplier['company_name']) ?></td>
            <td><?= htmlspecialchars($supplier['contact_person']) ?></td>
            <td><?= htmlspecialchars($supplier['email']) ?></td>
            <td><?= htmlspecialchars($supplier['phone']) ?></td>
            <td><?= htmlspecialchars($supplier['category_specialization']) ?></td>
            <?php if ($canManage): ?>
                <td>
                    <form class="d-flex gap-2" method="post" action="/index.php?action=suppliers.update">
                        <input type="hidden" name="supplier_id" value="<?= (int) $supplier['id'] ?>">
                        <input aria-label="Company name" class="form-control" name="company_name" value="<?= htmlspecialchars($supplier['company_name']) ?>" required>
                        <input aria-label="Contact person" class="form-control" name="contact_person" value="<?= htmlspecialchars($supplier['contact_person']) ?>" required>
                        <input aria-label="Supplier email" class="form-control" type="email" name="email" value="<?= htmlspecialchars($supplier['email']) ?>" required>
                        <input aria-label="Supplier phone" class="form-control" name="phone" value="<?= htmlspecialchars($supplier['phone']) ?>" required>
                        <select aria-label="Category specialization" class="form-select" name="category_specialization_id" required>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= (int) $category['id'] ?>" <?= (int) $supplier['category_specialization_id'] === (int) $category['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn btn-outline-primary btn-sm" type="submit">Save</button>
                    </form>
                </td>
            <?php endif; ?>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
