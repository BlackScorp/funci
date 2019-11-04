<?php
declare(strict_types=1);

function indexAction() {
    $data = [
        'loginWidget'=>router('/account/login')
    ];
    return render('index', $data);
}
