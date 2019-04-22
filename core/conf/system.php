<?php
/**
 * leaf
 * 系统配置文件
 * @author Heng.zhang at 2015-04-09
 */
return  array( 
     //系统设置  
    'SYS_STR'=>'@2014%%*&*#0769@',
    'DEFAULT_TIMEZONE'=>'PRC', //时区设置 
    'LANG_SWITCH_ON'=>false,   //开启多语言切换功能
    'LANG_AUTO_DETECT'=>false,   //自动检测客户端语言
    'DEFAULT_LANG'=>'zh-cn',     //默认语言包
    'VAR_LANGUAGE'=>'l',
    'LANG_LIST'=>'zh-cn,en-us',
    
    'FONT_PATH'=>WEB_ROOT.'public/fonts/', //字体路径
    
    /* SESSION设置 */
    'SESSION_AUTO_START'    =>  true,    // 是否自动开启Session
    'SESSION_OPTIONS'       =>  array(), // session 配置数组 支持type name id path expire domain 等参数
    'SESSION_TYPE'          =>  'Memcache', // session 驱动 除非扩展了session hander驱动
    'SESSION_PREFIX'        =>  '', // session 前缀
    'SESSION_EXPIRE'=>'',  //session过期时间 
    
    /* memcache缓存*/
    'MEMCACHE_HOST'=>'127.0.0.1',             //IP
    'MEMCACHE_PORT'=>'11211',            //端口
    'DATA_CACHE_TIMEOUT'=>false,     //连接持续（超时）时间，单位秒。默认值1秒 
    'MEMCACHED_SERVER'=>'',    
    'MEMCACHED_LIB'=>'',
    
    
    
     /* url路由设置 */  
    'URL_CASE_INSENSITIVE'  =>  true,   // 默认false 表示URL区分大小写 true则表示不区分大小写 
    'VAR_CONTROLLER'        =>  'c',    // 默认控制器获取变量
    'VAR_ACTION'            =>  'a',    // 默认操作获取变量  
    'DEFAULT_APP'        =>  'home',    // 默认应用
    'DEFAULT_CONTROLLER'    =>  'Index', // 默认控制器名称
    'DEFAULT_ACTION'        =>  'index', // 默认操作名称
    'DEFAULT_CHARSET'       =>  'utf-8', // 默认输出编码    
    "DENY_CONTROLLER"=>array("common"),        //禁止访问控制器
    'URL_PARAMS_BIND'       =>  true, // URL变量绑定到Action方法参数
    'URL_PARAMS_BIND_TYPE'  =>  0, // URL变量绑定的类型 0 按变量名绑定 1 按变量顺序绑定
    
 
    
    /* 数据缓存设置 */
    'DATA_CACHE_ON'         => false,    //开启数据缓存
    'DATA_CACHE_TIME'       =>  0,      // 数据缓存有效期 0表示永久缓存
    'DATA_CACHE_COMPRESS'   =>  false,   // 数据缓存是否压缩缓存
    'DATA_CACHE_CHECK'      =>  false,   // 数据缓存是否校验缓存
    'DATA_CACHE_PREFIX'     =>  '',     // 缓存前缀
    'DATA_CACHE_TYPE'       =>  'File',  // 数据缓存类型,支持:File|Memcache
    'DATA_CACHE_PATH'       =>  WEB_ROOT.'/cache/data',// 缓存路径设置 (仅对File方式缓存有效)
    'DATA_CACHE_KEY'        =>  '',	// 缓存文件KEY (仅对File方式缓存有效)    
    'DATA_CACHE_SUBDIR'     =>  false,    // 使用子目录缓存 (自动根据缓存标识的哈希创建子目录)
    'DATA_PATH_LEVEL'       =>  1,        // 子目录缓存级别
    
    
    
    /* Cookie设置 */
    'COOKIE_EXPIRE'         =>  0,       // Cookie有效期
    'COOKIE_DOMAIN'         =>  '',      // Cookie有效域名
    'COOKIE_PATH'           =>  '/',     // Cookie路径
    'COOKIE_PREFIX'         =>  '',      // Cookie前缀 避免冲突
    'COOKIE_SECURE'         =>  false,   // Cookie安全传输
    'COOKIE_HTTPONLY'       =>  '',      // Cookie httponly设置
    
 
 
     /* 日志设置 */
    'LOG_RECORD'            =>  false,   // 默认不记录日志
    'LOG_TYPE'              =>  'File', // 日志记录类型 默认为文件方式
    'LOG_LEVEL'             =>  'EMERG,ALERT,CRIT,ERR',// 允许记录的日志级别
    'LOG_FILE_SIZE'         =>  2097152,	// 日志文件大小限制
    'LOG_EXCEPTION_RECORD'  =>  false,    // 是否记录异常信息日志
    'LOG_PATH'=>CACHE_PATH.'logs/',      //日志路径
    
    
    /**模板*/ 
    'THEME_LIST'=>array(),                         //可用主题列表
    'TMPL_FILE_DEPR'=>'_',                        //模板文件分隔符
    'HTTP_CACHE_CONTROL'    =>  'private',        // 网页缓存控制
    'TMPL_CONTENT_TYPE'=>'text/html',             // 默认模板输出类型'
    'DEFAULT_THEME'=>'default',                   //主题
    "TMPL_DETECT_THEME"=>false,                   //自动检测
    'VAR_TEMPLATE'          =>  't',              // 默认模板切换变量
    'TPL_CACHE_PATH'=>CACHE_PATH.'tpl/',         //模板缓存路径
    'TPL_COMPILE_PATH'=>CACHE_PATH.'runtime/',   //模板编译路径
    'TPL_CACHE_ON'=>false,                        //是否开启模板缓存
    'TPL_CACHE_TIME'=> 120,                      //模板缓存时间(秒)
    'TPL_CACHE_SUFFIX'=>'.html',                //模板缓存后缀
    "TPL_SUFFIX"=>".php",                       //模板后缀
    'TMPL_L_DELIM'          =>  '{',            // 开始标记
    'TMPL_R_DELIM'          =>  '}',            // 结束标记
    
    /* 数据库设置 */
    'DB_TYPE'               =>  'mysqli', //留用
    'DB_COFING'             => 'CORE', //数据库配置
    'DB'=>array(
        "CORE"=>array( 
            'DB_HOST'               =>  '127.0.0.1', // 读服务器地址
            'DB_HOST_WRITE'         =>  '127.0.0.1',  // 写服务器
            'DB_NAME'               =>  'test',      // 数据库名
            'DB_USER'               =>  'root',      // 用户名
            'DB_PWD'                =>  'root',      // 密码
            'DB_PORT'               =>  '3306',        // 端口
            'DB_PREFIX'             =>  '',         // 数据库表前缀 
            'DB_DEBUG'  	    =>  TRUE,       // 数据库调试模式 开启后可以记录SQL日志 
            'DB_CHARSET'            =>  'utf8',      // 数据库编码默认采用utf8 
            'DB_RW_SEPARATE'        =>  false,       // 数据库读写是否分离 主从式有效 
        ),
    ), 
    
    
    //api接口相关
    'API_URL'=>'',
    'APP_KEY'=>'',
    'APP_SECRET'=>'',
);
 