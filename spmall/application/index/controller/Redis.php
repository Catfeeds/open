<?php
namespace app\index\controller;

use app\index\common\Base;
use app\common\redis\RedisClice;
use app\common\redis\RedisServers;
use think\Cache;
use think\Db;
/**
* 
*/
class Redis extends Base
{
	public $redis ;

	public function __construct()
	{
		$conf = [
			'host' =>  '127.0.0.1',
			'port' =>  '6379',
			'auth' =>  '3600',
			'index' =>  '11'
		];
		$redis = new RedisClice($conf);
	}

	public function ceshi()
	{
		$conf = [
			'host' =>  '127.0.0.1',
			'port' =>  '6379',
			'auth' =>  '3600',
			'index' =>  '11'
		];
		$redis = new RedisClice($conf);

		




$strQueueName  = 'Test_bihu_queue';
 $redis->delete($strQueueName);
//进队列
$redis->rPush($strQueueName, json_encode(['uid' => 1,'name' => 'Job']));
$redis->rPush($strQueueName, json_encode(['uid' => 2,'name' => 'Tom']));
$redis->rPush($strQueueName, json_encode(['uid' => 3,'name' => 'John']));
echo "---- 进队列成功 ---- <br /><br />";
 
//查看队列
$strCount = $redis->lRanges($strQueueName, 0, -1);
echo "当前队列数据为： <br />";
print_r($strCount);
 
//出队列
$strCounta = $redis->lPop($strQueueName);
echo "<br /><br /> ---- 出队列成功 ---- <br /><br />";
 
//查看队列
$strCounta = $redis->lRanges($strQueueName, 0, -1);
echo "当前队列数据为： <br />";
print_r($strCounta);

echo "<br>";
//以下是 pub.php 文件的内容 cli下运行
//
$strChannel = 'Test_bihu_channel';
 
//发布
$redis->publish($strChannel, "来自{$strChannel}频道的推送");
echo "---- {$strChannel} ---- 频道消息推送成功～ <br/>";
// $redis->close();



$strKey = 'Test_bihu_comments';

//设置初始值
$redis->set($strKey, 0);
 
$redis->INCR($strKey);  //+1
$redis->INCR($strKey);  //+1
$redis->INCR($strKey);  //+1
$redis->INCR($strKey);  //+1
 
$strNowCount = $redis->get($strKey);
 
echo "---- 当前数量为{$strNowCount}。 ---- ";

$strKey = 'Test_bihu_score';
 
echo "<br>";
echo "<br>";
echo "<br>";
//存储数据
$Tom = ['name' => 'Tom'];
$John = ['name' => 'John'];
$Jerry = ['name' => 'Jerry'];
$Job = ['name' => 'Job'];
$LiMing = ['name' => 'LiMing'];

$redis->zadd($strKey, $Tom, '50');
$redis->zadd($strKey, $John, '70');
$redis->zadd($strKey, $Jerry, '90');
$redis->zadd($strKey, $Job, '30');
$redis->zadd($strKey, $LiMing, '100');
// var_dump($arr);
// var_dump($redis->zadd($strKey, $arr, '100'));
// die;
$dataOne = $redis->zRange($strKey, 0, 0, 'desc');
echo "---- {$strKey}由大到小的排序 ---- <br /><br />";
print_r($dataOne);
 
$dataTwo = $redis->zRange($strKey, 0, 0, 'asc');
echo "<br /><br />---- {$strKey}由小到大的排序 ---- <br /><br />";
print_r($dataTwo);




// $redis->rPop('rp');
var_dump($redis->rPop('rp'));
var_dump($redis->lPop('rp'));

		die;
		$time = time();
		for ($i=0; $i < 10000; $i++) { 
			// $map = ['tp_name' => $i,'tp_pass' => '123456'];
			// Db::table('shop_ceshi')->insert($map);
			echo "插入数据：".$i."成功<br>";
		}
		$time1 = time();
		var_dump($this->timediff($time1,$time));
	}

function timediff($begin_time,$end_time)
{
      if($begin_time < $end_time){
         $starttime = $begin_time;
         $endtime = $end_time;
      }else{
         $starttime = $end_time;
         $endtime = $begin_time;
      }
      //计算天数
      $timediff = $endtime-$starttime;
      $days = intval($timediff/86400);
      //计算小时数
      $remain = $timediff%86400;
      $hours = intval($remain/3600);
      //计算分钟数
      $remain = $remain%3600;
      $mins = intval($remain/60);
      //计算秒数
      $secs = $remain%60;
      $res = array("day" => $days,"hour" => $hours,"min" => $mins,"sec" => $secs);
      return $res;
}

	public function ruhuo()
	{
		$conf = [
			'host' =>  '127.0.0.1',
			'port' =>  '6379',
			'auth' =>  '3600',
			'index' =>  '11'
		];
		$redis = new RedisClice($conf);
		$redis->delete('goods_list');
		$redis->delete('order_info');
		$redis->delete('bought_list');
		for($i = 1;$i<=20;$i++){
			$redis->logger($i);
            $redis->rPush('goods_list',$i);
        	echo '进货'.$i.'成功';
		}
        $redis->close();
	}
	public function chuhuo()
	{
		$conf = [
			'host' =>  '127.0.0.1',
			'port' =>  '6379',
			'auth' =>  '3600',
			'index' =>  '11'
		];
		$redis = new RedisClice($conf);
        echo '出货rPop'.$redis->rPop('goods_list').'成功';
        $redis->close();
	}

	public function qg(){
		//查询库存
		$redis = $this->config();
		var_dump($redis->lLen('goods_list'));
        if($redis->lLen('goods_list') == 0){
            var_dump('商品已售完...');
            return false;
        }
        // $uid = $_SERVER['REMOTE_PORT'];
        
        $uid = time();

        //查询是否购买过
        if($redis->sismember('bought_list',$uid)){
            var_dump('你已经购买过了!');
            return false;
        }
        $goods_id = $redis->rpop('goods_list');
        $redis->sAdd('bought_list',$uid);
        $value = array(
            'uid'   =>  $uid,
            'goods_id'   =>  $goods_id,
            'time'  =>  time(),
        );
        $redis->rPush("run","one");
        $redis->hSet('order_info',$uid,json_encode($value));
        var_dump('购买成功。');
        var_dump(date('ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8));
	}

	public function rinfo(){

		$redis = $this->config();
		var_dump( $redis->hLan('order_info'));
		var_dump( $redis->hGet('order_info'));
	}

	public function deleteinfo($uid){

		$redis = $this->config();
		$redis->hDel("order_info",input('get.uid'));
	}

	public function config(){

		$conf = [
			'host' =>  '127.0.0.1',
			'port' =>  '6379',
			'auth' =>  '3600',
			'index' =>  '11'
		];
		return new RedisClice($conf);
	} 

	public function lock(){
		$redis = $this->config();
		$lock_key = 'LOCK_PREFIX' . $redis_key;
		$is_lock = $redis->setnx($lock_key, 1); // 加锁
		if($is_lock == true){ // 获取锁权限
			$redis->setex($redis_key, $expire, $data); // 写入内容
			// 释放锁
			$redis->del($lock_key);
		}else{
			// 防止死锁
			if($redis->ttl($lock_key) == -1){
				$redis->expire($lock_key, 5);
			}
			return true; // 获取不到锁权限，直接返回
		}

	}
}