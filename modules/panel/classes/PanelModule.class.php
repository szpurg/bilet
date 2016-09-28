<?php
define ("MODULE_PATH", dirname(__FILE__) . "/../");
define ("MODULE_URI", "/panel/");

class PanelModule {
    protected $args;
    
    public function __construct($args) {
        array_shift($args);
        $action = reset($args);
        $this->args = $args;
        if ($action && method_exists($this, "Action" . ucfirst($action))) {
            $method = "Action" . ucfirst($action);
            array_shift($args);
            $this->args = $args;
            call_user_func(array($this, $method));
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
    
    public function ActionManage() {
        $identifier = $this->args[0];
        $index = $this->args[1];
        
        $event = event::fetch($identifier, $index);
        if (!$event) {
            die("Brak zdefiniowanego wydarzenia $event");
        }
        
        $proxy = new proxy(null, null, $identifier);
        
        $this->setVar('event', $event);
        $this->setVar('sectors', $proxy->getSectors());
        $this->setVar('identifier', $identifier);
        $this->setVar('index', $index);
        $this->loadTemplate('manage');
        
    }
    
    public function ActionNewEvent() {
        $this->loadTemplate('newEvent');
    }
    
    private function getEvent($identifier) {
        $events = $this->loadData('events');
        return isset($events[$identifier]) ? $events[$identifier] : null;
    }
    
    public function ActionSaveEvent() {
        $identifier = $this->args[0];
        $index = $this->args[1];
        
        $event = event::fetch($identifier, $index);
        if ($event) {
            
        }
        
    }
    
    public function ActionSaveNewEvent() {
        if ($this->getUserVar('back')) {
            $this->redirect("index");
        }
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
        return filter_input(INPUT_POST, $name);
    }
    
    public function __get($name) {
        return isset($this->{'__' . $name}) ? $this->{'__' . $name} : null;
    }
    
}