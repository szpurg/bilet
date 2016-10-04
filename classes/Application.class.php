<?php
class Application {
    protected $page;
    
    public function __construct() {
        $this->page = filter_input(INPUT_GET, 'pagepagepage');
        if (preg_match("#^panel[/]{0,1}#", $this->page)) {
            $this->panel();
            die;
        }
        if (getCli()) {
            
            new CliModule(getCli());
            
//            $proxy = new proxy("mikimouse722", "Dania2016", 'ms2018polskadania/sektor/G22');
//            $body = "[" . date("Y-m-d H:i:s") . "] " . ($proxy->captchaVerificationNeeded() ? 'ROBOT' : 'OK');
//            //print $body . "\n";
//            new email('mpfc@tlen.pl', 'Kup Bilet', 'Kup bilet', '[STATUS]', $body);
            die;
        }
        $this->showPage();
    }
    
    public static function loadData($dataName) {
        if (file_exists(DATA_PATH . $dataName . ".dat")) {
            $data = file_get_contents(DATA_PATH . $dataName . ".dat");
            if ($data) {
                return unserialize($data);
            }
        }
        return null;
    }
    
    public static function saveData($dataName, $data, $serialize = true, $append = false) {
        $file = fopen(DATA_PATH . $dataName . ".dat", $append ? 'a' : 'w');
        flock($file, LOCK_EX);
        fputs($file, $serialize ? serialize($data) : $data);
        flock($file, LOCK_UN);
        fclose($file);
    }
    
    public static function log($string) {
        if (filesize(DATA_PATH . "logs.dat") > 1024 * 1024) {
            rename(DATA_PATH . "logs.dat", DATA_PATH . "logs.old.dat");
        }
        Application::saveData('logs', "[" . date("Y-m-d H:i:s") . "] " . "$string\n", false, true);
    }
    
    public static function incrementDataIfLessThan($dataName, $lessThan) {
        if (!file_exists(DATA_PATH . $dataName . ".dat")) {
            $file = fopen(DATA_PATH . $dataName . ".dat", 'w');
            flock($file, LOCK_EX);
            fputs($file, 0);
            flock($file, LOCK_UN);
            fclose($file);
        }
        
        $file = fopen(DATA_PATH . $dataName . ".dat", 'r+');
        flock($file, LOCK_EX);
        $value = fread($file, 1024);
        $success = false;
        if ($value < $lessThan) {
            $value++;
            rewind($file);
            fputs($file, $value);
            $success = true;
        }
        flock($file, LOCK_UN);
        fclose($file);
        return $success;
    }
    
    public static function decreaseData($dataName) {
        $file = fopen(DATA_PATH . $dataName . ".dat", 'r+');
        flock($file, LOCK_EX);
        $value = fread($file, 1024);
        $value--;
        rewind($file);
        $success = fputs($file, $value);
        ftruncate($file, strlen($value));
        flock($file, LOCK_UN);
        fclose($file);
        return $success;
    }
    
    public static function urlToURI($url) {
        return preg_replace("#^.*?//.+?/(.+?)$#", "\\1", $url);
    }
    
    protected function showPage() {
        $currentUser = currentUser::fetch('currentUser');
        if (!$currentUser) {
            $currentUser = new currentUser;
        }
        if (isset($_GET['cuser'])) {
            $newCurrentUserLogin = $_GET['cuser'];
            $currentUser->login = $newCurrentUserLogin;
            $currentUser->save('currentUser');
        }
        $proxy = new proxy(
                            $currentUser->getUser() ? $currentUser->getUser()->getLogin() : null, 
                            $currentUser->getUser() ? $currentUser->getUser()->getPassword() : null, 
                            isset($_GET['capac']) && $currentUser->getUser() 
                                ? $currentUser->getUser()->getActiveRelatedSectorUri() 
                                : null
        );
        $proxy->outputResponse();
    }
    
    protected function panel() {
        $module = new PanelModule(explode("/", $this->page));
    }
}
