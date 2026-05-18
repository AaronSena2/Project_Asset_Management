<h1 class="h3 mb-3">Users & Role Assignments</h1>
<div class="card mb-4"><div class="card-body">
    <h2 class="h5">Create User</h2>
    <form method="post" action="/index.php?action=users.create">
        <div class="row g-3">
            <div class="col-md-4"><label class="form-label" for="new_user_full_name">Full Name</label><input id="new_user_full_name" class="form-control" name="full_name" required></div>
            <div class="col-md-3"><label class="form-label" for="new_user_email">Email</label><input id="new_user_email" class="form-control" type="email" name="email" required></div>
            <div class="col-md-3"><label class="form-label" for="new_user_password">Password</label><input id="new_user_password" class="form-control" type="password" name="password" autocomplete="new-password" required></div>
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
    <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Edit User</th><th>Reset Password</th><th>Remove User</th></tr></thead>
    <tbody>
    <?php foreach ($users as $userItem): ?>
        <tr>
            <td><?= (int) $userItem['id'] ?></td>
            <td><?= htmlspecialchars($userItem['full_name']) ?></td>
            <td><?= htmlspecialchars($userItem['email']) ?></td>
            <td><?= htmlspecialchars($userItem['role_name']) ?></td>
            <td>
                <span class="badge <?= (int) $userItem['is_active'] === 1 ? 'badge-success' : 'badge-danger' ?>">
                    <?= (int) $userItem['is_active'] === 1 ? 'Active' : 'Removed' ?>
                </span>
            </td>
            <td>
                <form class="d-flex gap-2" method="post" action="/index.php?action=users.update">
                    <input type="hidden" name="user_id" value="<?= (int) $userItem['id'] ?>">
                    <input aria-label="Update full name for user" class="form-control" name="full_name" value="<?= htmlspecialchars($userItem['full_name']) ?>" required>
                    <input aria-label="Update email for user" class="form-control" type="email" name="email" value="<?= htmlspecialchars($userItem['email']) ?>" required>
                    <select aria-label="Update role for user" class="form-select" name="role_id" required>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= (int) $role['id'] ?>" <?= (int) $userItem['role_id'] === (int) $role['id'] ? 'selected' : '' ?>><?= htmlspecialchars($role['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn btn-outline-primary btn-sm" type="submit">Save</button>
                </form>
            </td>
            <td>
                <form class="d-flex gap-2" method="post" action="/index.php?action=users.reset_password">
                    <input type="hidden" name="user_id" value="<?= (int) $userItem['id'] ?>">
                    <input aria-label="Set new password for user" class="form-control" type="password" name="new_password" autocomplete="new-password" minlength="8" required>
                    <button class="btn btn-outline-primary btn-sm" type="submit">Reset</button>
                </form>
            </td>
            <td>
                <form class="js-user-remove-form" method="post" action="/index.php?action=users.remove">
                    <input type="hidden" name="user_id" value="<?= (int) $userItem['id'] ?>">
                    <button
                        class="btn btn-outline-primary btn-sm"
                        type="submit"
                        <?= (int) $userItem['id'] === (int) ($currentUserId ?? 0) || (int) $userItem['is_active'] !== 1 ? 'disabled' : '' ?>
                    >
                        Remove
                    </button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>

<script>
document.querySelectorAll('.js-user-remove-form').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!window.confirm('Remove this user from active access?')) {
            event.preventDefault();
        }
    });
});
</script>
