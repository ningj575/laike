<?php

/**
 * model
 */

namespace app\common\model\sales;

use app\common\model\BaseModel;

class SalesLeadModel extends BaseModel {

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'c_time';
    protected $updateTime = 'u_time';
    protected $type = [
        'submit_time' => 'timestamp',
    ];
    protected $append = ['lead_fllow_status_text', 'intent_level_text'];
    public $intent_level_arr = [
        1 => '低',
        2 => '中',
        3 => '高',
    ];
    public $lead_fllow_status_arr = [
        1 => ' 待分配',
        2 => ' 待跟进',
        3 => ' 已处理',
    ];
    public $lead_source_arr = ['主动咨询', '客户介绍'];

    public function __construct($data = []) {
        $this->table = 'sales_lead';
        parent::__construct($data);
    }

    public function record() {
        return $this->hasMany('app\common\model\order\OrderFllowRecordModel', 'order_id', 'lead_num')->where('type', 3)->order('id desc');
    }

    public function customer() {
        return $this->belongsTo('app\common\model\order\CustomerModel', 'customer_id', 'id');
    }

    public function admin() {
        return $this->belongsTo('app\common\model\admin\SysAdminModel', 'admin_id', 'id');
    }

    public function getLeadFllowStatusTextAttr($val, $data) {
        return $this->lead_fllow_status_arr[$data['lead_fllow_status']] ?? '';
    }

    public function getIntentLevelTextAttr($val, $data) {
        return $this->intent_level_arr[$data['intent_level']] ?? '';
    }

    /**
     * 插入角色信息
     * @param $param
     * @return array
     */
    public function insert($param) {
        try {
            $result = $this->save($param);
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
            $result = $this->save($param, ['id' => $param['id']]);
            if ($result) {
                return ['code' => 1000, 'data' => '', 'msg' => langCommon('edit_success')];
            }
            return ['code' => 1001, 'data' => '', 'msg' => $this->getError()];
        } catch (\PDOException $e) {
            return ['code' => 1001, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

}
