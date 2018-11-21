<?php
namespace app\admin\model;

use think\Db;
use think\Session;
use think\Model;
use think\Loader;
use Exception;
use think\Request;
use app\common\admin\Log;

/**
* 商品类
*/
class Goods_data extends Model
{
	protected $table = 'shop_goods';

	/**
	 * [GetgoodsSpend 返回规格的html]
	 * @param [type] $data [数据]
	 * @param [type] $type [类型]->field('speclist_name')
	 */
	public function GetgoodsSpend($index,$type = 0){
		$data = Db_speclist::where('type_id',$index)->field('speclist_id,speclist_name')->select();
		$list = array();
		$a = Db('shop_speclist_item')->where('speclist_id',2)->field('item_count')->select();
		for ($i=0; $i < count($data); $i++) { 
			$list[$i][0] = $data[$i];
			$list[$i][1] = Db('shop_speclist_item')->where('speclist_id',$data[$i]['speclist_id'])->field('item_count,item_id')->select();
		}
		/**********   设置规格属性  *****************/
$html = '';
         for ($a=0; $a < count($list); $a++) { 
         	$html = $html.'<tr class="delect_class_speclist"><td>'.$list[$a][0]['speclist_name'].':</td><td>';
         	for ($b=0; $b < count($list[$a][1]); $b++) { 
         		$html = $html.'
			<span>
				<span data-spec_id="'.$list[$a][0]["speclist_id"].'" class="Get_add_cate button_add" value="'.$list[$a][1][$b]["item_id"].'">'.$list[$a][1][$b]["item_count"].'</span>
				<input type="hidden" disabled="disabled" name="speclist_'.$list[$a][0]["speclist_id"]."_".$list[$a][1][$b]["item_id"].'" class="'.$list[$a][1][$b]["item_count"].'" value="'.$list[$a][1][$b]["item_count"].'" />
				<img name="'.$list[$a][0]["speclist_id"]."_".$list[$a][1][$b]["item_id"].'" id="Get_add_cate_img" class="Get_add_cate_img" width="35" height="35" src="/static/image/images/add-button.jpg">
				<input type="hidden" disabled="disabled" style="display: none;" name="speclist_img_'.$list[$a][0]["speclist_id"]."_".$list[$a][1][$b]["item_id"].'" />
			</span>';
         	}
         	$html = $html.'</td></tr>';
         }
/**
 * 
				<input type="file" style="display: none;" class="img_file_upload"  name="'.$list[$a][0]["speclist_id"]."_".$list[$a][1][$b]["item_id"].'" />
 */
         /**************属性******************/
         $attribute = Db::table('shop_goods_attribute')->where('type_id',$index)->select();
         $html_attr = '';
         $select_attr = '';
         for ($m=0; $m < count($attribute); $m++) { 
         	switch ($attribute[$m]["attribute_index_type"]) {
         		case '0':
         			$html_attr = $html_attr.'<tr><td>'.$attribute[$m]["attribute_name"].'</td><td><input type="text" name="attribute_'.$attribute[$m]["attribute_id"].'" data-val="'.$attribute[$m]["attribute_id"].'"></td>
					</tr>';
         			break;
         		
         		case '1':
         		$data_attr = explode('_M_m',$attribute[$m]["attribute_value"]);
         		$select_attr = '<select style="display: block;" class="'.$attribute[$m]["attribute_id"].'" name="attribute_'.$attribute[$m]["attribute_id"].'" lay-filter="'.$attribute[$m]["attribute_id"].'"><option value="null">请选择</option>';
         		for ($n=0; $n < count($data_attr); $n++) { 
         			$select_attr = $select_attr.'<option value="'.$data_attr[$n].'">'.$data_attr[$n].'</option>';
         		}
         		$select_attr = $select_attr.'</select>';
         			$html_attr = $html_attr.'<tr><td>'.$attribute[$m]["attribute_name"].'</td><td>'.$select_attr.'</td>
					</tr>';
         			break;
         		case '2':
         			$html_attr = $html_attr.'<tr><td>'.$attribute[$m]["attribute_name"].'</td><td><textarea name="attribute_'.$attribute[$m]["attribute_id"].'" class="'.$attribute[$m]["attribute_id"].'" rows="3" cols="20"></textarea></td>
					</tr>';
         			break;
         		
         		default:
         			# code...
         			break;
         	}
         }
         $mom['html'] = $html;
         $mom['html_attr'] = $html_attr;
         return $mom;
		/***************************************/
	}
 /**
     * 获取 规格的 笛卡尔积
     * @param $goods_id 商品 id     
     * @param $data 笛卡尔积
     * @return string 返回表格字符串
     */
	public function setAttr_list($goods_id,$data)
	{
		/***  分解前台的规格  拆分出来  ***/
		$arr = array();
		$amb = array(array());
		$index = array();
		$b = 0;
		foreach ($data as $key => $value) {
			$arr[$key] = explode('_M_n_',$value);
		}
		foreach ($arr as $value) {
				$index[$b] = $value[0];
				$b++;
		}
		$index = explode("__",implode("__", array_unique($index)));
		for ($t=0; $t < count($index); $t++) { 
			$text = '';
			$mm = 0;
			foreach ($arr as $value) {
				if ($index[$t] == $value[0]) {
					$amb[$t][$mm] = $value[1];
					$mm++;
				}
			}
		}
		

		/**
		 * [$len 长度]
		 * @var [type]
		 * 冒泡排序   将二维数组的大小从小到大排序
		 * 例如   
		 * 
		 * array(4) { 
		 *	[0]=> array(2) { [0]=> string(2) "99" [1]=> string(2) "96" }
		 *	[1]=> array(1) { [0]=> string(1) "9" }
		 *	[2]=> array(1) { [0]=> string(2) "14" }
		 *	[3]=> array(3) { [0]=> string(2) "71" [1]=> string(2) "72" [2]=> string(3) "101" } }
		 *	换成
		 * array(4) { 
		 *	[0]=> array(1) { [0]=> string(1) "9" }
		 *	[1]=> array(1) { [0]=> string(2) "14" }
		 *	[2]=> array(2) { [0]=> string(2) "99" [1]=> string(2) "96" }
		 *	[3]=> array(3) { [0]=> string(2) "71" [1]=> string(2) "72" [2]=> string(3) "101" } }
		 */
		$len = count($amb);
		$index_list = array();
		for($k=0;$k<=$len;$k++)
		{
		    for($j=$len-1;$j>$k;$j--){
		        if(count($amb[$j])<count($amb[$j-1])){   //$index
		            $temp = $amb[$j];
		            $amb[$j] = $amb[$j-1];
		            $amb[$j-1] = $temp;
		            ///////////
		            $temp1 = $index[$j];
		            $index[$j] = $index[$j-1];
		            $index[$j-1] = $temp1;
		        }
		    }
		}
		$attr_list_html = $this->get_speclistlist_item(Db::table('shop_speclist_item')->field('item_count,item_id')->select());  //规格项
		$attr_list = $this->get_speclistlist(Db::table('shop_speclist')->field('speclist_id,speclist_name')->select()); // 规格表
		/**  amb  就是一个二维数组  */
		$list = $this->combineDika($amb);
		// var_dump($list);die;
		$count = '';
		/**
		 * [$h description]
		 * @var integer
		 * 遍历输出table样式
		 */
		/***  头部*/
		$attr_list_count = '<tr>';
		for ($h=0; $h < count($index); $h++) {
			$attr_list_count = $attr_list_count."<td>".$attr_list[$index[$h]].'</td>';
		}
		$attr_list_count = $attr_list_count.'<td><b>价格</b></td><td><b>库存</b></td><td><b>SKU</b></td><td><b>操作</b></td></tr>';
		$bbmm = array();
		if (count($index) == 1) {
			$list_demo = $list;
			for ($i=0; $i < count($list_demo); $i++) { 
				$bbmm[$i][0] = $list_demo[$i];
			}
			$list = $bbmm;
		}
		for ($t=0; $t < count($list); $t++) { 
		$text_list = '';
		$b_list = '';
			$html = '<tr>';
			for ($M=0; $M < count($list[$t]); $M++) {
				$html = $html.'<td><b>'.$attr_list_html[$list[$t][$M]].'</b></td>';
				$b_list = $b_list.$list[$t][$M]."_";
				$text_list = $text_list.$attr_list[$index[$M]].':'.$attr_list_html[$list[$t][$M]].' ';
			}
			$b_list = substr($b_list, 0, -1);
			$b = '<td><input name="item['.$b_list.'][price]" value="0" onkeyup="this.value=this.value.replace(/[^\d.]/g,'."''".')" onpaste="this.value=this.value.replace(/[^\d.]/g,'."''".')" /></td>
	<td><input name="item['.$b_list.'][store_count]" value="0" onkeyup="this.value=this.value.replace(/[^\d.]/g,'."''".')" onpaste="this.value=this.value.replace(/[^\d.]/g,'."''".')"/></td>
	<td><input name="item['.$b_list.'][sku]" value="" /><input type="hidden" name="item['.$b_list.'][key_name]" value="'.$text_list.'" /></td>
	<td><span id="speclist_wuxiao" onclick="speclist_wuxiao(this)" class="btn btn-default delete_item">无效</span></td>';
			$html = $html.$b.'</tr>';
			$count = $count.$html;
		}
		// $succes = '<table class="speclist_mou_index" border= "1 " style="margin-left: 50px;width: 700px;">'.$attr_list_count.$count.'</table>';
		$succes = $attr_list_count.$count;
		return $succes;
	}






	/** -------    笛卡尔积    ---------    */
		public function combineDika($arr){
		  $arr1 = array();
		  $result = array_shift($arr);
		  while($arr2 = array_shift($arr)){
		    $arr1 = $result;
		    $result = array();
		    foreach($arr1 as $v){
		      foreach($arr2 as $v2){
		        if(!is_array($v))$v = array($v);
		        if(!is_array($v2))$v2 = array($v2);
		        $result[] = array_merge_recursive($v,$v2);
		      }
		    }
		  }
		  return $result;
	}

	/******/
	public function get_speclistlist($data)
	{
		$list = array();
		for ($i=0; $i < count($data); $i++) { 
			$list[$data[$i]["speclist_id"]] = $data[$i]["speclist_name"];
		}
		return $list;
	}
	public function get_speclistlist_item($data)
	{
		$list = array();
		for ($i=0; $i < count($data); $i++) { 
			$list[$data[$i]["item_id"]] = $data[$i]["item_count"];
		}
		return $list;
	}

	/**
	 * [goodsAddUpdate 商品的新增以及修改]
	 * @param  [type] $data [商品数据]
	 * @return [type]       [返回一串json]
	 */
	public static function goodsAddUpdate($data,$request)
	{
		// var_dump($data['exchange_integral']);
		// die;
		$emptyId = $data["emptyId"];
		$validate = Loader::Validate("GoodsValidate");
		if(!$validate->check($data)){  // 错误返回码
    		return $validate->getError();
		}

/******   商品分类 *********/
$goods_two = empty($data['goods_two'])?'0':$data['goods_two'];
$goods_thr = empty($data['goods_thr'])?'0':$data['goods_thr'];
$goods_mom = $data['goods_one'].'M'.$goods_two.'M'.$goods_thr;
$catName = $goods_two != '0'?($goods_thr != '0'?$goods_thr:$goods_two):$data['goods_one'];

$catName = Db('shop_goods_category')->where('parent_id_path',$catName)->field('cate_name')->find();
$speclist_path = Db('shop_goods_category')->where('parent_id_path',empty($data['goods_thr'])?(empty($data['goods_two'])?$data['goods_one']:$data['goods_two']):$data['goods_thr'])->field('cate_id')->find();

		/******  goods_id  ********/
$map = ['goods_name'=>$data['goods_name'],         //  商品名称
				'goods_remark'=>$data['goods_remark'],         //  商品简介
				'spu'=>$data['spu'],         //  SPU
				'sku'=>$data['sku'],         //  sku
				'cat_id'=> $goods_mom,         //  分类
				'cat_name'=> $catName['cate_name'],         //  分类name
				'brand_id'=>$data['brand'],         //  商品品牌id
				'suppliers_id'=>$data['suppliers_id'],         //  供应商
				'shop_price'=>$data['shop_price'],         //  本店售价
				'market_price'=>$data['market_price'],         //  市场价
				'cost_price'=>$data['cost_price'],         //  成本价
				'commission'=>$data['commission'],         //  佣金
				'is_free_shipping'=>empty($data['is_free_shipping'])?'0':$data['is_free_shipping'],         //  是否包邮
				'original_img'=>$data['upload_class_img'],         //  图片上传
				'video'=>$data['upload_class_video'],         //  视频上传
				'weight'=>$data['weight'],         //  商品重量
				'volume'=>$data['volume'],         //  商品体积
				'store_count'=>$data['store_count'],         //  总库存 
				'keywords'=>$data['keywords'],         //  商品关键词
				'is_virtual'=>empty($data['is_virtual'])?'0':$data['is_virtual'],         //  是否虚拟商品
				'virtual_indate'=>strtotime($data['is_virtual_time']),         //  虚拟商品时间
				'virtual_limit'=>$data['is_virtual_num'],         //  虚拟商品数量
				'goods_content'=>empty($data['editorValue'])?'':$data['editorValue'],         //  纤细内容
				'add_time'=>time(),         //  商品添加时间
				'goods_type_id'=>$data['speclist_list'],         //  商品添加时间
				'speclist_id'=>$speclist_path,         //  商品类型cate
				'template_id'=>empty($data['template_id'])?'0':$data['template_id'],         //  运费模板
			
				
				'give_integral'=>$data['give_integral'],         //  赠送积分
				'Gold_offset'=>$data['Gold_offset'],         //  金币可抵
				'exchange_integral'=>empty($data['exchange_integral'])?'0':$data['exchange_integral'],         //  积分兑换
				];		
	if ($data["emptyId"] == "emptyId") {
			$map['goods_sn'] = $data['goods_sn'];        //  商品货号
	}
		/******  变量的定义  ********/

		/**  判断日期是否规范 */
		if(isset($data['is_virtual'])){
           if (!strtotime($data['is_virtual_time']) || !(strtotime($data['is_virtual_time'])>time())) {
				return "虚拟商品时间选择出错，必须大于现在的时间 :( ";
           }
		}

		/** @var    [分析规格的图片路径]  specImg  */
		$specImg = array();
		foreach ($data as $key => $value) {
			// var_dump($key);
			if (strpos($key,'speclist_img_') !== false) {
				$specImg[substr($key,13)] = $value;
			}
		}
		/** @var    [分析规格项]  specImg  */
		$specII = array();
		foreach ($data as $key => $value) {
			if (strpos($key,'speclist_') !== false && $key != "speclist_list" ) {
				$specII[$key] = $value;
			}
		}
		/** @var    [分析规格属性]  specImg  */
		$item = array(array());
		$bb = array();
		$cc = array();
		foreach ($data as $key => $value) {
			if (strpos($key,'item') !== false) {
				$bb[$key] = $value;
				$b = explode("][",substr(substr($key,5),0,-1));
				$cc[count($cc)] = $b[0];
			}
		}
		$box = explode("mmnn",implode("mmnn",array_unique($cc)));
		/***  商品相册  uploadImg ***/
		$uploadImg = explode(',',$data['upload']);
		/***  商品属性for  ***/
		$attrIm = array();
		foreach ($data as $key => $value) {
			if (strpos($key,'attribute_') !== false) {
				$attrIm[substr($key,10)] = $value;
			}
		}
		if( $box[0] == ""){
			$box = array();
		}
		/**
		 *   事务操作 
		 *   1: 先插入商品表获取id，没有错误继续下一行
		 *   2：for插入商品相册
		 *   3：插入规格项以及规格项的图片  
		 */

		Db::startTrans();
		try{
			// 插入goods表
			if ($data['emptyId'] == "emptyId") {
				$goods_id = Db('shop_goods')->insertGetId($map);
			}else{
				Db('shop_goods')->where('goods_id',$data['emptyId'])->update($map);
				if (count($uploadImg) != 0) {
					Db('shop_goods_img')->where('goods_id',$data['emptyId'])->delete();
				}
				if (count($specII) != 0) {
					Db('shop_goods_speclist')->where('goods_id',$data['emptyId'])->delete();
				}
				if (count($box) != 0) {
					Db('shop_goods_price')->where('goods_id',$data['emptyId'])->delete();
				}
				if (count($attrIm) != 0) {
					Db('shop_attr_goods')->where('goods_id',$data['emptyId'])->delete();
				}

				$goods_id = $data['emptyId'];
			}
			if(!$goods_id){
				throw new Exception("插入错误 :( ");
			}
			// 图片表插入
			for ($i=0; $i < count($uploadImg); $i++) { 
				$updaImg = Db::table('shop_goods_img')->insert(['goods_id'=>$goods_id,
															'img_src'=>$uploadImg[$i]]);
				if (!$updaImg) {
					throw new Exception("图片表插入错误 :( ");
				}
			}
			// 规格插入
			foreach ($specII as $key => $value) {
				# code...
				$specId = [ 'goods_id' => $goods_id,
							'gs_name' => substr($key,9),
							'gs_text' => $value
							];
				$specIMD = Db::table('shop_goods_speclist')->insertGetId($specId);
				if (!$specIMD) {
					throw new Exception("规格插入错误 :( ");
				}
			}
			// 规格属性插入
			for($b = 0 ; $b < count($box) ; $b++){
				$priceDb = ['goods_id' => $goods_id,
							'item_id' => $box[$b],
							'item_name' => $bb['item['.$box[$b].'][key_name]'],
							'store_count' => $bb['item['.$box[$b].'][store_count]'],
							'sku' => $bb['item['.$box[$b].'][sku]'],
							'price_img'=> ''];
				$proceId = Db::table('shop_goods_price')->insertGetId($priceDb);
				if (!$proceId) {
					throw new Exception("规格属性插入错误 :( ");
				}
			}
			//  商品属性 attrIm
			 foreach ($attrIm as $key => $value) {
			 	# code...
			 	$attrIm = ['goods_id' => $goods_id,
							'attr_id' => $key,
							'sap_text' => $value
							];
				$proceIdDb = Db::table('shop_attr_goods')->insertGetId($attrIm);
				if (!$proceIdDb) {
					throw new Exception("商品属性插入错误 :( ");
				}
			 }
		//  提交事务
			Db::commit();
			//添加日志
			Log::operation($request,Session::get('admin_name'),"添加 ".$goods_id." 商品");
			$bin = "succes";
		}catch(Exception $e) {
			//回滚事务
			Db::rollback();
			$bin = $e->getMessage();
		}
		/**********  事务操作   ***********/
		return $bin;
		// return json_encode();
	}












	public function itemCount($data, $item_count){

		var_dump($data);
		var_dump($item_count);
		die;
	}
}