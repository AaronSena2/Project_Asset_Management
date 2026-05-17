<div class="row justify-content-center">
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h1 class="h4 mb-3">Login</h1>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <form method="post" action="/index.php?action=login">
                    <div class="mb-3">
                        <label class="form-label" for="login_email">Email</label>
                        <input id="login_email" class="form-control" type="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="login_password">Password</label>
                        <input id="login_password" class="form-control" type="password" name="password" required>
                    </div>
                    <button class="btn btn-primary w-100" type="submit">Sign in</button>
                </form>
            </div>
        </div>
        <p class="small text-muted mt-3">Default sample password: Password123!</p>
    </div>
</div>
