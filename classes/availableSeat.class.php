<?php

class availableSeat extends DataObject {
    protected $eventIdentifier;
    protected $eventIndex;
    protected $assignedTo;
    protected $uri;
    protected $success = false;
    protected $running = false;
    
    public function __construct(event $event, $uri) {
        $this->setEvent($event);
        $this->setUri($uri);
    }
    
    public function getDataIdentifier() {
        return 'available' . md5($this->getEvent()->getIdentifier() . $this->getEvent()->getIndex() . $this->getSectorName());
    }
    
    public function getAssignedTo() {
        return user::fetch($this->assignedTo);
    }

    public function getSuccess() {
        return $this->success;
    }

    public function setAssignedTo(user $user) {
        $this->assignedTo = $user->getLogin();
    }

    public function setSuccess($success) {
        $this->success = $success;
    }
    public function getUri() {
        return $this->uri;
    }

    public function setUri($uri) {
        $this->uri = $uri;
    }
    
    public function getSectorName() {
        if (preg_match("#/sektor/([^/]+)#", $this->getUri(), $matches)) {
            return $matches[1];
        }
    }

    public function getRunning() {
        return $this->running;
    }

    public function setRunning($running) {
        $this->running = $running;
    }

        
    public function save($dataKey = 0, $allowMultiply = false, $index = null) {
        parent::save($this->getSectorName(), true, $this->getIndex());
    }
    
    /**
     * @return event
     */
    public function getEvent() {
        return event::fetch($this->eventIdentifier, $this->eventIndex);
    }
    
    public function setEvent(event $event) {
        $this->eventIdentifier = $event->getIdentifier();
        $this->eventIndex = $event->getIndex();
    }
    /**
     * @param event $event
     * @param type $uri
     * @param type $nextItem
     * @return \availableSeat
     */
    public static function fetchByUri(event $event, $uri, $nextItem = false) {
        $list = availableSeat::fetchList(array($event, $uri));
        $returnNext = false;
        foreach($list as $sector) {
            foreach($sector as $index => $availableSeat) {
                if ($availableSeat instanceof availableSeat) {
                    if ($returnNext && !$availableSeat->running && !$availableSeat->success) {
                        $availableSeat->setIndex($index);
                        return $availableSeat;
                    }
                    else if (!$returnNext && $availableSeat->getUri() == $uri) {
                        if ($nextItem) {
                            $returnNext = true;
                        }
                        else {
                            $availableSeat->setIndex($index);
                            return $availableSeat;
                        }
                    }
                }
            }
        }
        return null;
    }
    
    public static function availableSeatTableReady(event $event, $seatURI) {
        $limit = 1200;
        while (!availableSeat::fetchList(array($event, $seatURI))) {
            usleep(100000);
            $limit--;
            if ($limit <= 0) {
                break;
            }
        }
        return true;
    }

}