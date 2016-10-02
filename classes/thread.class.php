<?php

class thread {
    public function __construct($action, array $args = array()) {
        $cmd = "cd " . APP_PATH . " && /usr/bin/php pagepageserver.php $action" . ($args ? " " . implode(" ", $args) : "") . " > /dev/null 2>/dev/null &";
        exec($cmd);
    }
}