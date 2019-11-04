<form method="POST">
    <div class="card">
        <div class="card-header">
            Login
        </div>
        <div class="card-body">
            <?php if (count($errors) > 0): ?>
                <div class="alert alert-danger" role="alert">
                    <?php foreach ($errors as $message): ?>
                        <p class="mb-0"><?= $message ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <div class="form-group">
                <label for="username">Benutzername</label>
                <input class="form-control" id="username" type="text" name="username"
                       placeholder="Benutzername"
                       value="<?= escape($username) ?>">
            </div>
            <div class="form-group">
                <label for="username">Password</label>
                <input class="form-control" id="password" type="text" name="password"
                       placeholder="Password"
                       value="<?= escape($password) ?>">
            </div>
            <div class="form-group">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="stayLoggedIn" id="stayLoggedIn"<?= $stayLoggedIn ? ' checked="checked"' : '' ?>>
                    <label class="form-check-label" for="stayLoggedIn">Eingeloggt bleiben?</label>
                </div>
            </div>
            <div class="form-group">

                <a class="btn btn-danger"href="passwordLost">Password vergessen?</a>
            </div>
        </div>
        <div class="card-footer">
            <button class="btn btn-success" name="login">Login</button>

        </div>
    </div>
</form>