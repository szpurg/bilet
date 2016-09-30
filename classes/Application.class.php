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
    
    public static function saveData($dataName, $data) {
        $file = fopen(DATA_PATH . $dataName . ".dat", "w");
        flock($file, LOCK_EX);
        fputs($file, serialize($data));
        flock($file, LOCK_UN);
        fclose($file);
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
        $proxy = new proxy($currentUser->getUser() ? $currentUser->getUser()->getLogin() : null, $currentUser->getUser() ? $currentUser->getUser()->getPassword() : null, null);
        $proxy->outputResponse();
    }
    
    protected function panel() {
        $module = new PanelModule(explode("/", $this->page));
    }
    
}
