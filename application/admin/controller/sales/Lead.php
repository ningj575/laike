<?php

/**
 * 销售线索
 */

namespace app\admin\controller\sales;

use app\common\server\admin\AdminServer;
use app\common\model\sales\SalesLeadModel;
use app\common\server\laike\SalesServer;
use app\common\model\order\CustomerModel;
use app\common\model\order\OrderFllowRecordModel;

class Lead extends AdminServer {

    public function index() {
        //用于API - JSON
        $mod = new SalesLeadModel();
        if (input('get.page')) {
            $where = [];
            $consult_content = input('get.consult_content', '');
            $intent_level = input('get.intent_level', '');
            $lead_source = input('get.lead_source', '');
            $buyer_info_name = input('get.buyer_info_name', '');
            $buyer_info_phone = input('get.buyer_info_phone', '');
            $lead_fllow_status = input('get.lead_fllow_status', '');
            $page = input('get.page/d', 1);
            $limit = input('get.limit/d', 10);
            if (!empty($consult_content)) {
                $mod = $mod->where('consult_content', 'like', '%' . $consult_content . '%');
            }
            if (!empty($intent_level)) {
                $mod = $mod->where('intent_level', $intent_level);
            }
            if (!empty($buyer_info_phone)) {
                $mod = $mod->where('phone', $buyer_info_phone);
            }
            if (!empty($buyer_info_name)) {
                $mod = $mod->where('name', $buyer_info_name);
            }
            if (!empty($lead_source)) {
                $mod = $mod->where('lead_source', $lead_source);
            }
            if (!empty($lead_fllow_status)) {
                $mod = $mod->where('lead_fllow_status', $lead_fllow_status);
            }
            $count = $mod->where($where)->count();
            $lists = $mod->with(['admin', 'customer'])->where($where)->page($page, $limit)->order('id desc')->select();
            foreach ($lists as &$val) {
                
            }
            $data = ['msg' => '', 'code' => 1000, 'data' => $lists, 'count' => $count];
            return json($data);
        }
        $sales_list = (new SalesServer())->getSales();
        $lead_fllow_status = input('get.lead_fllow_status', '');
        $buyer_info_phone = input('get.buyer_info_phone', '');
        $this->assign([
            'sales_list' => !empty($sales_list['admin_user']) ? $sales_list['admin_user'] : [],
            'intent_level_arr' => $mod->intent_level_arr,
            'lead_fllow_status_arr' => $mod->lead_fllow_status_arr,
            'lead_source_arr' => $mod->lead_source_arr,
            'lead_fllow_status' => $lead_fllow_status,
            'buyer_info_phone' => $buyer_info_phone
        ]);
        return $this->fetch('sales/lead/index');
    }

    /**
     * 编辑
     */
    public function edit() {
        $mod = new SalesLeadModel();
        if (request()->isAjax()) {
            $param = input('post.');
            $customer_mod = new CustomerModel();
            $customer_info = $customer_mod->where('buyer_info_phone', $param['phone'])->find();
            if (empty($customer_info)) {
                $customer_data = [
                    'buyer_info_phone' => $param['phone'],
                    'buyer_info_name' => $param['name'],
                ];
                $order_ser = new \app\common\server\laike\OrderServer();
                $mobile_addr = $order_ser->getMobileAddr($param['phone']);
                if (!empty($mobile_addr['result'])) {
                    $sys_zone = new \app\common\model\admin\SysZoneModel();
                    $province = $sys_zone->where('zone_name', 'like', '%' . $mobile_addr['result']['province'] . '%')->value('zone_name');
                    $city = $sys_zone->where('zone_name', 'like', '%' . $mobile_addr['result']['city'] . '%')->value('zone_name');
                    $customer_data['province'] = $province;
                    $customer_data['city'] = $city;
                }
                $customer_mod->allowField(true)->save($customer_data);
                $param['customer_id'] = $customer_mod->id;
            } else {
                $param['customer_id'] = $customer_info['id'];
            }
            if (empty($param['id'])) {
                $param['admin_id'] = $this->ADMIN_INFO['uid'];
                $flag = $mod->insert($param);
            } else {
                $flag = $mod->edit($param);
            }
            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }
        $id = input('param.id');
        $sales_list = (new SalesServer())->getSales();
        $this->assign([
            'lead_num' => 'xs' . getOrderSn(4),
            'lead' => $mod->with('customer')->where('id', $id)->find(),
            'sales_list' => !empty($sales_list['admin_user']) ? $sales_list['admin_user'] : [],
            'intent_level_arr' => $mod->intent_level_arr,
            'lead_fllow_status_arr' => $mod->lead_fllow_status_arr,
            'lead_source_arr' => $mod->lead_source_arr,
        ]);
        return $this->fetch('sales/lead/edit');
    }

    /**
     * 详情
     */
    public function info() {
        $mod = new SalesLeadModel();
        $id = input('param.id');
        $sales_ser = new SalesServer();
        $sales_list = $sales_ser->getSales();
        $info = $mod->with(['customer', 'record'])->where('id', $id)->find();
        $this->assign([
            'lead' => $info,
            'sales_list' => !empty($sales_list['admin_user']) ? $sales_list['admin_user'] : [],
            'lead_static' => $sales_ser->getCustomerStatic($info['customer_id'])
        ]);
        return $this->fetch('sales/lead/info');
    }

    /**
     * 删除
     */
    public function del() {
        $id = input('param.id');
        $mod = new SalesLeadModel();
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
            $param['type'] = 3;
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
        $mod = new SalesLeadModel();
        $lead_info = $mod->where('id', $id)->find();
        $lead_info->save(["$field" => $value]);
        if ($field == 'sales_user_id') {
            $lead_info->lead_fllow_status == 1 && $lead_info->save(["lead_fllow_status" => 2]);
            $order_assign_mod = new \app\common\model\order\OrderAssignModel();
            $assign_param = [
                'order_id' => $lead_info['lead_num'],
                'sales_user_id' => $value,
                'admin_id' => $this->ADMIN_INFO['uid'],
                'rule_id' => -1,
                'type' => 3
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
        $mod = new SalesLeadModel();
        $mod->save(["$field" => $value], [['id', 'in', $ids]]);
        if ($field == 'sales_user_id') {
            $mod->save(["lead_fllow_status" => 2], [['id', 'in', $ids], ['lead_fllow_status', 'eq', 1]]);
            $order_assign_mod = new \app\common\model\order\OrderAssignModel();
            foreach ($ids as $vv) {
                $lead_num = $mod->where('id', $vv)->value('lead_num');
                $assign_param[] = [
                    'order_id' => $lead_num,
                    'sales_user_id' => $value,
                    'admin_id' => $this->ADMIN_INFO['uid'],
                    'rule_id' => -1,
                    'type' => 3
                ];
            }
            $order_assign_mod->saveAll($assign_param);
        }
        return json(['code' => 1000, 'data' => [], 'msg' => '修改成功']);
    }

}
