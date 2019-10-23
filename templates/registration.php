<?php layout('layouts/default');?>

<?php section('title');?>
Registrierung
<?php section('title');?>

<?php section('content');?>
<div class="container">
    <form method="POST" action="account/create" class="form-group">
    <div class="panel panel-default">
        <div class="panel-heading"><h2>Neuen Account anlegen</h2></div>
        <div class="panel-body">
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
                <label for="password">Passwort</label>
                <input class="form-control" id="password" type="password" name="password"
                       placeholder="Passwort"
                       value="<?= escape($password) ?>">
            </div>
              <div class="form-group">
                <label for="passwordRepeat">Password wiederholen</label>
                <input class="form-control" id="passwordRepeat" type="password" name="passwordRepeat"
                       placeholder="Password wiederholen"
                       value="<?= escape($passwordRepeat) ?>">
            </div>
            <div class="form-group">
                <label for="email">E-Mail</label>
                <input class="form-control" id="email" type="email" name="email"
                       placeholder="E-Mail"
                       value="<?= escape($email) ?>">
            </div>
            <div class="form-group">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="terms" id="terms"<?= $terms?' checked="checked"':'' ?>>
                    <label class="form-check-label" for="terms">Akzeptiere die <a href="terms" target="_blank">Regeln</a></label>
                </div>
            </div>

        </div>
        <div class="panel-footer">
            <button name="register" class="btn btn-outline-primary">Account anlegen</button>
        </div>
    </div>

</form>
</div>
<?php section('content');?>

<?php layout('layouts/default');