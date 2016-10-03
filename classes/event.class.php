<?php
class event extends DataObject {
    protected $name;
    protected $url;
    protected $identifier;
    protected $sectors;
    protected $users;
    protected $settings;
    
    public function __toString() {
        return $this->identifier . "[" . $this->getIndex() . "]";
    }
    
    public function getName() {
        return $this->name;
    }

    public function getUrl() {
        return $this->url;
    }
    
    public function getIdentifier($base64 = false) {
        if ($base64) {
            return base64_encode($this->identifier);
        }
        return $this->identifier;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function setUrl($url) {
        $this->url = $url;
    }

    public function setIdentifier($identifier) {
        $this->identifier = $identifier;
    }

    public function getDataIdentifier() {
        return 'events';
    }
    
    public function getSectors() {
        return $this->sectors;
    }

    public function setSectors($sectors) {
        $this->sectors = $sectors;
    }
    
    public function getUsers() {
        $assignedUsers = $this->users ? $this->users : array();
        $returner = array();
        
        foreach($assignedUsers as $login) {
            $user = user::fetchUser($login);
            if ($user instanceof user) {
                $returner[] = $user;
            }
        }
        return $returner;
    }
    
    public function setUsers($users) {
        $this->users = $users;
    }
    public function getSettings() {
        return $this->settings;
    }
    
    public function getTurbo() {
        return $this->getSetting('turbo');
    }
    
    public function setTurbo($value) {
        $this->setSetting('turbo', $value);
    }
    
    public function getActive() {
        return $this->getSetting('active');
    }
    
    public function setActive($value) {
        $this->setSetting('active', $value);
    }
    
    public function setSettings(array $settings) {
        $this->settings = $settings;
    }

    public function setSetting($name, $value) {
        $settings = $this->getSettings();
        $settings[$name] = $value;
        $this->settings = $settings;
    }
    
    public function getReverseBuy() {
        return $this->getSetting('reverseBuy');
    }
    
    public function getSetting($name) {
        $settings = $this->getSettings();
        return isset($settings[$name]) ? $settings[$name] : null;
    }
    
    public function seek() {
        if ($this->getActive()) {
            if ($this->getTurbo()) {
                $this->turboSeek();
            }
            else {
                $this->normalSeek();
            }
        }
    }
    
    protected function normalBuy($availableSectors) {
        foreach($this->getUsers() as $user) {
            foreach($availableSectors as $sectorArray) {
                if ($user instanceof user) {
                    $user->addSeatsToBasket($this, $sectorArray['name']);
                }
            }
        }
    }
    protected function normalSeek() {
        $availableSectors = $this->getAvailableSectors();
        if ($availableSectors) {
            $this->normalBuy($availableSectors);
        }
    }
    
    protected function turboSeek() {
        Application::log("start {$this->getIdentifier()}");
        $threadsCount = settings::getInstance()->getTurboSeekingThreads();
        if (is_numeric($threadsCount) && $threadsCount > 0) {
            while((int)$threadsCount--) {
                Application::log("starting new thread in {$this->getIdentifier()}");
                new thread('turboPreIndex', array($this->getIdentifier(), $this->getIndex()));
            }
        }
    }
    
    public function getAvailableSectors() {
        $eventSectors = $this->getSectors();
        //anonymously checking if there are seats available in defined sectors
        $proxy = new proxy(null, null, $this->getIdentifier());
        $sectors = $proxy->getSectors();
        $availableSectors = array();
        foreach($sectors as $sectorArray) {
            if ($sectorArray['available'] && in_array($sectorArray['name'], $eventSectors)) {
                $availableSectors[] = $sectorArray;
            }
        }
        return $availableSectors;
    }
    
    public static function fetchAllList() {
        $events = event::fetchList();
        $Events = array();
        foreach($events as $eventArray) {
            foreach($eventArray as $event) {
                if ($event instanceof event && $event->getActive()) {
                    $Events[] = $event;
                }
            }
        }
        return $Events;
    }
    
    public static function getActiveEventsUsers() {
        $users = array();
        foreach(self::fetchAllList() as $event) {
            if ($event instanceof event && $event->getActive()) {
                $users = array_merge($users, $event->getUsers());
            }
        }
        return array_unique($users);
    }
    
}