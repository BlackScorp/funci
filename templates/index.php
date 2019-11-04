<?php layout('layouts/' . LAYOUT); ?>

<?php section('title'); ?>
Willkommen bei Funci
<?php section('title'); ?>

<?php section('content'); ?>
<div class="container">
    <div class="card">
        <div class="card-body">
            <?php include __DIR__ . '/partials/accountFlashMessage.php'; ?>
            <div class="row">
                <div class="col-4">
                    <?= $loginWidget ?>
                </div>
                <div class="col-8">
                    Willkommen bei Funci
                </div>
            </div>


        </div>
    </div>

</div>
<?php section('content'); ?>

<?php
layout('layouts/' . LAYOUT);
