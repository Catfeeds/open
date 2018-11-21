<?php

namespace app\admin\model;

use Exception;
use think\Db;
use think\Model;

/**
*
*   @param [type] $[name] [description]
*/
class Goods_cate extends Model
{
	protected $table = 'shop_goods_category'; 

	public static function goos_list__handel($data)
    {
    	$list_handle_one = array();
    	$list_handle_two = array();
    	$list_handle_three = array();
    	$one = 0;
    	$two = 0;
    	$three = 0;
        for ($i=0; $i < count($data); $i++) { 
        	if (substr_count($data[$i]['parent_id_path'],'_') == 1) {
        		$list_handle_one[$one] = $data[$i];
        		$one++;
        	}
        	if (substr_count($data[$i]['parent_id_path'],'_') == 2) {
        		$list_handle_two[$two] = $data[$i];
        		$two++;
        	}
        	if (substr_count($data[$i]['parent_id_path'],'_') == 3) {
        		$list_handle_three[$three] = $data[$i];
        		$three++;
        	}
        }
        $data = array($list_handle_one,$list_handle_two,$list_handle_three);
        // 	'list_handle_one' => $list_handle_one,
        // 	'list_handle_two' => $list_handle_two,
        // 	'list_handle_three' => $list_handle_three,
        // ];
        return json_encode($data,true);
    }
}