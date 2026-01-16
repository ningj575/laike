<?php

namespace app\admin\controller;

use app\common\server\BaseServer;
use think\App;
use app\common\server\laike\DouyinServer;
use app\common\model\order\OrderLogModel;

class Douyin extends BaseServer {
    /*
     * 生活服务消息推送
     * https://partner.open-douyin.com/docs/resource/zh-CN/local-life/develop/OpenAPI/preparation/massages.push
     */

    public function messages_push() {
        $Msg_Id = $this->request->header('Msg-Id');
        $Signature = $this->request->header('X-Douyin-Signature');
        $headers = $this->request->header();
        $rawBody = file_get_contents('php://input');
        $douyin_ser = new DouyinServer();
        $verify_res = $douyin_ser->verifySignature($Signature, $rawBody);
        if (!$verify_res) {
            return json([
                'code' => 201,
                'message' => '验签失败'
            ]);
        }
        $param['type'] = 0;
        $param['rawbody'] = $rawBody;
        $param['headers'] = json_encode($headers);
        $mod = new OrderLogModel();
        $mod->insert($param);
        $data = json_decode($rawBody, true);
        switch ($data['event']) {
            case 'verify_webhook':
                $response = [
                    'challenge' => $data['content']['challenge']
                ];
                // 输出 JSON 格式（文本格式）
                echo json_encode($response);
                break;
        }
    }

    /*
     * 预售券创建预约订单SPI
     * https://partner.open-douyin.com/docs/resource/zh-CN/local-life/develop/OpenAPI/JiuLv/vacation/presale_coupon/travel-order-creation/ta_presale_coupon_create_book_order
     */

    public function create_order() {
        $headers = $this->request->header();
        $rawBody = file_get_contents('php://input');
        $signature = $this->request->header('x-life-sign');
        $query=input('get.');
        $mod = new OrderLogModel();
        $param['type'] = 2;
        $param['query']= json_encode($query);
        $param['rawbody'] = $rawBody;
        $param['headers'] = json_encode($headers);
        $param['order_no'] = getOrderSn(6);
        $mod->insert($param);
        $data = json_decode($rawBody, true);
        return json(['data'=>[
            'error_code' => 0,
            'order_id' => $data['order_id'],
            'order_out_id' => $param['order_no'],
            'description' => 'success'
        ]]);
    }

    /*
     * 预售券创建预售订单SPI
     * https://partner.open-douyin.com/docs/resource/zh-CN/local-life/develop/OpenAPI/JiuLv/vacation/presale_coupon/travel-presale-orders/ta_presale_coupon_create_presale_order
     */

    public function create_presale_order() {
        $headers = $this->request->header();
        $rawBody = file_get_contents('php://input');
        $signature = $this->request->header('x-life-sign');
        $query=input('get.');
        $mod = new OrderLogModel();
        $param['type'] = 1;
        $param['query']= json_encode($query);
        $param['rawbody'] = $rawBody;
        $param['headers'] = json_encode($headers);
        $param['order_no'] = getOrderSn(6);
        $mod->insert($param);
        $data = json_decode($rawBody, true);
        return json(['data'=>[
            'error_code' => 0,
            'order_id' => $data['order_id'],
            'order_out_id' => $param['order_no'],
            'description' => 'success'
        ]]);
    }

    /*
     * 预售券订单取消通知SPI
     * https://partner.open-douyin.com/docs/resource/zh-CN/local-life/develop/OpenAPI/JiuLv/vacation/presale_coupon/travel-cancel-notice/ta_presale_coupon_order_cancel
     */

    public function order_cancel() {
        $headers = $this->request->header();
        $rawBody = file_get_contents('php://input');
        $signature = $this->request->header('x-life-sign');
        $query=input('get.');
        $mod = new OrderLogModel();
        $param['type'] = 3;
        $param['query']= json_encode($query);
        $param['rawbody'] = $rawBody;
        $param['headers'] = json_encode($headers);
        $mod->insert($param);
        return json(['data'=>[
            'code' => 0,
            'message' => 'success'
        ]]);
    }

    /*
     * 预售券支付通知SPI
     * https://partner.open-douyin.com/docs/resource/zh-CN/local-life/develop/OpenAPI/JiuLv/vacation/presale_coupon/travel-payment-notice/ta_presale_coupon_pay_notify
     */

    public function pay_notify() {
        $headers = $this->request->header();
        $rawBody = file_get_contents('php://input');
        $signature = $this->request->header('x-life-sign');
        $query=input('get.');
        $mod = new OrderLogModel();
        $param['type'] = 4;
        $param['query']= json_encode($query);
        $param['rawbody'] = $rawBody;
        $param['headers'] = json_encode($headers);
        $mod->insert($param);
        return json(['data'=>[
            'code' => 0,
            'message' => 'success'
        ]]);
    }

    /*
     * 预售券退款通知
     * https://partner.open-douyin.com/docs/resource/zh-CN/local-life/develop/OpenAPI/JiuLv/vacation/presale_coupon/travel-refund-notice/ta_presale_coupon_refund_notify
     */

    public function refund_notify() {
        $headers = $this->request->header();
        $rawBody = file_get_contents('php://input');
        $signature = $this->request->header('x-life-sign');
        $query=input('get.');
        $mod = new OrderLogModel();
        $param['type'] = 5;
        $param['query']= json_encode($query);
        $param['rawbody'] = $rawBody;
        $param['headers'] = json_encode($headers);
        $mod->insert($param);
        return json(['data'=>[
            'code' => 0,
            'message' => 'success'
        ]]);
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

    public function test() {
        $pass = '3773f9a3c6da0e2e8175d62af43afa3d';
        $body = '{"event":"verify_webhook","client_key":"aw846lz89q2aun7f","from_user_id":"","content":{"challenge":1768378043},"log_id":"021768378042135f27b22e4a7b63395a339ba291eaf1a8f2d13c2","event_id":""}';
        echo sha1($pass . $body);
    }

}
