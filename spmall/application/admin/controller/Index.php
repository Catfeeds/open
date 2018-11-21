<?php
namespace app\admin\controller;

use Exception;
use app\admin\common\Base;
use think\Session;
use think\Request;

class Index extends Base
{
	public function welcome(Request $request)
	{
    	$admin_name = Session::get('admin_name');
    	$admin_last_time = Session::get('admin_last_time');
    	$admin_last_ip = Session::get('admin_last_ip');
		$this->assign([
    			'admin_name'=>$admin_name,
    			'admin_last_time'=>$admin_last_time,
    			'admin_last_ip'=>$admin_last_ip,
			]);
		return $this->fetch();
	}

    public function index()
    {
    	$navigate = include APP_PATH.'common/navigate.php';
    	$admin_name = Session::get('admin_name');
    	$this->assign([
    			'navigate'=>$navigate,
    			'admin_name'=>$admin_name,
    		]);
        return $this->fetch();
    }
    public function logout()
    {
    	Session::clear();
    	return register('login/index');
    }
}
