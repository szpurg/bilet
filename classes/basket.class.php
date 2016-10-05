<?php

class basket extends DataObject {
    protected $login;
    protected $count = 0;
    
    public function __construct(user $user) {
        $this->login = $user->getLogin();
    }
    
    public function getDataIdentifier() {
        return 'basket' . $this->login;
    }
    public function getLogin() {
        return $this->login;
    }

    public function setLogin($login) {
        $this->login = $login;
    }
    public function getCount() {
        return $this->count;
    }

    public function setCount($count) {
        $this->count = $count;
    }
    
    public function incrementCount() {
        $this->count++;
        $this->save();
        return $this->count;
    }
    
    public function saveCount($count) {
        $this->count = $count;
        $this->save();
    }

}