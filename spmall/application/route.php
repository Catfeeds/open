<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\Route;
return [
    '__pattern__' => [
        'name' => '\w+',
    ],

    //定义路由交换
    'Shopcart/id/:id'=>'Shopcart/index',

    'login'=>'index/index/login',
    'login'=>'index/index/login',
    'login'=>'login',
    'index/login'=>'login',
    'register'=>'index/index/register',
    'register'=>'register',
    'index/register'=>'register',
    'index/login_register'=>'index/index/login_register',
    'index/index/login_register'=>'index/login_register',
    'index/index/index/login_register'=>'index/login_register',
    'RedisterClass/email_register'=>'index/RedisterClass/email_register',
    'index/RedisterClass/email_register'=>'index/RedisterClass/email_register',
    'index/Search/index.html/:id'=>'index/Search/index',
    'index/Search/index/:id'=>'index/Search/index',
];