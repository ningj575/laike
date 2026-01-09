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
            if (empty($param['id'])) {
                $flag = $mod->insert($param);
            } else {
                $flag = $mod->edit($param);
            }
            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }
        $id = input('param.id');
        $this->assign([
            'rules' => $mod->where('id', $id)->find()
        ]);
        return $this->fetch('order/dispatch/edit');
    }

    public function delete() {
        $id = input('param.id');
        $mod = new DispatchRuleModel();
        $flag = $mod->del($id);
        return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
    }

    public function status() {
        $id = input('param.id');
        $status = input('param.status',0);
        $mod = new DispatchRuleModel();
        $mod->where('id',$id)->setField('status',$status);
        return json(['code' => 1000, 'data' => [], 'msg' => '状态修改成功']);
    }

}
