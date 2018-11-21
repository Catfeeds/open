<?php
namespace app\index\controller;

use app\index\common\Base;

//购物车.class.php
class Introduction extends Base
{
    public function index()
    {
        return $this->fetch();
    }

    //demo
    public function demo()
    {
        return $this->fetch();
    }
}
