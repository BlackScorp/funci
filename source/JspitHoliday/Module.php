<?php
declare(strict_types=1);
namespace BlackScorp\Funci\JspitHoliday;
class Module{static function load(){}}; //this line is required for autoloading!!


require_once __DIR__.'/source/class/JspitHoliday.php';

$date = new \DateTime('2019-10-31');

$holidayClass = new \JspitHoliday("de");
$layout = 'default';
if ($holidayClass->isHoliday($date)) {
    $layout = str_replace(' ', '_', strtolower($holidayClass->holidayName($date)));
}

define('LAYOUT', $layout);