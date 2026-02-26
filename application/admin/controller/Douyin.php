<?php

namespace app\admin\controller;

use app\common\server\BaseServer;
use think\App;
use app\common\server\laike\DouyinServer;
use app\common\model\order\SpiLogModel;
use think\Model;

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
                'code' => 8,
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
     * 预售券创建预售订单SPI
     * https://partner.open-douyin.com/docs/resource/zh-CN/local-life/develop/OpenAPI/JiuLv/vacation/presale_coupon/travel-presale-orders/ta_presale_coupon_create_presale_order
     */

    public function create_presale_order() {
        $headers = $this->request->header();
        $rawBody = file_get_contents('php://input');
        $signature = $this->request->header('x-life-sign');
        $query = input('get.');
        $douyin_ser = new DouyinServer();
        $verify_res = $douyin_ser->spiSignature($query, $rawBody, $signature);
        if (!$verify_res) {
            return json(['data' => [
                    'error_code' => 8,
                    'description' => '验签失败'
            ]]);
        }
        $body_data = json_decode($rawBody, true);
        $this->addSpiLog(1, $headers, $query, $body_data);
        $logid = $headers['x-bytedance-logid'];
        $order_ser = new \app\common\server\laike\OrderServer();
        $res = $order_ser->create_presale_order($logid, $body_data);
        return json(['data'=>$res]);
    }

    /*
     * 预售券创建预约订单SPI
     * https://partner.open-douyin.com/docs/resource/zh-CN/local-life/develop/OpenAPI/JiuLv/vacation/presale_coupon/travel-order-creation/ta_presale_coupon_create_book_order
     */

    public function create_book_order() {
        $headers = $this->request->header();
        $rawBody = file_get_contents('php://input');
        $signature = $this->request->header('x-life-sign');
        $query = input('get.');
        $douyin_ser = new DouyinServer();
        $verify_res = $douyin_ser->spiSignature($query, $rawBody, $signature);
        if (!$verify_res) {
            return json(['data' => [
                    'error_code' => 8,
                    'description' => '验签失败'
            ]]);
        }
        $body_data = json_decode($rawBody, true);
        $this->addSpiLog(2, $headers, $query, $body_data);
        $logid = $headers['x-bytedance-logid'];
        $order_ser = new \app\common\server\laike\OrderServer();
        $res = $order_ser->create_book_order($logid, $body_data);
        return json(['data'=>$res]);
    }   

    /*
     * 预售券订单取消通知SPI
     * https://partner.open-douyin.com/docs/resource/zh-CN/local-life/develop/OpenAPI/JiuLv/vacation/presale_coupon/travel-cancel-notice/ta_presale_coupon_order_cancel
     */

    public function order_cancel() {
        $headers = $this->request->header();
        $rawBody = file_get_contents('php://input');
        $signature = $this->request->header('x-life-sign');
        $query = input('get.');
        $douyin_ser = new DouyinServer();
        $verify_res = $douyin_ser->spiSignature($query, $rawBody, $signature);
        if (!$verify_res) {
            return json(['data' => [
                    'error_code' => 8,
                    'description' => '验签失败'
            ]]);
        }
        $body_data = json_decode($rawBody, true);
        $this->addSpiLog(3, $headers, $query, $body_data);
        $order_ser = new \app\common\server\laike\OrderServer();
        $res = $order_ser->orderCancel($body_data);
        return json(['data'=>$res]);
    }

    /*
     * 预售券退款通知
     * https://partner.open-douyin.com/docs/resource/zh-CN/local-life/develop/OpenAPI/JiuLv/vacation/presale_coupon/travel-refund-notice/ta_presale_coupon_refund_notify
     */

    public function refund_notify() {
        $headers = $this->request->header();
        $rawBody = file_get_contents('php://input');
        $signature = $this->request->header('x-life-sign');
        $query = input('get.');
        $douyin_ser = new DouyinServer();
        $verify_res = $douyin_ser->spiSignature($query, $rawBody, $signature);
        if (!$verify_res) {
            return json(['data' => [
                    'error_code' => 8,
                    'description' => '验签失败'
            ]]);
        }
        $body_data = json_decode($rawBody, true);
        $this->addSpiLog(4, $headers, $query, $body_data);
        $order_ser = new \app\common\server\laike\OrderServer();
        $res = $order_ser->orderRefund($body_data);
        return json(['data'=>$res]);
    }

    /*
     * 预售券支付通知SPI(没用到)
     * https://partner.open-douyin.com/docs/resource/zh-CN/local-life/develop/OpenAPI/JiuLv/vacation/presale_coupon/travel-payment-notice/ta_presale_coupon_pay_notify
     */

    private function pay_notify() {
        $headers = $this->request->header();
        $rawBody = file_get_contents('php://input');
        $signature = $this->request->header('x-life-sign');
        $query = input('get.');
        $douyin_ser = new DouyinServer();
        $verify_res = $douyin_ser->spiSignature($query, $rawBody, $signature);
        if (!$verify_res) {
            return json(['data' => [
                    'error_code' => 8,
                    'description' => '验签失败'
            ]]);
        }
        $mod = new OrderLogModel();
        $param['type'] = 4;
        $param['query'] = json_encode($query);
        $param['rawbody'] = $rawBody;
        $param['headers'] = json_encode($headers);
        $mod->insert($param);
        return json(['data' => [
                'code' => 0,
                'message' => 'success'
        ]]);
    }

    public function queryProduct() {
//        $product_ser = new \app\common\server\laike\ProductServer();
//        dump($product_ser->productDo());
    }

    private function addSpiLog($type, $headers, $query, $body_data) {
        //1预售券订单 2预约订单 3订单取消 4订单退款 5订单商品快照查询
        $type_arr = [1 => '预售券订单', 2 => '预约订单', 3 => '订单取消', 4 => '订单退款', 5 => '订单商品快照查询'];
        $mod = new SpiLogModel();
        $logid = $headers['x-bytedance-logid'];
        $param['type'] = $type;
        $param['type_name'] = $type_arr[$type];
        $param['logid'] = $logid;
        $param['query'] = json_encode($query);
        $param['rawbody'] = json_encode($body_data);
        $param['headers'] = json_encode($headers);
        $param['order_id'] = $body_data['order_id'];
        $mod->insert($param);
    }

}
