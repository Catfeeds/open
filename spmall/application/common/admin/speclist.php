<?php
namespace app\common\admin;

/**
* @param list speclist 
*/
class speclist
{
	/**
	 * 
	 * @param  接受规格的分类过来
	 * @return 返回一个多维数组 1 ： 规格id  2 ：规格所内容
	 */
	public function spec_list($data)
	{
		$db = 0;
		$list = [];
		$max = $this->adba($data);
		// var_dump($max[0]);die;
		for ($i=0; $i < count($max); $i++) {
			$array = [];
			$adb = 0;
			for ($e=0; $e < count($data); $e++) { 
				if ((int)$max[$i] == $data[$e]['speclist_id']) {
					$array[$adb] = $data[$e];
					$adb ++;
				}
			}
			$list[$db] = $this->list_open($array);
			$db++;
		}
		return $list;
	}
	public function list_open($data)
	{
		// var_dump($data);die;
		$list = $data[0];
		$abb = '';
		for ($i=0; $i < count($data); $i++) { 
			$abb = $abb.', '.$data[$i]["item_count"];
		}
		$list['item_count'] = substr($abb,1);
		return $list;
	}
	
	// 分解模型
	public function adba($data)
	{
		$li = '';
		for ($i=0; $i < count($data); $i++) {
			$li = $li.','.$data[$i]['speclist_id'];
		}
		$strs = explode(',',substr(implode(',',array_unique(explode(',',$li))),1));
		return $strs;
	}

	//模型分类
	public function type_name($data)
	{
		$list = array();
		for ($i=0; $i < count($data); $i++) { 
			$list[$i] = $data[$i]['type_name'];
		}
		return array_unique($list);
	}
}