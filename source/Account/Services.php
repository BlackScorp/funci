<?php
declare(strict_types=1);
function hashPassword(string $password){
    return password_hash($password, PASSWORD_DEFAULT);
}
