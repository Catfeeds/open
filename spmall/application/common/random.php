<?php
namespace app\common;

/**
* 
*/
class random
{
	
	/**
 * 随机生成字符串
 * @param int $length
 * @return null|string
 */
	public function getRandChar($length = 16){
	  $str = null;
	  $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz-_=+!";
	  $max = strlen($strPol)-1;
	  
	  for($i=0;$i<$length;$i++){
	    $str.=$strPol[rand(0,$max)]; //rand($min,$max)生成介于min和max两个数之间的一个随机整数
	  }
	  return $str;
	}

	public function MD5Salt(){
		// md5(md5($data['p']).md5($salt)+101)
	}
}