<?php

class settings extends DataObject {
    public static function definition() {
        return array(
            'email' => array(
                'label' => 'E-mail powiadomień',
                'type' => 'text',
            ),
            'smtpHost' => array(
                'label' => 'Host poczty wychodzącej (SMTP)',
                'type' => 'text',
            ),
            'smtpUsername' => array(
                'label' => 'Login poczty wychodzącej',
                'type' => 'text',
            ),
            'smtpPassword' => array(
                'label' => 'Hasło poczty wychodzącej',
                'type' => 'password',
            ),
            'smtpSecurity' => array(
                'label' => 'Szyfrowanie poczty wychodzącej',
                'type' => 'select',
                'choices' => array(
                    '' => 'brak',
                    'ssl' => 'ssl',
                    'tls' => 'tls',
                ),
                'default' => '',
            ),
            'smtpPort' => array(
                'label' => 'Port poczty wychodzącej',
                'type' => 'text',
                'default' => 25,
            ),
            'turboMainInterval' => array(
                'label' => 'Interwał sprawdzania sektorów w minutach (tryb turbo)',
                'type' => 'text',
                'default' => 3,
            ),
            'turboSeekingThreads' => array(
                'label' => 'Liczba procesów wyszukiwania dostępnych miejsc (tryb turbo)',
                'type' => 'text',
                'default' => 3,
            ),
            'turboSeekingConnections' => array(
                'label' => 'Maksymalna liczba połączeń podczas przeszukiwania sektorów (tryb turbo)',
                'type' => 'text',
                'default' => 2,
            ),
            'turboBasketConnections' => array(
                'label' => 'Maksymalna liczba połączeń podczas dodawania miejsc do koszyka (tryb turbo)',
                'type' => 'text',
                'default' => 10,
            ),
            'turboTimeBetweenConnections' => array(
                'label' => 'Przerwa w sekundach pomiędzy połączeniami z serwisem (w każdym procesie - tryb turbo)',
                'type' => 'text',
                'default' => 1,
            ),
        );
    }
    
    protected $data = array();
    
    public function __construct() {
        foreach(self::definition() as $key => $definition) {
            if (isset($definition['default'])) {
                $this->$key = $definition['default'];
            }
        }
    }
    
    public function __set($name, $value) {
        $this->data[$name] = $value;
    }
    
    public function __get($name) {
        return isset($this->data[$name]) ? $this->data[$name] : null;
    }
    
    public function getData($key = null) {
        if ($key !== null && isset($this->data[$key])) {
            return $this->data[$key];
        }
        else if ($key === null) {
            return $this->data;
        }
        return null;
    }
    
    public function getDataIdentifier() {
        return 'settings';
    }
    
    public function getSmtpHost() {
        return $this->smtpHost;
    }

    public function getSmtpUsername() {
        return $this->smtpUsername;
    }

    public function getSmtpPassword() {
        return $this->smtpPassword;
    }

    public function getSmtpSecurity() {
        return $this->smtpSecurity;
    }

    public function getSmtpPort() {
        return $this->smtpPort;
    }

    public function getTurboSeekingThreads() {
        return $this->turboSeekingThreads;
    }

    public function getTurboBasketConnections() {
        return $this->turboBasketConnections;
    }

    public function getTurboTimeBetweenConnections() {
        return $this->turboTimeBetweenConnections;
    }

    public function setSmtpHost($smtpHost) {
        $this->smtpHost = $smtpHost;
    }

    public function setSmtpUsername($smtpUsername) {
        $this->smtpUsername = $smtpUsername;
    }

    public function setSmtpPassword($smtpPassword) {
        $this->smtpPassword = $smtpPassword;
    }

    public function setSmtpSecurity($smtpSecurity) {
        $this->smtpSecurity = $smtpSecurity;
    }

    public function setSmtpPort($smtpPort) {
        $this->smtpPort = $smtpPort;
    }

    public function setTurboSeekingThreads($turboSeekingThreads) {
        $this->turboSeekingThreads = $turboSeekingThreads;
    }

    public function setTurboBasketConnections($turboBasketConnections) {
        $this->turboBasketConnections = $turboBasketConnections;
    }

    public function setTurboTimeBetweenConnections($turboTimeBetweenConnections) {
        $this->turboTimeBetweenConnections = $turboTimeBetweenConnections;
    }

    public static function get($key) {
        $instance = self::getInstance();
        return $instance->$key;
    }
    /**
     * @return settings
     */
    public static function getInstance() {
        if (isset(self::$instance)) {
            return self::$instance;
        }
        $instance = self::fetch();
        if (!$instance) {
            $instance = new self;
        }
        return self::$instance = $instance;
    }
    
    protected static $instance;

}