<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// [ 应用入口文件 ]

namespace think;


//$origin = isset($_SERVER['HTTP_ORIGIN'])? $_SERVER['HTTP_ORIGIN'] : '*';
//if(in_array($origin,array('http://m.t.letaoyh.com','http://127.0.0.1','http://wx.t.xykejii.com'))){
//    header('Access-Control-Allow-Origin:'.$origin);
//}
//header('Access-Control-Allow-Methods:*');
//	header('Access-Control-Allow-Headers:*');
//	header('Access-Control-Allow-Credentials:false');
//路径符 重定义
define('DS', DIRECTORY_SEPARATOR);

// 定义框架根目录
define('ROOT_PATH', dirname(dirname((__FILE__))) . DS);

//处理 自动加载的锅
$_SERVER['SCRIPT_FILENAME'] = ROOT_PATH . 'wwwroot';

require ROOT_PATH . 'thinkphp' . DS . 'base.php';
$site = retSite();

// 定义站点根目录
define('SITE_ID', $site);

// 定义当前时间
define('SYS_TIME', time());
// 执行应用并响应
Container::get('app')->path(ROOT_PATH . 'application')->bind($site)->run()->send();

/**
 * 根据耳机域名返回对应访问模块
 * @return string
 */
function retSite() {
    try {
        $prefix = explode('.', $_SERVER['HTTP_HOST']); //获取前缀
        $siteArr = [//定义站点对应二级目录
            'admin' => ['admin'],
            'home' => ['golf'],
        ];
        foreach ($siteArr as $key => $val) {
            if (in_array($prefix[0], $val)) {
                return $key;
            }
        }
    } catch (\Exception $ex) {
        return 'home'; //默认访问前台模板
    }
    return 'home';
}