<?php

defined("WEB_ROOT") or exit();
/**
 * 系统函数库
 * @author Heng.zhang at 2015-04-09
 */

/**
 * 浏览器友好的变量输出 （此函数可删除）
 * @param mixed $var 变量
 * @param boolean $echo 是否输出 默认为True 如果为false 则返回输出字符串
 * @param string $label 标签 默认为空
 * @param boolean $strict 是否严谨 默认为true
 * @return void|string
 */
function dump($var, $echo = true, $label = null, $strict = true) {
    $label = ($label === null) ? '' : rtrim($label) . ' ';
    if (!$strict) {
        if (ini_get('html_errors')) {
            $output = print_r($var, true);
            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        } else {
            $output = $label . print_r($var, true);
        }
    } else {
        ob_start();
        var_dump($var);
        $output = ob_get_clean();
        if (!extension_loaded('xdebug')) {
            $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        }
    }
    if ($echo) {
        echo($output);
        return null;
    } else
        return $output;
}

/**
 * 获取和设置配置参数 支持批量定义（系统必须）
 * @param string|array $name 配置变量
 * @param mixed $value 配置值
 * @return mixed
 */
function C($name = null, $value = null) {
    static $_config = array();
    // 无参数时获取所有
    if (empty($name)) {
        if (!empty($value) && $array = S('c_' . $value)) {
            $_config = array_merge($_config, array_change_key_case($array));
        }
        return $_config;
    }
    // 优先执行设置获取或赋值
    if (is_string($name)) {
        if (!strpos($name, '.')) {
            $name = strtolower($name);
            if (is_null($value))
                return isset($_config[$name]) ? $_config[$name] : null;
            $_config[$name] = $value;
            return;
        }
        // 二维数组设置和获取支持
        $name = explode('.', $name);
        $name[0] = strtolower($name[0]);
        if (is_null($value))
            return isset($_config[$name[0]][$name[1]]) ? $_config[$name[0]][$name[1]] : null;
        $_config[$name[0]][$name[1]] = $value;
        return;
    }
    // 批量设置
    if (is_array($name)) {
        $_config = array_merge($_config, array_change_key_case($name));
        if (!empty($value)) {// 保存配置值
            S('c_' . $value, $_config);
        }
        return;
    }
    return null; // 避免非法参数
}

/**
 * 加载配置文件 支持格式转换 仅支持一级配置(系统必须)
 * @param string $file 配置文件名
 * @param string $parse 配置解析方法 有些格式需要用户自己解析
 * @return array
 */
function load_config($file) {
    $ext = pathinfo($file, PATHINFO_EXTENSION);
    switch ($ext) {
        case 'php':
            return include $file;
        case 'ini':
            return parse_ini_file($file);
        case 'yaml':
            return yaml_parse_file($file);
        case 'xml':
            return (array) simplexml_load_file($file);
        case 'json':
            return json_decode(file_get_contents($file), true);
    }
}

//异常处理(系统必须)
function E($message = "", $num = 0) {
    throw new system\Exception($num, $message);
}

/**
 * session管理函数（系统必须）
 * @param string|array $name session名称 如果为数组则表示进行session设置
 * @param mixed $value session值
 * @return mixed
 */
function session($name = '', $value = '') {
    $prefix = C('SESSION_PREFIX');
    if (is_array($name)) { // session初始化 在session_start 之前调用
        if (isset($name['prefix']))
            C('SESSION_PREFIX', $name['prefix']);
        if (isset($name['id']))
            session_id($name['id']);
        if (isset($name['name']))
            session_name($name['name']);
        if (isset($name['path']))
            session_save_path($name['path']);
        if (isset($name['domain']))
            ini_set('session.cookie_domain', $name['domain']);
        if (isset($name['expire'])) {
            ini_set('session.gc_maxlifetime', $name['expire']);
            ini_set('session.cookie_lifetime', $name['expire']);
        }
        if (isset($name['use_trans_sid']))
            ini_set('session.use_trans_sid', $name['use_trans_sid'] ? 1 : 0);
        if (isset($name['use_cookies']))
            ini_set('session.use_cookies', $name['use_cookies'] ? 1 : 0);
        if (isset($name['cache_limiter']))
            session_cache_limiter($name['cache_limiter']);
        if (isset($name['cache_expire']))
            session_cache_expire($name['cache_expire']);
        if (isset($name['type']))
            C('SESSION_TYPE', $name['type']);
        $type = C('SESSION_TYPE');
        if ($type) { // 设置session驱动 
            $class = strpos($type, '\\') ? $type : 'driver\\session\\' . ucwords(strtolower($type));
            $hander = new $class();
            session_set_save_handler(
                    array(&$hander, "open"), array(&$hander, "close"), array(&$hander, "read"), array(&$hander, "write"), array(&$hander, "destroy"), array(&$hander, "gc")
            );
        }
        // 启动session
        if (C('SESSION_AUTO_START'))
            session_start();
    }elseif ('' === $value) {
        if ('' === $name) {
            // 获取全部的session
            return $prefix ? $_SESSION[$prefix] : $_SESSION;
        } elseif (0 === strpos($name, '[')) { // session 操作
            if ('[pause]' == $name) { // 暂停session
                session_write_close();
            } elseif ('[start]' == $name) { // 启动session
                session_start();
            } elseif ('[destroy]' == $name) { // 销毁session
                $_SESSION = array();
                session_unset();
                session_destroy();
            } elseif ('[regenerate]' == $name) { // 重新生成id
                session_regenerate_id();
            }
        } elseif (0 === strpos($name, '?')) { // 检查session
            $name = substr($name, 1);
            if (strpos($name, '.')) { // 支持数组
                list($name1, $name2) = explode('.', $name);
                return $prefix ? isset($_SESSION[$prefix][$name1][$name2]) : isset($_SESSION[$name1][$name2]);
            } else {
                return $prefix ? isset($_SESSION[$prefix][$name]) : isset($_SESSION[$name]);
            }
        } elseif (is_null($name)) { // 清空session
            if ($prefix) {
                unset($_SESSION[$prefix]);
            } else {
                $_SESSION = array();
            }
        } elseif ($prefix) { // 获取session
            if (strpos($name, '.')) {
                list($name1, $name2) = explode('.', $name);
                return isset($_SESSION[$prefix][$name1][$name2]) ? $_SESSION[$prefix][$name1][$name2] : null;
            } else {
                return isset($_SESSION[$prefix][$name]) ? $_SESSION[$prefix][$name] : null;
            }
        } else {
            if (strpos($name, '.')) {
                list($name1, $name2) = explode('.', $name);
                return isset($_SESSION[$name1][$name2]) ? $_SESSION[$name1][$name2] : null;
            } else {
                return isset($_SESSION[$name]) ? $_SESSION[$name] : null;
            }
        }
    } elseif (is_null($value)) { // 删除session
        if (strpos($name, '.')) {
            list($name1, $name2) = explode('.', $name);
            if ($prefix) {
                unset($_SESSION[$prefix][$name1][$name2]);
            } else {
                unset($_SESSION[$name1][$name2]);
            }
        } else {
            if ($prefix) {
                unset($_SESSION[$prefix][$name]);
            } else {
                unset($_SESSION[$name]);
            }
        }
    } else { // 设置session
        if (strpos($name, '.')) {
            list($name1, $name2) = explode('.', $name);
            if ($prefix) {
                $_SESSION[$prefix][$name1][$name2] = $value;
            } else {
                $_SESSION[$name1][$name2] = $value;
            }
        } else {
            if ($prefix) {
                $_SESSION[$prefix][$name] = $value;
            } else {
                $_SESSION[$name] = $value;
            }
        }
    }
    return null;
}

/**
 * 获取和设置语言定义(不区分大小写)（系统必须）
 * @param string|array $name 语言变量
 * @param mixed $value 语言值或者变量
 * @return mixed
 */
function L($name = null, $value = null) {
    static $_lang = array();
    if (empty($name)) {
        return $_lang;
    }
    if (is_string($name)) {
        $name = strtoupper($name);
        if (is_null($value)) {
            return isset($_lang[$name]) ? $_lang[$name] : $name;
        } elseif (is_array($value)) {
            // 支持变量
            $replace = array_keys($value);
            foreach ($replace as &$v) {
                $v = '{$' . $v . '}';
            }
            return str_replace($replace, $value, isset($_lang[$name]) ? $_lang[$name] : $name);
        }
        $_lang[$name] = $value; // 语言定义
        return null;
    }
    // 批量定义
    if (is_array($name))
        $_lang = array_merge($_lang, array_change_key_case($name, CASE_UPPER));
    return null;
}

/**
 * 字符串命名风格转换（系统必须）
 * type 0 将Java风格转换为C的风格 1 将C风格转换为Java的风格
 * @param string $name 字符串
 * @param integer $type 转换类型
 * @return string
 */
function parse_name($name, $type = 0) {
    if ($type) {
        return ucfirst(preg_replace_callback('/_([a-zA-Z])/', function($match) {
                    return strtoupper($match[1]);
                }, $name));
    } else {
        return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
    }
}

/**
 * 数据缓存管理
 * @param mixed $name 缓存名称，如果为数组表示进行缓存设置
 * @param mixed $value 缓存值
 * @param mixed $options 缓存参数
 * @return mixed
 */
function S($name, $value = '', $options = null) {
    if (!C("DATA_CACHE_ON"))
        return;
    static $cache = '';
    if (is_array($options)) { // 缓存操作的同时初始化
        $type = isset($options['type']) ? $options['type'] : '';
        $cache = system\Cache::getInstance($type, $options);
    } elseif (is_array($name)) { // 缓存初始化
        $type = isset($name['type']) ? $name['type'] : '';
        $cache = system\Cache::getInstance($type, $name);
        return $cache;
    } elseif (empty($cache)) { // 自动初始化
        $cache = system\Cache::getInstance();
    }
    if ('' === $value) { // 获取缓存
        return $cache->get($name);
    } elseif (is_null($value)) { // 删除缓存
        return $cache->rm($name);
    } else { // 缓存数据
        if (is_array($options)) {
            $expire = isset($options['expire']) ? $options['expire'] : NULL;
        } else {
            $expire = is_numeric($options) ? $options : NULL;
        }
        return $cache->set($name, $value, $expire);
    }
}

//实例化模型(系统必须)
function M($name) {
    static $_model = array();
    $class = "\\model\\" . ucfirst($name) . 'Model';
    $guid = md5("Model" . $class);
    if (!isset($_model[$guid])) {
        $_model[$guid] = new $class();
    }
    return $_model[$guid];
}

/**
 * 根据PHP各种类型变量生成唯一标识号(系统必须)
 * @param mixed $mix 变量
 * @return string
 */
function to_guid_string($mix) {
    if (is_object($mix)) {
        return spl_object_hash($mix);
    } elseif (is_resource($mix)) {
        $mix = get_resource_type($mix) . strval($mix);
    } else {
        $mix = serialize($mix);
    }
    return md5($mix);
}

/**
 * 去除代码中的空白和注释(系统必须)
 * @param string $content 代码内容
 * @return string
 */
function strip_whitespace($content) {
    $stripStr = '';
    //分析php源码
    $tokens = token_get_all($content);
    $last_space = false;
    for ($i = 0, $j = count($tokens); $i < $j; $i++) {
        if (is_string($tokens[$i])) {
            $last_space = false;
            $stripStr .= $tokens[$i];
        } else {
            switch ($tokens[$i][0]) {
                //过滤各种PHP注释
                case T_COMMENT:
                case T_DOC_COMMENT:
                    break;
                //过滤空格
                case T_WHITESPACE:
                    if (!$last_space) {
                        $stripStr .= ' ';
                        $last_space = true;
                    }
                    break;
                case T_START_HEREDOC:
                    $stripStr .= "<<<HD\n";
                    break;
                case T_END_HEREDOC:
                    $stripStr .= "HD;\n";
                    for ($k = $i + 1; $k < $j; $k++) {
                        if (is_string($tokens[$k]) && $tokens[$k] == ';') {
                            $i = $k;
                            break;
                        } else if ($tokens[$k][0] == T_CLOSE_TAG) {
                            break;
                        }
                    }
                    break;
                default:
                    $last_space = false;
                    $stripStr .= $tokens[$i][1];
            }
        }
    }
    return $stripStr;
}

/**
 * URL重定向(系统必须)
 * @param string $url 重定向的URL地址
 * @param integer $time 重定向的等待时间（秒）
 * @param string $msg 重定向前的提示信息
 * @return void
 */
function redirect($url, $time = 0, $msg = '', $type = '') {
    if ($type == '301') {
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: ' . $url);
        exit();
    } elseif ($type == '404') {
        header('HTTP/1.1 404 Not Found');
        echo file_get_contents($url);
        exit();
    } else {
        $url = str_replace(array("\n", "\r"), '', $url);
        if (empty($msg))
            $msg = "系统将在{$time}秒之后自动跳转到{$url}！";
        if (!headers_sent()) {
            if (0 === $time) {
                header('Location: ' . $url);
            } else {
                header("refresh:{$time};url={$url}");
                echo($msg);
            }
            exit();
        } else {
            $str = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
            if ($time != 0)
                $str .= $msg;
            exit($str);
        }
    }
}

//删除目录下的文件（删除模板缓存）
function unlinkRecursive($dir, $deleteRootToo = false) {
    if (!$dh = @opendir($dir)) {
        return;
    }
    while (false !== ($obj = readdir($dh))) {
        if ($obj == '.' || $obj == '..') {
            continue;
        }
        if (!@unlink($dir . '/' . $obj)) {
            unlinkRecursive($dir . '/' . $obj, true);
        }
    }
    closedir($dh);
    if ($deleteRootToo) {
        @rmdir($dir);
    }
    return;
}

/**
 * Cookie 设置、获取、删除(系统必须)
 * @param string $name cookie名称
 * @param mixed $value cookie值
 * @param mixed $option cookie参数
 * @return mixed
 */
function cookie($name = '', $value = '', $option = null) {
    // 默认设置
    $config = array(
        'prefix' => C('COOKIE_PREFIX'), // cookie 名称前缀
        'expire' => C('COOKIE_EXPIRE'), // cookie 保存时间
        'path' => C('COOKIE_PATH'), // cookie 保存路径
        'domain' => C('COOKIE_DOMAIN'), // cookie 有效域名
        'secure' => C('COOKIE_SECURE'), //  cookie 启用安全传输
        'httponly' => C('COOKIE_HTTPONLY'), // httponly设置
    );
    // 参数设置(会覆盖黙认设置)
    if (!is_null($option)) {
        if (is_numeric($option))
            $option = array('expire' => $option);
        elseif (is_string($option))
            parse_str($option, $option);
        $config = array_merge($config, array_change_key_case($option));
    }
    if (!empty($config['httponly'])) {
        ini_set("session.cookie_httponly", 1);
    }
    // 清除指定前缀的所有cookie
    if (is_null($name)) {
        if (empty($_COOKIE))
            return null;
        // 要删除的cookie前缀，不指定则删除config设置的指定前缀
        $prefix = empty($value) ? $config['prefix'] : $value;
        if (!empty($prefix)) {// 如果前缀为空字符串将不作处理直接返回
            foreach ($_COOKIE as $key => $val) {
                if (0 === stripos($key, $prefix)) {
                    setcookie($key, '', time() - 3600, $config['path'], $config['domain'], $config['secure'], $config['httponly']);
                    unset($_COOKIE[$key]);
                }
            }
        }
        return null;
    } elseif ('' === $name) {
        // 获取全部的cookie
        return $_COOKIE;
    }
    $name = $config['prefix'] . str_replace('.', '_', $name);
    if ('' === $value) {
        if (isset($_COOKIE[$name])) {
            $value = $_COOKIE[$name];
            if (0 === strpos($value, 'think:')) {
                $value = substr($value, 6);
                return array_map('urldecode', json_decode(MAGIC_QUOTES_GPC ? stripslashes($value) : $value, true));
            } else {
                return $value;
            }
        } else {
            return null;
        }
    } else {
        if (is_null($value)) {
            setcookie($name, '', time() - 3600, $config['path'], $config['domain'], $config['secure'], $config['httponly']);
            unset($_COOKIE[$name]); // 删除指定cookie
        } else {
            // 设置cookie
            if (is_array($value)) {
                $value = 'think:' . json_encode(array_map('urlencode', $value));
            }
            $expire = !empty($config['expire']) ? time() + intval($config['expire']) : 0;
            setcookie($name, $value, $expire, $config['path'], $config['domain'], $config['secure'], $config['httponly']);
            $_COOKIE[$name] = $value;
        }
    }
    return null;
}

/**
 * 记录和统计时间（微秒）和内存使用情况
 * 使用方法:
 * <code>
 * G('begin'); // 记录开始标记位
 * // ... 区间运行代码
 * G('end'); // 记录结束标签位
 * echo G('begin','end',6); // 统计区间运行时间 精确到小数后6位
 * echo G('begin','end','m'); // 统计区间内存使用情况
 * 如果end标记位没有定义，则会自动以当前作为标记位
 * 其中统计内存使用需要 MEMORY_LIMIT_ON 常量为true才有效
 * </code>
 * @param string $start 开始标签
 * @param string $end 结束标签
 * @param integer|string $dec 小数位或者m
 * @return mixed
 */
function G($start, $end = '', $dec = 4) {
    static $_info = array();
    static $_mem = array();
    if (is_float($end)) { // 记录时间
        $_info[$start] = $end;
    } elseif (!empty($end)) { // 统计时间和内存使用
        if (!isset($_info[$end]))
            $_info[$end] = microtime(TRUE);
        return number_format(($_info[$end] - $_info[$start]), $dec);
    }else { // 记录时间和内存使用
        $_info[$start] = microtime(TRUE);
    }
    return null;
}

/**
 * 获取客户端IP地址
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @param boolean $adv 是否进行高级模式获取（有可能被伪装） 
 * @return mixed
 */
function get_client_ip($type = 0, $adv = false) {
    $type = $type ? 1 : 0;
    static $ip = NULL;
    if ($ip !== NULL)
        return $ip[$type];
    if ($adv) {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos = array_search('unknown', $arr);
            if (false !== $pos)
                unset($arr[$pos]);
            $ip = trim($arr[0]);
        }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = sprintf("%u", ip2long($ip));
    $ip = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}

/**
 * 获取远程数据
 * @param  $url 地址
 * @param  $post_data 参数
 * @param  $method 方法
 * @param  $timeout 延时
 */
function send($url = "", $post_data = '', $method = 'POST', $timeout = 3) {
    if (is_array($post_data)) {
        $post_data = http_build_query($post_data);
    }
    if ($method == 'GET') {
        if ($post_data)
            $url = $url . '?' . ltrim($post_data, '?');
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, trim($url));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('User-Agent: Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US) \r\n Accept: */*'));
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if ($method == 'POST') {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    }
    $content = curl_exec($ch);
    $response = curl_getinfo($ch);
    if($content){
        return json_decode($content,true);
    }
    return $content;
}

/**
 * 加载静态资源
 * @param type $str 加载资源
 * @param type $suffix 资源类型 js,css
 * @param type $debug 调试模式
 * @return string
 */
function load_static($str, $suffix = "js", $debug = false) {
    $html = "";
    if (!$str)
        return $html;
    if (is_string($str)) {
        $str = explode(",", $str);
    }
    foreach ($str as $vo) {
        if (!$debug && !APP_DEBUG)
            $vo = $vo . ".min";
        if ($suffix == 'js') {
            if (strpos($vo, "http://") === 0) { //绝对地址
                $html.='<script type="text/javascript" src="' . $vo . '"></script>';
            } else {
                $html.='<script type="text/javascript" src="' . PUBLIC_PATH . 'js/' . $vo . '.js"></script>';
            }
        } else if ($suffix == 'css') {
            if (strpos($vo, "http://") === 0) { //绝对地址
                $html.='<link rel="stylesheet" type="text/css" href="' . $vo . '" />';
            } else {
                $html.='<link rel="stylesheet" type="text/css" href="' . PUBLIC_PATH . 'css/' . $vo . '.css" />';
            }
        }
    }
    return $html;
}

/**
 * 生成分页字符串
 * @param int $total 总数
 * @param int $size 每页显示数
 * @return string  html
 */
function create_page($total, $size, $param = "p", $type = "rewrite", $jump = false, $oparam = "", $rule = "") {
    $obj = new \util\Pager($total, $size, 5, $param, $rule);
    $obj->set(array(
        "shenglv" => ' ... ',
        "curr" => "pagecurr",
        "type" => $type,
        "jump" => $jump,
        "oparam" => $oparam,
    ));
    $obj->setConfig(array(
        "prev" => L('PRE_PAGE'),
        "next" => L('NEXT_PAGE'),
        "theme" => "%upPage% %prePage% %linkPage% %nextPage% %downPage% ",
    ));
    $page = $obj->show();
    return $page;
}

/**
 * 结束当前页面并且输出
 * @param $error_code  错误代码
 * @param $info  提示信息 
 * @param $data  返回数据
 * @return  json;
 */
function finish($status, $info = "", $data = null) {
    $result ['status'] = $status;
    $result['info'] = $info;
    $result ['data'] = $data;
    echo json_encode($result);
    exit();
}

/**
 * 记录日志
 * @param type $content
 * @param type $level
 * @return type
 */
function logs($content) {
    if (!C('LOG_RECORD'))
        return;
    return \system\Log::write($content);
}

/**
 * 语言检查
 * 检查浏览器支持语言，并自动加载语言包
 * @access private
 * @return void
 */
function check_language() { 
    if (!C('LANG_SWITCH_ON')) { 
        L(include LAN_PATH.strtolower(C('DEFAULT_LANG')).'.php');//加载默认语言包 
        return;
    }
    $langSet = C('DEFAULT_LANG');
    $varLang = C('VAR_LANGUAGE');
    $langList=array();
    if(C('LANG_LIST')){
        $langList = explode(',', C('LANG_LIST'));
    } 
    if (C('LANG_AUTO_DETECT')) {
        if (isset($_GET[$varLang])) { 
            $langSet = $_GET[$varLang]; // url中设置了语言变量 
            cookie('hd_language', strtolower($langSet), 3600);
        } elseif (cookie('hd_language')) {// 获取上次用户的选择 
            $langSet = cookie('hd_language');
        } elseif (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {// 自动侦测浏览器语言
            preg_match('/^([a-z\d\-]+)/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $matches);  
            $langSet = $matches[1];
            cookie('hd_language', strtolower($langSet), 3600);
        }
        $langSet = strtolower($langSet);
        if(empty($langList) || !in_array($langSet, $langList)){ // 非法语言参数
            $langSet = C('DEFAULT_LANG');
        } 
    } 
    define('LANG_SET', strtolower($langSet));  
    $file = LAN_PATH .LANG_SET . '.php'; 
    if (is_file($file))
        L(include $file);          
}


/**
 * 字符串截取，支持中文按单个字符截取，支持自动添加省略号
 * @param $str 字符串
 * @param $start 开始
 * @param $length 长度
 * @param $code 编码
 * @param $dot 是否添加...
 */
function cut_str($str, $start, $length, $code = 'utf-8', $dot = true) {
    $str = strip_tags(trim($str));
    if ($dot === true) {
        $flag = mb_substr($str, $length, 1, $code);
        if ($flag)
            $str = mb_substr($str, $start, $length, $code) . '...';
        else
            $str = mb_substr($str, $start, $length, $code);
    }else {
        $str = mb_substr($str, $start, $length, $code);
    }
    return $str;
}


  
/**
 * @param string $string 原文或者密文
 * @param string $operation 操作(ENCODE | DECODE), 默认为 DECODE
 * @param string $key 密钥
 * @param int $expiry 密文有效期, 加密时候有效， 单位 秒，0 为永久有效
 * @return string 处理后的 原文或者 经过 base64_encode 处理后的密文
 */
function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
    $ckey_length = 4;
    $key = md5($key ? $key : C('SYS_STR'));
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';
    $cryptkey = $keya . md5($keya . $keyc);
    $key_length = strlen($cryptkey);
    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
    $string_length = strlen($string);
    $result = '';
    $box = range(0, 255);
    $rndkey = array();
    for ($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }
    for ($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }
    for ($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }
    if ($operation == 'DECODE') {
        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        return $keyc . str_replace('=', '', base64_encode($result));
    }
}

/**
 * 生成签名(参数为空时不参与签名) 
 * @param type $param 参数
 */
function sign($param = "") { 
    if ($param) {
        if (is_string($param)) {
            $param = url_to_array($param); 
        }
        $temp = array();
        if (!is_array($param))  return;
        ksort($param); //按照键名升序排序  
        foreach ($param as $key => $vo) {
            $temp[] = "{$key}={$vo}";
        }
        $str = implode("&", $temp);
        $sign = md5(C('APP_KEY') . $str . C('APP_SECRET'));
    } else {
        $sign = md5(C('APP_KEY') . C('APP_SECRET'));
    }
    return $sign;
}

//验签
function check_sign($sign,$param = "") {
    if ($sign == sign($param)) {
        return true;
    }
    return false;
}

//url参数转数组
function url_to_array($query) {
    $queryParts = explode('&', $query);
    $params = array();
    foreach ($queryParts as $param) {
        $item = explode('=', $param);
        $params[$item[0]] = $item[1];
    }
    return $params;
}


/**
 * 获取输入参数 支持过滤和默认值
 * 使用方法:
 * <code>
 * I('id',0); 获取id参数 自动判断get或者post
 * I('post.name','','htmlspecialchars'); 获取$_POST['name']
 * I('get.'); 获取$_GET
 * </code>
 * @param string $name 变量的名称 支持指定类型
 * @param mixed $default 不存在的时候默认值
 * @param mixed $filter 参数过滤方法
 * @param mixed $datas 要获取的额外数据源
 * @return mixed
 */
function I($name, $default = '', $filter = null, $datas = null) {
    static $_PUT = null;
    if (strpos($name, '/')) { // 指定修饰符
        list($name, $type) = explode('/', $name, 2);
    } elseif (C('VAR_AUTO_STRING')) { // 默认强制转换为字符串
        $type = 's';
    }
    if (strpos($name, '.')) { // 指定参数来源
        list($method, $name) = explode('.', $name, 2);
    } else { // 默认为自动判断
        $method = 'param';
    }
    switch (strtolower($method)) {
        case 'get' :
            $input = & $_GET;
            break;
        case 'post' :
            $input = & $_POST;
            break;
        case 'put' :
            if (is_null($_PUT)) {
                parse_str(file_get_contents('php://input'), $_PUT);
            }
            $input = $_PUT;
            break;
        case 'param' :
            switch ($_SERVER['REQUEST_METHOD']) {
                case 'POST':
                    $input = $_POST;
                    break;
                case 'PUT':
                    if (is_null($_PUT)) {
                        parse_str(file_get_contents('php://input'), $_PUT);
                    }
                    $input = $_PUT;
                    break;
                default:
                    $input = $_GET;
            }
            break;
        case 'path' :
            $input = array();
            if (!empty($_SERVER['PATH_INFO'])) {
                $depr = C('URL_PATHINFO_DEPR');
                $input = explode($depr, trim($_SERVER['PATH_INFO'], $depr));
            }
            break;
        case 'request' :
            $input = & $_REQUEST;
            break;
        case 'session' :
            $input = & $_SESSION;
            break;
        case 'cookie' :
            $input = & $_COOKIE;
            break;
        case 'server' :
            $input = & $_SERVER;
            break;
        case 'globals' :
            $input = & $GLOBALS;
            break;
        case 'data' :
            $input = & $datas;
            break;
        default:
            return null;
    }
    if ('' == $name) { // 获取全部变量
        $data = $input;
        $filters = isset($filter) ? $filter : C('DEFAULT_FILTER');
        if ($filters) {
            if (is_string($filters)) {
                $filters = explode(',', $filters);
            }
            foreach ($filters as $filter) {
                $data = array_map_recursive($filter, $data); // 参数过滤
            }
        }
    } elseif (isset($input[$name])) { // 取值操作
        $data = $input[$name];
        $filters = isset($filter) ? $filter : C('DEFAULT_FILTER');
        if ($filters) {
            if (is_string($filters)) {
                if (0 === strpos($filters, '/') && 1 !== preg_match($filters, (string) $data)) {
                    // 支持正则验证
                    return isset($default) ? $default : null;
                } else {
                    $filters = explode(',', $filters);
                }
            } elseif (is_int($filters)) {
                $filters = array($filters);
            }

            if (is_array($filters)) {
                foreach ($filters as $filter) {
                    if (function_exists($filter)) {
                        $data = is_array($data) ? array_map_recursive($filter, $data) : $filter($data); // 参数过滤
                    } else {
                        $data = filter_var($data, is_int($filter) ? $filter : filter_id($filter));
                        if (false === $data) {
                            return isset($default) ? $default : null;
                        }
                    }
                }
            }
        }
        if (!empty($type)) {
            switch (strtolower($type)) {
                case 'a': // 数组
                    $data = (array) $data;
                    break;
                case 'd': // 数字
                    $data = (int) $data;
                    break;
                case 'f': // 浮点
                    $data = (float) $data;
                    break;
                case 'b': // 布尔
                    $data = (boolean) $data;
                    break;
                case 's':   // 字符串
                default:
                    $data = (string) $data;
            }
        }
    } else { // 变量默认值
        $data = isset($default) ? $default : null;
    }
    is_array($data) && array_walk_recursive($data, 'hd_filter');
    return $data;
}

//读取接口配置文件
function get_config() {
    $lan = 'cn';
    if (LANG_SET == 'en-us') {
        $lan = 'en';
    }
    $sign = sign(array(
        'lan'=>$lan
    ));
    $rs = send(C('API_URL').'config/get', array(
        'appkey' => C('APP_KEY'),
        'lan' => $lan,
        'sign' => $sign
    ));  
    if(isset($rs['status']) && $rs['status']==0){
        return $rs['data'];
    }
    return; 
}

/**
 * 获取页面seo
 * @param type $default  
 * @param type $val 
 * @return array
 */
function get_seo($default=array(),$val=''){
    $rs = send(C('API_URL')."seo/get", array(
        'appkey'=>C("APP_KEY"),
        'url'=>URL,
        'val'=>$val,
    ));  
    if(isset($rs['status']) && $rs['status']===0){
        return $rs['data'];
    }  
    return $default;
}
