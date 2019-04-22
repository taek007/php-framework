<?php 
namespace system;
/**
 * 安全过滤
 * @author  zh 2681674909@qq.com 2015-04-14
 */
class Filter {

    public static function init() {
        $getfilter = "'|(and|or)\\b.+?(>|<|=|in|like)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";
        $postfilter = "\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";
        $cookiefilter = "\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";

        foreach ($_GET as $key => $value) {
            self::StopAttack($key, $value, $getfilter);
        }
        foreach ($_POST as $key => $value) {
            self::StopAttack($key, $value, $postfilter);
        }
        foreach ($_COOKIE as $key => $value) {
            self::StopAttack($key, $value,$cookiefilter);
        }
    }

    public static function StopAttack($StrFiltKey, $StrFiltValue, $ArrFiltReq) {
        if (is_array($StrFiltValue)) {
            $StrFiltValue = implode($StrFiltValue);
        }
        if (preg_match("/" . $ArrFiltReq . "/is", $StrFiltValue) == 1) {
            E("Notice:{$StrFiltKey} Illegal operation!");
        }
    }

}

