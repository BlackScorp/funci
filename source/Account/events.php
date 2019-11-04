<?php
declare(strict_types=1);

define('EVENT_ACCOUNT_CREATED', 'event.accountCreated');
define('EVENT_ACCOUNT_LOGIN', 'event.accoountLogin');
define('EVENT_ACCOUNT_LOGOUT', 'event.accoountLogout');

event(EVENT_ACCOUNT_LOGIN,[], function(string $username, bool $stayLoggedIn) {
    if (!$stayLoggedIn) {
        return null;
    }
    $salt = microtime().rand();
    $rememberMeToken =  hash('sha256',$username.$salt);
    $expires = date_modify(date_create(), '+30 days');
    setcookie('rememberMeToken',$rememberMeToken, date_timestamp_get($expires));
    updateRememberMeToken($username,$rememberMeToken);
});

event(EVENT_ACCOUNT_LOGOUT,[],function(int $userId){
    $username = findUserNameByUserId($userId);
    setcookie('rememberMeToken','',-1);
    updateRememberMeToken($username,'');
});

event(EVENT_BEFORE,[],function(){
    loginByRememberMeToken();
});