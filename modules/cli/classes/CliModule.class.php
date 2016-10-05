<?php

class CliModule {
    protected $argv;
    protected $args;
    /**
     * @var running
     */
    protected $process;
    protected $action;
    
    public function __destruct() {
        $process = $this->process;
        if ($process) {
            $process->delete($this->action, $this->process->getId());
        }
    }
    
    public function __construct($argv) {
        $this->argv = $argv;
        
        $this->action = $action = isset($argv[1]) ? $argv[1] : "index";
        $args = $argv;
        array_shift($args);
        array_shift($args);
        
        $this->args = $args;
        
        $this->initProcess();
        if (method_exists($this, "Action" . ucfirst($action))) {
            call_user_func(array($this, "Action" . ucfirst($action)));
        }
        else {
            die ("Action $action does not exist\n\n");
        }
    }
    
    public function ActionTest() {
        print "test\n";
    }
    
    public function ActionIndex() {
        $events = event::fetchAllList();
        foreach($events as $event) {
            if ($event instanceof event && $event->getActive()) {
                $event->seek();
            }
        }
    }
    
    public function ActionProcesses() {
        print_r(running::fetchList());
    }
    
    public function ActionNotifications() {
        $operator = isset($this->args[0]) ? $this->args[0] : null;
        if ($operator) {
            if ($operator == 'enabled') {
                Application::saveData('notifications', 1);
                die("OK\n");
            }
            if ($operator == 'disabled') {
                Application::saveData('notifications', 0);
                die("OK\n");
            }
            die("Invalid argument\n");
        }
        die("No arguments\n");
    }
    
    protected function initProcess() {
        $action = $this->action;
        $processID = md5(implode("", $this->argv) . microtime() . rand(0, 1000000));
        
        $process = new running();
        $process->setId($processID);
        $process->setAction($action);
        $process->save($action, true, $processID);
        $this->action = $action;
        $this->process = $process;
    }
    
    public function ActionTurboAddToBasket() {
        $login = isset($this->args[0]) ? $this->args[0] : null;
        $seatURI = isset($this->args[1]) ? $this->args[1] : null;
        $eventIndetifier = isset($this->args[2]) ? $this->args[2] : null;
        $eventIndex = isset($this->args[3]) ? $this->args[3] : null;
        
        $connectionLimits = settings::getInstance()->getTurboBasketConnections();
        
        if ($eventIndetifier && isset($eventIndex)) {
            $event = event::fetch($eventIndetifier, $eventIndex);
            if ($login && $seatURI) {
                $user = user::fetch($login);
                if ($user instanceof user) {
                    $sector = sector::getBySectorURI($event, $seatURI);
                    $seatURI = $sector->tryAddingSeatToBasket($event, $user, $seatURI, $connectionLimits);
                    if ($seatURI) {
                        if (availableSeat::availableSeatTableReady($event, $seatURI)) {
                            $availableSeat = availableSeat::fetchByUri($event, $seatURI);
                            if ($availableSeat instanceof availableSeat) {
                                $availableSeat->setSuccess(true);
                                $availableSeat->save();
                            }
                        }
                    }
                }
            }
        }
    }
    
    public function ActionTurboPreIndex() {
        $eventIndetifier = isset($this->args[0]) ? $this->args[0] : null;
        $eventIndex = isset($this->args[1]) ? $this->args[1] : null;
        application::log('starting preindex');
        
        if ($eventIndetifier && isset($eventIndex)) {
            $event = event::fetch($eventIndetifier, $eventIndex);
            $users = $event->getUsers();
            if (!$users) {
                return false;
            }
            if ($event instanceof event) {
                foreach($event->getSectors() as $sectorName) {
                    application::log('starting new thread in preindex');
                    new thread("turboIndex", array($eventIndetifier, $eventIndex, $sectorName));
                }
            }
        }
    }
    
    public function ActionTurboIndex() {
        $eventIndetifier = isset($this->args[0]) ? $this->args[0] : null;
        $eventIndex = isset($this->args[1]) ? $this->args[1] : null;
        $sectorName = isset($this->args[2]) ? $this->args[2] : null;
        $interval = settings::getInstance()->getTurboTimeBetweenConnections();
        
        application::log("Seeking $eventIndetifier: $sectorName");
        
        if ($eventIndetifier && isset($eventIndex)) {
            $event = event::fetch($eventIndetifier, $eventIndex);
            $users = $event->getUsers();
            $newList = array();
            foreach($users as $user) {
                if ($user instanceof user) {
                    if (!$user->getCaptchaNeeded()) {
                        $newList[] = $user;
                    }
                }
            }
            $users = $newList;
            if (!$users) {
                application::log('no captchaless users');
                return false;
            }
            $userIndex = 0;
            $used = -1;
            $indd = rand(0, count($users) - 1);
            
            if ($event instanceof event) {
                $end = time() + 60;
                while(time() < $end) {
                    if (Application::loadData('seeking' . base64_encode($event->getIdentifier() . $event->getIndex() . $sectorName)) == 'pause') {
                        Application::log("breaking " . $event->getIdentifier());
                        break;
                    }
                    $sector = sector::getInstance($event, $sectorName);
                    $currentUser = count($users) == 1 ? reset($users) : $users[$indd];
                    application::log('seeking for ' . $currentUser->getLogin());
                    $availableSeats = $sector->getAvailableSeats($currentUser, true);
                    if ($availableSeats) {
                        Application::saveData('seeking' . base64_encode($event->getIdentifier() . $event->getIndex() . $sectorName), 'pause');
                        $allAvailableSeats = array();
                        Application::saveData('available' . md5($event->getIdentifier() . $event->getIndex() . $sectorName), null);
                            foreach($availableSeats as $seatUrl) {
                                $user = isset($users[$userIndex]) ? $users[$userIndex] : null;
                                $seatURI = Application::urlToURI($seatUrl);
                                $availableSeatObject = new availableSeat($event, $seatURI);
                                $allAvailableSeats[$sectorName][] = $availableSeatObject;
                                if ($user instanceof user) {
                                    if ($used == -1) {
                                        $used = $user->getBasketCount();
                                    }
                                    $availableSeatObject->setAssignedTo($user);
                                    $availableSeatObject->setRunning(true);
                                    new thread('turboAddToBasket', array($user->getLogin(), $seatURI, $event->getIdentifier(), $event->getIndex()));
                                    $used++;
                                    if ($user->getLoginLimit() <= $used) {
                                        $used = -1;
                                        $userIndex++;
                                    }
                                }
                            }
                            Application::saveData('available' . md5($event->getIdentifier() . $event->getIndex() . $sectorName), $allAvailableSeats);
                        break;
                    }
                    else {
                        application::log("Not found in $eventIndetifier: $sectorName");
                        break;
                    }
                }
            }
        }
    }
    
    public function ActionEm() {
        $user = user::fetch('musictechnowarsaw');
        new notification($user, 3);
    }
    
    public function ActionCheckCaptchas() {
        running::clean();
        $activeUsers = event::getActiveEventsUsers();
        
        foreach($activeUsers as $user) {
            if ($user instanceof user && !$user->getCaptchaNeeded()) {
                Application::log("[STATUS] Checking {$user->getLogin()} status");
                $status = $user->checkAccount();
                $save = false;
                if ($status === -1) {
                    if (!$user->getInvalid()) {
                        $save = true;
                        $user->setInvalid(true);
                        Application::log("[STATUS-ERROR] {$user->getLogin()} login error");
                        new notification($user, notification::NOTIFICATION_USER_INVALID);
                    }
                }
                else {
                    if ($user->getInvalid()) {
                        $save = true;
                        $user->setInvalid(false);
                        Application::log("[STATUS-REVALID] {$user->getLogin()} was invalid, but aint anymore");
                    }
                }
                if ($status === false) {
                    if (!$user->getCaptchaNeeded()) {
                        $save = true;
                        $user->setCaptchaNeeded(true);
                        $user->cleanCookies();
                        Application::log("[STATUS-ERROR-CAPTCHA] {$user->getLogin()} Captcha needed");
                        new notification($user, notification::NOTIFICATION_USER_CAPTCHA_NEEDED);
                    }
                }
                else if ($status === true) {
                    if ($user->getCaptchaNeeded()) {
                        $save = true;
                        $user->setCaptchaNeeded(false);
                        Application::log("[STATUS-CAPTCHA-REOK] {$user->getLogin()} Captcha is reactivated");
                    }
                    else {
                        Application::log("[STATUS-CAPTCHA] {$user->getLogin()} Captcha is OK");
                    }
                }
                if ($save) {
                    $user->save($user->getLogin());
                }
            }
        }
        
        if (!running::fetch('turboAddToBasket') && !running::fetch('turboAddToBasket')) {
            foreach(event::fetchAllList() as $event) {
                $eventFreeUsers = array();
                foreach($event->getUsers() as $user) {
                    if ($user instanceof user) {
                        if ($user->getBasketCount() < $user->getLoginLimit()) {
                            $eventFreeUsers[] = $user;
                        }
                    }
                }
                if ($eventFreeUsers) {
                    foreach($event->getSectors() as $sectorName) {
                        Application::saveData('seeking' . base64_encode($event->getIdentifier() . $event->getIndex() . $sectorName), null);
                    }
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