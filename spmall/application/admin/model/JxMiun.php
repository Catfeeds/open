<?php
namespace app\admin\model;

use app\common\random;
use app\common\extend\Email\EmailServer;
use Exception;
use think\Db;
use think\Session;
use think\Loader;
/**
*   公共模板添加类
*/
class JxMiun
{
	/***************  公共逻辑类**************************/

   // SMS
   /**
    * [SMS_Save 短信模板]
    * @param [type] $info [判断添加还是修改]
    * @param [type] $data [数据]
    */
   public static function SMS_Save($info, $data)
   {
   	$data = json_decode($data,true);
   	$map = ["sms_sign"=> $data['sms_name'],
   			"sms_tpl_code"=> $data['sms_line'],
   			"tpl_content"=> $data['sms_textarea'],
   			"scene_id"=> $data['sms_cj'],
   			"up_time"=> time()];
   			Db::startTrans();
   		try {
   			// 添加
   			if ($info == '0') {
   				# code...
	   			$i = Db::table('shop_sms_tmplate')->insertGetId($map);
	   			$ii = Db::table('shop_sms_scene')->where('Id', $data['sms_cj'])->update(["scene_index"=>"1"]);
   			}else{
   				Db::table('shop_sms_tmplate')->where('st_id',$info)->update($map);
   			}
   			// 提交事务
   		Db::commit();
   		$data['core'] = '1';
   		$data['msg'] = "success :)";
   		} catch (Exception $e) {
   			// 回滚事务
   			Db::rollback();
   		    $data['core'] = '1';
   			$data['msg'] = $e->getMessage();
   		}
   		return json_encode($data);
   }

   /**********   文章管理   ********************/
   public static function CategoryUpdate($data,$info){

   	switch ($info) {
   		case 'update':
   			# code...
   			$map = [$data[1] => $data[0]];
   			$m = array();
   			$info = Db('shop_artcleclass')->where('ac_id',$data[2])->update($map);
   			// var_dump($map);die;
	   		if (!$info) {
	   			$m[0] = '0';
	   			$m[1] = '更新失败';
	   		}else{
	   			$m[0] = '1';
	   			$m[1] = '更新成功';
	   		}
   			break;
   		case 'dalete':

   			$in = Db('shop_artcleclass')->where('ac_id',$data[0])->field('is_sys')->find();
   			$ll = Db('shop_articlelist')->where('ac_id',$data[0])->select();
   			if (count($ll) != 0) {
   				$m[0] = '0';
	   			$m[1] = '请删除分类下的文章 :(';
   			}else if ($in["is_sys"] == 1) {
	   			$m[0] = '0';
	   			$m[1] = '系统公告不可删除 :(';
   			}else{
   				$info = Db('shop_artcleclass')->where('ac_id',$data[0])->delete();
   				if (!$info) {
		   			$m[0] = '0';
		   			$m[1] = '更新失败';
		   		}else{
		   			$m[0] = '1';
		   			$m[1] = '更新成功';
		   		}
   			}
   			break;
   		case 'insert':

   			$in = Db('shop_artcleclass')->where('ac_title',$data[0])->field('is_sys')->find();
   			// var_dump(count($in));
   			// die;
   			if (count($in) != 0) {
	   			$m[0] = '0';
	   			$m[1] = '分类名称已存在 :(';
   			}else{
   				$data = ['ac_title' => $data[0]];
   				$info = Db('shop_artcleclass')->insert($data);
   				if (!$info) {
		   			$m[0] = '0';
		   			$m[1] = '插入失败';
		   		}else{
		   			$m[0] = '1';
		   			$m[1] = '插入成功';
		   		}
   			}
   			break;
   		default:
   			# code...
	   			$m[0] = '0';
	   			$m[1] = '出错';
   			break;
   	}
	   	return $m;
   }


  /********************************************************************/
  /**
   * [artiListAdd 文章插入 或者更新]
   * @param  [type] $data [数据]
   * @param  [type] $type [样式]
   * @param  [type] $id [id]
   * @return [type]       [信息]
   */
  public static function artiListAdd($data,$type,$id){
		// 事务操作
  		Db::startTrans();
  		try {
  			if ($type == "insert") {
				$validate = Loader::Validate("ArticlelistValidate");
				if (!$validate->check($data)) {
					return $validate->getError();
				}
				if (empty($data['al_static'])) {
					$data['al_static'] = '0';
				}
	  			if ($id == "0") {
	  				# code...
	  				$m = Db('shop_articlelist')->insertGetId($data);
	  			}else{
	  				$m = Db('shop_articlelist')->where('Id',$id)->update($data);
	  			}
  			}else if ($type == "delete") {
  				$m = Db('shop_articlelist')->where('Id',$id)->delete();
  			}else if($type == "static"){
  				$m = Db('shop_articlelist')->where('Id',$id)->update(['al_static'=>$data]);
  			}

  			if (!$m) {
  				return "插入失败 !";
  			}

  			// 提交事务
  			Db::commit();
  			return "1";
  		} catch (Exception $e) {
  			// 回滚事务
  			Db::rollback();
  			return "插入失败 !";
  		}
  } 

  /**
   * [linkCDU 友情链接的增删改]
   * 
   * @param  [type] $data [数据包]
   *         数据包分析      0: 改变值
   *         				 1：字段名
   *         				 2: id
   *         				 3: 操作类型
   * @return [type]       [description]
   */
  public static function linkCDU($data){
  	Db::startTrans();
  	try {
  		$mp = '';
	  	switch ($data[3]) {
	  		case 'insert':
	  			# code...
	  			$i = Db('shop_link')->insertGetId(["link_name"=>$data[0]]);
	  			if (!$i) {
	  				throw new Exception("insert Error ");
	  			}
	  			break;
	  		
	  		case 'dalete':
	  			# code...# code...
	  			$i = Db('shop_link')->where("link_id",$data[2])->delete();
	  			if (!$i) {
	  				throw new Exception("dalete Error ");
	  			}
	  			break;
	  		
	  		case 'update':
	  			$code = [$data[1] => $data[0]];
	  			$i = Db('shop_link')->where("link_id",$data[2])->update($code);
	  			if (!$i) {
	  				throw new Exception("dalete Error ");
	  			}
	  			break;
	  		default:
	  			# code...
	  			$map[0] = "0";
	  			$map[1] = "error";
	  			break;
	  	}

	  	// 提交事务
	  	Db::commit();
	  	$map[0] = "1";
	  	$map[1] = "success :) ";
  	} catch (Exception $e) {
  		
	  	// 回滚事务
	  	Db::rollback();
	  	$map[0] = "0";
	  	$map[1] = $e->getMessage();
  	}
  	// die;
  	return $map;
  }


/******************   用户数据操作   *********************************/
  /**
   * [userADU 用户数据操作]
   * @param  [type] $data [数据]
   * @param  [type] $info [操作类型]
   * @return [type]       [状态]
   */
  public function userADU($data, $info){
    $random = new random();
    $da['info'] = '操作错误';
    $da['static'] = '0';
    if ($info == "info") {
      
    }
    //操作类型switch
    Db::startTrans();
    try {
      switch ($info) {
        case 'insert':     // 添加用户
          # code 验证规则
          $validate = Loader::Validate("UserValidate");
          if (!$validate->check($data)) {
            throw new Exception($validate->getError());
          }

          $salt = $random->getRandChar(6);
          $map = [
              'user_nick' => $data['n'],
              'user_password' => md5(md5($data['p']).md5($salt)+101),   //密码
              'user_mobile' => $data['i'],     // 状态吗
              'user_email' => $data['e'],
              'user_qq' => $data['q'],
              'user_reg_time' => time(),
          ];
          $i = Db('shop_user')->insertGetId($map);
          $b = Db('shop_salt')->insertGetId([
                  'user_id' => $i,
                  'salt_pw' => $salt,
                  'salt_pay_pwd' => 0,
                  'salt_time' => time(),
            ]);
          if (!$i && !$b) {
            throw new Exception("添加用户出错 :(");
          }
          // 提交事务
          Db::commit();
          $da['info'] = "添加成功";
          $da['static'] = '1';
          break;
        
        case 'znx':
           # code...
           $b = 0;
           $arr = array();
           if ($data[2] != 'whole') {
             $arr = explode('-',$data[1]);
             array_pop($arr);
           }
           
           $map = [
                'admin_id' => Session::get('admin_name'),
                'message' => $data[0],
                'type' => $data[2] == "whole"?1:0,
                'category' => 0,
                'send_time' => time()
           ];
           $e = Db('shop_message')->insertGetId($map);
           for ($i=0; $i < count($arr); $i++) { 
            $m = Db('shop_user_message')->insertGetId([
                  'user_id' => $arr[$i],
                  'message_id' => $e,
                  'category' => 0,
                  'status' => 0,
                  'delete' => 0,
              ]);
            if (!$m) {
             $b++;
            }
           }
           if (!$e && $b == count($arr)) {
             throw new Exception("出错 :( ");
           }

          // 提交事务
          Db::commit();
          $da['info'] = "添加成功";
          $da['static'] = '1';
           break; 

        case 'update':
          # code...
          $user_id = $data['user_id'];
          if (isset($data['user_password'])) {
            if ($data['user_password'] != '' && count($data['user_password']) != 0) {
              if ($data['user_password'] != $data['user_password_on']) {
               throw new Exception("密码要一致 :( ");
              }else{
                unset($data['user_password_on']);
              }
            }else{
                unset($data['user_password']);
                unset($data['user_password_on']);
            }
          }
          unset($data['user_id']);
          $u = Db('shop_user')->where('user_id',$user_id)->update($data);
          if (!$u) {
             throw new Exception("更新出错 :( ");
            }

          // 提交事务
          Db::commit();
          $da['info'] = "更新成功，请清除缓存";
          $da['static'] = '1';
          // die;
          break;

          case 'userfrozen':
            $map = [
                  'user_money' => $data['user_money_select'] == '1'?$data['user_money']:0-$data['user_money'],
                  'pay_points' => $data['pay_points_select'] == '1'?$data['pay_points']:0-$data['pay_points'],
                  'frozen_money' => $data['frozen_money_select'] == '1'?$data['frozen_money']:0-$data['frozen_money'],
            ];

            if($data['desc'] == '' || count($data['desc']) == 0){
                $da['info'] = "请填写操作说明";
              break;
            }
            if($data['id'] == '' || empty($data['id'])){
                $da['info'] = "参数有误";
              break;
            }
            $user = Db('shop_user')->field('user_id,user_money,user_frozen_money,user_pay_points,user_is_lock')->where('user_id',$data['id'])->find();

            //加减用户资金
            $user_money = (int)$user['user_money']+(int)$map['user_money'];
            if ($user_money < 0){
                $da['info'] = "用户剩余资金不足！！";
                break;
            }
            //加减用户积分
            $user_pay_points = (int)$user['user_pay_points']+(int)$map['pay_points'];
            if($user_pay_points < 0){
                $da['info'] = "用户剩余积分不足！！";
                break;
            }

            //有加减冻结资金的时候
            if ($map['frozen_money'] != '0') {
              $in = (int)$user['user_money'] - (int)$user['user_frozen_money'];
              $revision_frozen_money = (int)$user['user_frozen_money']+(int)$map['frozen_money'];
           

              if($data['frozen_money_select'] == 1 && $map['frozen_money'] > $in)
                {
                    $da['info'] = "用户剩余资金不足！！";
                    break;
                }
                if($data['frozen_money_select'] == 2 && abs($map['frozen_money']) > $user['user_frozen_money'])
                {
                    $da['info'] = "冻结的资金不足！！";
                    break;
                }
               $rfm = Db('shop_user')->where('user_id',$data['id'])->update(['user_frozen_money' => $revision_frozen_money]);
            }
              $d = Db('shop_user')->where('user_id',$data['id'])->update(['user_money' => $user_money,'user_pay_points' => $user_pay_points]);
              $insert = [
                    'user_id' => $data['id'],
                    'user_money' => $map['user_money'],
                    'frozen_money' => $map['frozen_money'],
                    'pay_points' => $map['pay_points'],
                    'change_time' => time(),
                    'desc' => $data['desc'],
              ];
              $log = Db('shop_account_log')->insertGetId($insert);
             if (!$d && !$rfm && !$log) {
                throw new Exception("数据库出错");
             }
            
            // 提交事务
          Db::commit();
          $da['info'] = "提交成功";
          $da['static'] = '1';

          break;

        default:
          # code...
          $da['info'] = "请正确使用 :)";
          break;
      }
    } catch (Exception $e) {
      
      // 回滚事务
      Db::rollback();
      $da['info'] = $e->getMessage();
    }
    return $da;
  }



    /**
     * [SendEmailM 发送Email]
     * @param [type]  $data [数据包]
     * @param integer $info [炒作]
     */
    public static function SendEmailM($data,$info=0){
      $EmailServer = new EmailServer();

      $DbEmail = Db('shop_email')->select();

      $setmail = $DbEmail[0]['email_name'];
      $smtp = $DbEmail[0]['email_smtp'];
      $email_dk = $DbEmail[0]['email_dk'];
      $pw = $DbEmail[0]['email_password'];

      $getmail = $data[0];
      $body = $data[2];
      $title= "系统通知";   //标题
      $subject= $data[1];    //body

      return json_decode($EmailServer->send_out_mail($smtp,$setmail,$pw,$email_dk,$getmail,$body,$subject,$title,$subject),true);
    }

    public function UserLeverEdit($data,$info=0){
      $valist = Loader::Validate('UserLeverValidate');
      $Ui['static'] = '0';
      $Ui['info'] = '';
      if ($info == "add") {
        if (!$valist->check($data)) {
          $Ui['info'] = $valist->getError();
        return $Ui;
        }
      }else{
        $l = DB::query("call S_user_lever('shop_user_lever',".($info-1).")");
        $r = DB::query("call S_user_lever('shop_user_lever',".($info+1).")");
        // $r = DB::query("call S_user_lever('shop_user_lever','1')");
        // var_dump($r);
        // die;
        if (!empty($l[0][0])) {
          $lm = $l[0][0];
          if ($lm['lever_grade'] > (int)$data['lever_grade'] || $lm['lever_grade'] == (int)$data['lever_grade']) {
            $Ui['info'] = "等级lever 不可以小于上一个".$l[0][0]['lever_grade'];
            return $Ui;
          }
          if ($lm['lever_position'] > (int)$data['lever_position'] || $lm['lever_position'] == (int)$data['lever_position']) {
            $Ui['info'] = "消费额度 不可以小于上一个".$l[0][0]['lever_position'];
            return $Ui;
          }
          if ($lm['lever_discount'] < (int)$data['lever_discount'] || $lm['lever_discount'] == (int)$data['lever_discount']) {
            $Ui['info'] = "折扣率 不可以大于上一个".$l[0][0]['lever_discount'];
            return $Ui;
          }
        }
        if (!empty($r[0][0])) {
          if ($r[0][0]['lever_grade'] < (int)$data['lever_grade'] || $r[0][0]['lever_grade'] == (int)$data['lever_grade']) {
            $Ui['info'] = "等级lever 不可以大于下一个".$r[0][0]['lever_grade'];
            return $Ui;
          }
          if ($r[0][0]['lever_position'] < (int)$data['lever_position'] || $r[0][0]['lever_position'] == (int)$data['lever_position']) {
            $Ui['info'] = "消费额度 不可以大于下一个".$r[0][0]['lever_position'];
            return $Ui;
          }
          if ($r[0][0]['lever_discount'] > (int)$data['lever_discount'] || $r[0][0]['lever_discount'] == (int)$data['lever_discount']) {
            $Ui['info'] = "折扣率 不可以小于下一个".$r[0][0]['lever_discount'];
            return $Ui;
          }
        }
      }

      Db::startTrans();
      try{
        $map = [
            "lever_name"=> $data['lever_name'],
            "lever_grade"=> $data['lever_grade'],
            "lever_position"=> $data['lever_position'],
            "lever_discount"=> $data['lever_discount'] ,
            "lever_desc"=> $data['lever_desc'],
        ];
        if ($info == "add") {
          $o = Db('shop_user_lever')->insertGetId($map);
        }else{
          $o = Db('shop_user_lever')->where('lever_grade',$info)->update($map);
        }
        if (!$o) {
          throw new Exception("操作失败");
        }
        // 提交事务
        Db::commit();
      $Ui['static'] = '1';
      $Ui['info'] = '操作成功';

      }catch(Exception $e){
        // 回滚事务
        
        Db::rollback();
        $Ui['info'] = $e->getMessage();
      }

        return $Ui;
    }





/**
 * 导出excel
 * @param $strTable 表格内容
 * @param $filename 文件名
 */
public static function downloadExcel($strTable,$filename)
{
  header("Content-type: application/vnd.ms-excel");
  header("Content-Type: application/force-download");
  header("Content-Disposition: attachment; filename=".$filename."_".date('Y-m-d').".xls");
  header('Expires:0');
  header('Pragma:public');
  echo '<html><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />'.$strTable.'</html>';
}


}