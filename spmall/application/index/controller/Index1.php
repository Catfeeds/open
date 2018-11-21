<?php
namespace app\index\controller;

use app\index\common\Base;
use app\index\validate\Register;
use app\index\sms\SendSMS;
use app\index\sms\SMS_dd;
use app\index\model\Shop_user;
use app\index\model\Goods_cate;
use think\viewport;
use think\Request;
use think\Session;
use think\Cache;
use think\Db;

//前台商品显示 .class.php

class Index extends Base
{


	//显示index
    public function index()
    {
    
        $buttom_order = array();
        $user_id = Session::get('user_id');
        $user = Session::get('user');
        $buttom_conter = Db::table('shop_optimi')->select();
        $data = Cache::get('link');//去缓存  
        $goods_cate = Goods_cate::goos_list__handel(Goods_cate::select());    //  商品类别表
       $goods_cate = json_decode($goods_cate,true);
       // var_dump($goods_cate);die;
        if ($data) {
            $link = Cache::get('link');
        }else{
            $link = Db::table('shop_link')->select();
            Cache::set('link',$link,3600);
        }
        if(isset($user)){
            $data = Db::table('shop_user')->where('user_id',$user_id)->select();
            $lever = Db::table('shop_user_lever')->where('lever_id',$data[0]['user_lever'])->select();
            //var_dump($lever[0]["lever_name"]);die;
            $this->assign([
                'data' => $data[0],
                'lever' => $lever[0]["lever_name"],
                ]);
        }
        $this->assign([
            'user'=> $user,
            'buttom_conter' => $buttom_conter,
            'link' => $link,
            'goods_cate' => $goods_cate
        ]);
        return $this->fetch();
    }


    // 显示 login 
    public function login()
    {
        return $this->fetch();
    }

     // 显示 login  路由跳转
    public function hello($id)
    {
        return $id;
    }

    // 显示 register 
    public function register()
    {
        return $this->fetch();
    }
    

    //验证登录
    public function login_register(Request $request)
    {
        $obj = $request->post('obj/a');
        if (!captcha_check($obj['login_yzm'])) {
        	$data['static'] = 0;
        	$data['message'] = "验证码错误";
       		return json_encode($data);
       		break;
        }else{
        	if ($obj['setter'] == 0) {   //  会员名登录
      			$map = ['user_nick'=>$obj['login_user']];
        		$datt = Shop_user::get($map);
	       	}else if ($obj['setter'] == 1) {   // 手机号登录
	       		$map = ['user_mobile'=>$obj['login_user']];
        		$datt = Shop_user::get($map);
	       	}else if ($obj['setter'] == 2) {    //邮箱登录
	       		$map = ['user_emaile'=>$obj['login_user']];
        		$datt = Shop_user::get($map);
	       	}
	       		if (!is_null($datt)) {
        	$pw = Db::table('shop_salt')->where('user_id',$datt->user_id)->field('salt_pw')->select();
	       		}
	       	if (is_null($datt)) {
	       			$data['static'] = 0;
        			$data['message'] = "账号或密码错误！！";
	       		}else if($datt->user_is_lock == 0){
	       			$data['static'] = 0;
        			$data['message'] = "账号被锁定，请联系管理员解封";
	       		}else if($datt->user_password == MD5(MD5($obj['login_password']).$pw['0']['salt_pw'])){
	       			$data['static'] = 1;
        			$data['message'] = "登录成功。。";
                    $user_log_insert = ['user_id' => $datt->user_id,'user_log_time' => time(), 'user_log_ip' => $request->ip(), 'user_log_list_address' => $_SERVER["HTTP_REFERER"]];
                    Db::table('shop_user_log')->insert($user_log_insert);
        			/* - - 登录成功  保存用户session - -   */
        			Session::set('user_id',$datt->user_id);
                    Session::set('user',$obj['login_user']);
        			Session::set('user_last_time',$datt->user_last_time);
        			Session::set('user_last_ip',$datt->user_last_ip);
        			/* - - 登录成功  保存用户session - -   */
        			Shop_user::where('user_nick',$obj['login_user'])->setInc('user_is_num');    //自增登录次数
	       		}else{
	       			$data['static'] = 0;
        			$data['message'] = "账号或密码错误！！";
	       		}
        }

        return json_encode($data);
       	
    }
    
    public function loginout(){
        var_dump(input('post.username'));
    }
}
