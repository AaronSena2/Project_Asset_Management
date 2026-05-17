<h1 class="h3 mb-3">Category Specification Management</h1>
<div class="card mb-4"><div class="card-body">
    <h2 class="h5">Add Custom Specification Field</h2>
    <form method="post" action="/index.php?action=categories.definition.create">
        <div class="row g-3">
            <div class="col-md-3"><label class="form-label">Category</label>
                <select class="form-select" name="category_id" required>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int) $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2"><label class="form-label">Field Key</label><input class="form-control" name="field_key" required></div>
            <div class="col-md-3"><label class="form-label">Field Label</label><input class="form-control" name="field_label" required></div>
            <div class="col-md-2"><label class="form-label">Field Type</label>
                <select class="form-select" name="field_type">
                    <option value="text">Text</option>
                    <option value="number">Number</option>
                    <option value="date">Date</option>
                    <option value="textarea">Textarea</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end"><div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_required" value="1" id="is_required">
                <label class="form-check-label" for="is_required">Required</label>
            </div></div>
        </div>
        <button class="btn btn-primary mt-3" type="submit">Add Field</button>
    </form>
</div></div>

<div class="table-responsive">
<table class="table table-striped table-bordered">
    <thead><tr><th>Category</th><th>Field Key</th><th>Label</th><th>Type</th><th>Required</th></tr></thead>
    <tbody>
    <?php foreach ($definitions as $definition): ?>
        <tr>
            <td><?= htmlspecialchars($definition['category_name']) ?></td>
            <td><?= htmlspecialchars($definition['field_key']) ?></td>
            <td><?= htmlspecialchars($definition['field_label']) ?></td>
            <td><?= htmlspecialchars($definition['field_type']) ?></td>
            <td><?= (int) $definition['is_required'] === 1 ? 'Yes' : 'No' ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
