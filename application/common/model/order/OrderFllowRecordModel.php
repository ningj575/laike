<?php

/**
 * 订单跟进model
 */

namespace app\common\model\order;

use app\common\model\BaseModel;
class OrderFllowRecordModel extends BaseModel
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'c_time';
    protected $type=[
        'next_fllow_time' => 'timestamp',    
        'fllow_time'=>'timestamp'
    ];
    public function __construct($data = [])
    {
        $this->table = 'order_fllow_record';
        parent::__construct($data);
    }   
    
    public function admin(){
        return $this->belongsTo('app\common\model\admin\SysAdminModel','admin_id','id');
    }

    /**
     * 
     * @param $param
     * @return array
     */
    public function insert($param)
    {
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
     * 
     * @param $id
     * @return array
     * @throws \Exception
     */
    public function del($id)
    {
        try {
            $this->where('id', $id)->data('is_del',1)->update();
            return ['code' => 1000, 'data' => '', 'msg' => langCommon('delete_success')];
        } catch (\PDOException $e) {
            return ['code' => 1001, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

    /**
     * 
     * @param $param
     * @return array
     */
    public function edit($param)
    {
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
