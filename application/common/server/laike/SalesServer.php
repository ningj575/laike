<?php

/**
  销售server
 */

namespace app\common\server\laike;

use app\common\server\BaseServer;
use app\common\model\admin\SysAuthGroupModel;
use app\common\model\order\CustomerModel;
use app\common\model\sales\SalesUserModel;
use app\common\model\order\DispatchRuleModel;

class SalesServer extends BaseServer {
    /*
     * 获取销售账号
     */

    public function getSales() {
        $mod = new SysAuthGroupModel();
        $sales = $mod->with(['adminUser'])->where('title', '销售人员')->find();
        return $sales;
    }

    /*
     * 取出需要分配的订单
     */

    public function getOrderAssign() {
        $order_mod = new \app\common\model\order\OrderModel();
        $order_list = $order_mod->where('sales_user_id', 0)->select();
        if (empty($order_list->toArray())) {
            return returnPubData('没有需要分配的订单');
        }
        $sales_user = new SalesUserModel();
        $sales = $sales_user->join('sys_admin', 'sales_user.admin_id = sys_admin.id')->where([['sys_admin.status', 'eq', 1]])->select()->toArray();
        if (empty($sales)) {
            return returnPubData('系统未配置销售人员');
        }
        foreach ($order_list as $val) {
            if (!$val) {
                continue;
            }
            $this->orderAssign($val);
        }
        return returnPubData('分配成功');
    }

    /*
     * 订单分配销售
     */

    public function orderAssign($order) {
        $sales_user = new SalesUserModel();
        $dispatch_mod = new DispatchRuleModel();
        $dispatch_list = $dispatch_mod->where('is_del', 0)->order('sort desc')->select();
        $sales = [];
        $rule = [];
        foreach ($dispatch_list as $val) {
            if (!$val['no_time']) {
                $week_limit_arr = explode(',', $val['week_limit']);
                $day_week = date('w', strtotime($order['pay_time_unix']));
                $day_time = date('H:i:s', strtotime($order['pay_time_unix']));
                if (!($day_time > $val['time_start'] && $day_time < $val['time_end'] && in_array($day_week, $week_limit_arr))) {
                    continue;
                }
            }
            if ($val['account_id'] && $order['account_id'] != $val['account_id']) {
                continue;
            }
            if ($val['departures'] && !in_array($order['departure'], $val['departures_arr'])) {
                continue;
            }
            if ($val['customer_citys']) {
                $customer_info = (new CustomerModel())->where('buyer_info_phone', $order['buyer_info_phone'])->find();
                if ($customer_info) {
                    $sys_zone = new \app\common\model\admin\SysZoneModel();
                    $city_arr = $sys_zone->where('zone_id', 'in', $val['customer_citys_arr'])->column('zone_name');
                    if (!in_array($customer_info['city'], $city_arr)) {
                        continue;
                    }
                }
            }
            if (!$val['no_product']) {
                if (!in_array($order['product_id'], $val['use_products_arr'])) {
                    continue;
                }
            }
            $sales_where = [];
            $sales_where[] = ['sys_admin.status', 'eq', 1];
            if (!$val['no_limit']) {
                $sales_where[] = ['sales_user.orders', 'lt', $val['limit_count']];
            }
            if ($val['online_limit']) {
                $sales_where[] = ['sales_user.online', 'eq', ($val['online_limit'] == 1 ? 1 : 0)];
            }
            if (!$val['no_sale']) {
                $sales_where[] = ['sales_user.admin_id', 'in', $val['use_sales_arr']];
            }
            $sales = $sales_user->join('sys_admin', 'sales_user.admin_id = sys_admin.id')->where($sales_where)->select()->toArray();
            if (!empty($sales)) {
                $rule = $val;
                break;
            }
        }
        if (empty($sales)) {
            $list = $this->getSales();
            $sales_list = [];
            foreach ($list['admin_user'] as $vv) {
                if ($vv['status'] == 1) {
                    $sales_list[] = $vv;
                }
            }
            $randomKey = array_rand($sales_list);
            $randomElement = $sales_list[$randomKey];
            $sales_user_id = $randomElement['id'];
        } else {
            $randomKey = array_rand($sales);
            $randomElement = $sales[$randomKey];
            $sales_user_id = $randomElement['admin_id'];
        }
        $order_ser = new OrderServer();
        $order_ser->addSysFllowRecord($order['order_id'], $sales_user_id);
        $order->save(['sales_user_id' => $sales_user_id]);
        $order_assign_mod = new \app\common\model\order\OrderAssignModel();
        $param_assign = [
            'rule_id' => $rule['id'] ?? 0,
            'order_id' => $order['order_id'],
            'sales_user_id' => $sales_user_id
        ];
        $order_assign_mod->insert($param_assign);
        return $sales_user_id;
    }

    /*
     * 获取客户数据
     */

    public function getCustomerStatic($customer_id) {
        $customer_mod = new CustomerModel();
        $customer_info = $customer_mod->where('id', $customer_id)->find();
        $order_mod = new \app\common\model\order\OrderModel();
        $order_res = $order_mod->where('buyer_info_phone', $customer_info['buyer_info_phone'])->field('count(id) as order_count,sum(actual_amount) as order_sum_money')->select();
        $sales_mod = new \app\common\model\sales\SalesLeadModel();
        $sales_lead_count = $sales_mod->where('customer_id', $customer_id)->count();
        $order_combain_mod = new \app\common\model\order\OrderComplainModel();
        $order_complain_count = $order_combain_mod->where('customer_id', $customer_id)->count();
        return [
            'order_complain_count' => $order_complain_count,
            'order_count' => $order_res[0]['order_count'],
            'order_sum_money' => $order_res[0]['order_sum_money'] / 100,
            'sales_lead_count' => $sales_lead_count,
        ];
    }

}
