<?php 
namespace util;
/**
 * token 防刷新提交，安全验证类
 * @author zh
 */
class Token { 
    
    private static $token_name="__hash__";

    /**
     * 生成表单
     * @return string
     */
    public static function createInput(){ 
        list($tokenName,$tokenKey,$tokenValue)=  self::getToken();
        $input_token = '<input type="hidden" id="'.$tokenName.'" name="'.$tokenName.'" value="'.$tokenKey.'_'.$tokenValue.'" />';
        return $input_token;
    }

    /**
     * 生成token
     * @return type
     */
    private static function getToken(){ 
        $tokenType  ='md5';
        if(!session(self::$token_name)) {
            session(self::$token_name,array());
        } 
        $tokenKey   =  md5($_SERVER['REQUEST_URI']);
        $data = session(self::$token_name);
        if(isset($data[$tokenKey])) { 
            $tokenValue =$data[$tokenKey];
        }else{
            $tokenValue = $tokenType(microtime(TRUE));
            $data[$tokenKey]   =  $tokenValue;
            session(self::$token_name,$data);
        }
        return array(self::$token_name,$tokenKey,$tokenValue); 
    }

    
    /**
     * 表单令牌验证 
     * @param type $data post数据
     * @param type $unset 是否 验证完成销毁session
     * @return boolean true表示成功通过
     */
    public static function checkToken($data,$unset=true) {   
        if (!isset($data[self::$token_name]) || !session(self::$token_name)) { // 令牌数据无效
            return false;
        }  
        list($key, $value) = explode('_', $data[self::$token_name]);
        $data = session(self::$token_name);
        if (isset($data[$key]) && $value && $data[$key] === $value) { // 防止重复提交
            if($unset){
                unset($data[$key]); 
                session(self::$token_name,$data);
            }
            return true;
        } 
        return false;
    }
}