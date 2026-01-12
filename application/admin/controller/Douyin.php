<?php

namespace app\admin\controller;

use app\common\server\BaseServer;
use think\App;
use app\common\server\laike\DouyinServer;
use app\common\model\order\OrderLogModel;

class Douyin extends BaseServer {
    /*
     * 预售券创建预约订单SPI
     */

    public function create_order() {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $headerKey = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                $headers[strtolower($headerKey)] = $value;
            }
        }
        $rawBody = file_get_contents('php://input');
        $mod = new OrderLogModel();
        $param['type'] = 2;
        $param['rawbody'] = $rawBody;
        $param['headers'] = json_encode($_SERVER);
        $mod->insert($param);
        return json([
            'code' => 0,
            'message' => 'success'
        ]);
    }

    /*
     * 预售券创建预售订单SPI
     */

    public function create_presale_order() {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $headerKey = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                $headers[strtolower($headerKey)] = $value;
            }
        }
        $rawBody = file_get_contents('php://input');
        $mod = new OrderLogModel();
        $param['type'] = 1;
        $param['rawbody'] = $rawBody;
        $param['headers'] = json_encode($_SERVER);
        $mod->insert($param);
        return json([
            'code' => 0,
            'message' => 'success'
        ]);
    }

    public function push() {
        $ser = new DouyinServer();
        $order_id = '1091049173996500820';
        dump($ser->getToken());
        exit;
    }

    public function des() {
        $ser = new DouyinServer();
        dump($ser->decryptAES('Z0H4lUWbGJWtiWSjlDJG+A=='));
    }

}
