<?php
namespace app\common\extend\qrcode;
    /**
     ** Action : 生成二维码图片
     * $para : $url : 二维码的信息
     *         $fileName : 图片名称 默认后缀 .png
     *         $level : 容错级别
     *         $size  : 设置二维码大小
     **/
public function creat_qrcode($url,$fileName,$level='L',$size='4'){
        //引入二维码类文件(这里特别提示  如果你的类文件命名 后缀为   .class.php 那么引入就是要带后缀如下图)
        // Loader::import('phpqrcode', EXTEND_PATH);
    Vendor('Phpqrcode.phpqrcode');  

        //实例化生成二维码类
        $object = new \QRcode();

        //设置生成的二维码图片属性
        $errorCorrectionLevel =intval($level) ;//容错级别
        $matrixPointSize = intval($size);//生成图片大小

        $object->png($url,$fileName.'.png', $errorCorrectionLevel, $matrixPointSize, 2);
    }