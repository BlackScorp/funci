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
    $terms = filter_input(INPUT_POST,'terms') === 'on';

    $emailIsUsed = isEmailUsed($email);
    $usernameIsUsed = isUsernameUsed($username);


    $errors = validateAccountCreation($username, $password, $passwordRepeat, $email, $terms,$usernameIsUsed, $emailIsUsed );
    if (count($errors) === 0) {
        $created = createAccount($username, $password, $email);
        if ($created) {
            event(EVENT_ACCOUNT_CREATED, [$username, $password, $email]);
            flashMessages('accountCreate', ['Registrierung erfolgreich']);
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
