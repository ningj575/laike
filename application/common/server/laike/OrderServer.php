<?php

/**
  订单server
 */

namespace app\common\server\laike;

use app\common\server\BaseServer;
use app\common\model\order\OrderModel;
use app\common\model\product\ProductModel;
use app\common\model\order\OrderBookModel;
use app\common\model\order\OrderBookCustomerModel;
use app\common\server\laike\DouyinServer;
use app\common\model\order\CustomerModel;
use think\cache\driver\Redis;

class OrderServer extends BaseServer {
    /*
     * 创建预售订单
     * $data 抖音请求过来的数据
     */

    public function create_presale_order($logid, $data) {
        $res_data = [
            'error_code' => 0,
            'order_id' => $data['order_id'],
            'order_out_id' => '',
            'description' => 'success'
        ];
        $redis = new Redis;
        $redis_key = 'logid:' . $logid;
        $is_lock = $redis->setLock($redis_key);
        if (!$is_lock) {
            $res_data['error_code'] = 100;
            $res_data['description'] = '订单处理中，请稍后重试';
            return $res_data;
        }
        $order_mod = new OrderModel();
        $order_info = $order_mod->where('order_id', $data['order_id'])->find();
        if (!empty($order_info)) {
            $res_data['order_out_id'] = $order_info['order_out_id'];
            $res_data['description'] = '订单已经处理过了';
            return $res_data;
        }
        $douyin_ser = new DouyinServer();
        $product = $data['product_snap_shot'][0];
        $product_mod = new ProductModel();
        $product_info = $product_mod->where('product_id', $product['product_id'])->find();
        $data['product_id'] = $product['product_id'];
        $data['account_id'] = $product_info['owner_account_id'] ?? 0;
        $data['departure'] = $product_info['departure'] ?? '';
        $data['use_end_date'] = $product_info['use_end_date'] ?? '';
        $data['buyer_info_phone'] = $douyin_ser->decryptAES($data['buyer_info']['phone']);
        $data['departure_info'] = empty($data['departure_info']) ? '' : $data['departure_info'];
        $data['order_out_id'] = getOrderSn(6);
        if (!empty($data['pay_info'])) {
            $data['pay_time_unix'] = $data['pay_info']['pay_time_unix'] ?? '';
        }
        if (!empty($data['commerce_info'])) {
            $data['channel'] = $data['commerce_info']['channel'] ?? '';
            $data['host_info_name'] = $data['commerce_info']['host_info']['name'] ?? '';
            $data['host_info_role'] = $data['commerce_info']['host_info']['role'] ?? '';
        }
        $order_mod->insert($data);
        $customer_mod = new CustomerModel();
        $customer_info = $customer_mod->where('buyer_info_phone', $data['buyer_info_phone'])->find();
        if (empty($customer_info)) {
            $customer_save = [
                'buyer_info_phone' => $data['buyer_info_phone']
            ];
            $mobile_addr = $this->getMobileAddr($data['buyer_info_phone']);
            if (!empty($mobile_addr['result'])) {               
                $sys_zone=new \app\common\model\admin\SysZoneModel();
                $province=$sys_zone->where('zone_name','like','%'.$mobile_addr['result']['province'].'%')->value('zone_name');
                $city=$sys_zone->where('zone_name','like','%'.$mobile_addr['result']['city'].'%')->value('zone_name');
                $customer_save['province'] = $province;
                $customer_save['city'] = $city;
            }
            $customer_mod->allowField(true)->save($customer_save);
        }
        $res_data['order_out_id'] = $data['order_out_id'];
        $redis->delLock($redis_key);
        return $res_data;
    }

    /*
     * 创建预约订单
     * $data 抖音请求过来的数据
     */

    public function create_book_order($logid, $data) {
        $res_data = [
            'error_code' => 0,
            'order_id' => $data['order_id'],
            'order_out_id' => '',
            'description' => 'success'
        ];
        $redis = new Redis;
        $redis_key = 'logid:' . $logid;
        $is_lock = $redis->setLock($redis_key);
        if (!$is_lock) {
            $res_data['error_code'] = 100;
            $res_data['description'] = '订单处理中，请稍后重试';
            return $res_data;
        }
        $order_book_mod = new OrderBookModel();
        $order_book_info = $order_book_mod->where('order_id', $data['order_id'])->find();
        if (!empty($order_book_info)) {
            $res_data['order_out_id'] = $order_book_info['order_out_id'];
            $res_data['description'] = '订单已经处理过了';
            return $res_data;
        }
        $order_book_mod->startTrans();
        $data['order_out_id'] = getOrderSn(6);
        $douyin_ser = new DouyinServer();
        if (!empty($data['buyer_info'])) {
            $data['buyer_info_name'] = $douyin_ser->decryptAES($data['buyer_info']['name'] ?? '');
            $data['buyer_info_phone'] = $douyin_ser->decryptAES($data['buyer_info']['phone'] ?? '');
            $data['buyer_info_email'] = $douyin_ser->decryptAES($data['buyer_info']['email'] ?? '');
        }
        if (!empty($data['book_info'])) {
            $data['book_start_date'] = $data['book_info']['book_start_date'] ?? '';
            $data['book_end_date'] = $data['book_info']['book_end_date'] ?? '';
        }
        if (!empty($data['pay_info'])) {
            $data['pay_time_unix'] = $data['pay_info']['pay_time_unix'] ?? '';
        }
        $order_book_mod->allowField(true)->save($data);
        $order_mod = new OrderModel();
        $order_mod->where('order_id', $data['source_order_id'])->setField('order_status', 2); //更新订单状态为待接单
        $book_customer_mod = new OrderBookCustomerModel();
        $book_customer = $data['book_info']['occupancies'];
        foreach ($book_customer as $val) {
            $customer_data[] = [
                'order_id' => $data['order_id'],
                'order_book_id' => $order_book_mod->id,
                'birthday' => $val['birthday'] ?? '',
                'email' => $douyin_ser->decryptAES($val['email'] ?? ''),
                'first_name' => $douyin_ser->decryptAES($val['first_name'] ?? ''),
                'gender' => $val['gender'] ?? 0,
                'last_name' => $douyin_ser->decryptAES($val['last_name'] ?? ''),
                'license_id' => $douyin_ser->decryptAES($val['license_id'] ?? ""),
                'license_type' => $val['license_type'] ?? 0,
                'license_validity' => $val['license_validity'] ?? '',
                'name' => $douyin_ser->decryptAES($val['name'] ?? ''),
                'phone' => $douyin_ser->decryptAES($val['phone'] ?? ''),
            ];
        }
        $book_customer_mod->allowField(true)->saveAll($customer_data);
        $customer_mod = new CustomerModel();
        $customer_info = $customer_mod->where('buyer_info_phone', $data['buyer_info_phone'])->find();
        if (!empty($customer_info) && empty($customer_info['buyer_info_name']) && !empty($data['buyer_info_name'])) {
            $customer_info->save(['buyer_info_name' => $data['buyer_info_name'], 'buyer_info_email' => $data['buyer_info_email']]);
        }
        $order_book_mod->commit();
        $res_data['order_out_id'] = $data['order_out_id'];
        $redis->delLock($redis_key);
        return $res_data;
    }

    /*
     * 订单取消
     */

    public function orderCancel($data) {
        $res_data = [
            'error_code' => 0,
            'description' => 'success'
        ];
        if ($data['biz_type'] == 3011) {//取消预售券订单
            $order_mod = new OrderModel();
            $order_info = $order_mod->where('order_id', $data['order_id'])->find();
            if (!empty($order_info)) {
                $order_info->save(['order_status' => -1, 'cancel_reason' => $data['cancel_reason']]);
            }
        } elseif ($data['biz_type'] == 3012) {//预约单取消
            $order_book_mod = new OrderBookModel();
            $order_book_info = $order_book_mod->where('order_id', $data['order_id'])->find();
            if (!empty($order_book_info)) {
                $order_book_info->save(['book_status' => -1, 'cancel_reason' => $data['cancel_reason']]);
            }
        }
        return $res_data;
    }

    /*
     * 订单退款
     */

    public function orderRefund($data) {
        $res_data = [
            'error_code' => 0,
            'description' => 'success'
        ];
        $order_refund_mod=new \app\common\model\order\OrderRefundModel();
        $data['refund_item_list']= json_encode($data['refund_item_list']);
        $order_refund_mod->insert($data);
        if ($data['refund_type'] == 1) {//订单退款
            $order_mod = new OrderModel();
            $order_info = $order_mod->where('order_id', $data['order_id'])->find();
            if (!empty($order_info)) {
                $order_info->save(['refund_status' => -1]);
            }
        } elseif ($data['refund_type'] == 2) {//补差价退款
            $order_book_mod = new OrderBookModel();
            $order_book_info = $order_book_mod->where('order_id', $data['order_id'])->find();
            if (!empty($order_book_info)) {
                $order_book_info->save(['refund_status' => -1]);
            }
        }
        return $res_data;
    }

    /*
     * 订单预约确认
     */

    public function orderConfirm($order_id, $confirm_result, $reject_code = 0) {
        $order_mod = new OrderModel();
        $order_book_mod = new OrderBookModel();
        $book_info = $order_book_mod->where('order_id', $order_id)->find();
        if (empty($book_info)) {
            return returnPubData('预约信息不存在');
        }
        $source_order_id = $book_info['source_order_id'];
        $order_info = $order_mod->where('order_id', $source_order_id)->find();
        $douyin_ser = new DouyinServer();
        $confirm_info = [
            'confirm_result' => $confirm_result, //确认订单结果。1：接单 2：拒单
            'reject_code' => $reject_code, //拒单原因。1:库存已约满 2：商品需加价 3：无法满足顾客需求           
            'extra_msg' => ''//其他注意事项
        ];
        if ($order_info['account_id'] == '7325036766577035315') {//测试
            $confirm_info['free_travel_info'] = [//境内自由行类目预定信息
                'oneday_tour_list' => [
                        ['hotel_info_list' => [
                                ['hotel_confirm_no' => '',
                                'poi_info' => [
                                        ['poi_id' => '7412885572005398555', 'poi_name' => '酒店']
                                ],
                                'room_items' => [
                                        ['meals' => 1, 'room_type' => '酒店客栈']
                                ]
                            ]
                        ],
                        'sequence' => 1
                    ],
                ],
                'travel_num' => [
                    "day_num" => 1,
                    "night_num" => 0
                ]
            ];
        }
        $douyin_res = $douyin_ser->order_confirm($source_order_id, $order_id, $confirm_info);
        if ($douyin_res['data']['error_code'] != 0) {
            return returnPubData($douyin_res['data']['description']);
        }
        $book_data = [
            'reject_code' => $reject_code,
            'book_status' => $confirm_result == 1 ? 2 : -1,
            'confirm_time_unix' => time(),
        ];
        $book_info->save($book_data);
        $order_data = [
            'order_status' => $confirm_result == 1 ? 3 : 1
        ];
        $order_info->save($order_data);
        return returnPubData('处理成功', 1000);
    }

    /*
     * 统计
     */

    public function getStatic($uid) {
        $sales_user_mod = new \app\common\model\sales\SalesUserModel();
        $is_sales = $sales_user_mod->where('admin_id', $uid)->find();
        $where = [];       
        if ($is_sales) {
            $where[] = ['sales_user_id', 'eq', $uid];
            $is_sales = 1;
        }else{
             $is_sales = 0;
        }
        $order = new OrderModel();
        $order_static = $order->where($where)->field('count(id) as all_order_count,sum(if(order_status=1,1,0)) as stay_book_count,sum(if(order_status=2,1,0)) as stay_confirm_count,'
                        . 'sum(if(order_status=3,1,0)) as book_count,sum(if(fllow_status=3,1,0)) as completed_count,sum(if(fllow_status=2,1,0)) as fllow_count,sum(if(fllow_status=1,1,0)) as dist_count'
                        . ',sum(if(refund_status=-1,1,0)) as refund_count')->select();
        $lead_mod = new \app\common\model\sales\SalesLeadModel();
        $lead_static = $lead_mod->where($where)->field('sum(if(lead_fllow_status=1,1,0)) as dist_count,sum(if(lead_fllow_status=2,1,0)) as fllow_count,sum(if(lead_fllow_status=3,1,0)) as completed_count')->select();
        $complain_mod = new \app\common\model\order\OrderComplainModel();
        $complain_static = $complain_mod->where($where)->field('sum(if(complain_fllow_status=1,1,0)) as dist_count,sum(if(complain_fllow_status=2,1,0)) as fllow_count,sum(if(complain_fllow_status=3,1,0)) as completed_count')->select();
        $static = [
            'is_sales' => $is_sales,
            'order' => $order_static[0],
            'lead' => $lead_static[0],
            'complain' => $complain_static[0],
        ];
        return $static;
    }

    /*
     * 获取用户手机号的归属地
     * {
      "resultcode":"200",
      "reason":"Return Successd!",
      "result":{
      "province":"浙江",
      "city":"杭州",
      "areacode":"0571",
      "zip":"310000",
      "company":"移动",
      "card":""
      }
      }
     */

    public function getMobileAddr($mobile) {
        $url = "http://apis.juhe.cn/mobile/get";
        $params = array(
            "phone" => $mobile, //需要查询的手机号码
            "key" => "7f0ca65cf3e3d96dd34a8927906fd30f", //应用APPKEY(应用详细页查询)
        );
        $paramstring = http_build_query($params);
        $content = curl($url, $paramstring);
        $result = json_decode($content, true);
        return $result;
    }

    /*
     * 添加系统跟进记录
     */

    public function addSysFllowRecord($order_id, $sales_user_id, $type = 1, $admin_id = 0) {
        $admin_mod = new \app\common\model\admin\SysAdminModel();
        $admin_name = $admin_mod->where('id', $sales_user_id)->value('admin_name');
        $param = [
            'admin_id' => $admin_id,
            'order_id' => $order_id,
            'fllow_method' => '系统',
            'fllow_time' => time(),
            'fllow_content' => '系统自动分配给:' . $admin_name,
            'type' => $type,
            'out_fllow_time'=>time()+7200,
        ];
        $fllow_mod = new \app\common\model\order\OrderFllowRecordModel();
        $fllow_mod->insert($param);
    }

}
