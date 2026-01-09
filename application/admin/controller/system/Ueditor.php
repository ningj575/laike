<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\admin\controller\system;

/**
 * Description of Ueditor
 *
 * @author Administrator
 */
class Ueditor
{

    public function index()
    {
        header('Access-Control-Allow-Origin: *'); //设置http://www.baidu.com允许跨域访问
        header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With'); //设置允许的跨域header
        date_default_timezone_set("Asia/chongqing");
        error_reporting(E_ERROR);
        header("Content-Type: text/html; charset=utf-8");

        $CONFIG = config('ueditor.');

        $action = $_GET['action'];

        switch ($action) {
            case 'config':
                $result = ($CONFIG);
                break;

            /* 上传图片 */
            case 'uploadimage':
            /* 上传涂鸦 */
            case 'uploadscrawl':
            /* 上传视频 */
            case 'uploadvideo':
            /* 上传文件 */
            case 'uploadfile':
                $mod = new \com\ueditor\ActionUpload();
                $result = $mod->index($CONFIG);
                break;

            /* 列出图片 */
            case 'listimage':
                $mod = new \com\ueditor\ActionList();
                $result = $mod->index($CONFIG);
                break;
            /* 列出文件 */
            case 'listfile':
                $mod = new \com\ueditor\ActionList();
                $result = $mod->index($CONFIG);
                break;

            /* 抓取远程文件 */
            case 'catchimage':
                $mod = new \com\ueditor\ActionCrawler();
                $result = $mod->index($CONFIG);
                break;

            default:
                $result = [
                    'state' => '请求地址出错'
                ];
                break;
        }

        if (isset($_GET["callback"])) {
            if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
                return htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
            } else {

                return [
                    'state' => 'callback参数不合法'
                ];
            }
        }

        /* 输出结果 */
        if (isset($_GET["callback"])) {
//            dump($result);
            if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
                echo htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
            } else {
                echo json_encode(array(
                    'state' => 'callback参数不合法'
                ));
            }
        } else {//echo 3;exit;
//            dump($result);
            echo json_encode($result);
        }
    }

}
