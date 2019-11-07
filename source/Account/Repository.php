<?php

declare(strict_types=1);

function isEmailUsed(string $email) {
    $sql = "SELECT 1 FROM users WHERE email = '" . escapeString($email) . "' LIMIT 1";
    $result = query($sql);

    if (!$result) {
        trigger_error(getDBError(), E_USER_ERROR);
    }

    return mysqli_num_rows($result) > 0;
}

function isUsernameUsed(string $username) {
    $sql = "SELECT 1 FROM users WHERE username = '" . escapeString($username) . "' LIMIT 1";
    $result = query($sql);
    if (!$result) {
        trigger_error(getDBError(), E_USER_ERROR);
    }
    return mysqli_num_rows($result) > 0;
}

function createAccount(string $username, string $password, string $email) {
    $sql = sprintf("INSERT INTO users "
            . "SET username='%s',"
            . "email='%s',"
            . "passwordHash='%s',"
            . "created=NOW(),"
            . "updated=NOW()",
            escapeString($username),
            escapeString($email),
            hashPassword($password)
    );

    $result = query($sql);
    if (!$result) {
        trigger_error(getDBError(), E_USER_ERROR);
        return false;
    }
    return $result;
}

function findPasswordHashByUsername(string $username): string {
    $sql = "SELECT passwordHash FROM users WHERE username ='" . escapeString($username) . "'";
    $result = query($sql);
    if (!$result) {
        trigger_error(getDBError(), E_USER_ERROR);
    }
    if (mysqli_num_rows($result) === 0) {
        return '';
    }

    return mysqli_fetch_row($result)[0];
}






function findUserIdByUsername(string $username): int {
    static $userIdsForUsername = [];

    if (isset($userIdsForUsername[$username])) {
        return $userIdsForUsername[$username];
    }
    $sql = "SELECT userId FROM users WHERE username ='" . escapeString($username) . "'";
    $result = query($sql);
    if (!$result) {
        trigger_error(getDBError(), E_USER_ERROR);
    }
    if (mysqli_num_rows($result) === 0) {
        return 0;
    }
    $userId = (int) mysqli_fetch_row($result)[0];
    $userIdsForUsername[$username] = $userId;
    return $userId;
}

function findUserNameByUserId(int $userId):string{
    static $usernamesForUserId = [];
    if(isset($usernamesForUserId[$userId])){
        return $usernamesForUserId[$userId];
    }
     $sql = "SELECT username FROM users WHERE userId =" . $userId . "";
    $result = query($sql);
    if (!$result) {
        trigger_error(getDBError(), E_USER_ERROR);
    }
    if (mysqli_num_rows($result) === 0) {
        return 0;
    }
    $username = mysqli_fetch_row($result)[0];
    $usernamesForUserId[$userId] = $username;
    return $username;   
}

function updateRememberMeToken(string $username, string $rememberMeToken): bool {

    $sql = "UPDATE users SET rememberMeToken='" . escape($rememberMeToken) . "' WHERE username='" . escape($username) . "'";
    $result = query($sql);
    if (!$result) {
        trigger_error(getDBError(), E_USER_ERROR);
    }
    
    return $result;
}

function findUserIdByRememberMeToken(string $rememberMeToken) :int{
     $sql = "SELECT userId FROM users WHERE rememberMeToken ='" . escapeString($rememberMeToken) . "'";
    $result = query($sql);
    if (!$result) {
        trigger_error(getDBError(), E_USER_ERROR);
    }
    if (mysqli_num_rows($result) === 0) {
        return 0;
    }
    return (int) mysqli_fetch_row($result)[0];
}
