<?php
declare(strict_types=1);

function isEmailUsed(string $email){
    $sql ="SELECT 1 FROM users WHERE email = '".escapeString($email)."'";
    $result = query($sql);
  
    if(!$result){
        trigger_error(getDBError(),E_USER_ERROR);
        return false;
    }

    return mysqli_num_rows($result) > 0;
}

function isUsernameUsed(string $username){
    $sql ="SELECT 1 FROM users WHERE username = '".escapeString($username)."'";
    $result = query($sql);
    if(!$result){
        trigger_error(getDBError(),E_USER_ERROR);
        return false;
    }
 return mysqli_num_rows($result) > 0;
}

function createAccount(string $username,string $password,string $email){
    $sql =sprintf("INSERT INTO users "
            . "SET username='%s',"
            . "email='%s',"
            . "passwordHash='%s',"
            . "created=NOW(),"
            . "updated=NOW()", escapeString($username), escapeString($email), hashPassword($password));
    
    $result = query($sql);
    if(!$result){
       trigger_error(getDBError(),E_USER_ERROR);
        return false;
    }
    return $result;
}