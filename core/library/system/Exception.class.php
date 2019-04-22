<?php 
namespace system; 
/**
 * 异常处理类
 * @author  zh 2681674909@qq.com 2015-04-14
 */
defined('WEB_ROOT') or exit();
class Exception extends \Exception {

    public function __construct($status, $message = '') {  
        parent::__construct($message, $status);
    }
    
    // 自定义字符串输出的样式
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message} \n";
    }
}


