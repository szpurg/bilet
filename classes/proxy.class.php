<?php
if (!function_exists('getallheaders'))
{
    function getallheaders()
    {
           $headers = [];
       foreach ($_SERVER as $name => $value)
       {
           if (substr($name, 0, 5) == 'HTTP_')
           {
               $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
           }
       }
       return $headers;
    }
} 
class proxy {
    
    private $cookiesFile;
    
    private $url;
    private $posts;
    private $cookies;
    private $response;
    
    private $argv;
    private $login;
    private $password;
    private $uri;
    private $connectionLimits;
    private $intervalBetweenConnections;
    private $cli = false;
    
    public function __construct($login = null, $password = null, $uri = null, $argv = null, $connectionLimits = false, $intervalBetweenConnections = 0) {
        $this->argv = $argv;
        $this->cli = isset($argv);
        $this->cookiesFile = DATA_PATH . "cookies" . md5($login . $password) . ".dat";
        $this->login = $login;
        $this->password = $password;
        $this->uri = $uri;
        $this->url = $this->getUrl();
        $this->posts = $this->getPosts();
        $this->cookies = $this->getCookies();
        $this->connectionLimits = $connectionLimits;
        $this->response = $this->getResponse();
        $this->intervalBetweenConnections = $intervalBetweenConnections;
        
        if ($login && $password) {
            $this->requiresLogin();
        }
        if ($login) {
            $user = user::fetch($login);
            if ($user instanceof user) {
                if ($user->getCaptchaNeeded() && $this->captchaVerificationNeeded() === false) {
                    $user->setCaptchaNeeded(false);
                    $user->save($login);
                }
                if ($this->getBasketCount() !== -1) {
                    $basketCount = $this->getBasketCount();
                    if (is_numeric($basketCount)) {
                        $user->updateBasketCount($basketCount);
                    }
                }
                if ($this->basketCleanedPreg()){
                    $user->updateBasketCount(0);
                }
            }
        }
    }
    
    public function basketCleanedPreg() {
        return preg_match("#koszyk jest pusty#si", $this->response);
    }
    
    public function getSectors() {
        $sectors = array();
        if (preg_match_all("#<area .+?<font .+?>([^<]+).+?href=\"(.+?)\"#si", $this->response, $Matches)) {
            foreach($Matches[1] as $index => $matches) {
                $freePlacesInfo = $Matches[1][$index];
                $sectorUrl = 'https:' . $Matches[2][$index];
                $sectorName = preg_replace("#^.+/([^/]+)$#", "\\1", $sectorUrl);
                $freePlaces = 0;
                if (preg_match("#([0-9]+)#", $freePlacesInfo, $m)) {
                    $freePlaces = $m[1];
                }
                $sectors[] = array(
                    'url' => $sectorUrl,
                    'name' => $sectorName,
                    'available' => $freePlaces
                );
            }
        }
        return $sectors;
    }
    
    public function isHomePage() {
        return preg_match("#KostkiMain#si", $this->response);
    }


    public function isSector() {
        return preg_match("#/sektor/#", $this->getUrl());
    }
    
    public function getBasketCount() {
        if (!$this->captchaVerificationNeeded() && $this->isSector() && $this->login && $this->password) {
            if (preg_match("#koszyku:.*?<span.+?>.*?([0-9]+).*?</span>#si", $this->response, $matches)) {
                return $matches[1];
            }
            return 0;
        }
        return -1;
    }
    
    public function successfullyAddedToBasket() {
        return preg_match("#dodano do koszyka#si", $this->response);
    }
    
    public function getSeats() {
        if ($this->isSector()) {
            if (preg_match_all("#<div class=\"miejsce\".+?(?(?=href\=)href\='(.+?)'.+?)>#si", $this->response, $Matches)) {
                $available = array();
                $returner = array(
                    'seats' => count($Matches[0]),
                );
                foreach($Matches[1] as $seat) {
                    if ($seat) {
                        $available[] = $seat;
                    }
                }
                $returner['available'] = $available;
                return $returner;
            }
            return false;
        }
        return -1;
    }
    
    public function captchaVerificationNeeded() {
        if ($this->isSector() && !$this->isHomePage()) {
            $response = $this->response;
            if (preg_match("#<div class=\"miejsce\"#si", $response)) {
                return false;
            }
            return true;
        }
        return 0;
    }
    
    private function getPage() {
        $page = filter_input(INPUT_GET, 'pagepagepage');        
        return $page;
    }
    
    private function getUrl() {
        $uri = isset($this->uri) ? $this->uri : $this->getPage();
        $this->uri = preg_replace("#^/#", "", $uri);
        $gets = array();
        foreach($_GET as $getvar => $getval) {
            if ($getvar !== 'pagepagepage') {
                $gets[$getvar] = $getval;
            }
        }
        $queryString = null;
        if ($gets) {
            $queryString = http_build_query($gets);
        }
        
        return "https://www.kupbilet.pl/{$this->uri}" . ($queryString ? '?' . $queryString : '');
    }
    
    private function getPosts() {
        $post = $_POST;
        if ($post) {
            return http_build_query($post);
        }
        return null;
    }
    
    private function getCookies() {
        $cookies = null;
        $cookiesArray = array();
        if (file_exists($this->cookiesFile)) {
            $cookies = file_get_contents($this->cookiesFile);
        }
        if ($cookies) {
            $cookiesArray = unserialize($cookies);
        }
        $headersCookies = $this->getCookiesFromHeaders();
        return array_merge($cookiesArray, $headersCookies);
    }
    
    private function getCookiesFromHeaders() {
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        }
        else {
            $headers['Cookie'] = '';
        }
        
        $cookie = isset($headers['Cookie']) ? $headers['Cookie'] : null;
        
        $cookiesStringArray = explode(";", $cookie);
        $cookies = array();
            
        foreach($cookiesStringArray as $cookiesString) {
            if ($cookiesString) {
                $exp = explode("=", $cookiesString);
                $cookies[trim($exp[0])] = trim($exp[1]);
            }
        }
        return $cookies;
    }
    
    private function saveCookies() {
        $file = fopen($this->cookiesFile, "w");
        flock($file, LOCK_EX);
        fputs($file, serialize($this->cookies));
        flock($file, LOCK_UN);
        fclose($file);
    }
    private function outputCookies() {    
        $cookies = $this->cookies;
        $cookiesStringArray = array();
        foreach($cookies as $name => $value) {
            $cookiesStringArray[] = "$name=$value";
        }
        return implode("; ", $cookiesStringArray);
    }
    
    private function getAndSaveCookiesFromResponse(&$response) {
        $headers = array();
        if (preg_match("#^(.+?)\r{0,1}\n\r{0,1}\n#s", $response, $matches)) {
            $headers = array_map(function($a){return trim($a);}, explode("\n", $matches[1]));
            $response = preg_replace("#^(.+?)\r{0,1}\n\r{0,1}\n#s", "", $response);
        }
        
        $cookies = array();
        foreach($headers as $header) {
            if (preg_match("#^set\-cookie: (.+?)\=(.+?);#", $header, $matches)) {
                $cookies[$matches[1]] = $matches[2];
            }
        }
        
        $this->cookies = array_merge($this->cookies, $cookies);
        $this->saveCookies();
    }
    
    public function loggedIn() {
        return preg_match("#<a href=\".+?/wyloguj#s", $this->response);
    }
    
    private function requiresLogin() {
        $response = $this->response;
        if (preg_match("#<form .+?action=\"(.+?)/zaloguj\"#s", $response, $matches)) {
            $action = 'https:' . $matches[1] . "/zaloguj";
            $posts = "Login={$this->login}&Password={$this->password}";
            $this->getResponse($action, $posts);
            $this->response = $this->getResponse();
            if ($this->loggedIn()) {
                Application::log("{$this->login} logged in");
            }
            else {
                Application::log("[error] {$this->login} login failed");
            }
        }
    }
    
    private function getResponse($url = null, $posts = null) {
        if (!$url) {
            $url = $this->url;
        }
        if (!$posts) {
            $posts = $this->posts;
        }
        
        $options = array(
            CURLOPT_RETURNTRANSFER => true, // return web page
            CURLOPT_HEADER => true, //return headers in addition to content
            CURLOPT_FOLLOWLOCATION => true, // follow redirects
            CURLOPT_ENCODING => "", // handle all encodings
            CURLOPT_AUTOREFERER => true, // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120, // timeout on connect
            CURLOPT_TIMEOUT => 120, // timeout on response
            CURLOPT_MAXREDIRS => 10, // stop after 10 redirects
            CURLINFO_HEADER_OUT => true,
            CURLOPT_SSL_VERIFYPEER => false, // Disabled SSL Cert checks
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_COOKIE => $this->outputCookies(),
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:49.0) Gecko/20100101 Firefox/49.0',
        );

        if ($posts) {
            $options[CURLOPT_POST] = 1;
            $options[CURLOPT_POSTFIELDS] = $posts;
        }
        $ch = curl_init($url);
        curl_setopt_array($ch, $options);
        
        
        if ($this->connectionLimits || $this->connectionLimits === 0) {
            // Two minutes waiting for free conection, then return false
            $counter = 1200;
            while (!Application::incrementDataIfLessThan('connections', $this->connectionLimits)) {
                usleep(100000);
                $counter--;
                if ($counter <= 0) {
                    return false;
                }
            }
            if ($this->intervalBetweenConnections) {
                sleep($this->intervalBetweenConnections);
            }
        }
        $rough_content = curl_exec($ch);
        if ($this->connectionLimits || $this->connectionLimits === 0) {
            Application::decreaseData('connections');
        }
        
//        $err = curl_errno($ch);
//        $errmsg = curl_error($ch);
//        $header = curl_getinfo($ch);
        curl_close($ch);
        
        $this->getAndSaveCookiesFromResponse($rough_content);
        return $rough_content;
    }
    
    public function outputResponse() {
        header("Content-type: text/html; charset=utf-8");
        print $this->response;
    }
    
}
