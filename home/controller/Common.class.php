<?php
namespace controller; 
class Common{
    
    private $tpl=null;
    protected $config;

    public function __construct() {
        
    }
    
   /****模板相关****/
    private function initTpl(){
        if(!$this->tpl){
            $this->tpl = new \system\Template();
        }
    }  
    protected function display($templateFile=""){  
        $this->initTpl();
        $this->tpl->display($templateFile);
    } 
    protected function assign($name,$value=""){ 
        $this->initTpl();
        return $this->tpl->assign($name,$value);
    } 
    protected function isCached($templateFile=""){
        $this->initTpl();
        return $this->tpl->isCached($templateFile);
    } 
    protected function clearCache($templateFile=""){
        $this->initTpl();
        return $this->tpl->clearCache($templateFile);
    }
    public function __call($name, $arguments) {
        exit(L("_ACTION_").':'.$name." is not exsit"); 
    }
     

}
