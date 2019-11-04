<?php

declare(strict_types=1);

function hashPassword(string $password): string {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword(string $password, string $passwordHash): bool {
    return password_verify($password, $passwordHash);
}

function loginByRememberMeToken() {
    if (isLoggedIn() || !isset($_COOKIE['rememberMeToken'])) {
        return null;
    }

    $userId = findUserIdByRememberMeToken($_COOKIE['rememberMeToken']);
    if (!(bool) $userId) {
        return false;
    }
    session('userId', $userId);
    return true;
}

function loginAccount(string $username): bool {
    $userId = findUserIdByUsername($username);

    if (!(bool) $userId) {
        return false;
    }
    session('userId', $userId);
    return true;
}

function isLoggedIn(): bool {
    return (bool) session('userId');
}
