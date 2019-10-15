<?php
declare(strict_types=1);
namespace BlackScorp\Funci\Demo;
class Actions{static function load(){}}; //this line is required for autoloading!!

use function BlackScorp\Funci\Core\{render};

function indexAction(){
  
    $data = [];   
    return render('index',$data);
}