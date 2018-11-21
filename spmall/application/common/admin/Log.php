<?php
namespace app\common\admin;

use think\Session;
use think\Request;
use think\Controller;
use think\Db;

/**
*
*	@param 日志表
* 
*/
class Log extends Controller
{
	/**
	 *
	 * @param  [type] $request   [对象]
	 * @param  [type] $admin     [管理员]
	 * @param  [type] $operation [操作]
	 *
	 */
	public static function operation($request,$admin,$operation){
		$map = ['admin_name' => $admin,'log_info'=>$operation,'log_ip'=>$request->ip(),'log_time'=>time()];
		Db::table('shop_admin_log')->insert($map);
	}
}