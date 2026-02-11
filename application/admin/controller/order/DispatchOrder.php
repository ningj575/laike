<?php

/**
 * 智能派单
 */

namespace app\admin\controller\order;

use app\common\server\admin\AdminServer;
use app\common\model\order\DispatchRuleModel;

class DispatchOrder extends AdminServer {

    public function rule() {
        $mod = new DispatchRuleModel();
        $rules = $mod->where('is_del', 0)->order('sort desc')->select();
        $this->assign('rules', $rules);
//        $cache = new \think\cache\driver\Redis();
//        $online_arr = $cache->handler()->keys('admin:active:*');
//        $this->assign('online_count',count($online_arr));
        return $this->fetch('order/dispatch/rule');
    }

    /**
     * 新增
     */
    public function add() {
        $mod = new DispatchRuleModel();
        if (request()->isAjax()) {
            $param = input('post.');
            if (empty($param['status'])) {
                $param['status'] = 0;
            }
            if (empty($param['no_product'])) {
                $param['no_product'] = 0;
            }
            if (empty($param['no_use'])) {
                $param['no_use'] = 0;
            }
            if (empty($param['no_sale'])) {
                $param['no_sale'] = 0;
            }
            if (empty($param['no_limit'])) {
                $param['no_limit'] = 0;
            }
            if (!empty($param['week_limit'])) {
                $param['week_limit'] = implode(',', $param['week_limit']);
            }
            if (!empty($param['time_range'])) {
                $time_range = explode('-', $param['time_range']);
                $param['time_start'] = $time_range[0];
                $param['time_end'] = $time_range[1];
            }
            $rule_info = $mod->where([['rule_type', 'eq', $param['rule_type']], ['is_del', 'eq', 0]])->find();
            if (!empty($rule_info)) {
                $param['id'] = $rule_info['id'];
            }
            $param['online_limit'] = $param['online_limit_' . $param['rule_type']];
            if (empty($param['id'])) {
                $flag = $mod->insert($param);
            } else {
                $flag = $mod->edit($param);
            }
            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }
        $rule_id = input('id');
        $this->assign([
            'main_rules' => $mod->main_rule,
            'rule' => $mod->where('id', $rule_id)->find()
        ]);
        return $this->fetch('order/dispatch/add_rule');
    }

    public function rule_type() {
        $rule_type = input('rule_type', 1);
        $mod = new DispatchRuleModel();
        $rule = $mod->where([['rule_type', 'eq', $rule_type], ['is_del', 'eq', 0]])->find();
        $sales = new \app\common\server\laike\SalesServer();
        $sales_list = $sales->getSales();
        $product_mod = new \app\common\model\product\ProductModel();
        $shop_mod = new \app\common\model\shop\ShopModel();
        $shop_list = $shop_mod->where('is_del', 0)->select();
        $rule_config = $mod->main_rule[$rule_type];
        $rule_config['main_name'] = $rule_config['main_name'] . '规则';
        $sys_zone = new \app\common\model\admin\SysZoneModel();
        $city_list = $sys_zone->where('Rank', 1)->select();
        $this->assign([
            'rules' => $rule,
            'sales_list' => !empty($sales_list['admin_user']) ? $sales_list['admin_user'] : [],
            'products_list' => $product_mod->where('online_status', 1)->select(),
            'rule_config' => $rule_config,
            'business_list' => $shop_list,
            'main_rules' => $mod->main_rule,
            'city_list' => $city_list
        ]);
        $this->assign('rule_type', $rule_type);
        return $this->fetch('order/dispatch/rule_type');
    }

    public function delete() {
        $id = input('param.id');
        $mod = new DispatchRuleModel();
        $flag = $mod->del($id);
        return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
    }

    public function status() {
        $id = input('param.id');
        $status = input('param.status', 0);
        $mod = new DispatchRuleModel();
        $mod->where('id', $id)->setField('status', $status);
        return json(['code' => 1000, 'data' => [], 'msg' => '状态修改成功']);
    }

    public function record() {
        $mod = new \app\common\model\order\OrderAssignModel();
        if (input('get.page')) {
            $where = [];
            $where[] = ['type', 'eq', 1];
            $order_id = input('get.order_id', '');
            $page = input('get.page/d', 1);
            $limit = input('get.limit/d', 10);
            if (!empty($order_id)) {
                $mod = $mod->where('order_id', $order_id);
            }
            $count = $mod->where($where)->count();
            $lists = $mod->with(['admin', 'sales', 'rule'])->where($where)->page($page, $limit)->order('id desc')->select();
            foreach ($lists as &$val) {
                $val['admin_name'] = $val['admin']['admin_name'] ?: '系统';
                $val['sales_user_name'] = $val['sales']['admin_name'] ?: '';
                if ($val['rule_id'] == -1) {
                    $val['rule_name'] = '手动分配';
                } else {
                    $val['rule_name'] = $val['rule']['rule_name'] ?: '系统随机分配';
                }
            }
            $data = ['msg' => '', 'code' => 1000, 'data' => $lists, 'count' => $count];
            return json($data);
        }
        return $this->fetch('order/dispatch/record');
    }

}
