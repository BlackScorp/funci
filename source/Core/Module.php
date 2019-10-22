<?php
declare(strict_types=1);
namespace BlackScorp\Funci\Core;
class Module{static function load(){}}; //this line is required for autoloading!!

set_error_handler(function () {
    event(EVENT_500, ['message' => func_get_arg(1), 'context' => func_get_arg(4)]);
}, E_ALL & ~E_WARNING & ~E_NOTICE);

set_error_handler(function () {
    notice(func_get_arg(1),func_get_arg(4));
}, E_WARNING & E_NOTICE);


require_once __DIR__.'/DataBase.php';
require_once __DIR__.'/Event.php';
require_once __DIR__.'/Logger.php';
require_once __DIR__.'/Router.php';
require_once __DIR__.'/Template.php';
require_once __DIR__.'/Utilities.php';
require_once __DIR__.'/events.php';
require_once __DIR__.'/routes.php';

