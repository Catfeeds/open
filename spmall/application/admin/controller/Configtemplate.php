<?php
namespace app\admin\controller;

use Exception;
use app\admin\common\Base;
use app\admin\model\Area;
use app\admin\model\JxMiun;
use think\Db;
use app\common\extend\SMS\SmsSend;
use app\common\extend\Email\EmailServer;

/**
* 'supplierConfig'=>'供应商管理',
		'poatconfig'=>'邮费配置',
		'emeconfig'=>'短信配置',
		'emailConfig'=>'邮箱配置',
*/
class Configtemplate extends Base
{
/** ------------------ 供应商管理 ------------------------------------ */
	public function supplierconfig(){
		$pindex = input('get.pindex');  //过来的页数
		$psizeIntablet = input('get.psizeInt');  //一页显示
		$data = Db::table('shop_suppliers')->page($pindex,$psizeIntablet)->select();
		$this->assign('data',$data);
		$this->assign('datanum',count(Db::table('shop_suppliers')->select()));
		$this->assign('pindex',$pindex == null?1:$pindex);
		$this->assign('psizeIntablet',$psizeIntablet == null?10:$psizeIntablet);
		return $this->fetch();
	}

	public function supplierconfigadd(){
		$id = input('get.id');
		$dataList = Db::table("shop_suppliers")->where("suppliers_id",$id)->find();
		$adminList = Db::table("shop_admin")->field("admin_id,admin_name")->select();
			$this->assign("data",$dataList);
			$this->assign("suppliers_id",$id == "one"?"000":$dataList['suppliers_id']);
			$this->assign("adminList",$adminList);
		return $this->fetch('supplierconfig_add');
	}
	/**
	 * [supplierconfigadd_insert 供应商插入和更新]
	 * @return [type] [返回状态 信息]
	 */
	public function supplierconfigadd_insert()
	{
		$data = json_decode(input('get.data'),true);
		$id = input('get.id');
		$list['core'] = 0;
		$list['message'] = "失败!!!";
		switch ($id) {      // 0表示插入   1 表示 更新	
			case '0':
				$map = ["suppliers_name"=>$data['supp_name'],
						"suppliers_catacts"=>$data['supp_line'],
						"suppliers_phone"=>$data['supp_iphone'],
						"supplires_admin"=>$data['supplires_admin'],
						"suppliers_desc"=>input('get.text')
						];
					Db::startTrans();
					try {
						$line = Db::table('shop_suppliers')->insert($map);
						if (!$line) {
							throw new Exception("插入错误");
						}
						// 提交事务
						$list['core'] = 1;
					$list['message'] = "更新成功";
                        Db::commit();
					} catch (Exception $e) {
						$list['message'] = $e->getMessage();
						// 回滚事务
						Db::rollback();
					}
				break;
			
			case $id!=0:
				Db::startTrans();
				try {
					$line = Db::table('shop_suppliers')->where("suppliers_id",$id)->update([
							"suppliers_name" => $data['supp_name'],
							"suppliers_catacts" => $data['supp_line'],
							"suppliers_phone" => $data['supp_iphone'],
							"supplires_admin" => $data['supplires_admin'],
							"is_check" => 0,
							"suppliers_desc" => input('get.text'),
						]);
					// 提交事务
						$list['core'] = 1;
					$list['message'] = "更新成功";
                    Db::commit();
				} catch (\Exception $e) {
					$list['message'] = $e->getMessage();
						// 回滚事务
					Db::rollback();
				}
				break;

				case '_m_n':
					$arra = explode("_",$data);
				Db::startTrans();
				try{
				$line = Db::table('shop_suppliers')->where("suppliers_id",$arra[1])->update(["is_check"=>$arra[0]]);
					if (!$line) {
							throw new Exception("插入错误");
						}
					// 提交事务
						$list['core'] = 1;
					$list['message'] = "更新成功";
                    Db::commit();
				} catch (\Exception $e) {
					$list['message'] = $e->getMessage();
						// 回滚事务
					Db::rollback();
				}
					break;
			default:
				$list['message'] = "请求出错";
				break;
		}
		return json_encode($list);
	}

	/**
	 * [supplierconfigadd_delete 删除]
	 * @return [type] [description]
	 */
	public function supplierconfigadd_delete()
	{
		$data['core'] = 0;
		Db::startTrans();
		try {
			$list = Db::table('shop_suppliers')->where('suppliers_id',input('get.id'))->delete();
			if (!$list) {
				throw new Exception("Error Processing Request");
			}

			// 提交事务
			$data['core'] = 1;
			$data["message"] = "删除成功0.o";
			Db::commit();
		} catch (Exception $e) {
			// 回滚事务
			$data["message"] = $e->Message();
			Db::rollback();
		}
		return json_encode($data);
	}
/** ------------------ 邮费配置 ------------------------------------ */
	public function poatconfig(){
		$Area = new Area();
		$arrFh = $Area->Freighthandling();
		$this->assign('arrFh',$arrFh);
		return $this->fetch();
	}
	public function poatconfigadd()
	{
		$id = input('get.id');
		$china = Db::table('shop_area')->where('ParentId',100000)->field('Id,Name')->select();
		$Area = new Area();
		$arrFh = $Area->Freighthandling();
		for ($i=0; $i < count($arrFh); $i++) { 
			if($arrFh[$i]['template_id'] == $id){
				$data = $arrFh[$i];
			}
		}
		if (empty($data)) {
			$data = null;
			$HtmlText = null;
		}else{
			$HtmlText = Area::getHtmlType($data);
		}
		$this->assign('HtmlText',$HtmlText);
		$this->assign('data',$data);
		$this->assign('china',$china);
		return $this->fetch("poatconfig_add");
	}
	/**
	 * [poatconfigsave 更新添加]
	 * @return [type] [description]
	 */
	public function poatconfigsave(){
		$Area = new Area();
		return $Area->Areasave($_POST['Id'],$_POST['data']);
	}
	public function getcity(){
		$data = Db::table('shop_area')->where('ParentId',input('get.id'))->field('Id,Name')->select();
			$this->ajaxReturn(json_encode($data),input('get.index'),'0');
	}
	public function TConfig_delete(){
		return Area::templaertConfig_delete(input('get.info'),input('get.id'));
	}
/*******************短信配置***************************/

	public function emeimpor()
	{
		$data = Db::table('shop_sms')->find();
		$scene = Db::table('shop_sms_scene')->field('Id,scene_name,scene_dsc,static')->select();
		$this->assign('scene',$scene);
		$this->assign('data',$data);
		return view();
	}

	/**
	 * [ajaxemeimporsave 更改短信配置]
	 * @return [type] [description]
	 * sms_autograph    短信签名
	 * sms_templateCode    短信模板ID
	 * sms_keyId    阿里云短信KeyID
	 * sms_keySecret    阿里云KeySecret
	 * sms_sum    验证码数量
	 * 
	 */
	public function ajaxemeimporsave()
	{
		parse_str(input('post.data'),$data);
		$sms = ['sms_keyId'=>$data['ses_c_keyid'],
				'sms_keySecret'=>$data['ses_c_ks'],
				'sms_sum'=>$data['ses_c_num']];
		for ($i=1; $i < 7; $i++) { 
			if (empty($data['scene'][$i])) {
				$data['scene'][$i] = 0;
			}else{
				$data['scene'][$i] = (int)$data['scene'][$i];
			}
		}
		//事务操作
		Db::startTrans();
		try {
			Db::table('shop_sms')->where('sms_id',1)->update($sms);
			for ($e=1; $e <= count($data['scene']); $e++) { 
			 	Db::table('shop_sms_scene')->where('Id',$e)->update(['static'=>$data['scene'][$e]]);
			}

			// 提交事务
			Db::commit();
			$mm['info'] = '0';
			$mm['msg'] = 'success :)';
		} catch (Exception $e) {
			// 回滚事务
			Db::rollback();
			$mm['info'] = '1';
			$mm['msg'] = 'error :(';
		}
		return $mm;
	}

/** ------------------ 短信模板配置 ------------------------------------ */
	public function emeconfig(){
		$data = Db::table('shop_sms_tmplate')->select();
		$this->assign('sms_tt',$data);
		return $this->fetch();
	}	
	public function emeconfig_add(){
		$id = input('get.id');
		$scene = Db::table('shop_sms_scene')->where('scene_index','0')->field("scene_name,Id")->select();
		$data = DB::table('shop_sms_tmplate')->where('st_id',$id)->find();
		// var_dump($data);die;
		$this->assign("scene",$scene);
		$this->assign("id",$id == "one"?'00':$id);
		$this->assign("data",$data);
		return $this->fetch();
	}

	// 删除短信模板配置
	public function emeconfigDe()
	{
		try {
			$bb = Db::table('shop_sms_tmplate')->where('st_id',input('get.id'))->field('scene_id')->find();
			$i = Db::table('shop_sms_tmplate')->where('st_id',input('get.id'))->delete();
			$ii = Db::table('shop_sms_scene')->where('Id',$bb['scene_id'])->update(['scene_index'=>'0']);
			if (!$i) {
				throw new Exception("删除短信配置失败 :)");
			}
			// 提交事务
			Db::commit();
	   		$data['core'] = '1';
	   		$data['msg'] = "success :)";
		} catch (Exception $e) {
			// 回滚事务
	   		$data['core'] = '0';
	   		$data['msg'] = $e->getMessage();
		}
		return json_encode($data);
	}


/**
     * @page  阿里短信函数
     * @page  $mobile               为手机号码
     * @page  $code                 为自定义随机数  
     * @page  $SMS_autograph        短信签名，要审核通过  
     * @page  $SMS_templateCode     短信模板ID，记得要审核通过的  
     * @page  $SMS_keyId            短信获取的accessKeyId
     * @page  $SMS_keysecret        短信获取的accessKeySecret  
     * 
     */
	public function smsSendcofig(){
		$phone = input('post.phone');
		$key = input('post.key');
		$ks = input('post.ks');
		$num = input('post.num');
		$SendSMS = new SmsSend;
		$code = '';
		for ($i=0; $i < $num; $i++) { 
		  	$code = $code.rand(0,9);
		}
		// 调用短信模板
		$result =  $SendSMS->SendSMS_sme($phone,$code,'学生商城练习短信模块','SMS_136381125',$key,$ks);
		return $result["Message"];
	}
	// 添加修改短信模板配置
	public function emeconfigsave()
	{
		return JxMiun::SMS_Save(input('get.info'),input('get.data'));
	}
/** ------------------ 邮箱配置 ------------------------------------ */
	public function emailConfig(){
		$data = Db::table('shop_email')->where('email_id',1)->find();
		$this->assign('data',$data);
		return view();
	}

	public function ajaxEmailUpdate(){
		parse_str(input('post.data'),$data);
		if (empty($data["start_regin"])) {
			$data["start_regin"] = 0;
		}
		$map = ['email_smtp'=>$data["email_smtp"],
				'email_name'=>$data["email_name"],
				'email_password'=>$data["email_password"],
				'email_dk'=>$data["email_dk"],
				'start_regin'=>$data["start_regin"],
				'email_time'=>time()];
		Db::table('shop_email')->where('email_id',1)->update($map);
		return "修改完成 :) ";
	}

/**
 * 系统邮件发送函数
 * @param string $smtp 邮件发送服务器
 * @param string $setmail 邮件账号
 * @param string $pw 授权码
 * @param string $email_dk 端口号
 * @param string $getmail 收件人
 * @param string $body 邮件内容
 * @param string $subject 邮件主题
 * @param string $title 标题
 * @param string $subject 邮件主题
 * @param string $attachment 附件
 * @return boolean
 * @author static7 <static7@qq.com>
 */
	public function emailsaveconfig()
	{
		$getmail = input('post.getmail');
		$setmail = input('post.setmail');
		$smtp = input('post.smtp');
		$email_dk = input('post.email_dk');
		$pw = input('post.pw');
		$body = "如果你看到这条邮件，则证明你成功配置了邮箱，如果你不是操作人员，则忽略此邮件！";
        $title= '邮箱配置';   //标题
        $subject='邮箱配置';    //body
		/*
		return $EmailServer->send_out_mail($setmail,$pw,$name,$title,$subject,$content);
	*/
		$EmailServer = new EmailServer();
		return $EmailServer->send_out_mail($smtp,$setmail,$pw,$email_dk,$getmail,$body,$subject,$title,$subject);
	}

}