<?php

class running extends DataObject {
    protected $running = true;
    protected $id;
    protected $action;
    protected $time;
    
    public function __construct() {
        $this->time = time();
    }
    
    public function getDataIdentifier() {
        return 'running';
    }
    
    public function getRunning() {
        return $this->running;
    }

    public function getId() {
        return $this->id;
    }

    public function setRunning($running) {
        $this->running = $running;
    }

    public function setId($id) {
        $this->id = $id;
    }
    
    public function getAction() {
        return $this->action;
    }

    public function setAction($action) {
        $this->action = $action;
    }
    
    public static function clean() {
        foreach(running::fetchList() as $actionName => $action) {
            
            foreach($action as $process) {
                if ($process instanceof running) {
                    if ($process->time + 120 < time()) {
                        $process->delete($actionName, $process->getIndex());
                    }
                }
            }
        }
    }

}