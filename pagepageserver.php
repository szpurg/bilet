<?php
ini_set('display_errors', 1);

define("APP_PATH", dirname(__FILE__) . "/");
define("DATA_PATH", APP_PATH . "data/");

define ( "ADMIN_EMAIL", "mpfc@o2.pl" );

define( "MAIL_HOST", 'poczta.o2.pl' );
define( "MAIL_SECURE", '' );
define( "MAIL_PORT", '25' );
define( "MAIL_USERNAME", 'mpfc@o2.pl' );
define( "MAIL_PASSWORD", 'mmojpry1' );

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
