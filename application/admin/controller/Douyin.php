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
        $Msg_Id=$this->request->header('Msg-Id');
        $Signature=$this->request->header('X-Douyin-Signature');
        $headers=$this->request->header();        
        $rawBody = file_get_contents('php://input');
        $mod = new OrderLogModel();
        $param['type'] = 0;
        $param['rawbody'] = $rawBody;
        $param['headers'] = json_encode($headers);
        $mod->insert($param);
        $data =json_decode($rawBody,true);
        $response = [
            'challenge' => $data['content']['challenge']
        ];        
        // 输出 JSON 格式（文本格式）
        echo json_encode($response);
     
    }

    /*
     * 预售券创建预约订单SPI
     * https://partner.open-douyin.com/docs/resource/zh-CN/local-life/develop/OpenAPI/JiuLv/vacation/presale_coupon/travel-order-creation/ta_presale_coupon_create_book_order
     */

    public function create_order() {        
        $headers=$this->request->header();           
        $rawBody = file_get_contents('php://input');
        $mod = new OrderLogModel();
        $param['type'] = 2;
        $param['rawbody'] = $rawBody;
        $param['headers'] = json_encode($headers);
        $mod->insert($param);
        return json([
            'code' => 0,
            'message' => 'success'
        ]);
    }

    /*
     * 预售券创建预售订单SPI
     * https://partner.open-douyin.com/docs/resource/zh-CN/local-life/develop/OpenAPI/JiuLv/vacation/presale_coupon/travel-presale-orders/ta_presale_coupon_create_presale_order
     */

    public function create_presale_order() {
        $headers=$this->request->header(); 
        $rawBody = file_get_contents('php://input');
        $mod = new OrderLogModel();
        $param['type'] = 1;
        $param['rawbody'] = $rawBody;
        $param['headers'] = json_encode($headers);
        $mod->insert($param);
        return json([
            'code' => 0,
            'message' => 'success'
        ]);
    }

    /*
     * 预售券订单取消通知SPI
     * https://partner.open-douyin.com/docs/resource/zh-CN/local-life/develop/OpenAPI/JiuLv/vacation/presale_coupon/travel-cancel-notice/ta_presale_coupon_order_cancel
     */

    public function order_cancel() {
        $headers=$this->request->header();         
        $rawBody = file_get_contents('php://input');
        $mod = new OrderLogModel();
        $param['type'] = 3;
        $param['rawbody'] = $rawBody;
        $param['headers'] = json_encode($headers);
        $mod->insert($param);
        return json([
            'code' => 0,
            'message' => 'success'
        ]);
    }

    /*
     * 预售券支付通知SPI
     * https://partner.open-douyin.com/docs/resource/zh-CN/local-life/develop/OpenAPI/JiuLv/vacation/presale_coupon/travel-payment-notice/ta_presale_coupon_pay_notify
     */

    public function pay_notify() {
        $headers=$this->request->header();  
        $rawBody = file_get_contents('php://input');
        $mod = new OrderLogModel();
        $param['type'] = 4;
        $param['rawbody'] = $rawBody;
        $param['headers'] = json_encode($headers);
        $mod->insert($param);
        return json([
            'code' => 0,
            'message' => 'success'
        ]);
    }

    /*
     * 预售券退款通知
     * https://partner.open-douyin.com/docs/resource/zh-CN/local-life/develop/OpenAPI/JiuLv/vacation/presale_coupon/travel-refund-notice/ta_presale_coupon_refund_notify
     */

    public function refund_notify() {
        $headers=$this->request->header();  
        $rawBody = file_get_contents('php://input');
        $mod = new OrderLogModel();
        $param['type'] = 5;
        $param['rawbody'] = $rawBody;
        $param['headers'] = json_encode($headers);
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

    public function test() {
        dump(strlen('14586'));
    }

}
