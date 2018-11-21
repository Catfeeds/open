<?php 
namespace app\index\validate;

use think\Validate;
/**
* 声明register 验证器
*/
class Register extends Validate
{
	//受保护的验证规则
	protected $rule = [
    'email'=>'require|email',
    'mobile'=>'require|max:11|regex:/^1[3-8]{1}[0-9]{9}$/',
    'email_pwd'=>'require|confirm:email_con_pwd',
	];

	//受保护的验证信息
	protected $msg = [
	    'email.require' => '名称必须',
	    'email'        => '邮箱格式错误',
	    'mobile.require' => '请输入手机号码',
	    'mobile.max' => '手机号码最多不能超过11个字符',
	    'mobile.regex' => '手机号码格式不正确',
	    'email_pwd.require' => '密码不存在',
	    'email_pwd.confirm'  => '两次密码不一致',
	];
}