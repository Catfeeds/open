<?php

namespace app\admin\controller;


use app\common\redis\RedisClice;
use app\admin\common\Base;
use think\Session;
use think\Db;
use Exception;

/**
*    订单类
*/
class Order extends Base
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

	public function index()
	{
		return $this->fetch();
	}
}