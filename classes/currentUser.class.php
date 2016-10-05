<?php

class currentUser extends DataObject {
    public $login;
    
    public function getDataIdentifier() {
        return 'current_user';
    }
    /**
     * @return user
     */
    public function getUser() {
        return user::fetch($this->login);
    }
}