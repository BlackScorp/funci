<?php

router('/message/create',function(){
    $data = [];

    return render('messageCreate',$data);
});