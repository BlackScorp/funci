<?php
declare(strict_types=1);


function indexAction(){
  
    $data = [];   
    return render('index',$data);
}