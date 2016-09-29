<?php
class event extends DataObject {
    protected $name;
    protected $url;
    protected $identifier;
    protected $sectors;
    protected $users;
    protected $settings;
    
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
        return $this->users;
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
    
    public function getSetting($name) {
        $settings = $this->getSettings();
        return isset($settings[$name]) ? $settings[$name] : null;
    }
    

}