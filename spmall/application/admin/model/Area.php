<?php

namespace app\admin\model;

use Exception;
use think\Db;
use think\Model;

/**
* 
*/
class Area extends Model
{
	// 设置数据库
	protected $table = 'shop_area';

	/**
	 * [Areasave 更新添加数据]
	 * @param [type] $info [判断是更新还是添加]
	 * @param [type] $data [返回数据]
	 *  shop_freight_template  字段
	 *  shop_freight_region		配置
	 *  shop_freight_config		模板
	 */
	public function Areasave($info, $data){
		$arr['data'] ='';
		$arr['info'] ='';
		// 将 serialize 过来的字符串转化为数组
		parse_str($data,$myArray);
		// 判断信息是否填写完整
		if ($myArray["supp_name"] == '' || empty($myArray["VM"])) {
			# code...
			$arr['data'] ='请填写完信息';
			$arr['info'] ='0';
			return $arr;
		}
		// 设置 type 
		$d = $myArray["VM"] == 'number'?0:($myArray["VM"] == 'weight'?1:($myArray["VM"] == 'volume'?2:'error'));
		// 区域判断
		if(empty($myArray['area_id'])){
			$arr['data'] ='请输入区域信息';
			$arr['info'] ='0';
			return $arr;
		}
		// 判断区域是否存在重复
		for ($i=1; $i < count($myArray['area_id']); $i++) { 
			if (count(array_intersect(explode(',',$myArray['area_id'][0]),explode(',',$myArray['area_id'][$i]))) != 0) {
				$arr['data'] ='区域存在重复';
				$arr['info'] ='0';
				return $arr;
			}
		}
		// 判断运费信息是否填写完整
		for ($b=0; $b < count($myArray['first_unit']); $b++) { 
			if ($myArray['first_unit'][$b] == "" || $myArray['first_money'][$b] == "" ) {
				$arr['data'] ='请填写完信息';
				$arr['info'] ='0';
				return $arr;
			}
		}
		// 事务提交
		Db::startTrans();
		try {

			// 插入
			if ($info == "" || $info == null) {
				// 单个插入运费模板表
				$map = ['template_name'=>$myArray["supp_name"],
						'type'=>$d,
						'is_enable_default'=> $myArray['TC'] == "true" ? 1 : 0];
				$userId = Db::table('shop_freight_template')->insertGetId($map);
				if (!$userId) {
					throw new Exception("Error Processing Request");
				}
				// for 循环插入 配置信息表
				$arrConfig = array();
				for ($i=0; $i < count($myArray['area_hide_id']); $i++) { 
			    			$Config = ['area_id' => $myArray['area_hide_id'][$i],
			    						'area_name' => $myArray['area_id'][$i],
			    						'first_unit' => $myArray['first_unit'][$i],
			    						'first_money' => $myArray['first_money'][$i],
			    						'continue_unit' => $myArray['continue_unit'][$i],
			    						'continue_money' => $myArray['continue_money'][$i],
			    						'template_id' => $userId,
			    						'is_default' => $myArray['TC'] == "true" ? 1 : 0,
			    						];
			    			$arrConfigId = Db::table('shop_freight_config')->insertGetId($Config);
			    			if (!$arrConfigId) {
								throw new Exception("Error Processing Request");
							}else{
								$arrConfig[$i] = $arrConfigId;
							}
						}
				// 插入运费模板配置中心
					$ConfigId = implode("_M_n_",$arrConfig);
					$Region = ['tempplate_id'=>$userId,
						'config_id'=>$ConfigId];
			    	$arrRegionId = Db::table('shop_freight_region')->insertGetId($Region);
			    	if (!$arrRegionId) {
								throw new Exception("Error Processing Request");
					}
			}else{

				/**
				 * update  更新信息
				 * $info  Id
				 * $data  数据信息
				 * shop_freight_config
				 * shop_freight_region
				 * shop_freight_template
				 */
				$tempId = Db::table('shop_freight_template')->where('template_id',$info)->find();
				$regionId = Db::table('shop_freight_region')->where('tempplate_id',$info)->find();
				$configId = Db::table('shop_freight_config')->where('template_id',$info)->select();
				 
					// 单个插入运费模板表
				$map = ['template_name'=>$myArray["supp_name"],
						'type'=>$d,
						'is_enable_default'=> $myArray['TC'] == "true" ? 1 : 0];
				Db::table('shop_freight_template')->where('template_id',$info)->update($map);

				// 删除config 再添加 回去
				Db::table('shop_freight_config')->where('template_id',$info)->delete();

				// for 循环插入 配置信息表
				$arrConfig = array();
				for ($i=0; $i < count($myArray['area_hide_id']); $i++) { 
			    			$Config = ['area_id' => $myArray['area_hide_id'][$i],
			    						'area_name' => $myArray['area_id'][$i],
			    						'first_unit' => $myArray['first_unit'][$i],
			    						'first_money' => $myArray['first_money'][$i],
			    						'continue_unit' => $myArray['continue_unit'][$i],
			    						'continue_money' => $myArray['continue_money'][$i],
			    						'template_id' => $info,
			    						'is_default' => $myArray['TC'] == "true" ? 1 : 0,
			    						];
			    			$arrConfigId = Db::table('shop_freight_config')->insertGetId($Config);
			    			if (!$arrConfigId) {
								throw new Exception("Error Processing Request");
							}else{
								$arrConfig[$i] = $arrConfigId;
							}
						}
				// 插入运费模板配置中心
					$ConfigId = implode("_M_n_",$arrConfig);
					$Region = ['tempplate_id'=>$info,
						'config_id'=>$ConfigId];
			    	$arrRegionId = Db::table('shop_freight_region')->where('tempplate_id',$info)->update($Region);
			    	if (!$arrRegionId) {
								throw new Exception("Error Processing Request");
					}
			}
			// die;
			// 提交事务
			Db::commit();	
			$arr['data'] ='修改成功';
			$arr['info'] ='1';
		} catch (Exception $e) {
				
			// 回滚事务
			Db::rollback();
			$arr['data'] = $e->getMessage();
			$arr['info'] ='0';
		}
		return $arr;
	}

	/**
	 * [Freighthandling 这是一个解析数据库的函数]
	 * 搭配->arrayfunc->arrayfunc_index
	 */
	public function Freighthandling(){
		// $freight_region = Db::table('shop_freight_region')->field('tempplate_id,config_id')->select();
		$sele = "select shop_freight_config.template_id,shop_freight_config.first_unit,shop_freight_config.first_money,shop_freight_config.continue_unit,shop_freight_config.continue_money,shop_freight_config.area_name,shop_freight_config.area_id,shop_freight_template.type,shop_freight_template.template_name,shop_freight_template.is_enable_default from shop_freight_config,shop_freight_template where shop_freight_config.template_id = shop_freight_template.template_id";
		return $this->arrayfunc(Db::query($sele));
	}

	public function arrayfunc($data)
	{
		$bb =array();  // 定义一个空数组
		for ($i=0; $i < count($data); $i++) { 
			if ($i == 0) {
				$bb[$i]['template_id'] = $data[$i]['template_id'];
				$bb[$i]['type'] = $data[$i]['type'];
				$bb[$i]['is_enable_default'] = $data[$i]['is_enable_default'];
				$bb[$i]['template_name'] = $data[$i]['template_name'];
				$bbaa = array(array($data[$i]['first_unit'],$data[$i]['first_money'],$data[$i]['continue_unit'],$data[$i]['continue_money'],$data[$i]['area_name'],$data[$i]['area_id']));
				$bb[$i]['info'] = $bbaa;
				continue;
			}
			$bb = $this->arrayfunc_index($data[$i],$bb);
		}
		return $bb;
	}

	/**  数据处理分析层  */
	public function arrayfunc_index($data, $info){
		$b = 0;
		for ($i=0; $i < count($info); $i++) {
			if ($info[$i]["template_id"] == $data["template_id"]) {
				$b++;
				$bb = array($data['first_unit'],$data['first_money'],$data['continue_unit'],$data['continue_money'],$data['area_name'],$data['area_id']);
				$info[$i]['info'][count($info[$i]['info'])] = $bb;
			}
		}
		$a = count($info);
		if ($b == 0) {

			$info[$a]['template_id'] = $data['template_id'];
				$info[$a]['type'] = $data['type'];
				$info[$a]['is_enable_default'] = $data['is_enable_default'];
				$info[$a]['template_name'] = $data['template_name'];
				$bbaa = array(array($data['first_unit'],$data['first_money'],$data['continue_unit'],$data['continue_money'],$data['area_name'],$data['area_id']));
				$info[$a]['info'] = $bbaa;
		}
		return $info;
	}

	/**
	 * [getHtmlType 返回更新的HTML]
	 * @param  [type] $data [数据]
	 * @return [type]       [返回HTML]
	 */
	public static function getHtmlType($data){
		// 编辑HTML 所需的文本代码
		$tetx = $data['type'] == '0'?"件数":($data['type'] == '1'?"重量":($data['type'] == '2'?"体积":"error"));
		// 导航
		// var_dump($data);die;
        // 默认配置
        $moren = '<tr class="momomom mrpzdq"><th style="width: 8%;">默认配置</th><th style="width: 20%;" abbr="article_title" axis="col3" class="" align="left"><div style="text-align: center; width: 90%;" class="pdszfh"><input type="text" name="area_id[]" value="中国" readonly value="'.$data['info'][0][4].'"/><input name="area_hide_id[]" value="100000" type="hidden" value="'.$data['info'][0][5].'"></div></th><th style="width: 17%;" abbr="ac_id" axis="col4" class="" align="left"><div style="text-align: center; width: 90%;" class="90"><input onkeyup="this.value=this.value.replace(/[^\\d.]/g,\'\')" type="text" name="first_unit[]" value="'.$data['info'][0][0].'"/></div></th><th style="width: 17%;" abbr="ac_id" axis="col4" class="" align="left"><div style="text-align: center; width: 90%;" class="pdszfh"><input type="text" onkeyup="this.value=this.value.replace(/[^\\d.]/g,\'\')" name="first_money[]" value="'.$data['info'][0][1].'"/></div></th><th style="width: 17%;" abbr="article_show" axis="col5" class="" align="center"><div style="text-align: center; width: 90%;" class="pdszfh"><input type="text" onkeyup="this.value=this.value.replace(/[^\\d.]/g,\'\')" name="continue_unit[]" value="'.$data['info'][0][2].'"/></div></th><th style="width: 17%;" abbr="article_time" axis="col6" class="" align="center"><div style="text-align: center; width: 100%;" class=""><input type="text" onkeyup="this.value=this.value.replace(/[^\\d.]/g,\'\')" name="continue_money[]" value="'.$data['info'][0][3].'"/></div></th><th style="width: 10%;" abbr="article_time" axis="col6" class="" align="center"><div style="text-align: center; width: 100%;" class=""></div></th></tr>';
        $bbhtml = '';
        $a = 0;
        if ($data['is_enable_default'] == 1) {
        	$bbhtml = $moren;
        	$a++;
        }
        $bmn = count($data['info']);
        for ($i=0; $i < ($bmn-$a); $i++) { 
        	$bbhtml = $bbhtml.'<tr class="momomom"><th style="width: 8%;"></th><th style="width: 20%;" abbr="article_title" axis="col3" class="" align="left"><div style="text-align: center; width: 90%;" class="pdszfh"><input type="text" name="area_id[]" onclick="clickTextCity(this)" value="'.$data['info'][$i][4].'"/><input type="hidden" name="area_hide_id[]"  value="'.$data['info'][$i][5].'"/></div></th><th style="width: 15%;" abbr="ac_id" axis="col4" class="" align="left"><div style="text-align: center; width: 90%;" class="pdszfh"><input onkeyup="this.value=this.value.replace(/[^\\d.]/g,\'\')" type="text" name="first_unit[]" value="'.$data['info'][$i][0].'"/></div></th><th style="width: 15%;" abbr="ac_id" axis="col4" class="" align="left"><div style="text-align: center; width: 90%;" class="pdszfh"><input type="text" onkeyup="this.value=this.value.replace(/[^\\d.]/g,\'\')" name="first_money[]" value="'.$data['info'][$i][1].'"/></div></th><th style="width: 15%;" abbr="article_show" axis="col5" class="" align="center"><div style="text-align: center; width: 90%;" class="pdszfh"><input type="text" onkeyup="this.value=this.value.replace(/[^\\d.]/g,\'\')" name="continue_unit[]" value="'.$data['info'][$i][2].'"/></div></th><th style="width: 15%;" abbr="article_time" axis="col6" class="" align="center"><div style="text-align: center; width: 100%;" class=""><input type="text" onkeyup="this.value=this.value.replace(/[^\\d.]/g,\'\')" name="continue_money[]" value="'.$data['info'][$i][3].'"/></div></th><th style="width: 10%;" abbr="article_time" axis="col6" class="" align="center"><div style="text-align: center; width: 100%;" class=""><span class="deleteAdd" onclick="deleteAdd(this)">删除</span></div></th></tr>';
        }
        $bbhtml = '<div class="flexigrid" style="width: 98%;"><div class="mDiv"><div class="ftitle"><span onclick="addtext()" id="addText" style="height: 28px;line-height: 28px;padding: 0px 12px;color: #009688;background: white;border: 1px solid #009688;border-radius: 7px;" class="layui-btn"><i  class="layui-icon"">&#xe654;</i>新增区域</span></div><span class="dqxxxstj"></span></div><div class="hDiv"><div class="hDivBox"><table id="addTitle" cellspacing="0" style="width: 98%;" cellpadding="0"><thead><tr><th style="width: 8%;"></th><th align="left" style="width: 20%;" abbr="article_title" axis="col3" class=""><div style="text-align: center; width: 100%;" class="">配送区域</div></th><th align="left" style="width: 15%;" abbr="ac_id" axis="col4" class=""><div style="text-align: center; width: 100%;" class="">首'.$tetx.'</div></th><th align="left" style="width: 15%;" abbr="ac_id" axis="col4" class=""><div style="text-align: center; width: 100%;" class="">运费(元)</div></th><th align="center" style="width: 15%;" abbr="article_show" axis="col5" class=""><div style="text-align: center; width: 100%;" class="">续'.$tetx.'</div></th><th align="center" style="width: 15%;" abbr="article_time" axis="col6" class=""><div style="text-align: center; width: 100%;" class="">运费(元)</div></th><th align="center" style="width: 10%;" abbr="article_time" axis="col6" class=""><div style="text-align: center; width: 100%;" class="">操作</div></th></tr></thead>'.$bbhtml.'</table></div></div></div>';
		return $bbhtml;
	}

	public static function templaertConfig_delete($info, $id){
		switch ($info) {
			case 'templateConfig':     // 运费模板配置
			/**
				 * update  更新信息
				 * $info  Id
				 * $data  数据信息
				 * shop_freight_config
				 * shop_freight_region
				 * shop_freight_template
				 */
				# code...
				Db::startTrans();
				try {
					# // 删除shop_freight_config 
				$config_id = Db::table('shop_freight_config')->where('template_id',$id)->delete();
				// 删除shop_freight_region 
					$region_id = Db::table('shop_freight_region')->where('tempplate_id',$id)->delete();
				// 删除shop_freight_template 
					$tempplate_id = Db::table('shop_freight_template')->where('template_id',$id)->delete();
					if (!$config_id && !$config_id && !$config_id) {
						throw new Exception("Error Processing Request");
					}

					// 提交事务
					Db::commit();	
					$arr['data'] ='已删除';
					$arr['info'] ='1';
				} catch (Exception $e) {
					// 回滚事务
					Db::rollback();
					$arr['data'] = $e->getMessage();
					$arr['info'] ='0';
					}
				break;
			
			default:
				# code...
					$arr['data'] ='error！！！';
					$arr['info'] ='0';
				break;
		}
		return $arr;
	}
  
}