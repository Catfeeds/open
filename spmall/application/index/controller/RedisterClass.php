<?php
namespace app\index\controller;

use app\index\common\Base;
use app\index\validate\Register;
use app\index\sms\SendSMS;
use app\index\sms\SMS_dd;
use app\index\model\Shop_user;
use app\common\login\Send_mail;
use think\viewport;
use think\Request;
use think\Session;
use think\Db;

/**
*	@param 这是注册是类
*
* 
*/
class RedisterClass extends Base
{
	
	//邮箱注册
    public function email_register(Request $request)
    {
    	$obj = $request->post('obj/a');  // 接受前台传进来的信息
    	$data = array();
    	$Register = new Register;        //实例化控制器
			// if ($obj['emaile_reader'] == 0) {
			// 	$data['static'] = 0;
			// 	$data['message'] = "请勾选商城协议" ;
			// }else 
			if(!captcha_check($obj['email_yzm'])){
				$data['static'] = 0;
				$data['message'] = "验证码错误" ;
		return json_encode($data);
			}else{
				//if($Register->check($obj)){
					$data = $this->email($obj['email']);
		return $data;
				// }else{
				// 	$data['static'] = 0;
				// 	$data['message'] =$Register->getError();
				// }
			}
    }


    // 显示 phpmailer 
     public function email($getmail) {
     	$name = $getmail;
     	$setmail='2697419714@qq.com';   //发件人
        $title= '商城注册验证提醒';   //标题
        $subject='商城验证';    //body
        $content='<h3>尊敬的 '.$name.' 您好</h3><br>你将在'.date('Y-m-d h:i:s').'申请验证邮箱，点击一下按钮，即可完成验证<br><button><a href="http://www.baidu.com">点击验证</a></button>'; // 内容
		$Send_mail = new Send_mail;
		//var_dump($Send_mail->send_out_mail());die;
		return $Send_mail->send_out_mail($setmail,$getmail,$title,$subject,$content);
		//var_dump($data['message']);die;
    }

//手机注册
public function mobile_register(Request $request)
    {
    	$obj = $request->post('obj/a');  // 接受前台传进来的信息
    	$data = array();
    	$Register = new Register;        //实例化控制器
			if ($obj['emaile_reader'] == 0) {
				$data['static'] = 0;
				$data['message'] = "请勾选商城协议" ;
			}else if(!captcha_check($obj['email_yzm'])){
				$data['static'] = 0;
				$data['message'] = "验证码错误" ;
			}else{
				if($Register->check($obj)){
					$data['static'] = 1;
					$data['message'] = "请前往邮箱进行验证" ;
					$this->email($obj['email']);
				}else{
					$data['static'] = 0;
					$data['message'] =$Register->getError();
				}
			}
		return json_encode($data);
    }

    // 显示 注册  0 阿里云 1 叮咚 短信调用
    public function SendSMS(Request $request)
    {
    /**
  	 * @page  阿里短信函数
  	 * @page  $mobile               为手机号码
  	 * @page  $code                 为自定义随机数  
     * @page  $SMS_autograph        短信签名，要审核通过  
     * @page  $SMS_templateCode     短信模板ID，记得要审核通过的  
     * @page  $SMS_keyId            短信获取的accessKeyId
     * @page  $SMS_keysecret        短信获取的accessKeySecret  
     * 
  	 *  SendSMS_sme($mobile,$code,$SMS_autograph,$SMS_templateCode,$SMS_keyId,$SMS_keysecret)
  	 */
  	$mobile = $request->POST('mobile');
  	$db = DB::table('shop_sms')->where('sms_catengory','0')->where('sms_static','0')->select();
		$code = "";
		for ($i=0; $i < $db[0]['sms_sum']; $i++) { 
		  		$code = $code.rand(0,9);
		  	}  	
		  if ($db[0]['sms_code'] == 0) {
		   		$SendSMS = new SendSMS;
       			$result =  $SendSMS->SendSMS_sme($mobile,$code,$db[0]['sms_autograph'],$db[0]['sms_templateCode'],$db[0]['sms_keyId'],$db[0]['sms_keySecret']);
       			if($result['Code'] == 'ok' || $result['Code'] == 'OK'){
       				$data['staic'] = 1;
       			}else{
       				$data['staic'] = 0;
       				$data['message'] = "短信发送失败，请稍后尝试";
       			}
		  }else{
		  		// $mobil = "15768225713";
        		// $apikey = "fcb4c6fcca5d70ed9dea53d3bb9d5094";
        		// $count = "【叮咚云】您的验证码是：1234";
        		// $apikey,$mobile,$content,$sendTs
		  		$SMS_dd = new SMS_dd;
      			$result = $SMS_dd->SMS($db[0]['sms_apikey'],$mobile,$db[0]['sms_count'].$code);
      			if($result != ""){
       				$data['staic'] = 1;
       			}else{
       				$data['staic'] = 0;
       				$data['message'] = "短信发送失败，请稍后尝试";
       			}
		  }
        return json_encode($data);
    }
}