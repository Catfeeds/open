<?php
namespace app\common\admin;

use think\Session;
use think\Request;
use think\Db;

/**
* 
*/
class Upload
{
	/**
	 * [img 图片上传]
	 * @param  [type] $data     [数据]
	 * @param  [type] $type     [类型]
	 * @param  [type] $filename [文件名]
	 * @param  [type] $url      [文件路径]
	 * @return [type]           [状态]
	 */
	public function img($file,$type,$filename,$url,$chicun=array())
	{
		    //创建目录
		    $destina = ROOT_PATH;
		    $destination = 'public\static\image\\'.$url.'\\'.date('Ymd',time()).'\\';
		 //    if (!file_exists($destination)){
	  //           mkdir ($destination,0777,true);
			// }
	        // 判断上箭头
	        // 
	        	if (isset($file) == '' || $url == '' ) {
	        		$data['core'] = 0;
	        		$data['msg'] = "参数错误！";
	        		$data['url'] = "";
	        		return json_encode($data);
	        	}
			    $suffx = explode('.',$file["name"]);    //获取后缀
			    $name = date('Ymd',time()).substr(time(),3).'.'.$suffx[count($suffx)-1];    //设置名称
			    $fn = $destination.basename($name);
			    $destinationa = $destina.$fn;
			    //地址
			    $image = \think\Image::open($file['tmp_name']);    // mid
			    if (count($chicun) != 0) {
			   		$image->thumb($chicun[0], $chicun[1])->save($destinationa); 
			    }else{
			    	$image->save($destinationa); 
			    }
			    if (file_exists($destinationa)){
	        		$data['core'] = 0;
	        		$data['msg'] = "添加成功";
	        		$data['url'] = substr($fn, 7);
	        		return json_encode($data);
				}else{
	        		$data['core'] = 0;
	        		$data['msg'] = "上传图片失败！";
	        		$data['url'] = substr($fn, 7);
	        		return json_encode($data);
				}
	}

	// public function video(){

	// }

	// public function file(){

	// }
}