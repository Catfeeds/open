<?
namespace app\admin\model;

use Exception;
use think\Db;
use think\Model;

/**
* 
*/
class Brand_class extends Model
{
	protected $table = "shop_brand";
	
	public static function select(){

	}

	public static function brank_insert($data)
	{
		$meg = '已有此品牌';
		$a = $data['L_order'] == '' || $data['L_order'] == 0?'50':$data['L_order'];
		$cate = Db::table('shop_goods_category')->where('parent_id_path',$data['one'])->find();
		$map = ['brand_name'=>$data['L_name'],
				'brand_logo'=>$data['logo'],
				'brand_desc'=>$data['L_textarea'],
				'brand_url'=>$data['L_url'],
				'brand_sort'=>$a,
				'cat_name'=>$cate["cate_name"],
				'parent_cat_id'=>$cate['cate_id'],
				'cat_id'=>$data['two'],
				'brand_is_hot'=> 0
				];

		$order = Db::table('shop_brand')
					->where('brand_name',$data['L_name'])
					->where('parent_cat_id',$cate['cate_id'])
					->where('cat_id',$data['two'])
					->find();

		if (count($order) == 0) {
			//启动事务	
			Db::startTrans();
			try {
				$a = Brand_class::insert($map);
				if (!$a) {
					throw new Exception('insert brank Error');
				}
				//提交事务
				Db::commit();
				$meg = 1;
			} catch (Exception $e) {
				
				//回滚事务
				Db::rollback();
				$meg = $e->getMessage();
			}
		}
			
		return json_encode($meg);
	}

	/**
	 * 汇聚品牌的全部更新
	 * @param  [type] $data   [数据]
	 * @param  [type] $type   [类型]
	 * @param  [type] $index [id]
	 * @return [type]         [mes]
	 *
	 * $type 介绍:
	 *  @param tj   	修改推荐
	 *  @param order    修改排序
	 *  @param index   	修改全部内容
	 */
	public static function brank_update($data, $type, $index)
	{
		//启动事务
		Db::startTrans();
		try {
			switch ($type) {
				case 'tj':
					//SQL操作
						Brand_class::where('brand_id',$index)
						->update([
							"brand_is_hot"=>$data,
							]);
					break;
				case 'order':
					//SQL操作
						Brand_class::where('brand_id',$index)
						->update([
							"brand_sort"=>(int)$data,
							]);
					break;
				case 'list':
						//SQL操作
						$data = json_decode($data,true);
						$one = Db::table('shop_goods_category')->where('parent_id_path',$data['one'])->field('cate_name,cate_id')->find();
						if (!is_numeric($data['two'])) {
							$twoa = Db::table('shop_goods_category')->where('parent_id_path',$data['two'])->field('cate_name,cate_id')->find();
							$two = $twoa['cate_id'];
						}else{
							$two = $data['two'];
						}
						
						$a = Brand_class::where('brand_id',$index)
						->update([
							"brand_url"=>$data['L_url'],
							"brand_logo"=>$data['logo'],
							"cat_name"=>$one['cate_name'],
							"brand_desc"=>$data['L_textarea'],
							"brand_sort"=>$data['L_order'],
							"parent_cat_id"=>$one['cate_id'],
							"cat_id"=>$two,
							]);
					if (!$a) {
						throw new Exception("更新失败");
					}
					break;
				default:
					$mes = '错误类型';
					break;
			}
			// 提交事务
			Db::commit();
			$mes = 1;
		} catch (Exception $e) {
			
			//回滚事务
			Db::rollback();
			$mes = $e->getMessage();
		}
		return json_encode($mes);
	}

	/**
	 * [brand_delect 删除类]
	 * @param  [type] $data [数据]
	 * @param  [type] $type [类型 del 单个删除  delAll  多个删除 ]
	 * @return [type]       [成功  1 失败  失败信息]
	 */
	public static function brand_delect($data, $type)
	{
		// 启动事务
		DB::startTrans();
		try {
			switch ($type) {
			case 'del':
				$a = Db::table('shop_brand')->where('brand_id',$data)->delete();
				break;
			case 'delAll':
			$data = json_decode($data);
				for ($i=0; $i < count($data); $i++) { 
					$a = Db::table('shop_brand')->where('brand_id',(int)$data[$i])->delete();
					if (!$a) {
						throw new Exception("删除出错");
					}
				}
				break;
			default:
				$mes = "错误类型";
				break;
			}
			if (!$a) {
				throw new Exception("删除出错");
			}

			//提交事务
			Db::commit();
			$mes = 1;
		} catch (Exception $e) {
			
			// 回滚事务
			Db::rollback();
			$mes = $e->gertMessage();
		}
		return json_encode($mes);
	}
}