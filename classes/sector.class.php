<?php

class sector extends DataObject {
    protected $eventIdentifier;
    protected $eventIndex;
    protected $sectorName;
    
    public function __construct(event $event, $sectorName) {
        $this->eventIdentifier = $event->getIdentifier();
        $this->eventIndex = $event->getIndex();
        $this->sectorName = $sectorName;
    }
    
    /**
     * @return event
     */
    public function getEvent() {
        return event::fetch($this->eventIdentifier, $this->eventIndex);
    }
    
    public function getDataIdentifier() {
        return 'sectors' . md5($this->eventIdentifier);
    }
    
    public function tryAddingSeatToBasket(event $event, user $user, $seatURI, $connectionLimits, $try = 1) {
        if ($try == 3) {
            sleep(5);
        }
        else if ($try == 4) {
            sleep(10);
        }
        else if ($try == 5) {
            return false;
        }
        $proxy = new proxy($user->getLogin(), $user->getPassword(), $seatURI, null, $connectionLimits);
        
        if ($proxy->successfullyAddedToBasket()) {
            new notification($user, notification::NOTIFICATION_NEW_ITEMS_IN_BASKET);
            Application::log($user->getLogin() . " added " . $seatURI . " to basket");
            return $seatURI;
        }
        else if ($proxy->getBasketCount() != -1) {
            if (availableSeat::availableSeatTableReady($event, $seatURI)) {
                $nextSeat = availableSeat::fetchByUri($event, $seatURI, true);
                if ($nextSeat) {
                    return $this->tryAddingSeatToBasket($event, $user, $nextSeat->getUri(), $connectionLimits);
                }
            }
        }
        else {
            return $this->tryAddingSeatToBasket($user, $seatURI, $connectionLimits, $try + 1);
        }
        return false;
    }
    
    public function getAvailableSeats(user $user = null, $forceUpdate = false) {
        $event = $this->getEvent();
        if (isset(self::$seats[$this->eventIdentifier][$event->getIndex()][$this->sectorName]) && !$forceUpdate) {
            return self::$seats[$this->eventIdentifier][$event->getIndex()][$this->sectorName];
        }
        
        $uri = $this->eventIdentifier . "/sektor/" . $this->sectorName;
        if (!$user) {
            $users = $event->getUsers();
            $user = reset($users);
        }
        $proxy = new proxy($user->getLogin(), $user->getPassword(), $uri, null, settings::getInstance()->getTurboBasketConnections());
        $seats = $proxy->getSeats();
        $availableSeats = isset($seats['available']) ? $seats['available'] : array();
        
        if ($this->getEvent()->getReverseBuy()) {
            $availableSeats = array_reverse($availableSeats);
        }
        return self::$seats[$this->eventIdentifier][$event->getIndex()][$this->sectorName] = $availableSeats;
    }
    
    public function unsetAvailableSeat(user $user, $seatUrl) {
        $availableSeats = $this->getAvailableSeats($user);
        $index = array_search($seatUrl, $availableSeats);
        if ($index !== false) {
            unset($availableSeats[$index]);
            self::$seats[$this->eventIdentifier][$this->eventIndex][$this->sectorName] = array_values($availableSeats);
        }
    }
    
    public function getSectorName() {
        return $this->sectorName;
    }

    public function setSectorName($sectorName) {
        $this->sectorName = $sectorName;
    }

    /**
     * @param event $event
     * @param type $sectorName
     * @return sector
     */
    public static function getInstance(event $event, $sectorName) {
        if (isset(self::$instances[$event->getIdentifier()][$event->getIndex()][$sectorName])) {
            return self::$instances[$event->getIdentifier()][$event->getIndex()][$sectorName];
        }
        return self::$instances[$event->getIdentifier()][$event->getIndex()][$sectorName] = new sector($event, $sectorName);
    }
    
    public static function getBySectorURI(event $event, $sectorURI) {
        if (preg_match("#/sektor/([^/]+)#", $sectorURI, $matches)) {
            return self::getInstance($event, $matches[1]);
        }
        return null;
    }
    
    protected static $instances = array();
    protected static $seats = array();
}