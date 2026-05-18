<h1 class="h3 mb-3">Asset Age & Distribution Reports</h1>
<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card"><div class="card-body">
            <h2 class="h5">Category Distribution</h2>
            <ul class="list-group list-group-flush">
                <?php foreach ($categoryDistribution as $item): ?>
                    <li class="list-group-item d-flex justify-content-between flex-wrap gap-2"><span><?= htmlspecialchars($item['label']) ?></span><span><?= (int) $item['total'] ?></span></li>
                <?php endforeach; ?>
            </ul>
        </div></div>
    </div>
    <div class="col-lg-6">
        <div class="card"><div class="card-body">
            <h2 class="h5">Status Distribution</h2>
            <ul class="list-group list-group-flush">
                <?php foreach ($statusDistribution as $item): ?>
                    <li class="list-group-item d-flex justify-content-between flex-wrap gap-2"><span><?= htmlspecialchars($item['label']) ?></span><span><?= (int) $item['total'] ?></span></li>
                <?php endforeach; ?>
            </ul>
        </div></div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h2 class="h5">Asset Age Report</h2>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead><tr><th>Serial Number</th><th>Category</th><th>Status</th><th>Date of Purchase</th><th>Age</th><th>Supplier</th></tr></thead>
                <tbody>
                <?php foreach ($assets as $asset): ?>
                    <tr>
                        <td><?= htmlspecialchars($asset['serial_number']) ?></td>
                        <td><?= htmlspecialchars($asset['category_name']) ?></td>
                        <td><?= htmlspecialchars($asset['status_name']) ?></td>
                        <td><?= htmlspecialchars($asset['date_of_purchase']) ?></td>
                        <td><?= htmlspecialchars($asset['age']) ?></td>
                        <td><?= htmlspecialchars($asset['supplier_name']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
