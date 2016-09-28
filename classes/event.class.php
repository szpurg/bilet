<?php
class event extends DataObject {
    protected $name;
    protected $url;
    protected $identifier;
    
    public function getName() {
        return $this->name;
    }

    public function getUrl() {
        return $this->url;
    }

    public function getIdentifier() {
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

}