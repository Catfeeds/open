<?php
namespace app\admin\controller;

use app\admin\common\Base;
use app\admin\model\JxMiun;
use think\Db;

/**
*  文章管理
*/
class Article extends Base
{
	
	// function __construct()
	// {
	// 	# code...
	// }

	/**
	 * [articlelist 文章列表]
	 * @return [type] [description]
	 */
	public function articlelist(){


		$pindex = empty(input('get.pindex'))?1:input('get.pindex');
		$artId = input('get.artclass');  //过来的页数
		$psizeIntablet = empty(input('get.psizeInt'))?10:input('get.psizeInt');  //一页显示
		$artclass = Db('shop_artcleclass')->field('ac_id,ac_title')->select();
		$mm = "select shop_articlelist.Id,shop_articlelist.al_title,shop_articlelist.al_static,shop_articlelist.al_time,shop_artcleclass.ac_title from shop_articlelist,shop_artcleclass where ";
		if (empty($artId)) {
			$m = '';
		}else{
			$m = "shop_articlelist.ac_id = ".$artId." and ";
		}
		$nn = "shop_articlelist.ac_id = shop_artcleclass.ac_id order by Id desc limit ".(($pindex-1)*$psizeIntablet).",".$psizeIntablet;
		$nm = "shop_articlelist.ac_id = shop_artcleclass.ac_id";
		// $mm = ;
		// var_dump($mm);die;
		$artlist = Db::query($mm.$m.$nn);
		// var_dump($artlist);die;
		$sum = Db::query($mm.$m.$nm);
		$this->assign([
				'artclass' => $artclass,
				'artlist' => $artlist,
				'pindex' => $pindex,
				'psizeIntablet' => $psizeIntablet,
				'datanum' => count($sum),
				'artId' => $artId,
				]);
		return view();
	}

	public function articlelist_edit(){

		$list = Db('shop_articlelist')->where('Id',input('get.name'))->find();
		$artclass = Db('shop_artcleclass')->select();
		$this->assign([
				'artclass' => $artclass,
				'list' => $list,
				'id' => input('get.name') == "add"?'0':input('get.name'),
				]);
		return view();
	}
	public function articlelist_insert(){
		$info = JxMiun::artiListAdd(json_decode(input('post.data'),true),input('post.info'),input('post.id'));
		if ($info == "1") {
			$this->ajaxReturn('1',"success :)",'1');
		}else{
			$this->ajaxReturn('0',$info,'1');
		}
	}

	/**
	 * [categorylist 分类列表]
	 * @return [type] [description]
	 */
	public function categorylist(){
		$data = Db('shop_artcleclass')->select();
		$this->assign(['data' => $data]);
		return view();
	}

	public function ajaxCategoryUpdate(){
		$data = JxMiun::CategoryUpdate(json_decode(input('post.data')),input('post.info'));
		$this->ajaxReturn($data[0],$data[1],'0');
	}


	/**
	 * [linkList 友情链接列表]
	 * @return [type] [description]
	 */
	public function linkList(){
		$data = Db('shop_link')->order('link_order')->select();
		$this->assign([
					'data' => $data,
				]);
		return view();
	}

	public function ajaxlinkListMou(){
		$data = JxMiun::linkCDU(json_decode(input('post.data'),true));
		 $this->ajaxReturn($data[0],$data[1],'0');
		// die;
	}
}