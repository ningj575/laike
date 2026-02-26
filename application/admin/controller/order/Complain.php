<?php

/**
 * 订单管理 
 */

namespace app\admin\controller\order;

use app\common\server\admin\AdminServer;
use app\common\model\order\OrderModel;
use app\common\model\order\OrderComplainModel;
use app\common\server\laike\SalesServer;
use app\common\model\order\CustomerModel;
use app\common\model\order\OrderFllowRecordModel;

class Complain extends AdminServer {

    public function index() {
        //用于API - JSON
        $mod = new OrderComplainModel();
        if (input('get.page')) {
            $where = [];
            $order_id = input('get.order_id', '');
            $buyer_info_name = input('get.buyer_info_name', '');
            $buyer_info_phone = input('get.buyer_info_phone', '');
            $complain_type = input('get.complain_type', '');
            $complain_fllow_status = input('get.complain_fllow_status', '');
            $page = input('get.page/d', 1);
            $limit = input('get.limit/d', 10);
            $admin_id = $this->ADMIN_INFO['uid'];
            $sale_user_mod = new \app\common\model\sales\SalesUserModel();
            $is_sales = $sale_user_mod->where('admin_id', $admin_id)->find();
            if ($is_sales) {
                $mod = $mod->where('sales_user_id', $admin_id);
            }
            if (!empty($order_id)) {
                $mod = $mod->where('order_id', $order_id);
            }
            if (!empty($complain_type)) {
                $mod = $mod->where('complain_type', $complain_type);
            }
            if (!empty($buyer_info_phone)) {
                $customer_mod = new CustomerModel();
                $customer_ids = $customer_mod->where('buyer_info_phone', $buyer_info_phone)->column('id');
                $mod = $mod->where('customer_id', 'in', $customer_ids);
            }
            if (!empty($buyer_info_name)) {
                $customer_mod = new CustomerModel();
                $customer_ids = $customer_mod->where('buyer_info_name', $buyer_info_name)->column('id');
                $mod = $mod->where('customer_id', 'in', $customer_ids);
            }
            if (!empty($complain_fllow_status)) {
                $mod = $mod->where('complain_fllow_status', $complain_fllow_status);
            }
            $count = $mod->where($where)->count();
            $lists = $mod->with(['admin', 'customer'])->where($where)->page($page, $limit)->order('id desc')->select();
            foreach ($lists as &$val) {
                
            }
            $data = ['msg' => '', 'code' => 1000, 'data' => $lists, 'count' => $count];
            return json($data);
        }
        $complain_fllow_status = input('get.complain_fllow_status', '');
        $buyer_info_phone = input('get.buyer_info_phone', '');
        $sales_list = (new SalesServer())->getSales();
        $this->assign([
            'sales_list' => !empty($sales_list['admin_user']) ? $sales_list['admin_user'] : [],
            'complain_fllow_status_arr' => $mod->complain_fllow_status_arr,
            'complain_type_arr' => $mod->complain_type_arr,
            'complain_fllow_status' => $complain_fllow_status,
            'buyer_info_phone' => $buyer_info_phone
        ]);
        return $this->fetch('order/complain/index');
    }

    /**
     * 编辑
     */
    public function edit() {
        $mod = new OrderComplainModel();
        if (request()->isAjax()) {
            $param = input('post.');
            $order_mod = new OrderModel();
            $order_info = $order_mod->with('customer')->where('order_id', $param['order_id'])->find();
            if (empty($order_info)) {
                return json(['code' => 1001, 'msg' => '关联的来客订单号不存在']);
            }
            $param['customer_id'] = $order_info['customer']['id'];
            if (empty($param['id'])) {
                $param['admin_id'] = $this->ADMIN_INFO['uid'];
                $flag = $mod->insert($param);
            } else {
                $flag = $mod->edit($param);
            }
            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }
        $id = input('param.id');
        $order_id = input('param.order_id');
        $sales_list = (new SalesServer())->getSales();
        $this->assign([
            'complain_num' => 'ts' . getOrderSn(4),
            'complain' => $mod->where('id', $id)->find(),
            'sales_list' => !empty($sales_list['admin_user']) ? $sales_list['admin_user'] : [],
            'complain_fllow_status_arr' => $mod->complain_fllow_status_arr,
            'complain_type_arr' => $mod->complain_type_arr,
            'urgent_level_arr' => $mod->urgent_level_arr,
            'order_id' => $order_id,
        ]);
        return $this->fetch('order/complain/edit');
    }

    /**
     * 详情
     */
    public function info() {
        $mod = new OrderComplainModel();
        $id = input('param.id');
        $sales_ser = new SalesServer();
        $sales_list = $sales_ser->getSales();
        $info = $mod->with(['customer', 'order'])->where('id', $id)->find();
        $complain_static = $sales_ser->getCustomerStatic($info['customer_id']);
        $this->assign([
            'complain' => $info,
            'sales_list' => !empty($sales_list['admin_user']) ? $sales_list['admin_user'] : [],
            'icon_color' => $this->icon_color,
            'complain_static' => $complain_static,
        ]);
        return $this->fetch('order/complain/info');
    }

    /**
     * 删除
     */
    public function del() {
        $id = input('param.id');
        $mod = new OrderComplainModel();
        $flag = $mod->del($id);
        return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
    }

    /*
     * 添加记录
     */

    public function addRecord() {
        if (request()->isAjax()) {
            $param = input('param.');
            $param['admin_id'] = $this->ADMIN_INFO['uid'];
            $param['type'] = 2;
            $param['manual_fllow'] = 2;
            $OrderFllowRecordModel = new OrderFllowRecordModel();
            $OrderFllowRecordModel->insert($param);
            return json(['code' => 1000, 'data' => [], 'msg' => '添加成功']);
        }
        return $this->fetch('order/add_record');
    }

    /*
     * 
     */

    public function updataValue() {
        $id = input('param.id');
        $field = input('param.field');
        $value = input('param.value');
        $mod = new OrderComplainModel();
        $complain_info = $mod->where('id', $id)->find();
        $complain_info->save(["$field" => $value]);
        if ($field == 'sales_user_id') {
            if ($complain_info['complain_fllow_status'] == 3) {
                return json(['code' => 1001, 'msg' => '该订单已处理，不能更改销售员']);
            }
            $complain_info->complain_fllow_status == 1 && $complain_info->save(["complain_fllow_status" => 2]);
            $order_assign_mod = new \app\common\model\order\OrderAssignModel();
            $assign_param = [
                'order_id' => $complain_info['complain_num'],
                'sales_user_id' => $value,
                'admin_id' => $this->ADMIN_INFO['uid'],
                'rule_id' => -1,
                'type' => 2,
            ];
            $order_assign_mod->insert($assign_param);
        }
        return json(['code' => 1000, 'data' => [], 'msg' => '修改成功']);
    }

    /*
     * 批量修改
     */

    public function batchUpdate() {
        $ids = input('param.ids');
        $field = input('param.field');
        $value = input('param.value');
        $mod = new OrderComplainModel();
        $mod->save(["$field" => $value], [['id', 'in', $ids]]);
        if ($field == 'sales_user_id') {
            $mod->save(["complain_fllow_status" => 2], [['id', 'in', $ids], ['complain_fllow_status', 'eq', 1]]);
            $order_assign_mod = new \app\common\model\order\OrderAssignModel();
            foreach ($ids as $vv) {
                $complain_num = $mod->where('id', $vv)->value('complain_num');
                $assign_param[] = [
                    'order_id' => $complain_num,
                    'sales_user_id' => $value,
                    'admin_id' => $this->ADMIN_INFO['uid'],
                    'rule_id' => -1,
                    'type' => 2
                ];
            }
            $order_assign_mod->saveAll($assign_param);
        }
        return json(['code' => 1000, 'data' => [], 'msg' => '修改成功']);
    }

}
