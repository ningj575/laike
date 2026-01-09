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
//header('Access-Control-Allow-Origin:*');     
//    	header('Access-Control-Allow-Methods:*');  
//	header('Access-Control-Allow-Headers:*');
//	header('Access-Control-Allow-Credentials:false');

Route::get('think', function () {
    return 'hello,ThinkPHP5!';
});

Route::get('hello/:name', 'index/hello');

Route::get('about', 'index/about');
Route::get('service', 'index/service');
Route::get('caselist', 'index/caselist');
Route::get('partner', 'index/partner');
Route::get('contact', 'index/contact');
return [
];
