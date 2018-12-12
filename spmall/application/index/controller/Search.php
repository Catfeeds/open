<?php
namespace app\index\controller;

use app\index\common\Base;
use app\index\common\G_Logic;
use think\Session;
use app\user\controller;
use think\Db;

//前台商品显示 .class.php

class Search extends Base
{
	//显示index
    public function index($id = 0)
    {
    	$link = $_SERVER['QUERY_STRING'];
		$sele = G_Logic::goodsList($id,$link);  // 返回商品类 -> 规格
		$navhtml = G_Logic::navigation($id);  // 返回导航栏

       $this->linkbutton();
       $this->assign([
				'd' => $sele['d'],
				'shtm' => $sele['ht1'],
				'nav' => $navhtml,
			]);
        return $this->fetch();
    }
    public function cat()
	{
		$user_id = Session::get('user_id');
		if(isset($user_id)){
			//var_dump(ROOT_PATH);die;
			return $this->redirect('./user/cat');
		}else{
			return $this->redirect('register');
		}
	}

	public function item()
	{
    	$obj = $this->decorateSearch_pre("apple猜猜我是谁你好吗");
	}

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
            <a href="/">返回首页</a>
        </div>
    </div>';
		return $html;
	}

    public function emp(){
        $a1 = null;
        $a2 = false;
        $a3 = "null";
        $a4 = " ";
        $a5 = 0;
        $a6 = array();
        $a7 = array(array());
        $a8 = 0;
        $a9 = "0";

        echo empty($a1)?"true":"false";
        echo empty($a2)?"true":"false";
        echo empty($a3)?"true":"false";
        echo empty($a4)?"true":"false";
        echo empty($a5)?"true":"false";
        echo empty($a6)?"true":"false";
        echo empty($a7)?"true":"false";
        echo "<br>";
        echo $a8==$a9;
    }
}
