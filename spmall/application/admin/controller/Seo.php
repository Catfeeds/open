<?php
namespace app\admin\controller;

use think\Session;
use think\Db;
use think\Request;
use app\admin\common\Base;
use app\common\admin\Log;
use app\common\admin\Upload;

/**
*
*	运营模块
*
* 	'shop_adver_infro'=>'广告版位',
                     'adver_infro'=>'广告管理', 
                     'coupon'=>'优惠券列表', 
                     'bannerimg'=>'首页海报',   
                     'kefu'=>'在线客服',        
*/
class Seo extends Base
{
	
	public function _initialize()
	{
		$this->auth();
	}

	/**
	 * [shop_adver_infro 广告版位]
	 * @return [type] [description]
	 */
	public function adver(){

		$start = empty(input('get.start'))?mktime(0,0,0,date('m'),date('d'),date('Y')):strtotime(input('get.start').' 00:00:00');
		$end = empty(input('get.end'))?mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1000:strtotime(input('get.end').' 23:59:59');
		
		$limit = empty(input('get.limit'))?'10':input('get.limit'); // 页面大小
		$curr = empty(input('get.curr'))?'1':input('get.curr');  // 当前页

		$data = Db::table('shop_adver')
				->alias('a')
				// ->where('a.adver_time','>',$start)
				// ->where('a.adver_time','<',$end)
				->page($curr,$limit)
				->select();

		$this->assign([
				'data'=>$data,
				'datanum'=>count($data),
				'limit'=>$limit,
				'curr'=>$curr,
				'start'=>$start,
				'end'=>$end,
			]);
		return view('adver');
	}

	/**
	 * [adverAddEdit 广告位的添加编辑]
	 * @return [type] [description]
	 */
	public function adverAddEdit(){

		$data = input('get.id')==0?null:Db::table('shop_adver')->where('adver_id',input('get.id'))->find();
		$chicun =  null;
		if($data != '' || $data != null){
			$chicun = explode("*", $data['adver_specif']);
		}
		$this->assign([
				'data'=>$data,
				'chicun'=>$chicun,
				'id' => input('get.id'),
			]);
		return view('adver_a_e');
	}


	/**
	 * [adverAjaxData 广告位的数据操作]
	 * @return [type] [description]
	 */
	public function adverAjaxData(Request $request){
		switch (input('post.type')) {
			case 'ined':
				// 等于0的时候为添加
				if (input('post.id') == '0') {
					$data = json_decode(input('post.data'),true);
					$data['adver_time'] = time();
					$data['adver_specif'] = $data['adver_specif0'].'*'.$data['adver_specif1'];
					unset($data['adver_specif0']);
					unset($data['adver_specif1']);
					$data['adver_static'] = 0;
					$i = Db::table('shop_adver')->insert($data);
					if (!$i) {
						$code['code'] = 0;
						$code['message'] = "添加广告位失败";
						return $this->ajaxReturn($code);
					}
						$code['code'] = 1;
						$code['message'] = "添加广告位成功";
						//添加日志
						Log::operation($request,Session::get('admin_name'),"添加".$data['adver_name']."广告位");
						return $this->ajaxReturn($code);
				}else{
					// 编辑
					$data = json_decode(input('post.data'),true);
					$data['adver_specif'] = $data['adver_specif0'].'*'.$data['adver_specif1'];
					unset($data['adver_specif0']);
					unset($data['adver_specif1']);
					$i = Db::table('shop_adver')->where('adver_id',input('post.id'))->update($data);
					if (!$i) {
						$code['code'] = 0;
						$code['message'] = "修改广告位失败";
						return $this->ajaxReturn($code);
					}
						$code['code'] = 1;
						$code['message'] = "修改广告位成功";
						return $this->ajaxReturn($code);

				}
				break;
			
			case 'del':
				if (is_numeric(input('post.id'))) {
					$av_data = Db::table('adver_infro')
								->where('adver_id',input('post.id'))
								->count();
					if ($av_data) {
						$i = Db::table('adver')->where('adver_id',input('post.id'))->delete();
						if ($i) {
							$code['code'] = 1;
							$code['message'] = "删除成功";
						//添加日志
						Log::operation($request,Session::get('admin_name'),"删除".input('post.id')."广告位成功");
						}else{
							$code['code'] = 0;
							$code['message'] = "删除失败！！！";
						//添加日志
						Log::operation($request,Session::get('admin_name'),"删除".input('post.id')."广告位失败");
						}
					}else{
						$code['code'] = 0;
						$code['message'] = "请删除完广告位下的广告才可以删除";
					}
				}else{
						$code['code'] = 0;
						$code['message'] = "参数错误！";
				}
				return $this->ajaxReturn($code);
				break;
			
			default:
				# code...
				break;
		}
	}

	/**
	 * [adver_infro 广告管理]
	 * @return [type] [description]
	 */
	public function adver_infro(){

		$start = empty(input('get.start'))?mktime(0,0,0,date('m'),date('d'),date('Y')):strtotime(input('get.start').' 00:00:00');
		$end = empty(input('get.end'))?mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1000:strtotime(input('get.end').' 23:59:59');
		
		$limit = empty(input('get.limit'))?'10':input('get.limit'); // 页面大小
		$curr = empty(input('get.curr'))?'1':input('get.curr');  // 当前页

		$data = Db::table('shop_adver_infro')
				->alias('a')
				->join('shop_adver b','a.adver_id = b.adver_id')
				->where('a.adver_infro_crater_time','>',$start)
				->where('a.adver_infro_crater_time','<',$end)
				->field('a.*,b.adver_name')
				->page($curr,$limit)
				->select();

		$this->assign([
				'data'=>$data,
				'datanum'=>count($data),
				'limit'=>$limit,
				'curr'=>$curr,
				'start'=>$start,
				'end'=>$end,
			]);
		return view('adver_infro');
	}

	/**
	 * [adverAddEdit 广告的添加编辑]
	 * @return [type] [description]
	 */
	public function adverIae(){

		$data = input('get.id')==0?null:Db::table('shop_adver_infro')->where('adver_infro_id',input('get.id'))->find();
		$adver = Db::table('shop_adver')->field('adver_id,adver_name,adver_specif')->where('adver_static',0)->select();
		$this->assign([
				'data'=>$data,
				'adver'=>$adver,
				'id' => input('get.id'),
			]);
		return view('adver_infro_a_e');
	}


	/**
	 * [coupon 优惠券列表]
	 * @return [type] [description]
	 */
	public function coupon(){

	}

	/**
	 * [bannerimg 首页海报]
	 * @return [type] [description]
	 */
	public function bannerimg(){

	}

	/**
	 * [kefu 在线客服]
	 * @return [type] [description]
	 */
	public function kefu(){

	}


	/**
	 * [img 图片上传]
	 * @param  [type] $data     [数据]
	 * @param  [type] $type     [类型]
	 * @param  [type] $filename [文件名]
	 * @param  [type] $url      [文件路径]
	 * @return [type]           [状态]
	 */
	public function uploadImg(){
		$adver = input('post.type');
		$url = input('post.url');
		$cc = input('post.cc');
		$file = $_FILES['file'];
		$chicun = explode("*", $cc);
		if ($adver == '' || $cc == 'undefined' || $file == '' || empty($file) || !count($chicun) == 2) {
			$reubfo['core']= 0;
			$reubfo['url']= '';
	        $reubfo['msg'] = "参数错误，上传失败";
	        return json_encode($reubfo);
		}
		$up = new Upload();
		return $up->img($file,$adver,'',$url,$chicun);
	}

	// 控制器不存在是调用
	public function _empty(){
		$html = '<div style="text-align: center;margin: 0px auto;">
		<br><br><br>
        <div style="font-size: 48px;
    line-height: 50px;
    margin-bottom: 50px;">
            o(╥﹏╥)o
        </div>
        <div style="    font-size: 64px;
    line-height: 80px;">
            404
        </div>
        <div style="    font-size: 36px;
    line-height: 72px;
    font-weight: 700;">
            页面找不到了
        </div>
        <div class="return">
            <a href="https://www.csdn.net">返回首页</a>
        </div>
    </div>';
		return $html;
	}

}