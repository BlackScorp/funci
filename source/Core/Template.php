<?php
declare(strict_types=1);


/**
 * @param string  $path
 * @param mixed[] $data
 *
 * @return string
 */
function render($path, array $data = []) {
    $fileName = _getTemplateFile($path);
    if(!$fileName){
        return '';
    }
    _templateData('data', $data);
    if (count($data) > 0) {
        extract($data, EXTR_SKIP);
    }

    ob_start();
    $originalErrorReporting = error_reporting();
    error_reporting(E_ALL & ~E_NOTICE); //Hide notices because of missing variables
    require_once $fileName;
    error_reporting($originalErrorReporting);//Restore
    $content = trim(ob_get_clean());
    return $content;
}

/**
 * @param string $key
 * @param mixed  $value
 *
 * @return mixed
 */
function _templateData($key, $value = null) {
    static $data = [];

    if ($value) {
        return $data[$key] = $value;
    }

    return isset($data[$key]) ? $data[$key] : [];
}

/**
 * @param string $path
 *
 * @return string
 */
function _getTemplateFile($path) {
    $templateDirectories = [];
    if (defined('TEMPLATE_DIRS')) {
        $templateDirectories = TEMPLATE_DIRS;
    }
    $fileName = '';
    foreach ($templateDirectories as $templateDirectory) {
        $fileName = $templateDirectory . $path . '.php';
      
        if (is_file($fileName)) {
            return realpath($fileName);
        }
    }

    trigger_error(
            sprintf(
                    'Template file "%s" not found in directories "%s"', $path . '.php', implode('","', $templateDirectories)
            ),
            E_USER_ERROR
    );
    return null;
}

/**
 * @param string $path
 *
 * @return mixed
 */
function layout($path) {
    static $layoutData = '';

    if (!(bool) $layoutData) {
        return $layoutData = $path;
    }
    $data = _templateData('data');

    echo render($path, $data);
    return null;
}

/**
 * @param string $name
 *
 * @return bool
 */
function section($name) {
    static $sections = [];

    if (!isset($sections[$name])) {
        return $sections[$name] = ob_start();
    }
    $content = trim(ob_get_clean());
    $data = _templateData('data');
    $data[$name] = $content;
    _templateData('data', $data);
    unset($sections[$name]);

    return true;
}


/**
 * @param string $value
 *
 * @return string
 */
function escape($value = null) {
    if(!$value){
        return '';
    }
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
