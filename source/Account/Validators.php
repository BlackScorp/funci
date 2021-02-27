<?php

declare(strict_types=1);

function validateAccountCreation(string $username, string $password, string $passwordRepeat, string $email, bool $terms, bool $usernameIsUsed, bool $emailIsUsed) {
    $errors = [];
    if (!trim($username)) {
        $errors[] = 'Bitte Benutzername angeben';
    }
    if (!trim($password)) {
        $errors[] = 'Password darf nicht leer sein';
    }
    if (trim($password) && mb_strlen($password) < 6) {
        $errors[] = 'Password muss mindestens 6 Zeichen lang sein';
    }
    if ($password !== $passwordRepeat) {
        $errors[] = 'Passwörter stimmen nicht überein';
    }
    if (!trim($email)) {
        $errors[] = 'E-Mail ist leer';
    }
    if (trim($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'E-Mail ist ungültig';
    }
    if ($emailIsUsed) {
        $errors[] = 'E-Mail ist vergeben';
    }
    if ($usernameIsUsed) {
        $errors[] = 'Benutzername ist vergeben';
    }
    if (!$terms) {
        $errors[] = 'Akzeptiere die Regeln';
    }
    return $errors;
}

function validateLogin(string $username, string $password, string $passwordHash, bool $passwordIsValid) {
    $errors = [];
    if (!trim($username)) {
        $errors[] = 'Bitte Benutzername angeben';
    }
    if (!trim($password)) {
        $errors[] = 'Password darf nicht leer sein';
    }
    if(!trim($passwordHash)){
        $errors[] = 'Benutzer existiert nicht';
    }
    if(!$passwordIsValid){
        $errors[]='Password ist ungültig';
    }
    return $errors;
}
