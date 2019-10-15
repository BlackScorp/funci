<?php

use function BlackScorp\Funci\Demo\{indexAction};
use function BlackScorp\Funci\Core\{router};


router('/', function(){
    return indexAction();
});
