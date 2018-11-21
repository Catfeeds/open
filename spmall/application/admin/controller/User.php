<?php
namespace app\admin\controller;

use app\admin\common\Base;
use app\common\redis\RedisClice;
use app\admin\model\JxMiun;
use think\Db;
use think\Config;

/**
* 
*/
class User extends Base
{
	// redis 示例话
	public function redisConfig()
	{
		$conf = [
			'host' =>  '127.0.0.1',
			'port' =>  '6379',
			'auth' =>  '3600',
			'index' =>  '11'
		];
		return new RedisClice($conf);
	}

	// 删除user
	public function deleteRedis(){
		$redis = $this->redisConfig();
		$redis->delete('hsah','userlist');
		$redis->delete('userlistAdd');
		$info = "清除失败 :(";
		if ($redis->hLan('hsah','userlist') == 0 && $redis->hLan('userlistAdd') == 0) {
		$info = "清除成功 :)";
		}
		return $info;
	}

	// 用户管理
	public function index(){
		$curr = empty(input('get.curr'))?1:input('get.curr');
		$limit = empty(input('get.limit'))?10:input('get.limit');
		$search = empty(input('get.search'))?'':input('get.search');
		$searchSelect = empty(input('get.searchSelect'))?'weikong':input('get.searchSelect');

		$redis = $this->redisConfig();

		$user = Db('shop_user')->select();

		if ($redis->hGetJson('hsah','userlist')) {
			$userlist = json_decode($redis->hGetJson('hsah','userlist'),true);
		}else{
			$userlist = Db::view('shop_user','user_id,user_nick,user_mobile,user_email,user_money,user_pay_points,user_total_amount,user_reg_time,user_is_lock')
					->view('shop_user_lever','lever_name','shop_user.user_lever = shop_user_lever.lever_grade')
					->order('user_id','desc')
					->select();
			$redis->hSetJson('hsah','userlist',json_encode($userlist),'86400');
			foreach ($userlist as $value) {
					$redis->hSetJson('userlistAdd', $value['user_id'],json_encode($value),'86400');
			}
	}

	$bin = 0;
	$ban = 0;
	$min = array();
	$max = array();
			if($searchSelect != "weikong") {
				for ($i= 0; $i < count($userlist); $i++) { 
						if ($userlist[$i][$searchSelect] == $search) {
						$max[$ban] = $userlist[$i];
						$ban++; 
					}
				}
				$userlist = $max;
			}
			for ($i= ($limit * ($curr-1)); $i < $limit * $curr; $i++) { 
				if (!empty($userlist[$i])) {
					$min[$bin] = $userlist[$i];
					$bin++; 
				}
			}
		$this->assign([
					'userlist' => $min,
					'datanum' => count($userlist),
					'limit' => $limit,
					'curr' => $curr,
					'search' => $search,
					'searchSelect' => $searchSelect,
			]);
		return view();
	}

	public function ajaxUserAUD(){
		$jm = new JxMiun();
		$data = $jm->userADU(json_decode(input('post.data'),true),input('post.info'));
		$this->ajaxReturn('',$data['info'],$data['static']);
	}

	public function SendEmail(){
		$arr = array(input('get.e'),input('get.t'),input('get.c'));
		$data = JxMiun::SendEmailM($arr);
		$this->ajaxReturn('1',$data['message'],$data['state']);
	}

	// 添加编辑用户
	public function UserAdd(){


		switch (input('get.name')) {
			case 'add':   //新增
				# code...
				return view('userAdd');
			
			case 'update':  // 更新
				# code...
				$lever = Db('shop_user_lever')->field('lever_grade,lever_name')->select();
				$data = Db('shop_user')->where('user_id',input('get.id'))->find();
				// var_dump($data);
				$this->assign([
							'lever' => $lever,
							'data' => $data,
						]);
				return view('userUpdate');


			case 'getznx':  // 站内信
				# code...
				$GetId = '';
				$text = '';
				$index = '';
				$userArr = explode(',', input('get.id'));
				$redis = $this->redisConfig();
				$user = $redis->hGet('userlistAdd'); // userlistAdd
				foreach ($userArr as $key) {
					$index = json_decode(json_decode($user[$key],true),true);
					$text = $text.'  '.$index['user_id'].'   '.$index['user_nick'].'&#10;';
					$GetId = $GetId.$index['user_id'].'-';
				}

				$this->assign([
							'text' => $text,
							'GetId' => $GetId,
					]);
				return view('userZNX');


			case 'getemail':   //邮箱
				# code...
				return view('useremaile');

			case 'capital':
				// # code...
				$capital = Db('shop_account_log')->where('user_id',input('get.id'))->field('user_money,frozen_money,pay_points,desc,change_time,log_id')->order('log_id desc')->select();
				$this->assign([
						'id' => input('get.id'),
						'capital' => $capital,
					]);
				return view('capital');
				break;

			case 'address':
				# code...
				$data = Db::query('SELECT a.consignee,a.address,a.mobile,a.zipcode,a.consignee,a.address,a.address_id,
			b.`Name` as country_1,
			c.`Name` as province_1,
			d.`Name` as city_1,
			e.`Name` as district_1,
			f.`Name` as twon_1,
			g.`user_nick` as user_name
			from shop_user_address a 
						inner join shop_area b on a.country = b.Id 
						inner join shop_area c on a.province = c.Id
						inner join shop_area d on a.city = d.Id
						inner join shop_area e on a.district = e.Id
						inner join shop_area f on a.twon = f.Id
						inner join shop_user g on a.user_id = g.user_id
						where a.user_id ='.input('get.id'));
		$this->assign('data',$data);
				return view('address');
				break;

			case 'zjtj':
				# code...
				$capital = Db('shop_user')->where('user_id',input('get.id'))->field('user_money,user_frozen_money,user_pay_points')->find();
				$this->assign([
						'id' => input('get.id'),
						'capital' => $capital,
					]);
				return view('capital_edit');
				break;
		}
	}
	
	// 导出列表数据 
	 public function export_user(){

	 	$text = input('get.text');
	 	$type = input('get.type');
	 	if ($type != "weikong") {
	 		$condition[$type] = $text;
	 	}
	 	$condition['user_id'] = ['>','0'];
    	$strTable ='<table width="500" border="1">';
    	$strTable .= '<tr>';
    	$strTable .= '<td style="text-align:center;font-size:12px;width:120px;">会员ID</td>';
    	$strTable .= '<td style="text-align:center;font-size:12px;" width="100">会员昵称</td>';
    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">会员等级</td>';
    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">手机号</td>';
    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">邮箱</td>';
    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">注册时间</td>';
    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">最后登陆</td>';
    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">余额</td>';
    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">积分</td>';
    	$strTable .= '<td style="text-align:center;font-size:12px;" width="*">累计消费</td>';
    	$strTable .= '</tr>';
        $count = DB::name('shop_user')->where($condition)->count();
    	$p = ceil($count/5000);
    	for($i=0;$i<$p;$i++){
    		$start = $i*5000;
    		$end = ($i+1)*5000;
    		$userList = Db('shop_user')->where($condition)->order('user_id')->limit($start.','.$end)->select();
    		if(is_array($userList)){
    			foreach($userList as $k=>$val){
    				$strTable .= '<tr>';
    				$strTable .= '<td style="text-align:center;font-size:12px;">'.$val['user_id'].'</td>';
    				$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['user_nick'].' </td>';
    				$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['user_lever'].'</td>';
    				$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['user_mobile'].'</td>';
    				$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['user_email'].'</td>';
    				$strTable .= '<td style="text-align:left;font-size:12px;">'.date('Y-m-d H:i',$val['user_reg_time']).'</td>';
    				$strTable .= '<td style="text-align:left;font-size:12px;">'.date('Y-m-d H:i',$val['user_last_time']).'</td>';
    				$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['user_money'].'</td>';
    				$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['user_pay_points'].' </td>';
    				$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['user_total_amount'].' </td>';
    				$strTable .= '</tr>';
    			}
    			unset($userList);
    		}
    	}

    	$strTable .='</table>';
    	JxMiun::downloadExcel($strTable,'users_'.$i);
    	exit();
    }


	// 等级列表
	public function levelList(){
		$lelist = Db('shop_user_lever')->order('lever_grade asc')->select();
		$this->assign('list',$lelist);
		return view('levelList');
	}

	public function levelList_edit()
	{
		$lever = null; // 定义一个
		if(input('get.name') == "edit"){
			$lever = Db('shop_user_lever')->where('lever_grade',input('get.id'))->find();
		}
		$this->assign('lever',$lever);
		$this->assign('name',input('get.name'));
		return view('levelList_edit');
	}

	public function AjaxLeverLi(){
		$i = new JxMiun();
		$d = $i->UserLeverEdit(json_decode(input('post.data'),true),input('post.info'));
		$this->ajaxReturn('',$d['info'],$d['static']);
	}

	public function AjaxLeverLiDe(){
		$i = Db::query('select count(*) from shop_user join shop_user_lever on shop_user.user_lever = shop_user_lever.lever_grade where shop_user_lever.lever_grade = '.input('post.id'));
		if ($i[0]['count(*)'] != 0) {
			$this->ajaxReturn('',"删除失败，用户表有此等级",'0');
		}else{
		$b = Db('shop_user_lever')->where('lever_grade',input('post.id'))->delete();
			if ($b != 0) {
				$this->ajaxReturn('','删除成功','1');
			}else{
				$this->ajaxReturn('','删除失败','0');
			}
		}

	}
	// 收货地址管理
	public function address(){
		$data = Db::query('SELECT a.consignee,a.address,a.mobile,a.zipcode,a.consignee,a.address,a.address_id,
			b.`Name` as country_1,
			c.`Name` as province_1,
			d.`Name` as city_1,
			e.`Name` as district_1,
			f.`Name` as twon_1,
			g.`user_nick` as user_name
			from shop_user_address a 
						inner join shop_area b on a.country = b.Id 
						inner join shop_area c on a.province = c.Id
						inner join shop_area d on a.city = d.Id
						inner join shop_area e on a.district = e.Id
						inner join shop_area f on a.twon = f.Id
						inner join shop_user g on a.user_id = g.user_id');
		$this->assign('data',$data);
		return view('address');
	}

	// 充值记录
	public function recharge(){

	}

	// 提现申请
	public function withdrawals(){

	}

	// 汇款记录
	public function remittance(){

	}

	// 会员签到
	public function signList(){
		$sign = empty(input('get.nick'))?'':input('get.nick');
		$data = Db('shop_user_sign')->where('user_nick','like','%'.$sign.'%')->select();
				$this->assign('data',$data);
				$this->assign('searchSelect',$data);
		return view('signList');
	}

	// 控制器不存在是调用
	public function _empty(){
		$html = '<div style="text-align: center;margin: 0px auto;">
		<br><br><br>
        <div style="font-size: 48px;
    line-height: 50px;
    margin-bottom: 50px;">
            o(╥﹏╥)o
        </div>
        <div style="    font-size: 64px;
    line-height: 80px;">
            404
        </div>
        <div style="    font-size: 36px;
    line-height: 72px;
    font-weight: 700;">
            页面找不到了
        </div>
        <div class="return">
            <a href="https://www.csdn.net">返回首页</a>
        </div>
    </div>';
		return $html;
	}

}