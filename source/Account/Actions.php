<?php

function viewRegistrationForm() {
    $data = [
        'errors' => [],
        'username' => '',
        'password' => '',
        'passwordRepeat' => '',
        'email' => '',
        'terms' => false,
    ];
    return render('registration', $data);
}

function accountCreateAction() {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, 'password');
    $passwordRepeat = filter_input(INPUT_POST, 'passwordRepeat');
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $terms = filter_input(INPUT_POST, 'terms') === 'on';

    $emailIsUsed = isEmailUsed($email);
    $usernameIsUsed = isUsernameUsed($username);

    $errors = validateAccountCreation($username, $password, $passwordRepeat, $email, $terms, $usernameIsUsed, $emailIsUsed);

    if (count($errors) === 0) {
        $created = createAccount($username, $password, $email);
        $errors[] = 'Account konnte nicht erstellt werden';
        if ($created) {
            event(EVENT_ACCOUNT_CREATED, [$username, $password, $email]);
            flashMessages('account', ['Registrierung erfolgreich']);
            redirect('/');
        }
    }

    $data = [
        'errors' => $errors,
        'username' => $username,
        'password' => $password,
        'passwordRepeat' => $passwordRepeat,
        'email' => $email,
        'terms' => $terms,
    ];
    return render('registration', $data);
}

function loginAction() {
    if (isLoggedIn()) {
        return '';
    }
    $errors = [];
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, 'password');
    $stayLoggedIn = filter_input(INPUT_POST, 'stayLoggedIn') === 'on';
    $loginButtonIsPressed = isset($_POST['login']);

    if (isPost() && $loginButtonIsPressed) {
        $passwordHash = findPasswordHashByUsername($username);
        $passwordIsValid = verifyPassword($password, $passwordHash);
        $errors = validateLogin($username, $password, $passwordHash, $passwordIsValid);
        if (count($errors) === 0) {
            $loggedIn = loginAccount($username);
            $errors = ['Login fehlgeschlagen'];
            if ($loggedIn) {
                event(EVENT_ACCOUNT_LOGIN, [$username, $stayLoggedIn]);
                flashMessages('account', ['Login erfolgreich']);
                redirect('/');
            }
        }
    }

    $data = [
        'errors' => $errors,
        'username' => $username,
        'password' => $password,
        'stayLoggedIn' => $stayLoggedIn
    ];

    return render('loginWidget', $data);
}

function logoutAction() {
    if (!isLoggedIn()) {
        return '';
    }
    $userId = session('userId');
    session('userId', null);
    event(EVENT_ACCOUNT_LOGOUT, [$userId]);
    flashMessages('account', ['Erfolgreich abgemeldet']);
    redirect('/');
}
