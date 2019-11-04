<?php

router('/account/create','viewRegistrationForm','GET');
router('/account/create','accountCreateAction','POST');

router('/account/login','loginAction','POST|GET',true);