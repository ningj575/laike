<?php

/**
 * 订单投诉model
 */

namespace app\common\model\order;

use app\common\model\BaseModel;

class OrderComplainModel extends BaseModel {

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'c_time';
    protected $updateTime = 'u_time';
    protected $append = ['complain_fllow_status_text','urgent_level_text','complain_type_text'];
    protected $type = [
        'complain_time' => 'timestamp',
    ];
    public $urgent_level_arr = [
        1 => '一般',
        2 => '紧急',
        3 => '特急',
    ];
    public $complain_type_arr = [
        1 => '服务态度',
    ];
    public $complain_fllow_status_arr = [
        1 => ' 待分配',
        2 => ' 待跟进',
        3 => ' 已处理',
    ];

    public function __construct($data = []) {
        $this->table = 'order_complain';
        parent::__construct($data);
    }

    public function admin() {
        return $this->belongsTo('app\common\model\admin\SysAdminModel', 'admin_id', 'id');
    }
    public function customer() {
        return $this->belongsTo('CustomerModel', 'customer_id', 'id');
    }
     public function record() {
        return $this->hasMany('OrderFllowRecordModel','order_id','order_id')->where('type',2)->order('id desc');
    }    
    public function order() {
        return $this->hasOne('OrderModel', 'order_id', 'order_id');
    }
     public function getUrgentLevelTextAttr($val,$data){
        return $this->urgent_level_arr[$data['urgent_level']]??'';
    }
     public function getComplainTypeTextAttr($val,$data){
        return $this->complain_type_arr[$data['complain_type']]??'';
    }
    public function getComplainFllowStatusTextAttr($val,$data){
        return $this->complain_fllow_status_arr[$data['complain_fllow_status']]??'';
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
