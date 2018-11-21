<?php

namespace app\index\sms;

header("Content-Type:text/html;charset=utf-8");

/**
* 
*/
class SMS_dd
{
	/**
  	 * @page  叮咚短信函数
  	 * @page  $mobile               为手机号码
  	 * @page  $code                 为自定义随机数  
     * @page  $SMS_autograph        短信签名，要审核通过  
     * @page  $SMS_templateCode     短信模板ID，记得要审核通过的  
     * @page  $SMS_keyId            短信获取的accessKeyId
     * @page  $SMS_keysecret        短信获取的accessKeySecret  
  	 * 
 	 */
  	
function SMS($apikey,$mobil,$count){

	//修改为您的apikey. apikey可在官网（https://www.dingdongcloud.com)登录后获取
	$apikey = $apikey; 

	//修改为您要发送的手机号
	$mobile = $mobil; 

	$ch = curl_init();

	/* 设置验证方式 */
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept:text/plain;charset=utf-8', 'Content-Type:application/x-www-form-urlencoded','charset=utf-8'));

	/* 设置返回结果为流 */
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	/* 设置超时时间*/
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);

	/* 设置通信方式 */
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

	// 取得用户信息
	$json_data = $this->get_user($ch,$apikey);
	$array = json_decode($json_data,true);
	echo '<pre>';print_r($array);



	// 发送单条短信
	// 修改为您要发送的短信内容,需要对content进行编码
	$yzmcontent=$count;  
	$data=array('content'=>urlencode($yzmcontent),'apikey'=>$apikey,'mobile'=>$mobile);
	$json_data = $this->send_single($ch,$data);
	$array = json_decode($json_data,true);
	echo '<pre>';print_r($array);

	// //指定模版单发
	// $data = array('tplId' => '1', 'tplValue' => ('验证码').
	//     '='.urlencode('1234'), 'apikey' => $apikey, 'mobile' => $mobile);
	// print_r ($data);
	// $json_data = $this->send_tpl_single($ch,$data);
	// $array = json_decode($json_data,true);
	// echo '<pre>';print_r($array);

}

/***************************************************************************************/
//获得账户
function get_user($ch,$apikey){
    curl_setopt ($ch, CURLOPT_URL, 'https://api.dingdongcloud.com/v2/user/get');
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('apikey' => $apikey)));
    return curl_exec($ch);
}

//单条短信
function send_single($ch,$data){
    curl_setopt ($ch, CURLOPT_URL, 'https://api.dingdongcloud.com/v2/sms/single_send');
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    return curl_exec($ch);
}

//指定模版单发
function send_tpl_single($ch,$data){
    curl_setopt ($ch, CURLOPT_URL, 'https://api.dingdongcloud.com/v2/sms/tpl_single_send');
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    return curl_exec($ch);
}

}