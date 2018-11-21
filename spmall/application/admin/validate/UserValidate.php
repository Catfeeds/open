<?php
namespace app\admin\validate;

use think\Validate;

/**
* 
*/
class UserValidate extends Validate
{
	protected $rule = [
		'n' => 'require|unique:shop_user,user_nick',//|unique.user_nick
		'p' => 'require|min:6|max:16',
		'e' => 'email|unique:shop_user,user_email',
		'i' => ['/^1[34578]\d{9}$/'],
		'i' => 'unique:shop_user,user_mobile',
		'q' => 'min:5|max:11',
	];

	protected $message = [
		'n.require' => '用户名必填',
		'n.unique' => '用户名不允许重复',
		'p.require' => '密码必填',
		'p.min' => '密码最小值为6',
		'p.max' => '密码最大值为16',
		'e.email' => '邮箱填写错误',
		'e.unique' => '邮箱不允许重复',
		'i' => '手机号码填写错误',
		'i.unique' => '手机号码不允许重复',
		'q.min' => 'QQ号码长度不对',
		'q.max' => 'QQ号码长度不对',
	];
}