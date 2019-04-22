<?php
namespace system;
defined('WEB_ROOT') or exit(); 
/**
 * 分布式文件存储类
 * @author  zh 2681674909@qq.com 2015-04-14
 */
class Storage {

    /**
     * 操作句柄
     * @var string
     * @access protected
     */
    static protected $handler    ;

    /**
     * 连接分布式文件系统
     * @access public
     * @param string $type 文件类型
     * @param array $options  配置数组
     * @return void
     */
    static public function connect($type='File',$options=array()) {
        $class  =   'system\\driver\\storage\\'.ucwords($type);
        self::$handler = new $class($options);
    }

    static public function __callstatic($method,$args){ 
        if(!self::$handler){
            self::connect();
        }
        if(method_exists(self::$handler, $method)){
           return call_user_func_array(array(self::$handler,$method), $args);
        }
    }
}
