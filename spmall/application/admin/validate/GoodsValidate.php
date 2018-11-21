<?php
namespace app\admin\validate;

use think\Validate;

/**
*  商品验证
*/
class GoodsValidate extends Validate
{
	protected $rule = [
        'goods_name'      =>'require|min:3|max:150',
        'goods_remark'  => 'min:3|max:150',
        'goods_one'     =>'require',
        'shop_price'  => 'require',
        'store_count'   =>  'require|regex:^\d+$',
        'weight'       =>'regex:\d{1,10}(\.\d{1,2})?$',
        'market_price'  => 'checkMarketPrice',
        'cost_price'    =>  'checkCostPrice',
        'give_integral'         =>'regex:^\d+$',
        'commission'    =>  'checkCostPrice',
    ];

    protected $message = [
        'goods_name.require'  => "商品名称必填",
        'goods_name.min'  => "名称长度至少3个字符",
        'goods_name.max'  => "名称长度至多50个汉字",
        'goods_remark.min'  => "名称长度至少3个字符",
        'goods_remark.max'  => "名称长度至多50个汉字",
        // 'goods_name.unique'  => "商品名称重复",
        'goods_one.require'     => '商品分类(父类)必填',
        'shop_price.require'  => "本店售价必填",
        'store_count.require'   =>  "库存必须填",
        'store_count.regex'      => '库存必须是正整数',
        'weight.regex'        => '重量格式不对',
        'market_price.checkMarketPrice'     => '市场价不得小于本店价',
        'cost_price.checkCostPrice'    => '成本价不得大于本店价',
        'commission.checkCostPrice'    => '佣金不得大于本店价',
        'give_integral.regex'           => '赠送积分必须是正整数',
        'is_free_shipping.require'           => '请选择商品是否包邮',
    ];

    protected function checkMarketPrice($value, $rule, $data){
       if($value < $data['shop_price']){
            return false;
        }else{
            return true;
        }
    }
    protected function checkCostPrice($value, $rule, $data){
       if($value > $data['shop_price']){
            return false;
        }else{
            return true;
        }
    }

    /**  $data['virtual_indate']  */
    // protected function checkIVTime($value, $rule, $data){ 
    //     var_dump(12);
    //     return false;
    //     // if (isset($data['is_virtual'])) {
    //     //    if (date("Y-m-d H:i:s",strtotime($data['is_virtual_time'])) == $data['is_virtual_time']) {
    //     //        # code...
    //     //         return true;
    //     //    }else{
    //     //         return false;
    //     //    }
    //     // }
    // }
    // 运费
    //  protected function checkShipping($value,$rule,$data){
    //     if($value == 0 && empty($data['template_id'])){
    //         return '请选择运费模板';
    //     }else{
    //         return true;
    //     }
    // }
}