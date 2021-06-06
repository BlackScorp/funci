<?php
declare(strict_types=1);
namespace BlackScorp\Funci\Message;
class Module{static function load(){}}; //this line is required for autoloading!!

define('KEY_DIR',STORAGE_DIR.'/keys');
if(!is_dir(KEY_DIR)){
    mkdir(KEY_DIR);
}
define('SEALED_MESSAGE_DIR',STORAGE_DIR.'/messages');
if(!is_dir(SEALED_MESSAGE_DIR)){
    mkdir(SEALED_MESSAGE_DIR);
}
define('KEY_MESSAGE_SEPARATOR',"\n---KEY---\n");
require_once __DIR__.'/functions.php';
require_once __DIR__.'/events.php';
require_once __DIR__.'/routes.php';