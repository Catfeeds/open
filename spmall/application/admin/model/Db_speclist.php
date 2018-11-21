<?php
namespace app\admin\model;

use Exception;
use think\Db;
use think\Model;


/**
*   @param [type] $[name] [description]
*/

class Db_speclist extends Model
{
	protected $table = 'shop_speclist'; 
	
	/**
	 * 
	 * @return 删除 [description]
	 * @param $[data] [数据]
	 * @param $[type] [操作模式] 1 多个删除 2 单个删除
	 */
	public static function speclist_delete($id,$type){
		if ($type == 2) {
			Db::table('shop_speclist')->where('speclist_id',(int)$id)->delete();
			return $id;
		}else{
			$max = '';
			$data = json_decode($id);
			for ($i=0; $i < count($data); $i++) { 
				Db::table('shop_speclist')->where('speclist_id',(int)$data[$i])->delete();
			}
			return $max;
		}
	}

/**
 * [edit_update 编辑规格]
 * @param  [type] $data     [一串数据]
 * @param  [type] $textarea [编译过的文本]
 * @return [type] $message  [成功返回 1 错误返回日志]
 */
	public static function edit_update($data, $textarea)
	{
		$ddd = Db::table('shop_speclist')->where('speclist_name',$data["L_name"])->find();
		$item = Db::table('shop_speclist_item')->where('speclist_id',$ddd["speclist_id"])->select();
		$count =array();
		for ($i=0; $i < count($item); $i++) { 
			$count[$i] = $item[$i]["item_count"];
		}
		$textarea = explode('_M_m',$textarea);
		$delete_index = array_diff($count,$textarea);
		$insert_index = array_diff($textarea,$count);

		// 启动事务
		$message = 1;
		Db::startTrans();
		try{
		    if (count($delete_index) != 0) {
		    	if (count($delete_index) == 1) {
		    		$a = Db::table('shop_speclist_item')->where('item_count',implode(',',$delete_index))->delete();
		    		if (!$a) {
					throw new \Exception($delete_index."删除失败");
					}
		    	}else{ 
		    		$delete_index = explode(',',implode(',',$delete_index));
		    		for ($b=0; $b < count($delete_index); $b++) {
					$a = Db::table('shop_speclist_item')->where('item_count',$delete_index[$b])->delete();
					if (!$a) {
					throw new \Exception($delete_index."删除失败");
						}
					}
		    	}
		    }
		    if (count($insert_index) != 0) {
				if (count($insert_index) == 1) {
					$map = ['speclist_id'=>$ddd["speclist_id"],'item_count'=>implode(',',$insert_index)];
					$bb =Db::table('shop_speclist_item')->insert($map);
					if (!$bb) {
						throw new \Exception($insert_index."插入失败");
					}
				}else{
					$insert_index = explode(',',implode(',',$insert_index));
					for ($b=0; $b < count($insert_index); $b++) {
					$map = ['speclist_id'=>$ddd["speclist_id"],'item_count'=>$insert_index[$b]];
					$bb =Db::table('shop_speclist_item')->insert($map);
					if (!$bb) {
						throw new \Exception($insert_index[$b]."插入失败");
					}
				}
			}
		}
		$speclist = Db::table('shop_goods_type')->where('type_name',$data["speclist"])->find();
		Db::table('shop_speclist')->where('speclist_name',$data["L_name"])->update([
				'type_id' => $speclist['type_id'],
				'speclist_order' => $data["L_order"],
				'search_index' => $data["static"],
			]);
		    // 提交事务
		    Db::commit();    
		} catch (\Exception $e) {
		    // 回滚事务
		    Db::rollback();
		    $message = $e->getMessage();
		   	// $message = $e->getMessage();
		}
		return $message;
	}

}