<h1 class="h3 mb-3">Users & Role Assignments</h1>
<div class="card mb-4"><div class="card-body">
    <h2 class="h5">Create User</h2>
    <form method="post" action="/index.php?action=users.create">
        <div class="row g-3">
            <div class="col-md-4"><label class="form-label" for="new_user_full_name">Full Name</label><input id="new_user_full_name" class="form-control" name="full_name" required></div>
            <div class="col-md-3"><label class="form-label" for="new_user_email">Email</label><input id="new_user_email" class="form-control" type="email" name="email" required></div>
            <div class="col-md-3"><label class="form-label" for="new_user_password">Password</label><input id="new_user_password" class="form-control" type="password" name="password" required></div>
            <div class="col-md-2"><label class="form-label" for="new_user_role_id">Role</label>
                <select id="new_user_role_id" class="form-select" name="role_id" required>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= (int) $role['id'] ?>"><?= htmlspecialchars($role['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <button class="btn btn-primary mt-3" type="submit">Create User</button>
    </form>
</div></div>

<div class="table-responsive">
<table class="table table-striped table-bordered">
    <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Update Role</th></tr></thead>
    <tbody>
    <?php foreach ($users as $userItem): ?>
        <tr>
            <td><?= (int) $userItem['id'] ?></td>
            <td><?= htmlspecialchars($userItem['full_name']) ?></td>
            <td><?= htmlspecialchars($userItem['email']) ?></td>
            <td><?= htmlspecialchars($userItem['role_name']) ?></td>
            <td>
                <form class="d-flex gap-2" method="post" action="/index.php?action=users.update_role">
                    <input type="hidden" name="user_id" value="<?= (int) $userItem['id'] ?>">
                    <select aria-label="Update role for user" class="form-select" name="role_id">
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= (int) $role['id'] ?>" <?= (int) $userItem['role_id'] === (int) $role['id'] ? 'selected' : '' ?>><?= htmlspecialchars($role['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn btn-outline-primary btn-sm" type="submit">Update</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
