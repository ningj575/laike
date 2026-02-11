<?php

/**
 * 订单分配日志model
 */

namespace app\common\model\order;

use app\common\model\BaseModel;

class OrderAssignModel extends BaseModel {

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'c_time';

    public function __construct($data = []) {
        $this->table = 'order_assign';
        // 监听销售分配处理事件
        \think\facade\Hook::listen('sales_orders',$this);
        parent::__construct($data);
    }

    public function admin() {
        return $this->belongsTo('app\common\model\admin\SysAdminModel', 'admin_id', 'id');
    }

    public function sales() {
        return $this->belongsTo('app\common\model\admin\SysAdminModel', 'sales_user_id', 'id');
    }

    public function rule() {
        return $this->belongsTo('DispatchRuleModel', 'rule_id', 'id');
    }

    public function getUseProductsArrAttr($val, $data) {
        if (empty($data['use_products'])) {
            return [];
        }
        return explode(',', $data['use_products']);
    }

    /**
     * 插入角色信息
     * @param $param
     * @return array
     */
    public function insert($param) {
        try {
            $result = $this->allowField(true)->save($param);
            if (false === $result) {
                return ['code' => 1001, 'data' => '', 'msg' => $this->getError()];
            } else {
                return ['code' => 1000, 'data' => '', 'msg' => langCommon('add_success')];
            }
        } catch (\PDOException $e) {
            return ['code' => 1001, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

    /**
     * 删除角色
     * @param $id
     * @return array
     * @throws \Exception
     */
    public function del($id) {
        try {
            $this->where('id', $id)->data('is_del', 1)->update();
            return ['code' => 1000, 'data' => '', 'msg' => langCommon('delete_success')];
        } catch (\PDOException $e) {
            return ['code' => 1001, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

    /**
     * 编辑角色信息
     * @param $param
     * @return array
     */
    public function edit($param) {
        try {
            $result = $this->allowField(true)->save($param, ['id' => $param['id']]);
            if ($result) {
                return ['code' => 1000, 'data' => '', 'msg' => langCommon('edit_success')];
            }
            return ['code' => 1001, 'data' => '', 'msg' => $this->getError()];
        } catch (\PDOException $e) {
            return ['code' => 1001, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

}
