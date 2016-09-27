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
            
            
            
            $proxy = new proxy("mikimouse722", "Dania2016", 'ms2018polskadania/sektor/G22');
            $body = "[" . date("Y-m-d H:i:s") . "] " . ($proxy->captchaVerificationNeeded() ? 'ROBOT' : 'OK');
            //print $body . "\n";
            new email('mpfc@tlen.pl', 'Kup Bilet', 'Kup bilet', '[STATUS]', $body);
            die;
        }
        $this->showPage();
    }
    
    protected function showPage() {
        $proxy = new proxy("mikimouse722", "Dania2016", null);
        $proxy->outputResponse();
    }
    
    protected function panel() {
        $module = new PanelModule(explode("/", $this->page));
    }
    
}
