<?php
declare(strict_types=1);

function server($variableName) {
    return isset($_SERVER[$variableName]) ? $_SERVER[$variableName] : null;
}


/**
 * @param string $name
 * @param string|null $value
 *
 * @return mixed
 */
function session($name, $value = null) {
    if (!$value && func_num_args() === 2) {
        unset($_SESSION[$name]);

        return null;
    }
    if ($value) {
        return $_SESSION[$name] = $value;
    }

    return isset($_SESSION[$name]) ? $_SESSION[$name] : null;
}


/**
 * @return bool is post request
 */
function isPost() {
    return server('REQUEST_METHOD') === 'POST';
}

/**
 * @return bool is get request
 */
function isGet() {
    return server('REQUEST_METHOD') === 'GET';
}

function isAjax() {
    return server('HTTP_X_REQUESTED_WITH') && 'XMLHttpRequest' === server('HTTP_X_REQUESTED_WITH');
}

/**
 * @param string $path

 */
function redirect($path) {
    if ($path === '/') {
        $path = BASE_DIR;
    }
    header('Location:' . $path);
    exit;
}


function flashMessages(string $messageId, array $messages = []) {
    $key = 'flashMessage.' . $messageId;
    if (count($messages) === 0) {
        if (isset($_SESSION[$key])) {
            $value = $_SESSION[$key];
            unset($_SESSION[$key]);
            return $value;
        }
        return [];
    }
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = [];
    }
    $_SESSION[$key] = array_merge($_SESSION[$key], $messages);
}