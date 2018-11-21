<?php
namespace app\admin\controller;

use think\Controller;
use think\Session;
use think\Request;
use think\Db;
use Exception;
use app\common\admin\Log;
use app\admin\model\Admin;
/**
*  @param 登录类
*  
*/
class Login extends Controller
{

	public function index(Request $request)
	{
		return $this->fetch('login');

	}

	public function register(Request $request)
	{
		$data = $request->post('data');
		$arr = json_decode($data, true);
		$mapp = ['admin_name' => $arr['username']];
		$max = Admin::get($mapp);
		if (is_null($max)) {  //为空
			$mon['static'] = 0;
			$mon['message'] = "账号不存在！！！"; 
		}else if($max->admin_password == md5(md5($arr['password']).$max->admin_salt)){
			//  返回数据
			$mon['static'] = 1;
			$mon['message'] = "132";

			//  定义Session 
			Session::set('admin_name',$arr['username']);
			Session::set('admin_last_time',$max->admin_login_time);
			Session::set('admin_last_ip',$max->admin_login_ip);

			//向数据库更新添加
			Db::table('shop_admin')->where('admin_name',$arr['username'])->update([
					'admin_login_ip' => $request->ip(),
					'admin_login_time' => time(),
				]);
			Log::operation($request,$arr['username'],"登录后台管理");

		}else{
			$mon['static'] = 0;
			$mon['message'] = "账号或密码错误"; 
		}
		return json_encode($mon);
	}
}