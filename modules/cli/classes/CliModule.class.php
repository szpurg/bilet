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
        $events = event::fetchAllList();
        foreach($events as $event) {
            if ($event instanceof event && $event->getActive()) {
                $event->buy();
            }
        }
        
    }
    
    public function ActionCheckCaptchas() {
        $activeUsers = event::getActiveEventsUsers();
        
        foreach($activeUsers as $user) {
            if ($user instanceof user) {
                $status = $user->checkAccount();
                $save = false;
                if ($status === -1) {
                    if (!$user->getInvalid()) {
                        $save = true;
                        $user->setInvalid(true);
                        new notification($user, NOTIFICATION_USER_INVALID);
                    }
                }
                else {
                    if ($user->getInvalid()) {
                        $save = true;
                        $user->setInvalid(false);
                    }
                }
                if ($status === false) {
                    if (!$user->getCaptchaNeeded()) {
                        $save = true;
                        $user->setCaptchaNeeded(true);
                        new notification($user, NOTIFICATION_USER_CAPTCHA_NEEDED);
                    }
                }
                else if ($status === true) {
                    if ($user->getCaptchaNeeded()) {
                        $save = true;
                        $user->setCaptchaNeeded(false);
                    }
                }
                if ($save) {
                    $user->save($user->getLogin());
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