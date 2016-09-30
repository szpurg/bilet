<?php

class CliModule {
    protected $argv;
    protected $args;
    
    public function __construct($argv) {
        $this->argv = $argv;
        
        $action = isset($argv[1]) ? $argv[1] : null;
        $args = $argv;
        array_shift($args);
        array_shift($args);
        
        $this->args = $args;
        
        if ($action) {
            if (method_exists($this, "Action" . ucfirst($action))) {
                call_user_func(array($this, "Action" . ucfirst($action)));
            }
            else {
                die ("Action $action does not exist\n\n");
            }
        }
        else {
            $this->ActionIndex();
        }
        
        
    }
    
    
    public function ActionIndex() {
        
    }
    
    public function ActionCheckCaptchas() {
        $activeUsers = event::getActiveEventsUsers();
        
        foreach($activeUsers as $user) {
            if ($user instanceof user) {
                $status = $user->checkAccount();
                if ($status === -1) {
                    print $user->getLogin() . " not logged in!\n";
                    new notification();
                }
                else if ($status === false) {
                    print $user->getLogin() . " needs captcha verification!\n";
                    new notification();
                }
                else {
                    print $user->getLogin() . " seems ok\n";
                }
            }
        }
        
        
    }
    
    public function getArgv() {
        return $this->argv;
    }

    public function setArgv($argv) {
        $this->argv = $argv;
    }


}