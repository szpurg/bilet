<?php
ini_set('display_errors', 1);

define("APP_PATH", dirname(__FILE__) . "/");
define("DATA_PATH", APP_PATH . "data/");
if (!is_dir(DATA_PATH)) {
    mkdir(DATA_PATH, 0777);
}

define ( "ADMIN_EMAIL", "wiktor@dusza.in" );

define( "MAIL_HOST", 'smtp.gmail.com' );
define( "MAIL_SECURE", 'tls' );
define( "MAIL_PORT", '587' );
define( "MAIL_USERNAME", 'wiktor@dusza.in' );
define( "MAIL_PASSWORD", '' );

$GLOBALS['modules'] = array(
    'panel',
    'cli',
);
function __autoload($name) {
    if (file_exists(dirname(__FILE__) . "/classes/$name.class.php")) {
        require_once dirname(__FILE__) . "/classes/$name.class.php";
        return;
    }
    $modules = $GLOBALS['modules'];
    foreach($modules as $module) {
        if (file_exists(dirname(__FILE__) . "/modules/$module/classes/$name.class.php")) {
            require_once dirname(__FILE__) . "/modules/$module/classes/$name.class.php";
            return;
        }
    }
}
function getCli() {
    global $argv;
    return $argv;
}
new Application;
