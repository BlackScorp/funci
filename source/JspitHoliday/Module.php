<?php
declare(strict_types=1);
namespace BlackScorp\Funci\JspitHoliday;
class Module{static function load(){}}; //this line is required for autoloading!!


require_once __DIR__.'/source/class/JspitHoliday.php';


$date = new \DateTime();
$layout = 'default';

$holidayClass = new \JspitHoliday("DE");

if ($holidayClass->isHoliday($date)) {
     $layout = str_replace(' ', '_', strtolower($holidayClass->holidayName($date)));
}

define('LAYOUT', $layout);