<?php
session_start();
ob_start();

header('Content-Type: text/html; charset=UTF-8');
date_default_timezone_set("Asia/Tehran");
/** Configuration Variables **/
define('DEVELOPMENT_ENVIRONMENT', true);

$HTTP = (isset($_SERVER['HTTPS'])) ? 'https://' : 'http://';
define('BASE_PATH', $HTTP . $_SERVER['SERVER_NAME'] . '/mzif');
define('PUBLIC_PATH', BASE_PATH . "/public");

define('DEFAULT_CONTROLLER', 'site');
define('DEFAULT_ACTION', 'index');

define('ERROR_404_PATH', BASE_PATH . '/error');

// Database config
require_once(ROOT . DS . 'config' . DS . 'db.php');
require_once(ROOT . DS . 'library' . DS . 'sql.php');

// REQUIRE MZIF CLASS
require_once(ROOT . DS . 'library' . DS . 'Mzif.php');

// REQUIRE CACHE CLASS
require_once(ROOT . DS . 'library' . DS . 'cache.php');
$memcache = new cacheMem;

// REQUIRE HELPER CLASSES
foreach (scandir(LIBRARY_PATH . 'helpers/') as $filename) {
    $path = LIBRARY_PATH . 'helpers/' . $filename;
    if (is_file($path)) {
        require_once($path);
    }
}

// REQUIRE COMPONENTS CLASSES
foreach (scandir(LIBRARY_PATH . 'components/') as $filename) {
    $path = LIBRARY_PATH . 'components/' . $filename;
    if (is_file($path)) {
        require_once($path);
    }
}

require_once(LIBRARY_PATH . 'components/phpmailer/PHPMailerAutoload.php');