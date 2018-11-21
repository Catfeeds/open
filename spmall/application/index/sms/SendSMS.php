<?php
namespace app\index\sms;

use Aliyun\Core\Config;  
use Aliyun\Core\Profile\DefaultProfile;  
use Aliyun\Core\DefaultAcsClient;  
use Aliyun\Api\Sms\Request\V20170525\SendSmsRequest;  
  
  /**
  * 
  */
  class SendSMS 
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
  	 */
function SendSMS_sme($mobile,$code,$SMS_autograph,$SMS_templateCode,$SMS_keyId,$SMS_keysecret){  
  // var_dump($SMS_keysecret);die;

    //这里的路径EXTEND_PATH就是指tp5根目录下的extend目录，系统自带常量。alisms为我们复制api_sdk过来后更改的目录名称  
    require_once EXTEND_PATH.'aliyun/alisms/vendor/autoload.php';  
    Config::load();             //加载区域结点配置  
  
    $accessKeyId = $SMS_keyId;  //阿里云短信获取的accessKeyId  
  
    $accessKeySecret = $SMS_keysecret;    //阿里云短信获取的accessKeySecret  
  
    //这个个是审核过的模板内容中的变量赋值，记住数组中字符串code要和模板内容中的保持一致  
    //比如我们模板中的内容为：你的验证码为：${code}，该验证码5分钟内有效，请勿泄漏！  
    $templateParam = array("code"=>$code);           //模板变量替换  
  
    $signName = $SMS_autograph; //这个是短信签名，要审核通过  
  
    $templateCode = $SMS_templateCode;   //短信模板ID，记得要审核通过的  
  
  
    //短信API产品名（短信产品名固定，无需修改）  
    $product = "Dysmsapi";  
    //短信API产品域名（接口地址固定，无需修改）  
    $domain = "dysmsapi.aliyuncs.com";  
    //暂时不支持多Region（目前仅支持cn-hangzhou请勿修改）  
    $region = "cn-hangzhou";  
  
    // 初始化用户Profile实例  
    $profile = DefaultProfile::getProfile($region, $accessKeyId, $accessKeySecret);  
    // 增加服务结点  
    DefaultProfile::addEndpoint("cn-hangzhou", "cn-hangzhou", $product, $domain);  
    // 初始化AcsClient用于发起请求  
    $acsClient= new DefaultAcsClient($profile);  
  
    // 初始化SendSmsRequest实例用于设置发送短信的参数  
    $request = new SendSmsRequest();  
    // 必填，设置雉短信接收号码  
    $request->setPhoneNumbers($mobile);  
  
    // 必填，设置签名名称  
    $request->setSignName($signName);  
  
    // 必填，设置模板CODE  
    $request->setTemplateCode($templateCode);  
  
    // 可选，设置模板参数  
    if($templateParam) {  
        $request->setTemplateParam(json_encode($templateParam));  
    }  
  
    //发起访问请求  
    $acsResponse = $acsClient->getAcsResponse($request);  
  
    //返回请求结果  
    $result = json_decode(json_encode($acsResponse),true);  
    return $result;  
  }  

}
