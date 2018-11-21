<?php

namespace app\index\controller;

use app\index\common\Base;
use think\Session;
use think\Request;


/**
*  @param 6-9  作为商品控制器交换到user 用户信息的控制器
* 
*/
class RouteUser extends Base
{
	
	function __construct()
	{
		$user = Session::get('user_id');
		if (!isset($user)) {
			return $this->redirect('login');
		}
	}

	//购物车
	public function shopcart(Request $request)
	{
		$user = $request->get('user');
		return $this->redirect('shopcart/id/'.$user);
	}

	// 路由交换转换
	public function index(Request $request)
	{
		$person = $request->get('person');
		$user = $request->get('user');
		$data = $request->get('data');
		return $this->redirect('./user/'.$person.'/'.substr($user*$data,2,8).$user);
	}
}