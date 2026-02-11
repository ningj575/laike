<?php

/**
 * 客户管理 
 */

namespace app\admin\controller\order;

use app\common\server\admin\AdminServer;
use app\common\model\order\CustomerModel;
use app\common\server\laike\SalesServer;
use app\common\model\order\TagsModel;

class Customer extends AdminServer {

    public function index() {
        //用于API - JSON
        $mod = new CustomerModel();
        if (input('get.page')) {
            $where = [];
            $id_card = input('get.id_card', '');
            $buyer_info_name = input('get.buyer_info_name', '');
            $buyer_info_phone = input('get.buyer_info_phone', '');
            $tags_id = input('get.tags_id', '');
            $page = input('get.page/d', 1);
            $limit = input('get.limit/d', 10);
            if (!empty($id_card)) {
                $mod = $mod->where('id_card', $id_card);
            }
            if (!empty($buyer_info_name)) {
                $mod = $mod->where('buyer_info_name', $buyer_info_name);
            }
            if (!empty($buyer_info_phone)) {
                $mod = $mod->where('buyer_info_phone', $buyer_info_phone);
            }
            if (!empty($tags_id)) {
                $tags_arr = explode(',', $tags_id);
                $sql = '';
                foreach ($tags_arr as $k => $v) {
                    if ($k != 0) {
                        $sql = $sql . ' and ';
                    }
                    $sql = $sql . 'JSON_CONTAINS(tags, \'"' . $v . '"\')';
                }
                $where = 'tags!="" and (' . $sql . ')';
            }
            $count = $mod->where($where)->count();
            $lists = $mod->where($where)->page($page, $limit)->order('id desc')->select();
            foreach ($lists as &$val) {
                
            }
            $data = ['msg' => '', 'code' => 1000, 'data' => $lists, 'count' => $count];
            return json($data);
        }
        $sales_ser=new SalesServer();
        $sales_list = $sales_ser->getSales();        
        $tags_mod = new TagsModel();
        $this->assign([
            'sales_list' => !empty($sales_list['admin_user']) ? $sales_list['admin_user'] : [],
            'tags_list' => $tags_mod->where([['type', 'eq', 1], ['status', 'eq', 1]])->select()
        ]);
        return $this->fetch('order/customer/index');
    }

    /**
     * 编辑
     */
    public function edit() {
        $mod = new CustomerModel();
        if (request()->isAjax()) {
            $param = input('post.');
            if (!empty($param['tags'])) {
                $param['tags'] = json_encode(explode(',', $param['tags']), JSON_UNESCAPED_UNICODE);
            }
            if($param['id_card']&& !check_id_number($param['id_card'])){
                return json(['code' => 1001,'msg' => '身份证有误']);
            }
            if (empty($param['id'])) {
                $flag = $mod->insert($param);
            } else {
                $flag = $mod->edit($param);
            }
            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }
        $id = input('param.id');
        $tags_mod = new TagsModel();
        $customer = $mod->where('id', $id)->find();
        $this->assign([
            'customer' => $customer,
            'tags_list' => $tags_mod->where([['type', 'eq', 1], ['status', 'eq', 1]])->select()
        ]);
        return $this->fetch('order/customer/edit');
    }

    /**
     * 详情
     */
    public function info() {
        $mod = new CustomerModel();
        $id = input('param.id');
        $phone = input('param.phone');
        if ($id) {
            $info = $mod->where('id', $id)->find();
        } else {
            $info = $mod->where('buyer_info_phone', $phone)->find();
        }
        $sales_ser=new SalesServer();
        $customer_static=$sales_ser->getCustomerStatic($info['id']);
        $this->assign([
            'customer' => $info,
            'icon_color' => $this->icon_color,
            'customer_static'=>$customer_static
        ]);
        return $this->fetch('order/customer/info');
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
     * 
     */

    public function updataValue() {
        $id = input('param.id');
        $field = input('param.field');
        $value = input('param.value');
        if ($field == 'id_card' && !check_id_number($value)) {
            return (['code' => 1001, 'msg' => '身份证号有误']);
        }
        if(is_array($value)){
            $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        }
        $mod = new CustomerModel();
        $mod->where('id', $id)->setField("$field", $value);
        return json(['code' => 1000, 'data' => [], 'msg' => '修改成功']);
    }

}
