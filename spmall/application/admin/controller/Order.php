<?php

namespace app\admin\controller;


use app\admin\common\Base;
use think\Session;
use think\Db;
use Exception;

/**
*    订单类
*/
class Order extends Base
{
	public function index()
	{
		return $this->fetch();
	}
}