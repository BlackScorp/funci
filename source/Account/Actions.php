<?php

function accountCreateAction(){
    $data = [
        'errors'=>[],
        'username'=>'',
        'password'=>'',
        'passwordRepeat'=>'',
        'email'=>'',
        'terms'=>false
    ];
    
    
    return render('registration',$data);
}
