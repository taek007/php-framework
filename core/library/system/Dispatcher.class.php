<?php

namespace system;

/**
 * 路由类
 * @author  zh 2681674909@qq.com 2015-04-14
 */
class Dispatcher {

    /**
     * URL映射到控制器
     * @access public
     * @return void
     */
    static public function dispatch() { 
        $varPath = C('VAR_PATHINFO');
        $varController = C('VAR_CONTROLLER'); //默认控制器url变量
        $varAction = C('VAR_ACTION');    //默认方法url变量
        $urlCase = C('URL_CASE_INSENSITIVE'); //url大小写  
        defined('APP_NAME') or define('APP_NAME', C('DEFAULT_MODULE'));  //当前应用
        if (is_dir(APP_PATH)) {
            C('TPL_CACHE_PATH', CACHE_PATH . APP_NAME . '/tpl/'); // 定义当前应用的模版缓存路径 
            C('LOG_PATH', CACHE_PATH . APP_NAME . '/logs/'); // 定义当前应用的日志目录 
            C("DATA_CACHE_PATH", CACHE_PATH . APP_NAME . '/data/'); //定义当前应用数据缓存路径 
            //加载应用配置文件
            if (is_file(APP_PATH . 'conf/system.php')) {
                C(load_config(APP_PATH . 'conf/system.php'));
            }
            // 加载应用函数文件
            if (is_file(APP_PATH . 'function/common.php')) {
                include APP_PATH . 'function/common.php';
            }
        } else {
            E(L('_MODULE_NOT_EXIST_') . ':' . APP_NAME);
        }  
        // 获取控制器和操作名
        $control = self::getController($varController, $urlCase); 
        define('CONTROLLER_NAME',$control);
        define("CONTROLLER_PATH", "controller\\".$control);
        define('ACTION_NAME',self::getAction($varAction, $urlCase));
        $_REQUEST = array_merge($_POST, $_GET);
    }

 
    /**
     * 获得实际的控制器名称
     */
    static private function getController($var, $urlCase) {
        $controller = (!empty($_GET[$var]) ? $_GET[$var] : C('DEFAULT_CONTROLLER'));
        unset($_GET[$var]);
        if ($maps = C('DENY_CONTROLLER')) {//禁止访问的控制器 
            if(in_array(strtolower($controller),$maps)){ 
                E(L('_ERROR_ACTION_'));
            } 
        }
        if ($urlCase) {// URL地址不区分大小写 
            $controller = parse_name($controller, 1);//user_type 识别到 UserTypeController 控制器
            return strip_tags(ucfirst($controller));
        } 
        return ucfirst($controller);
    }

    /**
     * 获得实际的操作名称
     */
    static private function getAction($var, $urlCase) {
        $action = !empty($_POST[$var]) ?$_POST[$var] :(!empty($_GET[$var]) ? $_GET[$var] : C('DEFAULT_ACTION'));
        unset($_POST[$var], $_GET[$var]); 
        return strip_tags($urlCase ? strtolower($action) : $action );
    }

}
