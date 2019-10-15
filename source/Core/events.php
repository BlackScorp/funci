<?php
use function BlackScorp\Funci\Core\{event,info,error};
define('EVENT_403','http.403');
define('EVENT_404','http.404');
define('EVENT_500','http.500');
/**
 * Setup basic events
 */
event(EVENT_403, [], function () {
    header('Content-Type:text/html;charset=utf-8');
    header('HTTP/1.0 403 Forbidden');
    return 'You are not logged in, please <a href="/login">login</a> first';
});

event(EVENT_404, [], function ($path) {
    header('Content-Type:text/html;charset=utf-8');
    header('HTTP/1.0 404 Not Found');
    $message = sprintf("Path '%s' not found", $path);
  
    info($message,array_merge($_SERVER,$_SERVER,$_COOKIE));
    return $message;
});

event(EVENT_500, [], function ($message, $context) {
    header('Content-Type:text/html;charset=utf-8');
    header('HTTP/1.0 500 Internal Server Error');
   
    error($message, $context);
    return sprintf('Something went wrong, got exception with message "<b style="color:indianred">%s</b>"', $message);
});

