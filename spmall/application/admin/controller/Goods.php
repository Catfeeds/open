<?php
namespace app\admin\controller;

use app\admin\common\Base;
use app\admin\model\Goods_cate;
use app\admin\model\Db_speclist;
use app\admin\model\Brand_class;
use app\admin\model\Attribute_class;
use app\admin\model\Goods_data;
use app\admin\model\Upload;   // 上传接口
use Exception;
use think\Db;
use think\Cache;
use think\Session;
use think\Request;
use app\common\admin\Log;
use app\common\admin\speclist;
/**
*
*	@param 商品分类  class
* 
*/
class Goods extends Base
{
	public function categoryList()
	{
			$catelist = json_decode(Goods_cate::goos_list__handel(Db::table('shop_goods_category')->order('cate_id desc')->select()),true);
		$this->assign([
				'catelist' => $catelist,
				'list' => Db::table('shop_goods_category')->select(),
			]);
		return $this->fetch();
	}

	//添加子栏目
	public function subsection(Request $request)
	{	
		$cate = Db::table('shop_goods_category')->where('cate_id',$request->get('column'))->select();
		// var_dump($aa);die;
		$this->assign('cate',$cate);
		return $this->fetch();
	}
	//编辑栏目
	public function edit_column(Request $request)
	{	
		$cate = Db::table('shop_goods_category')->where('cate_id',$request->get('column'))->select();
		$this->assign('cate',$cate);
		return $this->fetch();
	}
	//添加栏目
	public function sreach(Request $request)
	{
		$da['static'] = 1;
		$da['message'] = "插入失败";
		$bb = 0;
		$data = json_decode($request->post('data'), true);
		$catelist = json_decode(Goods_cate::goos_list__handel(Db::table('shop_goods_category')->select()),true);
		for($i = 0; $i< count($catelist[0]);$i++){
			if($catelist[0][$i]['cate_name'] == trim($data['cate_name'])){
				$da['static'] = 0;
				$da['message'] = "已有此栏目";
			}
		}
		if (trim($data['cate_name']) == '' || trim($data['cate_name']) == null) {
			$da['static'] = 0;
			$da['message'] = "请输入栏目";
		}
		if($da['static'] == 1){
			$abc = 0;
			for ($i=0; $i < count($catelist[0]); $i++) { 
				if ((int)substr($catelist[0][$i]['parent_id_path'], 2) >
					$abc) {
					$abc = (int)substr($catelist[0][$i]['parent_id_path'], 2);
				}
			}
			/**   bug   */
			$path = '0_'.($abc+1);
			$insert = ['cate_name'=>trim($data['cate_name']),'cate_last_name'=>trim($data['cate_name']),'parent_id'=>0,'parent_id_path'=>$path,'cate_lever'=>0,'cate_order'=>50,'cate_time'=>time(),'cate_is_hot'=>0,];
			$bb = Db::table('shop_goods_category')->insert($insert);
		}
		if ($bb > 0) {
			$da['static'] = 0;
			$da['message'] = "插入成功";
			//添加日志
			Log::operation($request,Session::get('admin_name'),"添加".$data['cate_name']."栏目");
			
		}
		return json_encode($da);
	}
	//跟新 栏目名
	public function updat_column_name(Request $request){
		$id = $request->POST('id');
		$val = $request->POST('val');
		$da['static'] = 0;
		$da['message'] = "更新成功";
			Db::table('shop_goods_category')->where('cate_id', $id)->update(['cate_name' => $val]);
		return json_encode($da);
	}
	//编辑 栏目名
	public function updat_column(Request $request){
		$data = json_decode($request->POST('data'),true);
		$da['static'] = 0;
		$da['message'] = "更新失败";
		$max = Db::table('shop_goods_category')->where('cate_id', $data['hidden_id'])->update([
				'cate_name' => $data['cate_name'],
				'cate_last_name' => $data['cate_last_name'],
				'cate_order' => $data['cate_order'],
				'cate_is_hot' => $data['hot'],
				'cate_static' => $data['static'],
				]);
		if ($max != 0) {
				$da['static'] = 1;
				$da['message'] = "更新成功";
			}
		return json_encode($da);
	}
	//跟新 状态热门
	public function updat_column_hotstate(Request $request){
		$da['static'] = 0;
		$da['message'] = "更新成功";
		$id = $request->POST('id');
		$cate = $request->POST('cate');

		//判断是热门还是状态
		if ($cate == 'state') {
			$static = Db::table('shop_goods_category')->where('cate_id', $id)->select();
			if ($static[0]['cate_static'] == 0) {
				Db::table('shop_goods_category')->where('cate_id', $id)->update(['cate_static' => 1]);
			}else{
				Db::table('shop_goods_category')->where('cate_id', $id)->update(['cate_static' => 0]);
			}
			
		}else{
			$hot = Db::table('shop_goods_category')->where('cate_id', $id)->select();
			if ($hot[0]['cate_is_hot'] == 0) {
				Db::table('shop_goods_category')->where('cate_id', $id)->update(['cate_is_hot' => 1]);
			}else{
				Db::table('shop_goods_category')->where('cate_id', $id)->update(['cate_is_hot' => 0]);
			}
		}
		return json_encode($da);
	}
	//添加子栏目
	public function ceater_column(Request $request){
		$sum = 0;
		$da['static'] = 0;
		$da['message'] = "已有此栏目";
		$data = json_decode($request->POST('data'),true);
		//筛选过的数据
		$catelist = json_decode(Goods_cate::goos_list__handel(Db::table('shop_goods_category')->select()),true);
		//没有筛选过的数据
		$mun = Db::table('shop_goods_category')->where('cate_id',$data['hidden'])->select();
		//判断是否有重复的栏目
		if (count(Db::table('shop_goods_category')->where('cate_name',$data["cate_name"])->select()) == 0) {
			//获取家族谱
			switch (substr_count($mun[0]["parent_id_path"],'_')) {
			case '1':
				$info = true;
				for ($i=0; $i < count($catelist[1]); $i++) { 
					if (strpos(','.$catelist[1][$i]["parent_id_path"], $mun[0]["parent_id_path"])) {
						$sum ++;
					}
				}
				$parent_id = $mun[0]['cate_id'];
				$path = $mun[0]["parent_id_path"].'_'.($sum+1);
				break;
			case '2':
				$info = true;
				for ($i=0; $i < count($catelist[2]); $i++) { 
					if (strpos(','.$catelist[2][$i]["parent_id_path"], $mun[0]["parent_id_path"])) {
						$sum ++;
					}
				}
				$parent_id = $mun[0]['cate_id'];
				$path = $mun[0]["parent_id_path"].'_'.($sum+1);
				break;
			default:
				$info = false;
				break;
		}
		//判断是否满足家族的条件
			if ($info) {
				$insert = ['cate_name'=>$data["cate_name"],'cate_last_name'=>$data["cate_last_name"],'parent_id'=>$parent_id,'parent_id_path'=>$path,'cate_lever'=>0,'cate_order'=>50,'cate_time'=>time(),'cate_is_hot'=>$data["hot"],'cate_static'=>$data["static"]];
				if (Db::table('shop_goods_category')->insert($insert) != 0) {
					$da['static'] = 1;
					$da['message'] = "栏目添加成功";
					//添加日志
					Log::operation($request,Session::get('admin_name'),"添加".$data['cate_name']."栏目");
				}else{
					$da['message'] = "栏目添加失败";
				}
			}else{
				$da['message'] = "家族条件有误";
			}
			
		}
		return json_encode($da);
	}

	//删除栏目   单个 1 或者多选 0
	public function del_column(Request $request){
		$delect = 0;
		$da['static'] = 0;
		$da['message'] = "删除失败";

		$type = $request->POST('type');
		$data = $request->POST('data');

		//单个删除
		if ($type == 'del') {
			$oo = Db::table('shop_goods_category')->where('cate_id',$data)->select();
			$open = Db::table('shop_goods_category')->where('parent_id_path','LIKE','%'.$oo[0]["parent_id_path"].'%')->select();
			if (count($open) == 1) {
				$delect = Db::table('shop_goods_category')->where('cate_id',$data)->delete();
			}else{
				$da['message'] = "此栏目下还有子栏目";
			}
		}else{    //  多个删除
			// $data = json_decode($request->POST('data'));
			// var_dump($data);die;
			$da['message'] = "多个删除不适合此";
		}
		if ($delect != 0) {
			$da['static'] = 1;
			$da['message'] = "删除成功";
		}
		return json_encode($da);
	}
	/*
	@  speclist   商品规格
	 */
	
	public function speclist(Request $request){
		// 调用解析的类
		$adb = new speclist;
		$bb = 0;
		$a = (int)$request->get('page');     //现在所在的页数
		$page_list = $request->get('page_list');     // 一页显示多少行
		$seacer = $request->get('seacer');   //前台模型
		if ($seacer == null) {
			$data = Db::query("select * from shop_goods_type,shop_speclist,shop_speclist_item where shop_goods_type.type_id = shop_speclist.type_id and shop_speclist.speclist_id = shop_speclist_item.speclist_id");
		}else{
			$data = Db::query("select * from shop_goods_type,shop_speclist,shop_speclist_item where shop_goods_type.type_id = shop_speclist.type_id and shop_speclist.speclist_id = shop_speclist_item.speclist_id and type_name='$seacer'");
		}
		//  模型数据
		$dataa = Db::table('shop_goods_type')->select();
		for ($i=0; $i < count($dataa); $i++) { 
			$type[$i] = $dataa[$i]["type_name"];
		}

		// 判断前台的数据是否出错
		if (!isset($page_list) || $page_list=='') {
			$page_list = 10;
		}

		if (count($data) != 0) {

		    	$page = (int)ceil(count($adb->spec_list($data))/$page_list);
				if (!isset($a) || $a > $page || !is_int($a) ||$a == 0) {
					$a = 1;
				}
		    	$data = array_reverse($adb->spec_list($data));   // 获取全部数据进行分页显示
		for ($i = $page_list*($a-1) ; $i < $page_list*$a; $i++) { 
			if (isset($data[$i])) {
				$datalist[$bb] = $data[$i];
				$bb ++;
			}else{
				break;
			}
		}
		    }else{
		    	$page = 1;
		    	$datalist = 'null';
		    }   //一共有多少页

		$this->assign([
				'data' => $datalist,
				'type' => $type,
				'page' => $page,
				'a' => $a,
				'seacer' => $seacer,
				'page_list' => $page_list
			]);
		return $this->fetch();
	}
	/**
	 * @basename( 添加规格)
	 */
	public function speclist_add()
	{
		// 调用解析的类
		$adb = new speclist;
		//  模型数据
		$dataa = Db::table('shop_goods_type')->select();
		for ($i=0; $i < count($dataa); $i++) { 
			$type[$i] = $dataa[$i]["type_name"];
		}
		$this->assign([
				'type' => $type,
			]);
		return $this->fetch();
	}
	
		/**
		 * 前台数据包 (规格刷入数据库)
		 * @mun 封装全部的规格项
		 */
	public function speclist_add_insert(Request $request)
	{
		//初始化所需要的变量
		$data['static'] = 0;
		$speclist_id = 0;
		$check = 0;
		$data['message'] = "初始化";
		$arrar = [];

		$count = json_decode($request->get('data'), true);
		$textarea = explode('_M_m', $request->get('textarea'));

		//向数据库查询是否有相关的规格
		$type = Db::table('shop_goods_type')->where('type_name',$count['speclist'])->field('type_id')->select();
		$speclist = Db::table('shop_speclist')
		            ->where('type_id',$type[0]['type_id'])
		            ->where('speclist_name',$count['L_name'])
		            ->select();
		
		if (isset($speclist[0]['speclist_id'])){
			$data['message'] = "该模型下也有其规格。";
		}else{
			// 启动事务
				Db::startTrans();
			//循环封装规格项
				try{
			$map = ['type_id' => $type[0]['type_id'],'speclist_name' => $count['L_name'],'speclist_order' =>$count['L_order'],'search_index'=>$count['static']];
			$speclist_id = Db::table('shop_speclist')->insertGetId($map);
			//添加日志
			Log::operation($request,Session::get('admin_name'),"添加 ".$count['L_name']." 规格");
				    for ($i=0; $i < count($textarea); $i++) { 
		    			$insert = ['speclist_id' => $speclist_id,'item_count' => $textarea[$i]];
		    			$check = Db::table('shop_speclist_item')->insertGetId($insert);
		    			
					}
				    // 提交事务
				    Db::commit(); 
				    $data['static'] = 1;
				    $data['message'] = '添加规格成功';   

				} catch (\Exception $e) {
				    // 回滚事务
				    Db::rollback();
				    $data['message'] = '添加规格出错';
				}
		    
		}
			// var_dump($data);die;
		return json_encode($data);
	}
	function search_index(Request $request){
		$speclist_id = $request->post('speclist_id');
		$search_index = $request->post('search_index');
		if ($search_index == 0) {
			$search_index = 1;
		}else{
			$search_index = 0;
		}
		Db::table('shop_speclist')->where('speclist_id',$speclist_id)->update(['search_index' => $search_index]);
		
	}
		/**
		 * 删除规格
		 */
	public function speclist_delete(Request $request){
		$data['static'] = 1;
		$data['message'] = Db_speclist::speclist_delete($request->post('id'),$request->post('type'));
		return json_encode($data);
		
	}
	/**
	 * 修改规格
	 * @return [type] [description]
	 */
	function speclist_edit(Request $request)
	{// 调用解析的类
		$adb = new speclist;
		$text = '';
		//  模型数据
		$dataa = Db::table('shop_goods_type')->select();
		for ($i=0; $i < count($dataa); $i++) { 
			$type[$i] = $dataa[$i]["type_name"];
		}

		$data = Db::table('shop_speclist')->where('speclist_id',(int)$request->get('name'))->select();
		$item = Db::table('shop_speclist_item')->where('speclist_id',(int)$request->get('name'))->select();
		for ($i=0; $i < count($item); $i++) { 
			$text = $text.'_'.$item[$i]["item_count"];
		}
		$specli = Db::table('shop_goods_type')->where('type_id',$data[0]["type_id"])->select();
		// var_dump();die;
		$this->assign([
				'data' => $data,
				'type' => $type,
				'text' => substr($text,1),
				'speclist' => $specli[0]["type_name"]
			]);
		return $this->fetch();
	}
	public function speclist_edit_update(Request $request)
	{
		$da = Db_speclist::edit_update(json_decode($request->get('data'),true),$request->get('textarea'));
		if ($da == 1) {
			$data['static'] = 1;
			$data['message'] = '修改成功';
		}else{
			$data['static'] = 0;
			$data['message'] = $da;
		}
		return json_encode($data);
	}
	/*
	@  商品属性
	 */
	
	public function attribute(Request $request)
	{
		// 调用解析的类
		$adb = new speclist;
		$bb = 0;
		$a = (int)$request->get('page');     //现在所在的页数
		$page_list = $request->get('page_list');     // 一页显示多少行
		$seacer = $request->get('seacer');   //前台模型

		//  数据筛选
		if ($seacer == null) {
			$data = Db::query("select * from shop_goods_type,shop_goods_attribute where shop_goods_attribute.type_id = shop_goods_type.type_id");
		}else{
			$data = Db::query("select * from shop_goods_type,shop_goods_attribute where shop_goods_attribute.type_id = shop_goods_type.type_id and type_name='$seacer'");
		}


		//  模型数据
		$dataa = Db::table('shop_goods_type')->select();
		for ($i=0; $i < count($dataa); $i++) { 
			$type[$i] = $dataa[$i]["type_name"];
		}



		// 判断前台的数据是否出错
		if (!isset($page_list) || $page_list=='') {
			$page_list = 10;
		}


		/* 
		 *  判断数据是否有
		 */
		if (count($data) != 0) {

		    	$page = (int)ceil(count($data)/$page_list);    //一共有多少页
				if (!isset($a) || $a > $page || !is_int($a) ||$a == 0) {
					$a = 1;
				}
		    	$data = array_reverse($data);     // 获取全部数据进行分页显示
		for ($i = $page_list*($a-1) ; $i < $page_list*$a; $i++) { 
			if (isset($data[$i])) {
				$datalist[$bb] = $data[$i];
				$bb ++;
			}else{
				break;
			}
		}
		    }else{
		    	$page = 1;
		    	$datalist = 'null';
		    }   //一共有多少页

/**
 * 结束判断
 */

		// var_dump($data);
		// die;
		// var_dump($a);die;
		$this->assign([
				'data' => $datalist,
				'type' => $type,
				'page' => $page,
				'a' => $a,
				'seacer' => $seacer,
				'page_list' => $page_list
			]);
		return $this->fetch();
	}
	/**
	 * 添加属性
	 * @return [type] [description]
	 */
	public function attribute_add()
	{// 调用解析的类
		$adb = new speclist;
		//  模型数据
		$dataa = Db::table('shop_goods_type')->select();
		for ($i=0; $i < count($dataa); $i++) { 
			$type[$i] = $dataa[$i]["type_name"];
		}
		$this->assign([
				'type' => $type,
			]);
		return $this->fetch();
	}
	public function attribute_add_insert(Request $request)
	{
		// 调用解析的类
		$attr = new Attribute_class;
		$count = json_decode($request->get('data'), true);
		$mess = $attr->attribute_insert($count,$request->get('text'));
		if ($mess == 1) {
			$data['static'] = 1;
			$data['message'] = "添加成功";
			//添加日志
			Log::operation($request,Session::get('admin_name'),"添加 ".$count['L_name']." 属性");
		}else{
			$data['static'] = 0;
			$data['message'] = $mess;
		}
		return json_encode($data);
	}
		/**
		 * 删除属性
		 */
	public function attribute_delete(Request $request){
		$data['static'] = 1;
		$data['message'] = Attribute_class::attribute_delete($request->post('id'),$request->post('type'));
		return json_encode($data);
		
	}

/**
 * attribute  修改状态
 * @param  Request $request [description]
 * @return [type]           [description]
 */
function attribute_index(Request $request){
		$attribute_id = $request->post('attribute_id');
		$attribute_index = $request->post('attribute_index');
		if ($attribute_index == 0) {
			$attribute_index = 1;
		}else{
			$attribute_index = 0;
		}
		Db::table('shop_goods_attribute')->where('attribute_id',$attribute_id)->update(['attribute_index' => $attribute_index]);
		
	}
	/**
	 * 修改属性
	 * @return [type] [description]
	 */
	
	public function attribute_edit(Request $request)
	{// 调用解析的类
		$adb = new speclist;
		$text = '';
		//  模型数据
		$dataa = Db::table('shop_goods_type')->select();
		for ($i=0; $i < count($dataa); $i++) { 
			$type[$i] = $dataa[$i]["type_name"];
		}

		// 索引数据
		$data = Db::table('shop_goods_attribute')->where('attribute_id',(int)$request->get('name'))->find();
		$specli = Db::table('shop_goods_type')->where('type_id',$data["type_id"])->find();
		// var_dump($specli["type_name"]);die;
		// var_dump();die;
		$this->assign([
				'data' => $data,
				'type' => $type,
				'speclist' => $specli["type_name"]
			]);
		return $this->fetch();
	}

	public function attribute_edit_update(Request $request)
	{

		$da = Attribute_class::attribute_update(json_decode($request->get('data'),true),$request->get('textarea'));
		if ($da == 1) {
			$data['static'] = 1;
			$data['message'] = '修改成功';
		}else{
			$data['static'] = 0;
			$data['message'] = $da;
		}
		return json_encode($data);
	}

	/**
	 * [goodstypelist  商品模型 输出模板]
	 * @return [type] [description]
	 */
	public function goodstypelist(Request $request)
	{
		// 调用解析的类
		$adb = new speclist;
		$bb = 0;
		$a = (int)$request->get('page');     //现在所在的页数
		$page_list = $request->get('page_list');     // 一页显示多少行
		//  数据筛选
		$data = array_reverse(Db::table('shop_goods_type')->select());

		//  模型数据
		$dataa = Db::table('shop_goods_type')->select();
		for ($i=0; $i < count($dataa); $i++) { 
			$type[$i] = $dataa[$i]["type_name"];
		}



		// 判断前台的数据是否出错
		if (!isset($page_list) || $page_list=='') {
			$page_list = 10;
		}


		$page = (int)ceil(count($data)/$page_list);    //一共有多少页
		if (!isset($a) || $a > $page || !is_int($a) ||$a == 0) {
			$a = 1;
		}
		// $data = $adb->spec_list($data);   // 获取全部数据进行分页显示
		for ($i = $page_list*($a-1) ; $i < $page_list*$a; $i++) { 
			if (isset($data[$i])) {
				$datalist[$bb] = $data[$i];
				$bb ++;
			}else{
				break;
			}
		}
		// var_dump($data);die;
		// var_dump($data);
		// die;
		// var_dump($a);die;
		$this->assign([
				'data' => $datalist,
				'type' => $type,
				'page' => $page,
				'a' => $a,
				'page_list' => $page_list
			]);
		return $this->fetch();
	}

		/**
		 * @param  Request $request [删除模型]
		 * @return [type]           [description]
		 */
	public function goodstypelist_delete(Request $request)
	{
		$type_id = $request->post('id');
		$db = Db::table('shop_speclist')->where('type_id',$type_id)->select();
		if (count($db) > 0) {
			$data['static'] = 1;
			$data['message'] = "删除失败,模型下面还有规格项";
		}else{
			Db::startTrans();
		try {
			$index = Db::table('shop_goods_type')->where('type_id',$type_id)->delete();
			if (!$index) {
				throw new Exception("Error Processing Request");
			}

			//提交事务
			Db::commit();
			$data['static'] = 0;
			$data['message'] = "删除成功";
		} catch (Exception $e) {
			$data['static'] = 1;
			$data['message'] = "删除失败";
		}
		}
		
		return json_encode($data);
	}
	/**
	 * [goodstypelist_insert 添加模型]
	 * @return [type] [description]
	 */
	public function goodstypelist_insert(Request $request)
	{
		$type_name = $request->post('data');
		$list = Db::table('shop_goods_type')->where('type_name',$type_name)->select();
		if (count($list) != 0) {
			$data['static'] = 0;
			$data['message'] = "已有此模型";
		}else{
			Db::startTrans();
		try {
			$map = ['type_name'=>$type_name];
			$a = Db::table('shop_goods_type')->insert($map);
			if (!$a) {
				throw new Exception("添加出错");
			}
			//提交事务
			Db::commit();
			$data['static'] = 1;
			$data['message'] = "添加模型成功";//添加日志
			Log::operation($request,Session::get('admin_name'),"添加 ".$type_name." 模型");
		} catch (Exception $e) {
			
			// 回滚事务
			Db::rollback();
			$data['static'] = 0;
			$data['message'] = $e->getMessage();
			}
		}
		// var_dump();die;
		return json_encode($data);
	}

	public function goodstypelist_update(Request $request){
		$fied = $request->get('fied');
		$index = $request->get('index');

		$data['static'] = 0;

		//启动事务
		Db::startTrans();
		try {
			$a = Db::table('shop_goods_type')->where('type_id',$index)->update([
					'type_name' => $fied
				]);
			if (!$a) {
				throw new Exception("更新失败");
			}
		// 提交事务
		Db::commit();
		$data['message'] = "更新成功";
		} catch (Exception $e) {
			
			// 失败 回滚事务
			Db::rollback();
			$data['message'] = $e->getMessage();
		}

		return json_encode($data);
	}

	/**
	 * [goods 商品类]
	 * @return [type] [description]
	 */
	public function goodslist()
	{

		/****************** 参数获取  ***************************/
		$start = empty(input("get.start"))?'1000000000':strtotime(input("get.start"));    // 起止日期
		$end = empty(input("get.end"))?time():strtotime(input("get.end").' 23:59:59');    // 终止日期
		$brandIs = input("get.brand");    // 品牌
		$catelist_one = input("get.catelist_one");    // 一级分类
		$catelist_two = input("get.catelist_two");    // 二级分类
		$catelist_thr = input("get.catelist_thr");    // 三级分类
		$is_on_sale = input("get.is_on_sale");    // 上下架 empty(input("get.is_on_sale"))?'':input("get.is_on_sale")
		$Keyword = input("get.Keyword");    // 关键词

		$pcountInt = input("get.pcountInt"); //总数据
		$psizeInt = empty(input("get.psizeInt"))?'10':input("get.psizeInt"); //页面大小
		$pindex = empty(input("get.pindex"))?'1':input("get.pindex"); //当前页
		$ptotalpages = input("get.ptotalpages");// 总记录数

		/****************** 参数获取  ***************************/



		/****************** 参数处理  ***************************/
		$parent_id = Db('shop_goods_category')->field('cate_id,parent_id_path')->select();
		
		$catelist_one_path = '';
		$catelist_two_path = '';
		$catelist_thr_path = '';
		$cateOneList = '';
		$cateTwoList = '';
		foreach ($parent_id as $value) {
			if ($value["cate_id"] == $catelist_one) {
				$catelist_one_path = $value["parent_id_path"];
			}
			if ($value["cate_id"] == $catelist_two) {
				$catelist_two_path = $value["parent_id_path"];
			}
			if ($value["cate_id"] == $catelist_thr) {
				$catelist_thr_path = $value["parent_id_path"];
			}
		}

		if (empty($catelist_one)) {
			$cat_id = 'M';
		}else{
			$t = empty($catelist_two_path)?'0':$catelist_two_path;
			$m = empty($catelist_thr_path)?'0':$catelist_thr_path;
			$cat_id = $catelist_one_path.'M'.$t.'M'.$m;
			$cateOneList = $this->brandListText($catelist_one);
		}
		if (!empty($catelist_two_path) && !empty($catelist_thr_path)) {
			$cateTwoList = $this->brandListText($catelist_two_path);
		}
		$map = [
				'add_time'	=> ['>',strtotime($start)],
				'add_time'	=> ['<',$end],
				'cat_id'	=> ['like',"%".$cat_id."%"],
				'goods_name'	=> ['like',"%".urldecode($Keyword)."%"],
				];
		if (!empty($brandIs)) {
			$map['brand_id'] = $brandIs;
		}
		if (!empty($is_on_sale) || $is_on_sale == '0') {
			var_dump($is_on_sale);
			$map['is_on_sale'] = $is_on_sale;
		}
		$data = Goods_data::where($map)
		                        ->order('goods_id desc')
		                        ->page($pindex,$psizeInt)
		                        ->select();
		$dataList = Goods_data::where($map)->order('goods_id desc')->select();
			// 品牌列表
		$brand = Db('shop_brand')->order('brand_id desc')->select();
		$catelist = json_decode(Goods_cate::goos_list__handel(Db::table('shop_goods_category')->order('cate_id desc')->select()),true);

		/****************** 参数处理  ***************************/
		/**********************   输出 ***************************/
		$this->assign([
				'start' => $start,      //  起止日期
				'end' => $end,      //  终止日期
				'brandIs' => $brandIs,      //  品牌
				'catelist_one_id' => $catelist_one,      //  一级分类
				'catelist_two_id' => $catelist_two,      //  二级分类
				'catelist_thr_id' => $catelist_thr,      //  三级分类
				'is_on_sale' => $is_on_sale,      //  上下架
				'Keyword' => urldecode($Keyword),      //  关键词
				'data' => $data,      //  总数据
				'datanum' => count($dataList),   //  总记录  dataList
				'psizeIntablet' => $psizeInt,   //  页面大小
				'pindex' => $pindex,		//  当前页
				'brand' => $brand,			//  品牌
				'catelist_one' => $catelist[0],
				'catelist_two' => empty($cateOneList)?$catelist[1]:$cateOneList,
				'catelist_thr' => $cateTwoList,
			]); //empty($cateTwoList)?$catelist[2]:$cateTwoList
		return $this->fetch();
		/**********************   输出 ***************************/
	}

	/**
	 * [goods_add 添加商品]
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function goods_add(Request $request)
	{
		$Goods_data = new Goods_data();
		$goods_id = $request->get('goods_id');
		$type = empty($goods_id)?0:1;   // 0  表示新建商品  1 表示编辑商品
		if ($type == 0) {
			$emptyId = "000000";
		}else{
			$emptyId = $goods_id;
		}
		// die;
		$data = Goods_data::where('goods_id',$goods_id)->find();
		$catelist = json_decode(Goods_cate::goos_list__handel(Db::table('shop_goods_category')->order('cate_id desc')->select()),true);  // 商品分类
		$brand = Brand_class::select();
		$speclist = DB::table('shop_goods_type')->select();
		//运费模板
		$temp = Db::table('shop_freight_template')->field('template_id,template_name')->select();
		// 供应商
		$supp = Db::table('shop_suppliers')->field('suppliers_id,suppliers_name')->select();
		// var_dump($data);die;
		$dataimg = Db('shop_goods_img')->where('goods_id',$emptyId)->field('Id,img_src')->select();
		$html = $Goods_data->GetgoodsSpend($data['goods_type_id']);
		// var_dump($html);die;
		$catt = explode('M',$data['cat_id']);
		$this->assign([
			'catelist_one' => $catelist[0],
			'catelist_two' => $catelist[1],
			'catelist_thr' => $catelist[2],
			'data' => $data,
			'temp' => $temp,
			'supp' => $supp,
			'catt' => $catt,
			'brand' => $brand,
			'html' => $html,
			'dataimg' => $dataimg,
			'speclist' => $speclist,
			'emptyId' => $emptyId,
			]);
		return $this->fetch();
	}

	/**
	 * [goodsAddUpdate 商品 更新  添加函数]
	 * @param  Request $request [传值为array]
	 * @return [type]           [返回一个ajax]
	 */
	public function goodsAddUpdate(Request $request){
		$bin = Goods_data::goodsAddUpdate(json_decode($request->post('data'),true),$request);
		if ($bin == "succes") {
			# code...
			$this->ajaxReturn('1',"success :) ",'1');
		}else{
			$this->ajaxReturn('0',$bin,'0');
		}
	}

	/**
	 * [ajaxgoodsUpSt 商品状态更新]
	 * @param [type] $[id] [主键id]
	 * @param [type] $[type] [更改键名]
	 * @param [type] $[val] [更改内容]
	 * @return [type] [description]
	 */
	public function ajaxgoodsUpSt(){
		$id = input('post.id');
		$type = input('post.type');
		$val = input('post.val');
		Db('shop_goods')->where('goods_id',$id)->update([$type => $val]);
		$this->ajaxReturn(1,'成功 :)',1);
	}

	/**
	 * 筛选
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function findCity(Request $request)
	{
		$data = $request->post('data');
		$type = $request->post('type');
		$list =array();
		$an = 0;
		if ($type == 'one') {
			$catelist = json_decode(Goods_cate::goos_list__handel(Db::table('shop_goods_category')->order('cate_id desc')->select()),true);
			for ($i=0; $i < count($catelist[1]); $i++) { 
				if (strpos('-'.$catelist[1][$i]['parent_id_path'],$data)) {
					$list[$an] = $catelist[1][$i];
					$an++;
				}
			}

		$blan_list = Brand_class::select();
		}else{
			$catelist = json_decode(Goods_cate::goos_list__handel(Db::table('shop_goods_category')->order('cate_id desc')->select()),true);
			for ($i=0; $i < count($catelist[2]); $i++) { 
				if (strpos('-'.$catelist[2][$i]['parent_id_path'],$data)) {
					$list[$an] = $catelist[2][$i];
					$an++;
				}
			}
			$mm = Goods_cate::where('parent_id_path',$data)->field('cate_id')->find();
			$blan_list = Db::table('shop_brand')->where('cat_id',$mm["cate_id"])->select();
			//  返回品牌
			// $blan_list = $data;
			// $data['list'] = $list;
			// $data['brand'] = $blan_list;
			$this->ajaxReturn($blan_list,$list,'0');
		}
		// var_dump($list);die;
		return $list;
	}

	//获取商品属性对应的规格   
	public function getGoodsSpeclist(Request $request)
	{
		//  传输过来的是商品规格id
		$Goods_data = new Goods_data();
		$html = $Goods_data->GetgoodsSpend($request->get('data'));
		$this->ajaxReturn('0',$html,'0');

	}

	//添加对应的规格属性
	public function getattr_index_list(Request $request)
	{
		$Goods_data = new Goods_data();
		$html = $Goods_data->setAttr_list($request->post('id'),json_decode($request->post('data'),true));
		$this->ajaxReturn('0',$html,'0');
	}


/**
 * [brandlist_sc_img description]
 *
 *   图片删除上传
 * @param  Request $request [description]
 * @return [type]           [description]
 */

public function goods_img_delete(Request $request)
{
	$a = 0;
	$data = json_decode($request->get('data'),true);
	if (!empty($data['msg'])) {  //true
		if (file_exists(ROOT_PATH.'public'.$data['msg'])) {
			$name = explode(".",$data['msg']);
			unlink(ROOT_PATH.'public'.$data['msg']);
			unlink(ROOT_PATH.'public'.$name[0].'_mid.'.$name[1]);
			unlink(ROOT_PATH.'public'.$name[0].'_small.'.$name[1]);
			$this->ajaxReturn('0','0',0);
		}else{
			$this->ajaxReturn('0','文件不存在，请重新上传',1);
		}
	}else{
		$this->ajaxReturn('0','文件有误',2);
	}
}





	// 图片上传
	public function goods_img_up(Request $request)
	{
		if(!empty($_FILES['file'])){
		    $fileinfo = $_FILES['file'];

		    //创建目录
		    $destination = ROOT_PATH.'public\static\image\goods\goods\\'.date('Ymd',time()).'\\';
		    if (!file_exists($destination)){
            mkdir ($destination,0777,true);
        }
        // 判断上箭头
        // 
		    $suffx = explode('.',$fileinfo["name"]);    //获取后缀
		    $name = date('Ymd',time()).substr(time(),3).'.'.$suffx[count($suffx)-1];    //设置名称
		    $destinationa = $destination.basename($name);
		    // move_uploaded_file($fileinfo['tmp_name'],$destinationa);
		    //设置缩略图
		    $suffxthumb = explode('.',$name);
		    $destinationthumb = $destination;
		    $destinationthumb = $destinationthumb.basename($suffxthumb[0]);
		    // var_dump($destinationthumb.'_mid.'.$suffx[count($suffx)-1]);die;
		    //地址
		    $image = \think\Image::open($fileinfo['tmp_name']);    // mid
		    $image->thumb(800, 800)->save($destinationthumb.'.'.$suffx[count($suffx)-1]); 
		    $imagemid = \think\Image::open($fileinfo['tmp_name']);    // mid
		    $imagemid->thumb(350, 350)->save($destinationthumb.'_mid.'.$suffx[count($suffx)-1]);   // mid
		    $imagesmall = \think\Image::open($fileinfo['tmp_name']);    // _small
		    $imagesmall->thumb(60, 60)->save($destinationthumb.'_small.'.$suffx[count($suffx)-1]);   // _small
	}
		return ['code'=>1,'msg'=>substr($destinationa,strpos($destinationa,'static')-1)];
	}

/** ------------------------------------------------------------------------- */
	/**
	 *  brandlist  商品品牌
	 */

	public function brandlist(Request $request)
	{

		$data = Db::table('shop_brand')->order('brand_id desc')->select();

		$a = $request->get('page');  //现在页的位置
		$page_list = $request->get('page_list');   // 一页显示多少条数据
		if (!is_numeric($a)) {
			$a = 1;
		}
		if (!is_numeric($page_list)) {
			$page_list = 10;
		}
		//判断一页显示多少条数据是否规范
		if (empty($page_list) && $page_list != 10 && $page_list != 20 &&$page_list != 50 &&$page_list != 100 &&$page_list != 300) {
			$page_list = 10 ;
		}
		//判断页数是否规范
		if (empty($a) || $a > ceil(count($data)/$page_list)) {
			$a = ceil(count($data)/$page_list);
		}
		$array = array();
		$b = 0;
		for ($i=(($a-1)*$page_list); $i < count($data); $i++) { 
			if (empty($data[$i])) {
				break;
			}else{
				$array[$b] = $data[$i];
				$b++;
				if ($b == $page_list) {
					break;
				}
			}
		}
		$this->assign([
				'data' => $array,
				'array' => $data,
				'page' => ceil(count($data)/$page_list), // 总页数
				'a' => $a,    //当前页数
				'page_list' => $page_list,   //要显示多少页数
			]);
		return $this->fetch();
	}

	/**
	 * 添加商品品牌类
	 * @return [type] [description]
	 */
	public function brandlist_add()
	{
		$catelist = json_decode(Goods_cate::goos_list__handel(Db::table('shop_goods_category')->order('cate_id desc')->select()),true);
		// var_dump($catelist[0]);die;
		$this->assign([
			'catelist_one' => $catelist[0],
			'catelist_two' => $catelist[1],
			'catelist_thr' => $catelist[2],
			]);
		return $this->fetch();
	}

	public function brandlist_sx()
	{

		return $this->brandListText(input("post.data"));
	}

	public function brandListText($bb){
		//定义变量
		$list = array();
		$nm = 0;
		//  请求数据库数据
		$catelist = json_decode(Goods_cate::goos_list__handel(Db::table('shop_goods_category')->order('cate_id desc')->select()),true);
		$parent_id_path = $bb;
		if(is_numeric($parent_id_path)){
			$parent_id_path = Db('shop_goods_category')->where('cate_id',$parent_id_path)->field('parent_id_path')->find();
			$parent_id_path = $parent_id_path["parent_id_path"];
		}
		if (count(explode('_',$parent_id_path)) == 2) {
			for ($i=0; $i < count($catelist[1]); $i++) { 
				if (strpos('_'.$catelist[1][$i]['parent_id_path'],$parent_id_path)) {
					$list[$nm] = $catelist[1][$i];
					$nm++;
				}
			}
		}else if (count(explode('_',$parent_id_path)) == 3) {
			for ($i=0; $i < count($catelist[2]); $i++) { 
				if (strpos('_'.$catelist[2][$i]['parent_id_path'],$parent_id_path)) {
					$list[$nm] = $catelist[2][$i];
					$nm++;
				}
			}
		}
		return $list;
	}
	/**
	 *  删除函数
	 */
	
	public function ajaxMessageDelete(Request $request)
	{
		// $this->ajaxReturn(Upload::uoload_cache($request->post('id'),$_FILES['file']),'JSON');
	}
	/**
	 * [ajaxMessageUpload goods 简介 图片 视频上传]
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function ajaxMessageUpload(Request $request)
	{
		$this->ajaxReturn(Upload::uoload_cache($request->post('id'),$_FILES['file']),'JSON');
	}
	public function ajaxSpeclistUpload(Request $request)
	{
		$this->ajaxReturn(Upload::uoload_cache("upload_speclist",$_FILES['file']),'JSON');
	}

/**
 * [brandlist_sc_img description]
 *
 *   品牌的img上传类
 * @param  Request $request [description]
 * @return [type]           [description]
 */
	public function brandlist_sc_img(Request $request)
	{
		if(!empty($_FILES['file'])){
			$fileinfo = $_FILES['file'];
			
			//创建目录
		    $destination = ROOT_PATH.'public\static\image\goods\brand\\'.date('Ymd',time()).'\\';
		    if (!file_exists($destination)){
            mkdir ($destination,0777,true);
        }

 // 获取图片的后缀
        $suff = explode('.',$fileinfo['name']);

        //设置名称
        $new_img = date('Ymd',time()).substr(time(),2).'.'.$suff[count($suff)-1];
        $destination = $destination.basename($new_img);
       move_uploaded_file($fileinfo['tmp_name'],$destination);
		}
		if (file_exists($destination)) {
			$code = 1;
			$url = substr($destination,strpos($destination,'static')-1);
		}else{
			$code = 0;
			$url = "上传出错";
		}
		return ['code'=>$code,'msg'=>$url];
	}
	/**
	 * [brandlist_insert 插入数据]
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function brandlist_insert(Request $request)
	{
		$a = json_decode($request->get('data'),true);
		$mes = json_decode(Brand_class::brank_insert(json_decode($request->get('data'),true)));
		// $mes = Brand_class::brank_insert(json_decode($request->get('data'),true));
		
		if ($mes == 1) {
			$data['static'] = 1;
			$data['message'] = 'insert success';
			//添加日志
			Log::operation($request,Session::get('admin_name'),"添加".$a['L_name']."品牌");
		}else{
			$data['static'] = 0;
			$data['message'] = $mes;
		}

		return json_encode($data);
	}


	/**
	 *   x显示编辑的内容
	 */

	public function brandlist_update_add(Request $request)
	{
		$data = Db::table('shop_brand')->where('brand_id',(int)$request->get('id'))->find();
		$cat = Goods_cate::where('cate_id',$data['parent_cat_id'])->find();
		$catelist = json_decode(Goods_cate::goos_list__handel(Db::table('shop_goods_category')->order('cate_id desc')->select()),true);
		// var_dump($catelist[1]);die;
		$tt = array();
		$a = 0;
		for ($i=0; $i < count($catelist[1]); $i++) {
			if (strpos('.'.$catelist[1][$i]['parent_id_path'],$cat['parent_id_path'])) {
				$tt[$a] = $catelist[1][$i];
				$a++;
			}
		}
		$this->assign([
			'catelist_one' => $catelist[0],
			'catelist_two' => $tt,
			'onea' => $data['parent_cat_id'],
			'twoa' => $data['cat_id'],
			'data' => $data,
			]);

		return $this->fetch();
	}
	/**
	 * [brandlist_update description]
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function brandlist_update(Request $request)
	{
		// 获取前台数据
		$mes = json_decode(Brand_class::brank_update($request->get('data'), $request->get('type'), $request->get('index')));
		if ($mes == 1) {
			# code...
			$data['static'] = 1;
			$data['message'] = "更新成功";
		}else{
			# code...
			$data['static'] = 0;
			$data['message'] = $mes;
		}
		return json_encode($data);
	}

	public function brandlist_delete(Request $request)
	{
		$mes = json_decode(Brand_class::brand_delect($request->get('data'),$request->get('type')));
		if ($mes == 1) {
			# code...
			$data['static'] = 1;
			$data['message'] = "删除成功";
		}else{
			# code...
			$data['static'] = 0;
			$data['message'] = $mes;
		}
		return json_encode($data);
	}

	//邮费模板
	public function ajaxFreeshipping()
	{

	}
}