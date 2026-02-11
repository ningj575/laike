<?php

/**
 * 派单规则
 */

namespace app\common\model\order;

use app\common\model\BaseModel;

class DispatchRuleModel extends BaseModel {

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'c_time';
    protected $updateTime = 'u_time';
    protected $append = ['use_products_arr', 'use_sales_arr', 'week_limit_arr', 'order_week_limit_arr','customer_citys_arr','departures_arr'];
    public $main_rule = [
        1 => ['rule_type' => 1, 'button' => 'fuzai_save', 'main_name' => '负荷均衡', 'desc' => '按员工当前接单量、在服务订单数计算负荷，优先派给负荷率低的员工（如 A 员工当前 1 单，B 员工 3 单，优先派给 A）'],
        2 => ['rule_type' => 2, 'button' => 'customer_save', 'main_name' => '客户关联延续', 'desc' => '同一客户的复购订单，优先派给此前服务过该客户的员工'],
        3 => ['rule_type' => 3, 'button' => 'lunxun_save', 'main_name' => '轮询公平', 'desc' => '无特殊优先级的普通订单，按在线员工接单顺序循环分配'],
        4 => ['rule_type' => 4, 'button' => 'time_save', 'main_name' => '区域/时段归属', 'desc' => '按在线员工常服务区域、可服务时段，匹配订单的目的地 / 服务时间派单。'],
        5 => ['rule_type' => 5, 'button' => 'business_save', 'main_name' => '按店铺', 'desc' => '按系统已对接的店铺进行派单'],
        6 => ['rule_type' => 6, 'button' => 'zidingyi_save', 'main_name' => '自定义', 'desc' => '按自定义规则进行派单，如指定商品类型分配给指定人员'],
    ];

    public function __construct($data = []) {
        $this->table = 'dispatch_rule';
        parent::__construct($data);
    }

    public function getUseProductsArrAttr($val, $data) {
        if (empty($data['use_products'])) {
            return [];
        }
        return explode(',', $data['use_products']);
    }
    public function getDeparturesArrAttr($val, $data) {
        if (empty($data['departures'])) {
            return [];
        }
        return explode(',', $data['departures']);
    }
    public function getCustomerCitysArrAttr($val, $data) {
        if (empty($data['customer_citys'])) {
            return [];
        }
        return explode(',', $data['customer_citys']);
    }
    public function getUseSalesArrAttr($val, $data) {
        if (empty($data['use_sales'])) {
            return [];
        }
        return explode(',', $data['use_sales']);
    }
    public function getWeekLimitArrAttr($val, $data) {
        if (empty($data['week_limit'])) {
            return [];
        }
        return explode(',', $data['week_limit']);
    }
    public function getOrderWeekLimitArrAttr($val, $data) {
        if (empty($data['order_week_limit'])) {
            return [];
        }
        return explode(',', $data['order_week_limit']);
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
