<?php
declare(strict_types=1);
namespace BlackScorp\Funci\Demo;
class Module{static function load(){}}; //this line is required for autoloading!!

Actions::load();

require_once __DIR__.'/routes.php';
