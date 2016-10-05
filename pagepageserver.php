<?php
ini_set('display_errors', 1);

define("APP_PATH", dirname(__FILE__) . "/");
define("DATA_PATH", APP_PATH . "data/");
if (!is_dir(DATA_PATH)) {
    mkdir(DATA_PATH, 0777);
}

define ( "ADMIN_EMAIL", settings::get('email') );

define( "MAIL_HOST", settings::get('smtpHost') );
define( "MAIL_SECURE", settings::get('smtpSecurity') );
define( "MAIL_PORT", settings::get('smtpPort') );
define( "MAIL_USERNAME", settings::get('smtpUsername') );
define( "MAIL_PASSWORD", settings::get('smtpPassword') );

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
