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
    
    public function getAvailableSeats(user $user, $forceUpdate = false) {
        $event = $this->getEvent();
        if (isset(self::$seats[$this->eventIdentifier][$event->getIndex()][$this->sectorName]) && !$forceUpdate) {
            return self::$seats[$this->eventIdentifier][$event->getIndex()][$this->sectorName];
        }
        
        $uri = $this->eventIdentifier . "/sektor/" . $this->sectorName;
        $proxy = new proxy($user->getLogin(), $user->getPassword(), $uri);
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
    protected static $instances = array();
    protected static $seats = array();
}