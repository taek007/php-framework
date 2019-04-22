<?php
/** 
 * @author Heng.zhang at 2015-04-17 leaf
 */
//系统配置
const EXT               =   '.class.php';    //类文件后缀
define("WEB_ROOT", str_replace("\\", "/", dirname(__DIR__))."/");   //网站根目录
define('CORE_PATH',WEB_ROOT."core/");                               //系统框架目录
define("LIB_PATH", CORE_PATH."library/");                           //系统类库目录
define("CONF_PATH", CORE_PATH."conf/");                             //系统配置文件目录
define("FUN_PATH", CORE_PATH."function/");                          //系统函数库
define("CACHE_PATH", WEB_ROOT."cache/");                            //缓存目录
define("PUBLIC_PATH", "/public/");                          //静态资源目录
define("LAN_PATH", CORE_PATH."language/");                          //语言包目录
define("APP_PATH", WEB_ROOT.APP_NAME."/");                          //当前应用目录
define("EXTEND_PATH", LIB_PATH."extend/");                          //扩展目录
define('HTML_PATH',APP_PATH.'html/');                      // 应用静态目录
define("ERROR_PAGE", WEB_ROOT."404.html");                  //404页面

//环境配置
define("URL",isset($_SERVER['HTTP_HOST'])?"http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']:"");  //当前url
define('IS_CGI',(0 === strpos(PHP_SAPI,'cgi') || false !== strpos(PHP_SAPI,'fcgi')) ? 1 : 0 ); 
define('IS_WIN',strstr(PHP_OS, 'WIN') ? 1 : 0 );     //Win环境
define('IS_CLI',PHP_SAPI=='cli'? 1   :   0);         //命令行模式
define('NOW_TIME', isset($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : time());//当前时间
define('REQUEST_METHOD', isset($_SERVER['REQUEST_METHOD'])?$_SERVER['REQUEST_METHOD']:"");
define('IS_GET', REQUEST_METHOD == 'GET' ? true : false);
define('IS_POST', REQUEST_METHOD == 'POST' ? true : false);
define('IS_PUT', REQUEST_METHOD == 'PUT' ? true : false);
define('IS_DELETE', REQUEST_METHOD == 'DELETE' ? true : false);
define('IS_AJAX', ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')));

  
//系统初始化
$system_file = array( 
    'config'    =>  array(
        CONF_PATH.'system.php',   // 系统配置 
    ),
    "fun"=>array(
        FUN_PATH."common.php",   //系统公共函数库
    )
);
require LIB_PATH.'system/App'.EXT;
system\App::run();
