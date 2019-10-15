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


Event::load();
Logger::load();
Router::load();
Template::load();
DataBase::load();
Mail::load();
Utitilities::load();



require_once __DIR__.'/events.php';
require_once __DIR__.'/routes.php';

