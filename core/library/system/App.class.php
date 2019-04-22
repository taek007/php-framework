<?php
namespace system;
defined('WEB_ROOT') or exit();
/**
 * 系统入口类
 * @author  zh 2681674909@qq.com 2015-04-14
 */
class App {

    // 类映射
    private static $_map = array();

    /**
     * 应用程序初始化
     * @access public
     * @return void
     */
    static public function run() {
        header("Content-type:text/html;charset=utf-8");
        global $system_file;
        //初始化自动加载类库
        spl_autoload_register('system\App::autoload');
        //载入系统函数库 
        foreach ($system_file['fun'] as $vo) {
            if(is_file($vo)){
                include $vo;
            }
        }
        //加载系统配置文件
        foreach ($system_file['config'] as $key=>$file){
            is_numeric($key)?C(load_config($file)):C($key,load_config($file));
        } 
        
        date_default_timezone_set(C('DEFAULT_TIMEZONE')); //设置时区  
        if (!IS_CLI) { //非命令行模式初始化session
            session(C('SESSION_OPTIONS'));
        } 
        check_language(); //检测语言 
        Filter::init(); //非法过滤
        Dispatcher::dispatch();
        self::exec(); 
    }

    /**
     * 执行应用程序
     * @access public
     * @return void
     */
    static public function exec() { 
        $conroller = CONTROLLER_PATH; 
        $module  =  new $conroller;       
        $action     =   ACTION_NAME; 
        try{
            if(!preg_match('/^[A-Za-z](\w)*$/',$action)){ 
                throw new \ReflectionException();
            }
            //执行当前操作
            $method =   new \ReflectionMethod($module, $action);
            if($method->isPublic() && !$method->isStatic()) {
                $class  =   new \ReflectionClass($module);
                // URL参数绑定检测
                if(C('URL_PARAMS_BIND') && $method->getNumberOfParameters()>0){
                    switch($_SERVER['REQUEST_METHOD']) {
                        case 'POST':
                            $vars    =  array_merge($_GET,$_POST);
                            break;
                        case 'PUT':
                            parse_str(file_get_contents('php://input'), $vars);
                            break;
                        default:
                            $vars  =  $_GET;
                    }
                    $params =  $method->getParameters();
                    $paramsBindType     =   C('URL_PARAMS_BIND_TYPE');
                    foreach ($params as $param){
                        $name = $param->getName();
                        if( 1 == $paramsBindType && !empty($vars) ){
                            $args[] =   array_shift($vars);
                        }elseif( 0 == $paramsBindType && isset($vars[$name])){
                            $args[] =   $vars[$name];
                        }elseif($param->isDefaultValueAvailable()){
                            $args[] =   $param->getDefaultValue();
                        }else{
                            E(L('_PARAM_ERROR_').':'.$name);
                        }   
                    }
                    $method->invokeArgs($module,$args);
                }else{
                    $method->invoke($module);
                }
            }else{
                // 操作方法不是Public 抛出异常
                throw new \ReflectionException();
            }
        } catch (\ReflectionException $e) { 
            // 方法调用发生异常后 引导到__call方法处理
            $method = new \ReflectionMethod($module,'__call');
            $method->invokeArgs($module,array($action,''));
        }
        return ;
    }
    
    /**
     * 类库自动加载
     * @param string $class 对象类名
     * @return void
     */
    public static function autoload($class) {
        if (isset(self::$_map[$class])) {// 检查是否存在映射  
            include self::$_map[$class];
        } elseif (false !== strpos($class, '\\')) { //命名空间调用
            $name = strstr($class, '\\', true); 
            if(in_array($name, array("system","driver","extend")) || is_dir(LIB_PATH.$name)){ //系统类库
                $path = LIB_PATH;
            }elseif(in_array($name, array("controller","model"))) { 
                $path = APP_PATH;
            }
            $filename = $path . str_replace('\\', '/', $class) . EXT;     
            if (file_exists($filename)) { 
                if (IS_WIN && false === strpos(str_replace('/', '\\', realpath($filename)), $class . EXT)) {
                    return;
                }
                self::addMap($filename);
                include $filename;
            }
        }
    }

    // 注册classmap
    static public function addMap($class, $map = '') {
        if (is_array($class)) {
            self::$_map = array_merge(self::$_map, $class);
        } else {
            self::$_map[$class] = $map;
        }
    }

    // 获取classmap
    static public function getMap($class = '') {
        if ('' === $class) {
            return self::$_map;
        } elseif (isset(self::$_map[$class])) {
            return self::$_map[$class];
        } else {
            return null;
        }
    }

    /**
     * 取得对象实例 支持调用类的静态方法
     * @param string $class 对象类名
     * @param string $method 类的静态方法名
     * @return object
     */
    static public function instance($class, $method = '') {
        if (class_exists($class)) {
            $o = new $class();
            if (!empty($method) && method_exists($o, $method))
                call_user_func(array(&$o, $method));
            else
                E(L('_METHOD_NOT_EXIST_'));
        } else {
            E(L('_CLASS_NOT_EXIST_'));
        }
    }

}
