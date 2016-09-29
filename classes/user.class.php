<?php

class user extends DataObject {
    protected $login;
    protected $password;
    
    public function getDataIdentifier() {
        return 'users';
    }
    
    public function getLogin() {
        return $this->login;
    }

    public function getPassword() {
        return $this->password;
    }

    public function setLogin($login) {
        $this->login = $login;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

}