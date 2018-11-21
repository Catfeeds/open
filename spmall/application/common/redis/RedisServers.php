<?php
namespace app\common\redis;

use think\Db;
use think\Cache;

/**
* 
*/
class RedisServers
{
	
	private static $_main;
    private static $_follow;
    private $_redis;

	/**
     * [__construct 初始化Redis连接]
     * @author         Shaowei Pu <pushaowei@sporte.cn>
     * @CreateTime    2016-12-24T16:50:16+0800
     * $host: Redis服务器的主机IP
	 * $port: Redis服务器的端口
	 * $callback: 连接成功后回调的函数
	 * $select  链接的数据库名
     */
	function __construct($config,$select)
	{
		 $this->_redis = new \Redis;
		 if(!empty($config))
        {
            // 创建连接
            $this->_redis ->connect(
                $config["redis_host"],
                $config["redis_port"],
                $config["redis_timeout"]
            );
            if(is_resource($this->_redis->socket) && !empty($config->redis_auth)){
                $this->_redis->auth($config->redis_auth);
            }
            $this->_redis->select($select);
        };
        return $this->_redis;
	}

	public function delStr($key)
    {
        $key = 'string:' . $key;
        $this->_redis->del($key);
    }
    /**
     * [setHash 设置一条Hash]
     * @author         Shaowei Pu <pushaowei@sporte.cn>
     * @CreateTime    2016-12-24T16:14:38+0800
     * @param                               [type] $key    [String]
     * @param                               [type] $arr    [String || Array]
     * @param                               [type] $expire [description]
     */
    public function setHash($key='', $arr='', $expire = null)
    {
        if(empty($key) || empty($arr)) return false;
        $key = 'hash:' . $key;
        $this->_redis->hMset($key, $arr);
        if (!is_null($expire)) {
            $this->_redis->setTimeout($key, $expire);
        }
    }
    /**
     * [getHash 获取一条Hash]
     * @author         Shaowei Pu <pushaowei@sporte.cn>
     * @CreateTime    2016-12-24T16:15:09+0800
     * @param                               [type] $key    [String]
     * @param                               [type] $fields [String || Array]
     * @return                              [type]         [Data]
     */
    public function getHash($key, $fields = null)
    {
        $key = 'hash:' . $key;
        if (is_null($fields)) {
            $arr = $this->_redis->hGetAll($key);
        } else {
            if (is_array($fields)) {
                $arr = $this->_redis->hmGet($key, $fields);
                foreach ($arr as $key => $value) {
                    if ($value === false) {
                        unset($arr[$key]);
                    }
                }
            } else {
                $arr = $this->_redis->hGet($key, $fields);
            }
        }
        return empty($arr) ? null : (is_array($arr) ? $arr : array($fields => $arr));
    }
    /**
     * [delHash 删除一条Hash]
     * @author         Shaowei Pu <pushaowei@sporte.cn>
     * @CreateTime    2016-12-24T16:15:38+0800
     * @param                               [type] $key   [String]
     * @param                               [type] $field [String]
     * @return                              [type]        [Boole]
     */
    public function delHash($key, $field = null)
    {
        $key = 'hash:' . $key;
        if (is_null($field)) {
            $this->_redis->del($key);
        } else {
            $this->_redis->hDel($key, $field);
        }
    }
}
