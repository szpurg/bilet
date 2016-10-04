<?php

class notification {
    const NOTIFICATION_USER_INVALID = 1;
    const NOTIFICATION_USER_CAPTCHA_NEEDED = 2;
    const NOTIFICATION_NEW_ITEMS_IN_BASKET = 3;
    
    protected $interval = 600;
    
    public function __construct(user $user, $type) {
        $email = ADMIN_EMAIL;
        if (Application::loadData('notifications') !== 0) {
            $this->send($user, $email, $type);
        }
    }
    
    protected function send(user $user, $email, $type) {
        switch($type) {
            case self::NOTIFICATION_NEW_ITEMS_IN_BASKET:
                $subject = "BILETY - {$user->getLogin()} - Nowe pozycje w koszyku!";
                $body = "<b>Nowe pozycje w koszyku!</b><br>";
                $body .= "Wejdź do panelu i dokończ zakup:<br>";
                $body .= "<a href='http://kupbilet.onet.pl/panel'>http://kupbilet.onet.pl/panel</a>";
                break;
            case self::NOTIFICATION_USER_INVALID:
                $subject = "BILETY - Nieprawidłowy użytkownik!";
                $body = "<b>Użytkownik {$user->getLogin()} prawdopodobnie jest nieprawidłowy!</b><br>";
                $body .= "Wejdź do panelu i usuń użytkownika:<br>";
                $body .= "<a href='http://kupbilet.onet.pl/panel'>http://kupbilet.onet.pl/panel</a>";
                $this->interval = 3600;
                break;
            case self::NOTIFICATION_USER_CAPTCHA_NEEDED:
                $subject = "BILETY - Wymagane Captcha!";
                $body = "<b>Jeden lub więcej użytkowników wymaga captcha! User {$user->getLogin()}</b><br>";
                $body .= "Wejdź do panelu i ustaw captcha:<br>";
                $body .= "<a href='http://kupbilet.onet.pl/panel'>http://kupbilet.onet.pl/panel</a>";
                break;
            default:
                return false;
        }
        
        if ($this->canSend($type)) {
            new email($email, '', '', $subject, $body);
            $emails = Application::loadData("emails");
            $emails[$type] = time();
            Application::saveData("emails", $emails);
        }
        
        
    }
    
    protected function canSend($type) {
        $emails = Application::loadData("emails");
        if (isset($emails[$type])) {
            $time = $emails[$type];
            if ($time + $this->interval > time()) {
                return false;
            }
        }
        return true;
    }
    
}