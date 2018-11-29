<?php
namespace app\admin\validate;

use think\Validate;
/**
* adver
* adver_infro_name
* adver_hidder
* adver_infro_img
* adver_infro_link
* adver_infro_static
* adver_infro_weight
* end
* start
*/
class AdverInValidate extends Validate
{
	
	protected $rule = [
			'adver_infro_name' => 'require|length:1,50',   // 广告名称
			'adver_id' => 'require|number',   // 广告位
			'adver_infro_img' => 'require',  //  图片
			'adver_infro_rasetime' => 'require|date|checkRasetime', // 开始时间
			'adver_infro_endtime' => 'date|require|checkrEndtime',  // 结束时间
			'adver_infro_link' => 'url|require',  // 跳转链接
			'adver_infro_weight' => 'require|number',  // 权重
			'adver_infro_static' => 'number|require|accepted',  // 状态
			'adver_infro_color' => 'require',    // 配图颜色
	];

	protected $message = [

			'adver_infro_name.require' => '广告名称必须填',
			'adver_infro_name.length' => '广告名称文字不可以超过50个文字',

			'adver_id.require' => '广告位必填',
			'adver_id.number' => '广告位参数不正确',

			'adver_infro_img.require' => '图片必填',

			'adver_infro_link.require' => 'url必须填写',
			'adver_infro_link.url' => 'url格式不正确',

			'adver_infro_static.number' => '状态参数错误',
			'adver_infro_static.require' => '状态必须选择',
			'adver_infro_static.accepted' => '状态参数错误',

			'adver_infro_weight.require' => '权重必须填写',
			'adver_infro_weight.number' => '权重参数错误',

			'adver_infro_rasetime.require' => '开始时间必须填写',
			'adver_infro_rasetime.date' => '时间格式不正确',
			'adver_infro_rasetime.checkRasetime' => '开始时间必须小于结束时间',

			'adver_infro_endtime.require' => '结束时间必须填写',
			'adver_infro_endtime.date' => '时间格式不正确',
			'adver_infro_endtime.checkrEndtime' => '结束时间不可以大于开始时间',

			'adver_infro_color.require' => '颜色必须选择',
	];

	// 验证场景 add 为新增  edit 为修改
	protected $scene = [
			"add" => ["adver_infro_name","adver_infro_img","adver_infro_rasetime","adver_infro_endtime","adver_infro_link","adver_infro_weight","adver_infro_static","adver_infro_color"],
	];

	//   结束时间
	protected function checkRasetime($value, $rule, $data){
       if(strtotime($value) < strtotime($data['adver_infro_endtime'])){
            return true;
        }else{
            return false;
        }
    }

    //  开始时间
	protected function checkrEndtime($value, $rule, $data){
       if(strtotime($value) > strtotime($data['adver_infro_rasetime'])){
            return true;
        }else{
            return false;
        }
    }
}