<?php

namespace app\admin\model;

use think\Session;
use think\Model;
use Exception;
use app\common\random;

/**
* 
*/
class Upload extends Model
{
	/**
	 * [uoload 上传函数]
	 * @param  [type] $type [类型 图片或者视频]
	 * @param  [type] $file [文件名]
	 * @return [type]       [返回json数据]
	 */
	public static function uoload_cache($type, $fileinfo)
	{
		$random = new random();
		$Numran = $random->getRandChar();
		$code = 0;
		$msg = "";
		$data = "";
		if (empty($fileinfo)) {
			$msg = "图片不存在，请重新上传";
		}else{
	         // 获取图片的后缀
	        $suff = explode('.',$fileinfo['name']);
			switch ($type) {
				case 'img':
			
		        //设置名称
				//创建目录
			$goodsFile = ROOT_PATH.'public\static\image\goods\goods_img\img\\'.date('Ymd',time()).'\\';
			if (!file_exists($goodsFile)){
	            mkdir ($goodsFile,0777,true);
	        }
		        $new_img = $Numran.substr(date('Ymd',time()),3).substr(time(),2).'.'.$suff[count($suff)-1];
		        $goodsFile = $goodsFile.basename($new_img);
		       move_uploaded_file($fileinfo['tmp_name'],$goodsFile);
					$data = substr($goodsFile,strpos($goodsFile,'static')-1);;
		       if (empty($goodsFile)) {
					$msg = "图片上传失败，请重新上传";
				}else{
					$code = 1;
					$msg = $new_img;
				}
				break;
				case 'video'://创建目录
			
					$goodsFile = ROOT_PATH.'public\static\image\goods\goods_img\video\\'.date('Ymd',time()).'\\';
					if (!file_exists($goodsFile)){
			            mkdir ($goodsFile,0777,true);
			        }
					$new_img = $Numran.substr(date('Ymd',time()),3).substr(time(),2).'.'.$suff[count($suff)-1];
			        $goodsFile = $goodsFile.basename($new_img);
			       move_uploaded_file($fileinfo['tmp_name'],$goodsFile);
					$data = substr($goodsFile,strpos($goodsFile,'static')-1);;
			       if (empty($goodsFile)) {
						$msg = "视频上传失败，请重新上传";
					}else{
					$code = 1;
					$msg = $new_img;
				}
					break;
				case 'upload_speclist':
					$goodsFile = ROOT_PATH.'public\static\image\goods\goods_img\thumb\\'.date('Ymd',time()).'\\';
						if (!file_exists($goodsFile)){
				            mkdir ($goodsFile,0777,true);
				        }

			$new_img = $Numran.substr(date('Ymd',time()),3).substr(time(),2).'.'.$suff[count($suff)-1];
			$new_img_mid = $Numran.substr(date('Ymd',time()),3).substr(time(),2).'_mid.'.$suff[count($suff)-1];
			$new_img_small = $Numran.substr(date('Ymd',time()),3).substr(time(),2).'_small.'.$suff[count($suff)-1];

			$img = $goodsFile.basename($new_img);
			$img_mid = $goodsFile.basename($new_img_mid);
			$img_small = $goodsFile.basename($new_img_small);

			$image = \think\Image::open($fileinfo['tmp_name']);    // mid

			$image->thumb(800, 800)->save($img); 
		    $imagemid = \think\Image::open($fileinfo['tmp_name']);    // mid
		    $imagemid->thumb(350, 350)->save($img_mid);   // mid
		    $imagesmall = \think\Image::open($fileinfo['tmp_name']);    // _small
		    $imagesmall->thumb(60, 60)->save($img_small);   // _small

					    // $image->thumb(30, 30)->save($goodsFile);
					    // $image->thumb(30, 30)->save($goodsFile); 
						$data = substr($img,strpos($img,'static')-1);;
				       if (empty($img)) {
							$msg = "图片上传失败，请重新上传";
						}else{
						$code = 1;
						$msg = "success";
					}
					break;
				default:
					$msg = "出错";
					break;
			}
		}
		$array = array("code"=>$code,"msg"=>$msg,"data"=>$data);
		return json_encode($array);
	}

	/**
	 * [ajaxMessageDelete 删除异步上传error 数据 (图片，视频)]
	 * @param  [type] $type [类型]
	 * @param  [type] $data [数据（路径，文件名，格式）]
	 * @return [type]       [返回值 0：成功 1：失败 -1：文件出错]
	 */
	public function ajaxMessageDelete($type, $data){
		return json_encode($data);
	}
}