<?php

class user extends DataObject {
    protected $login;
    protected $password;
    protected $captchaNeeded;
    protected $invalid;
    
    public function getDataIdentifier() {
        return 'users';
    }
    
    public function getLogin() {
        return $this->login;
    }

    public function getPassword() {
        return $this->password;
    }

    public function setLogin($login) {
        $this->login = $login;
    }

    public function setPassword($password) {
        $this->password = $password;
    }
    
    public function getCaptchaNeeded() {
        return $this->captchaNeeded;
    }

    public function getInvalid() {
        return $this->invalid;
    }

    public function setCaptchaNeeded($captchaNeeded) {
        $this->captchaNeeded = $captchaNeeded;
    }

    public function setInvalid($invalid) {
        $this->invalid = $invalid;
    }

    public function __toString() {
        return $this->login;
    }
    
    public function getActiveRelatedSectorUri() {
        foreach(event::fetchAllList() as $event) {
            if ($event->getActive()) {
                if (in_array($this->getLogin(), $event->getUsers())) {
                    $sectors = $event->getSectors();
                    $sector = reset($sectors);
                    $identifier = $event->getIdentifier();
                    return $identifier . "/sektor/" . $sector;
                }
            }
        }
        return null;
    }
    
    public function checkAccount() {
        $proxy = new proxy($this->getLogin(), $this->getPassword(), $this->getActiveRelatedSectorUri());
        if (!$proxy->loggedIn()) {
            return -1;
        }
        if ($proxy->captchaVerificationNeeded()) {
            return false;
        }
        if ($proxy->captchaVerificationNeeded() === 0){
            return 0;
        }
        return true;
    }
    
}