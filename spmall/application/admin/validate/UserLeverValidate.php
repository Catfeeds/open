<?php
namespace app\admin\validate;

use think\Validate;

/**
 * @param [type] $[lever_name] [会员等级名]
 * @param [type] $[lever_grade] [会员等级]
 * @param [type] $[lever_position] [消费额度]
 * @param [type] $[lever_discount] [会员优惠价]
 * @param [type] $[lever_desc] [等级描述]
 */
class UserLeverValidate extends Validate
{

	protected $rule = [
				'lever_name' => 'require|max:20|unique:shop_user_lever',
				'lever_grade' => 'require|number|unique:shop_user_lever|CheckLeverGrade',
				'lever_position' => 'require|number|CheckLeverPosition|unique:shop_user_lever',
				'lever_discount' => 'require|CheckLeverDiscount|number|unique:shop_user_lever',
				'lever_desc' => 'require|max:200',
	];

	protected $message = [
				'lever_name.require' => '会员等级名必须填',
				'lever_name.max' => '会员等级名不得超过20个字符',
				'lever_name.unique' => '会员等级名不得重复',
				'lever_grade.require' => '会员等级必须填',
				'lever_grade.number' => '会员等级必须是数字',
				'lever_grade.CheckLeverGrade' => '会员等级逻辑出错',
				'lever_grade.unique' => '会员等级不得重复',
				'lever_position.require' => '消费额度必须填',
				'lever_position.number' => '消费额度必须是数字',
				'lever_position.unique' => '消费额度不得重复',
				'lever_position.CheckLeverPosition' => '消费额度逻辑错误',
				'lever_discount.require' => '会员优惠价必须填',
				'lever_discount.number' => '会员优惠价必须是数字',
				'lever_discount.CheckLeverDiscount' => '会员优惠价逻辑错误',
				'lever_discount.unique' => '会员优惠价不得重复',
				'lever_desc.max' => '等级描述不得超过200个字符',
				'lever_desc.require' => '等级描述必须填',
	];

	protected function CheckLeverGrade($value, $rule, $data){
		if ($data['lever_grade'] == '0') {
			return "等级不可以出现0";
		}

		$i = Db('shop_user_lever')->field('lever_id,lever_discount')->select();
		if ((int)$data['lever_grade'] > (count($i)+1)) {
			return "下一个等级应该为：".(count($i)+1);
		}

		return true;
	}

	protected function CheckLeverDiscount($value, $rolu, $data){
		$m = Db('shop_user_lever')->where('lever_grade',((int)$data['lever_grade']-1))->field('lever_id,lever_discount')->find();
		if ((int)$data['lever_discount'] >= $m['lever_discount']) {
			return "下一个优惠价应该小于：".$m['lever_discount'];
		}
		return true;
	}

	protected function CheckLeverPosition($value, $rolu, $data){
		$m = Db('shop_user_lever')->where('lever_grade',((int)$data['lever_grade']-1))->field('lever_position')->find();
		if ((int)$data['lever_position'] <= $m['lever_position']) {
			return "下一个消费额度应该大于：".$m['lever_position'];
		}
		return true;
	}
}