<?php
declare(strict_types=1);
namespace BlackScorp\Funci\Core;
class DataBase{static function load(){}}; //this line is required for autoloading!!


/**
 * @return mysqli | null
 */
function getDb() {
    /**
     * @var mysqli|null
     */
    static $mysqli = null;

    if ($mysqli instanceof mysqli) {
        return $mysqli;
    }
    if(!defined('DATABASE_CONNECTION')){
        $message = sprintf('DATABASE_CONNECTION is not defined please check "%s"',CONFIG_DIR.'/database.php');
        trigger_error($message,E_USER_ERROR);
    }
    list($host, $user, $password, $database, $port, $charset) = array_values(DATABASE_CONNECTION);

    $mysqli = mysqli_init();
    /**
     * we need to add @ for mysqli_real_connect because of MAMMP PRO 3.3.0 it shows a warning
     */
    $connectionResult = @mysqli_real_connect($mysqli, $host, $user, $password, $database, $port);

    if (!$connectionResult) {
        mysqli_close($mysqli);
        $mysqli = null;
        trigger_error(mysqli_connect_error(),E_USER_ERROR);
        die(); //die if you have connection errors
    }
    mysqli_set_charset($mysqli, $charset);

    return $mysqli;
}

/**
 * @param mysqli|null $db
 * @return string
 */
function getDBError(mysqli $db = null) {
    if (is_null($db)) {
        $db = getDb();
    }

    return mysqli_error($db);
}

/**
 * @param string $sql
 * @param mysqli|null $db
 * @param int $resultMode
 * @return bool|mysqli_result
 */
function query($sql, mysqli $db = null, $resultMode = MYSQLI_STORE_RESULT) {
    if (is_null($db)) {
        $db = getDb();
    }

    return mysqli_query($db, $sql, $resultMode);
}

/**
 * @param string $text
 * @param mysqli|null $db
 * @return string
 */
function escapeString($text, mysqli $db = null) {
    if (is_null($db)) {
        $db = getDb();
    }

    return mysqli_real_escape_string($db, $text);
}
