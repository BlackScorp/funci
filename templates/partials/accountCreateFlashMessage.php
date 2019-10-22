<?php
$messages = flashMessages('accountCreate');
if (count($messages) > 0): ?>
    <div class="alert alert-success" role="alert">
        <?php foreach ($messages as $message): ?>
            <p class="mb-0"><?= $message ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
