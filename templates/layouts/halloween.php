<!DOCTYPE html>
<html lang="en">
    <head>
        <title><?= $title ?></title>
        <meta charset="UTF-8"/>
        <base href="<?= BASE_URL.BASE_DIR ?>">
        <link href="https://fonts.googleapis.com/css?family=Mansalva&display=swap" rel="stylesheet"> 
        <link rel="stylesheet" type="text/css" media="screen" href="assets/css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" media="screen" href="assets/css/fontawesome.min.css">
        <link rel="stylesheet" type="text/css" media="screen" href="assets/css/style.css">
        <link rel="stylesheet" type="text/css" media="screen" href="assets/css/halloween.css">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= $styles ?>
    </head>
    <body>

        <header class="container">
            <div class="jumbotron">
                 <h1 class="display-4"><a href="/">Willkommen bei FUNCI</a></h1>
            </div>

        </header>

        <?= $content ?>
        <script src="assets/js/jquery.min.js"></script>
        <script src="assets/js/bootstrap.min.js"></script>
        <script src="assets/js/script.js"></script>
        <?= $scripts ?>
    </body>
</html>