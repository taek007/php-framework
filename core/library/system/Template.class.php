<?php 
namespace system; 
/**
 * 模板处理类
 * @author  zh 2681674909@qq.com 2015-04-14
 */
class Template { 
    protected   $templateFile    =   ''; // 当前模板文件 
    public      $tVar            =   array(); // 模板变量
    public      $config          =   array(); // 模板配置 
    protected   $theme = '';          //主题

    public function __construct() {
        $this->config['cache_path']         =   C('TPL_CACHE_PATH');  //模板缓存路径
        $this->config['template_suffix']    =   C('TPL_SUFFIX');      //模板文件后缀
        $this->config['cache_suffix']       =   C('TPL_CACHE_SUFFIX'); //模板缓存文件后缀
        $this->config['tmpl_cache']         =   C('TPL_CACHE_ON');     //是否开启模板缓存
        $this->config['cache_time']         =   C('TPL_CACHE_TIME');   //模板缓存时间 
    }
 
    public function assign($name,$value) {
        $this->tVar[$name]= $value;
    }
    
    
    /**
     * 加载模板和页面输出 可以返回输出内容
     * @access public
     * @param string $templateFile 模板文件名
     * @param string $charset 模板输出字符集
     * @param string $contentType 输出类型
     * @param string $content 模板输出内容
     * @param string $prefix 模板缓存前缀
     * @return mixed
     */
    public function display($templateFile='',$charset='',$contentType='',$content='',$prefix='') {
        $content = $this->fetch($templateFile,$content,$prefix); 
        $this->render($content,$charset,$contentType); 
    }
    
    /**
     * 解析和获取模板内容 用于输出
     * @access public
     * @param string $templateFile 模板文件名
     * @param string $content 模板输出内容
     * @param string $prefix 模板缓存前缀
     * @return string
     */
    public function fetch($templateFile='',$content='',$prefix='') {
        if(empty($content)) {
            $templateFile   =   $this->parseTemplate($templateFile); 
            if(!is_file($templateFile)) E(L('_TEMPLATE_NOT_EXIST_').':'.$templateFile);
        }else{
            defined('THEME_PATH') or define('THEME_PATH', $this->getThemePath());
        } 
        ob_start();
        ob_implicit_flush(0); 
        $_content   =   $content; 
        extract($this->tVar, EXTR_OVERWRITE); 
        empty($_content)?include $templateFile:eval('?>'.$_content);  
        $content = ob_get_clean();  
        $content = strip_whitespace($content);  
        if($this->config['tmpl_cache'] && $content){ 
            $cachefile = $this->config['cache_path'].$prefix.md5(URL).$this->config['cache_suffix'];
            Storage::put($cachefile,$content);
        }
        return $content;
    }
 
    /**
     * 缓存检测
     * @param type $templateFile
     * @param type $charset
     * @param type $contentType
     * @param type $prefix
     * @return boolean
     */
    public function isCached($templateFile='',$charset='',$contentType='',$prefix=''){
        if(!$this->config['tmpl_cache']) return false;
        if($templateFile==''){
            $templateFile = md5(URL);
        }
        $cachefile = $this->config['cache_path'].$prefix.$templateFile.$this->config['cache_suffix'];
        if(file_exists($cachefile) && ((NOW_TIME-filemtime($cachefile))<$this->config['cache_time'])){ //存在没有过期的缓存
            $content = Storage::read($cachefile);
            $this->render($content,$charset,$contentType); 
            return true;
        }
        return false;
    }

    /**
     * 清除缓存
     * @param type $templateFile
     * @param type $prefix
     * @return boolean
     */
    public function clearCache($templateFile="",$prefix=""){
        if($templateFile==''){
            $templateFile = md5(URL);
        }
        $cachefile = $this->config['cache_path'].$prefix.$templateFile.$this->config['cache_suffix'];
        if(file_exists($cachefile)){
            return Storage::unlink($cachefile);
        }
        return false;
    }

    /**
     * 清除所有缓存
     */
    public function clearAll(){
        unlinkRecursive($this->config['cache_path']);
    }

    
    /**
     * 输出内容文本可以包括Html
     * @access private
     * @param string $content 输出内容
     * @param string $charset 模板输出字符集
     * @param string $contentType 输出类型
     * @return mixed
     */
    private function render($content,$charset='',$contentType=''){
        if(empty($charset))  $charset = C('DEFAULT_CHARSET');
        if(empty($contentType)) $contentType = C('TMPL_CONTENT_TYPE'); 
        header('Content-Type:'.$contentType.'; charset='.$charset);
        header('Cache-control: '.C('HTTP_CACHE_CONTROL'));  // 页面缓存控制
        header('X-Powered-By:HDCMS'); 
        echo $content;
    }
 

    /**
     * 自动定位模板文件
     * @access protected
     * @param string $template 模板文件规则
     * @return string
     */
    public function parseTemplate($template = '') {
        if (is_file($template)) {
            return $template;
        }
        $depr = C('TMPL_FILE_DEPR'); 
        $template = str_replace(':', $depr, $template);
    
        // 获取当前模块
        $module = '';
        if (strpos($template, '@')) { // 跨模块调用模版文件
            list($module, $template) = explode('@', $template);
        }  
        // 获取当前主题的模版路径
        defined('THEME_PATH') or define('THEME_PATH', $this->getThemePath($module));
      
        // 分析模板文件规则
        if ('' == $template) { // 如果模板文件名为空 按照默认规则定位 
            $template = CONTROLLER_NAME . $depr . ACTION_NAME;
        } elseif (false === strpos($template, $depr)) {
            $template = CONTROLLER_NAME . $depr . $template;
        }
           
        $file = THEME_PATH .'/'. $template . C('TPL_SUFFIX');  
        if (THEME_NAME != C('DEFAULT_THEME') && !is_file($file)) {
            // 找不到当前主题模板的时候定位默认主题中的模板
            $file = dirname(THEME_PATH) . '/' . C('DEFAULT_THEME') . '/' . $template . C('TPL_SUFFIX');
        }
        return $file;
    }

    /**
     * 获取当前的模板路径
     * @access protected
     * @param  string $appname 应用名
     * @return string
     */
    protected function getThemePath($appname = "") {
        // 获取当前主题名称 
        $theme='';
        if(strpos($appname, "#")!==false){
            list($appname,$theme)=  explode("#", $appname); 
        }
        $theme = $this->getTemplateTheme($theme); 
        if ($appname) {
            $tmplPath = WEB_ROOT . $appname . '/view/' . $theme;
        } else {
            $tmplPath = APP_PATH . 'view/' . $theme;
        }
        defined("TMPL_PATH") or define("TMPL_PATH", $tmplPath);
        return $tmplPath;
    }

    /**
     * 设置当前输出的模板主题
     * @access public
     * @param  mixed $theme 主题名称
     * @return View
     */
    public function theme($theme) {
        $this->theme = $theme;
        return $this;
    }

    /**
     * 获取当前的模板主题
     * @access private
     * @return string
     */
    private function getTemplateTheme($theme='') {   
        if($theme){
            $this->theme=$theme;
        }
        if ($this->theme) { // 指定模板主题
            $theme = $this->theme;
        } else {
            /* 获取模板主题名称 */
            $theme = C('DEFAULT_THEME');
            if (C('TMPL_DETECT_THEME')) {// 自动侦测模板主题
                $t = C('VAR_TEMPLATE');
                if (isset($_GET[$t])) {
                    $theme = $_GET[$t];
                } elseif (cookie('hd_template')) {
                    $theme = cookie('hd_template');
                }
                if (!in_array($theme, explode(',', C('THEME_LIST')))) {
                    $theme = C('DEFAULT_THEME');
                }
                cookie('hd_template', $theme, 864000);
            }
        }
        defined('THEME_NAME') || define('THEME_NAME', $theme);                  // 当前模板主题名称
        return $theme ? $theme . '/' : '';
    }

}
