<?php
declare(strict_types=1);
namespace BlackScorp\Funci\Core;
class Event{static function load(){}} //this line is required for autoloading!!


/**
 * @param string $name
 * @param array $data
 * @param mixed|null $action
 *
 * @return mixed
 */
function event(string $name, array $data = [], callable $action = null, int $priority = 0) {
 static $events = [];
    if ($action) {
        $events[$name][] = [
            'priority' => $priority,
            'action' => $action
        ];
        return null;
    }
    if (!isset($events[$name])) {
        return null;
    }
    $eventData = $events[$name];

    usort($eventData, function($a, $b) {
        return $b['priority'] <=> $a['priority'];
    });
    
    $content = ob_start();
    foreach ($eventData as $event) {
        $data = array_values($data);
        echo $event['action'](...$data);
    }
    $content = ob_get_clean();
    return $content;
}
