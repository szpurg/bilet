<?php

class user extends DataObject {
    protected $login;
    protected $password;
    protected $captchaNeeded;
    protected $invalid;
    
    protected $addedToBasketInSession = 0;
    
    const LOGIN_LIMIT = 8;
    
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
    
    public function cleanCookies() {
        if (file_exists(DATA_PATH . "cookies" . md5($this->getLogin() . $this->getPassword()) . ".dat")) {
            unlink(DATA_PATH . "cookies" . md5($this->getLogin() . $this->getPassword()) . ".dat");
            Application::log("Cleaning {$this->getLogin()} cookies");
        }
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
    
    public function addSeatsToBasket(event $event, $sectorName) {
        $added = 0;
        $sector = sector::getInstance($event, $sectorName);
        if ($this->getBasketCount() < $this->getLoginLimit()) {
            foreach($sector->getAvailableSeats($this) as $seatUrl) {
                $try = $this->addSeatToBasket($sector, $seatUrl);
                if (!$try) {
                    $try = $this->addSeatToBasket($sector, $seatUrl);
                }
                if (!$try) {
                    sleep(10);
                    $try = $this->addSeatToBasket($sector, $seatUrl);
                }
                if (!$try) {
                    sleep(30);
                    $try = $this->addSeatToBasket($sector, $seatUrl);
                }
                if ($try) {
                    $added++;
                    $realCount = $this->getBasketCount();
                    if ($realCount >= $this->getLoginLimit()) {
                        break;
                    }
                }
            }
        }
        return $added;
    }
    /**
     * 
     * @return \basket
     */
    public function getBasket() {
        $basket = basket::fetch(0, null, array($this));
        if (!$basket) {
            $basket = new basket($this);
        }
        return $basket;
    }
    
    public function updateBasketCount($count = null) {
        if ($count === null) {
            $proxy = new proxy($this->getLogin(), $this->getPassword(), $this->getActiveRelatedSectorUri());
            $count = $proxy->getBasketCount();
        }
        if (is_numeric($count) && $count >= 0) {
            $this->getBasket()->saveCount($count);
        }
    }
    
    public function incrementBasketCount() {
        return $this->getBasket()->incrementCount();
    }
    
    public function getBasketCount() {
        return $this->getBasket()->getCount();
    }

    public function addSeatToBasket(sector $sector, $seatUrl) {
        $uri = Application::urlToURI($seatUrl);
        $sector->unsetAvailableSeat($this, $seatUrl);
        print $this->getLogin() . ": " . $uri . "\n";
        
        $proxy = new proxy($this->getLogin(), $this->getPassword(), $uri);
        return $proxy->getBasketCount() ? true : false;
    }
    
    public function getLoginLimit() {
        return self::LOGIN_LIMIT;
    }
    
    public static function fetchUser($login) {
        if (isset(self::$usersCache[$login])) {
            return self::$usersCache[$login];
        }
        return self::$usersCache[$login] = user::fetch($login);
    }
    
    protected static $usersCache = array();
    
}