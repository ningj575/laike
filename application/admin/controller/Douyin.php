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
     * 预售券创建预约订单SPI
     * https://partner.open-douyin.com/docs/resource/zh-CN/local-life/develop/OpenAPI/JiuLv/vacation/presale_coupon/travel-order-creation/ta_presale_coupon_create_book_order
     */

    public function create_order() {
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
        $param['type'] = 2;
        $param['query'] = json_encode($query);
        $param['rawbody'] = $rawBody;
        $param['headers'] = json_encode($headers);
        $param['order_no'] = getOrderSn(6);
        $mod->insert($param);
        $data = json_decode($rawBody, true);
        return json(['data' => [
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
        $param['type'] = 1;
        $param['query'] = json_encode($query);
        $param['rawbody'] = $rawBody;
        $param['headers'] = json_encode($headers);
        $param['order_no'] = getOrderSn(6);
        $mod->insert($param);
        $data = json_decode($rawBody, true);
        return json(['data' => [
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
        $param['type'] = 3;
        $param['query'] = json_encode($query);
        $param['rawbody'] = $rawBody;
        $param['headers'] = json_encode($headers);
        $mod->insert($param);
        return json(['data' => [
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
        $mod = new OrderLogModel();
        $param['type'] = 5;
        $param['query'] = json_encode($query);
        $param['rawbody'] = $rawBody;
        $param['headers'] = json_encode($headers);
        $mod->insert($param);
        return json(['data' => [
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

    public function order_confirm() {
        $ser = new DouyinServer();
        $source_order_id = '1091143376496020200';
        $order_id = '1091122782528340200';
        $confirm_result = 1;
        $res = $ser->order_confirm($source_order_id, $order_id, $confirm_result, $reject_code = '');
        $mod = new OrderLogModel();
        $req_data = [
            'source_order_id' => $source_order_id,
            'order_id' => $order_id,
            'confirm_result' => $confirm_result,
            'reject_code'=>$reject_code
        ];
        $param['type'] = 6;
        $param['query'] = json_encode($req_data);
        $param['rawbody'] = json_encode($res);
        $param['headers'] = '';
        $mod->insert($param);
        dump($res);
    }

    public function test() {
        $ser = new DouyinServer();
        $query = [
            'timestamp' => '1768554977384',
            'client_key' => 'aw846lz89q2aun7f',
            'sign' => '0c96641d5840dd1cc7851cf96d2cebb5',
        ];
        $signature = '82646bf0e01a7b48a973a94b90287d07a98fd2fa93abd6464c573eed87aba39c';
        $body = '{"each_coupon_amount":60000,"actual_amount":60000,"pay_info":{"pay_time_unix":1768554418},"merchant_discount_amount":0,"commerce_info":{},"create_order_time_unix":1768554392,"total_coupon_count":1,"product_snap_shot":[{"category_full_name":"度假旅游服务·境内行程游·境内自由行","create_time":1768467132,"update_time":1768467234,"is_superimposed_discounts":false,"use_date":{"day_duration":360,"use_date_type":2},"merchant_break_description":[{"note_type":1,"content":"如按上述标准支付的违约金不足以赔偿旅游者的实际损失，旅行社应按照实际损失对旅游者予以赔偿。"},{"note_type":1,"content":"如发生旅行社已尽合理义务但仍不能避免的客观事件或不可抗力因素，导致旅行社取消订单的不属于旅行社违约，反之，旅行者亦然。"}],"out_product_id":"RP2SC25","category_id":"32006001","merchant_break_rule":{"calc_price_type":3,"refund_detail_list":[{"lost_unit":100,"max_cancel_time":{"hour":0,"minute":0,"day":90},"refer_time_type":3,"lost_num":0},{"min_cancel_time":{"day":89,"hour":0,"minute":0},"max_cancel_time":{"day":15,"hour":0,"minute":0},"refer_time_type":3,"lost_num":200,"lost_unit":100},{"lost_num":500,"lost_unit":100,"min_cancel_time":{"minute":0,"day":14,"hour":0},"max_cancel_time":{"hour":0,"minute":0,"day":7},"refer_time_type":3},{"refer_time_type":3,"lost_num":1000,"lost_unit":100,"min_cancel_time":{"day":6,"hour":0,"minute":0},"max_cancel_time":{"day":4,"hour":0,"minute":0}},{"max_cancel_time":{"hour":0,"minute":0,"day":1},"refer_time_type":3,"lost_num":1500,"lost_unit":100,"min_cancel_time":{"day":3,"hour":0,"minute":0}},{"min_cancel_time":{"day":0,"hour":0,"minute":0},"max_cancel_time":{"day":0,"hour":0,"minute":0},"refer_time_type":3,"lost_num":2000,"lost_unit":100}],"refund_rule_type":0,"refund_type":4},"presale_appointment_cancel_policy":{"policy_rule_type":2,"note_list":[{"note_type":1,"content":"未预约随时退款，过期未预约自动全额退款\n ·支持用户在“不收取买家违约金”期限发起取消预约，由商家审核24h同意后执行取消预约，用户可再次发起预约\n ·在“需收取买家违约金”期限内支持用户发起按照阶梯规则申请退款，由商家审核24h同意后从用户侧扣除违约金返还商家"}]},"self_funded_project":{"enable":false},"sku_id":"1854372158087180","name":"宁静测试国内自由行一日自由行","travel_details":[{"lodging":[{"lodging_type":1,"poi_list_v2":[{"poi_id":"7412885572005398555","poi_name":"酒店"}],"note":"","enable":true}],"free_time":[{"plan_minute":60,"instructions":""}],"name":"中国·上海 起航","address":"上海吴淞口国际邮轮码头"}],"description_rich_text":[{"note_type":1,"content":""}],"earliest_appointment":{"change_time_type":0,"need_appointment":true,"ahead_time_type":1,"ahead_time":10},"product_id":"1854372158087180","add_price_policy":{"enable":false,"notes":[{"content":"","note_type":0}]},"appointment":{"change_time_type":0,"need_appointment":true,"ahead_time_type":1,"ahead_time":1},"refund_rule":{"refund_type":4,"calc_price_type":3,"refund_detail_list":[{"refer_time_type":3,"lost_num":0,"lost_unit":100,"max_cancel_time":{"minute":0,"day":90,"hour":0}},{"min_cancel_time":{"day":89,"hour":0,"minute":0},"max_cancel_time":{"day":15,"hour":0,"minute":0},"refer_time_type":3,"lost_num":200,"lost_unit":100},{"max_cancel_time":{"day":7,"hour":0,"minute":0},"refer_time_type":3,"lost_num":500,"lost_unit":100,"min_cancel_time":{"day":14,"hour":0,"minute":0}},{"refer_time_type":3,"lost_num":1000,"lost_unit":100,"min_cancel_time":{"day":6,"hour":0,"minute":0},"max_cancel_time":{"day":4,"hour":0,"minute":0}},{"lost_num":1500,"lost_unit":100,"min_cancel_time":{"minute":0,"day":3,"hour":0},"max_cancel_time":{"day":1,"hour":0,"minute":0},"refer_time_type":3},{"refer_time_type":3,"lost_num":2000,"lost_unit":100,"min_cancel_time":{"day":0,"hour":0,"minute":0},"max_cancel_time":{"day":0,"hour":0,"minute":0}}],"refund_rule_type":0},"refund_description":[{"note_type":1,"content":"如按上述约定比例扣除的必要的费用低于实际发生的费用，旅游者按照实际发生的费用支付，但最高额不应当超过旅游费用总额。"},{"note_type":1,"content":"因部分旅游资源需提前预订的特殊性，本产品线路在预约成功后至出行前90日外取消的，也将产生实际损失，具体损失包括但不限于机票、酒店等，如旅游者需要取消订单，应及时联系旅行社，旅行社除协助旅游者减损并退还未实际发生的损失费用外不再承担其他赔偿责任。"}]}],"departure_info":null,"biz_type":3011,"discount_amount":0,"buyer_info":{"phone":"3MHS7ZKNiBFzFbGn/443sg=="},"item_list":[{"item_id":"800012886704878822613320200"}],"order_id":"1091087114806420200","total_amount":60000,"pay_amount":60000}';
        dump($ser->spiSignature($query, $body, $signature));
    }

}
