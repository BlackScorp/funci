<?php
declare(strict_types=1);
namespace BlackScorp\Funci\Core;
class Logger{static function load(){}}; //this line is required for autoloading!!


define('LOG_LEVEL_EMERGENCY', 'emergency');
define('LOG_LEVEL_ALERT', 'alert');
define('LOG_LEVEL_CRITICAL', 'critical');
define('LOG_LEVEL_ERROR', 'error');
define('LOG_LEVEL_WARNING', 'warning');
define('LOG_LEVEL_NOTICE', 'notice');
define('LOG_LEVEL_INFO', 'info');
define('LOG_LEVEL_DEBUG', 'debug');


function logger(string $level, callable $handler = null){
    static $handlers = [];
    if($handler){
        return $handlers[$level][]=$handler;
    }
    if(isset($handlers[$level])){
        return $handlers[$level];
    }
    return [function(){}];
}

function logMessage(string $level, string $message, array $context = []) {
    $handlers = logger($level);

    $record = [
        'message' => $message,
        'context' => $context,
        'level' => $level,
        'datetime' => date('Y-m-d H:i:s')
    ];
    foreach($handlers as $handler){
        $handler($record);
    }
}
function debug(string $message,array $context = []){
    logMessage(LOG_LEVEL_DEBUG, $message,$context);
}
function info(string $message,array $context = []){
    logMessage(LOG_LEVEL_INFO, $message,$context);
}
function notice(string $message,array $context = []){
    logMessage(LOG_LEVEL_NOTICE, $message,$context);
}
function warning(string $message,array $context = []){
    logMessage(LOG_LEVEL_WARNING, $message,$context);
}
function error(string $message,array $context = []){
    logMessage(LOG_LEVEL_ERROR, $message,$context);
}
function critical(string $message,array $context = []){
    logMessage(LOG_LEVEL_CRITICAL, $message,$context);
}
function emergency(string $message,array $context = []){
    logMessage(LOG_LEVEL_EMERGENCY, $message,$context);
}