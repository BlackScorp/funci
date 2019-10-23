<?php
declare(strict_types=1);
namespace BlackScorp\Funci\Account;
class Module{static function load(){}}; //this line is required for autoloading!!

require_once __DIR__.'/Services.php';
require_once __DIR__.'/Validators.php';
require_once __DIR__.'/Repository.php';
require_once __DIR__.'/Actions.php';
require_once __DIR__.'/routes.php';
require_once __DIR__.'/events.php';