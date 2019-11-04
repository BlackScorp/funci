<?php

router('/account/create','viewRegistrationForm','GET');
router('/account/create','accountCreateAction','POST');

router('/account/login','loginAction','POST|GET',true);
router('/account/logout','logoutAction');