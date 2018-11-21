<?php
namespace app\admin\model;

use Exception;
use think\Db;
use think\Model;
/**
* 
*/
class Attribute_class extends model
{
	protected $table = 'shop_goods_attribute';

	/**
	 *  属性
	 * @return  [description]
	 *  添加属性
	 */
	public function attribute_insert($data,$text)
	{
		$type = Db::table('shop_goods_type')->where('type_name',$data["speclist"])->find();
		// var_dump($data);die;
		if ($data["input_type"] != 1) {
			$text = $data["textarea"];
		}
		$sum = Db::table('shop_goods_attribute')->where('attribute_name',$data["L_name"])->where('type_id',$type['type_id'])->find();
		if (isset($sum["attribute_id"])) {  // you jin
			$message = "已有此属性";
		}else{
			Db::startTrans();
		try {
			$map = ['attribute_name'=>$data["L_name"],
				'type_id'=>$type['type_id'],
				'attribute_index'=>$data["static"],
				'attribute_type'=> 0,
				'attribute_index_type'=>$data["input_type"],
				'attribute_value'=>$text,
				'attribute_name'=>$data["L_name"],
				];
			$index = Db::table('shop_goods_attribute')->insert($map);
			if (!$index) {
				throw new \Exception("添加属性".$data["L_name"]."不成功");
			}

			// 提交事务
			Db::commit();
			$message = 1;

		} catch (Exception $e) {

			//回滚事务
			Db::rollback();
			$message = $e->getMessage();  // 赋值出错日志
		}
		}
		return $message;
		
	}

	/**
	 * 暂时不需要
	 * 当录入方式为  从下面列表中选择是  调用  解析换行 
	 * @param   $data [待处理数据]
	 * @return [analysis]   [返回一个带有换行的文本]
	 */
	public function attribute_analysis($data)
	{
		return $data;
	}

	/**
	 * 
	 * @return 删除 [description]
	 * @param $[data] [数据]
	 * @param $[type] [操作模式] 1 多个删除 2 单个删除
	 */
	public static function attribute_delete($id,$type){

		// var_dump($id);die;
		if ($type == 2) {
			Db::table('shop_goods_attribute')->where('attribute_id',(int)$id)->delete();
			return $id;
		}else{
			$max = '';
			$data = json_decode($id);
			for ($i=0; $i < count($data); $i++) { 
				Db::table('shop_goods_attribute')->where('attribute_id',(int)$data[$i])->delete();
			}
			return $max;
		}
	}

	/**
	 * 
	 * @return   更新  [description]
	 * @param $[data] [数据]
	 * @param $[type] [操作模式] 1 多个删除 2 单个删除
	 */
	public static function attribute_update($data,$text)
	{
		$bb = Db::table('shop_goods_type')->where('type_name',$data['speclist'])->find();
		Db::startTrans();
		try {
			$c = Db::table('shop_goods_attribute')->where('attribute_name',$data["L_name"])->update([
				'type_id' => $bb["type_id"],
				'attribute_index' => $data["static"],
				'attribute_index_type' => $data["input_type"],
				'attribute_value' => $text,
			]);
			if (!$c) {
				throw new \Exception("修改出错");
			}

			//提交事务
			Db::commit();
			$message = 1;
		} catch (Exception $e) {
			//回滚事务
			Db::rollback();
			$message = $e->getMessage();
		}
	
	return $message;
	}
}