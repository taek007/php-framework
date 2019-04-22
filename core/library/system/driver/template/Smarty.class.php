<?php

namespace system\driver\template; 
require_once EXTEND_PATH . 'smarty/Smarty.class.php'; 
/**
 * Smarty驱动 留用
 * @author  zh 2681674909@qq.com 2015-04-14
 */
class Smarty {

    private $smarty = null;  
    /**
     * 模板主题
     * @var theme
     * @access protected
     */
    protected $theme = '';

    public function __construct() {
        if (!$this->smarty) {
            $this->smarty = new \Smarty();
        }
    }

    public function init($tpl = '') {
        $tmplatefile = $this->parseTemplate($tpl);
        $this->smarty->caching = C("TPL_CACHE_ON");
        $this->smarty->cache_lifetime = C("TPL_CACHE_TIME");
        $this->smarty->template_dir = TMPL_PATH; //设置模板目录
        $this->smarty->compile_dir = C("TPL_COMPILE_PATH"); //设置编译目录
        $this->smarty->cache_dir = C("TPL_CACHE_PATH"); //缓存文件夹
        $this->smarty->left_delimiter = C("TMPL_L_DELIM");
        $this->smarty->right_delimiter = C("TMPL_R_DELIM"); 
        return $tmplatefile;
    }

    /**
     * 模板变量赋值
     * @access public
     * @param mixed $name
     * @param mixed $value
     */
    public function assign($name, $value = '') {
        $this->smarty->assign($name, $value);
    }

    /**
     * 加载模板和页面输出 可以返回输出内容
     * @access public
     * @param string $templateFile 模板文件名 
     * @return mixed
     */
    public function display($templateFile = "",$cache_id=null) {
        $tmplatefile = $this->init($templateFile); 
        $this->smarty->display($tmplatefile,$cache_id);
    }
    
    
    /**
     * 缓存检测
     * @param type $templateFile
     * @return type
     */
    public function isCached($templateFile="",$cache_id=null){
        $tmplatefile = $this->init($templateFile);
        return $this->smarty->isCached($tmplatefile,$cache_id);
    }

    /**
     * 清除缓存
     * @param type $templateFile
     * @param type $cache_id
     * @return type
     */
    public function clearCache($templateFile="",$cache_id=null){
        $tmplatefile = $this->init($templateFile);
        return $this->smarty->clearCache($tmplatefile,$cacheid);
    }
    
    
    public function clearAll(){
        $this->init();
        $this->smarty->clearAllCache();
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
        $file = THEME_PATH . $template . C('TPL_SUFFIX');
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
        $theme = $this->getTemplateTheme();
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
    private function getTemplateTheme() {
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
