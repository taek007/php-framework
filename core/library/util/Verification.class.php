<?php 
namespace util;
/**
 * 验证码类
 * @version $Id$  
 */ 
class Verification { 
    private $charset = "abcdefghkmnprstuvwxyzABCDEFGHKMNPRSTUVWXYZ23456789";
    private $code;
    private $codelen = 4;
    private $width = 130;
    private $height = 50;
    private $img;
    private $font;
    private $fontsize = 20;
    private $fontcolor;

    public function __construct($width=130,$height=50,$fontsize=20) {
        $this->width=$width;
        $this->height=$height;
        $this->fontsize=$fontsize;
        $this->font = C('FONT_PATH') . 'elephant.ttf'; 
    }

    //生成随机码
    private function createCode() {
        $_len = strlen($this->charset) - 1;
        for ($i = 0; $i < $this->codelen; $i++) {
            $this->code .= $this->charset[mt_rand(0, $_len)];
        }
    }

    //生成背景
    private function createBg() {
        $this->img = imagecreatetruecolor($this->width, $this->height);
        $color = imagecolorallocate($this->img, mt_rand(157, 255), mt_rand(157, 255), mt_rand(157, 255));
        imagefilledrectangle($this->img, 0, $this->height, $this->width, 0, $color);
    }

    //生成文字
    private function createFont() {
        $_x = $this->width / $this->codelen;
        for ($i = 0; $i < $this->codelen; $i++) {
            $this->fontcolor = imagecolorallocate($this->img, mt_rand(0, 156), mt_rand(0, 156), mt_rand(0, 156));
            imagettftext($this->img, $this->fontsize, mt_rand(-30, 30), $_x * $i + mt_rand(1, 5), $this->height / 1.4, $this->fontcolor, $this->font, $this->code[$i]);
        }
    }

    //生成线条、雪花
    private function createLine() {
        for ($i = 0; $i < 6; $i++) {
            $color = imagecolorallocate($this->img, mt_rand(0, 156), mt_rand(0, 156), mt_rand(0, 156));
            imageline($this->img, mt_rand(0, $this->width), mt_rand(0, $this->height), mt_rand(0, $this->width), mt_rand(0, $this->height), $color);
        }
        for ($i = 0; $i < 100; $i++) {
            $color = imagecolorallocate($this->img, mt_rand(200, 255), mt_rand(200, 255), mt_rand(200, 255));
            imagestring($this->img, mt_rand(1, 5), mt_rand(0, $this->width), mt_rand(0, $this->height), '*', $color);
        }
    }

    //输出
    private function outPut() {
        header('Content-type:image/png');
        imagepng($this->img);
        imagedestroy($this->img);
    }

    //对外生成
    public function show() {
        $this->createBg();
        $this->createCode();
        $this->createLine();
        $this->createFont();
        $this->save();
        $this->outPut();
    }

    //获取验证码
    public function getCode() {
        return strtolower($this->code);
    }
    
    //保存验证码
    private function save(){
        session('code', $this->getCode()); 
    }

    //检查验证码
    public function check($code){
        if(session('code')==strtolower($code)){
            return true;
        } 
        return false;
    }

}
