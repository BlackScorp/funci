<?php
declare(strict_types=1);
/**
 * This is a place where we configure our application
 */

define('ROOT_DIR', realpath(__DIR__));

define('STORAGE_DIR',realpath(__DIR__).'/storage');

/**
 * Load vendor
 */
require_once __DIR__.'/vendor/autoload.php';

/**
 * Include configuration files
 */
require_once __DIR__ . '/config/default.php';

if(is_file(CONFIG_DIR.'/database.php')){
    require_once CONFIG_DIR.'/database.php';
}
if(is_file(CONFIG_DIR.'/mail.php')){
    require_once CONFIG_DIR.'/mail.php';
}
/**
 * Define BASE_DIR
 */
$scriptUrl = '/';
$beforeIndexPosition = strpos($_SERVER['PHP_SELF'], '/index.php');

if (false !== $beforeIndexPosition && $beforeIndexPosition > 0) {
    $scriptUrl = substr($_SERVER['PHP_SELF'], 0, $beforeIndexPosition) . '/';
    $_SERVER['REQUEST_URI'] = str_replace(['/index.php', $scriptUrl], '/', $_SERVER['REQUEST_URI']);

}
define('BASE_DIR', $scriptUrl);

/**
 * define BASE_URL
 */
$protocol = 'http://';
if (isset($_SERVER['HTTPS']) &&
        ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
        isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
        $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
    $protocol = 'https://';
}

define('BASE_URL', $protocol . $_SERVER['HTTP_HOST']);

/**
 * Include all configured modules
 */
require_once __DIR__ . '/config/modules.php';



