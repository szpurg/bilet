<?php
define ("MODULE_PATH", dirname(__FILE__) . "/../");
define ("MODULE_URI", "/panel/");

class PanelModule {
    protected $args;
    
    public function __construct($args) {
        if ($this->getUserVar('back')) {
            $this->redirect("index");
        }
        array_shift($args);
        $action = reset($args);
        $this->args = $args;
        if ($action && method_exists($this, "Action" . ucfirst($action))) {
            $method = "Action" . ucfirst($action);
            array_shift($args);
            $this->args = $args;
            call_user_func(array($this, $method));
            if ($this->getUserVar('returner')) {
                $this->redirect($this->getUserVar('returner'));
            }
        }
        else if ($action) {
            die("Strona nie istnieje");
        }
        else {
            $this->index();
        }
    }
    public function index() {
        $this->setVar('events', event::fetchList());
        $this->loadTemplate('index');
    }
    
    public function ActionSaveAccount() {
        $account = $this->getUserVar('account');
        
        $login = $account['login'];
        if (user::fetch($login)) {
            die('konto juz istnieje');
        }
        
        $user = new user;
        $user->setLogin(trim($account['login']));
        $user->setPassword($account['password']);
        
        $user->save($account['login']);
        
    }
    
    public function ActionDeleteAccount() {
        $login = $this->args[0];
        
        $user = user::fetch($login);
        if ($user) {
            $user->delete($login);
        }
    }
    public function ActionManage() {
        $identifier = base64_decode($this->args[0]);
        $index = $this->args[1];
        
        $event = event::fetch($identifier, $index);
        if (!$event) {
            die("Brak zdefiniowanego wydarzenia $event");
        }
        
        $proxy = new proxy(null, null, $identifier);
        
        $this->setVar('event', $event);
        $this->setVar('sectors', $proxy->getSectors());
        $this->setVar('users', user::fetchList());
        $this->setVar('index', $index);
        $this->loadTemplate('manage');
        
    }
    
    public function ActionNewEvent() {
        $this->loadTemplate('editEvent');
    }
    
    public function ActionSaveEditedEvent() {
        $identifier = base64_decode($this->args[0]);
        $index = $this->args[1];
        
        $event = event::fetch($identifier, $index);
        if (!$event) {
            die("Brak zdefiniowanego wydarzenia $event");
        }
        $event->setName($this->getUserVar('name'));
        $event->setUrl($this->getUserVar('url'));
        $event->save($identifier, true, $index);
        $this->redirect('index');
    }
    
    public function ActionEditEvent() {
        $identifier = base64_decode($this->args[0]);
        $index = $this->args[1];
        
        $event = event::fetch($identifier, $index);
        if (!$event) {
            die("Brak zdefiniowanego wydarzenia $event");
        }
        $this->setVar('event', $event);
        $this->setVar('index', $index);
        $this->loadTemplate('editEvent');
    }
    
    private function getEvent($identifier) {
        $events = $this->loadData('events');
        return isset($events[$identifier]) ? $events[$identifier] : null;
    }
    
    public function ActionRemoveEvent() {
        $identifier = base64_decode($this->args[0]);
        $index = $this->args[1];
        $event = event::fetch($identifier, $index);
        if ($event instanceof event) {
            $event->delete($identifier, $index);
        }
        $this->redirect('index');
    }
    
    public function ActionSwitchEvent() {
        $identifier = base64_decode($this->args[0]);
        $index = $this->args[1];
        $event = event::fetch($identifier, $index);
        if ($event instanceof event) {
            if ($event->getActive()) {
                $event->setActive(0);
            }
            else {
                $event->setActive(1);
            }
            $event->save($identifier, true, $index);
        }
        $this->redirect('index');
    }
    
    public function ActionSaveEvent() {
        $identifier = base64_decode($this->args[0]);
        $index = $this->args[1];
        
        $event = event::fetch($identifier, $index);
        if ($event instanceof event) {
            $sectors = $this->getUserVar('sectors');
            $users = $this->getUserVar('users');
            $event->setSectors($sectors);
            $event->setUsers($users);
            $event->setSettings($this->getUserVar('settings'));
            $event->save($identifier, true, $index);
            $this->redirect('index');
        }
    }
    
    public function ActionSaveNewEvent() {
        $name = $this->getUserVar('name');
        $url = $this->getUserVar('url');
        
        if (preg_match("#^http[s]{0,1}://[^/]+/(.+)$#", $url, $matches)) {
           $eventIdentifier = $matches[1];
        }
        else {
            die("Nieprawidlowy adres url wydarzenia");
        }
        
        $event = new event;
        $event->setName($name);
        $event->setUrl($url);
        $event->setIdentifier($eventIdentifier);
        $event->save($eventIdentifier, true);
        $this->redirect('index');
    }
    
    public function redirect($action) {
        $url = 'http://kupbilet.onet.pl' . MODULE_URI . ($action != "index" ? $action : '');
        header("Location: " . $url);
        die;
    }
    
    public function setVar($var, $val) {
        $this->{'__' . $var} = $val;
    }
    
    public function loadTemplate($name) {
        require MODULE_PATH . "templates/$name.php";
    }
    
    public function getUri() {
        return MODULE_URI;
    }
    
    public function getUserVar($name) {
        if (filter_input(INPUT_POST, $name, FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY)) {
            return filter_input(INPUT_POST, $name, FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
        }
        if (filter_input(INPUT_POST, $name)) {
            return trim(filter_input(INPUT_POST, $name));
        }
        if (filter_input(INPUT_GET, $name)) {
            return trim(filter_input(INPUT_GET, $name));
        }
    }
    
    public function __get($name) {
        return isset($this->{'__' . $name}) ? $this->{'__' . $name} : null;
    }
    
}