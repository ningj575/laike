<?php

/**
 * 订单model
 */

namespace app\common\model\order;

use app\common\model\BaseModel;
class OrderModel extends BaseModel
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'c_time';
    protected $updateTime = 'u_time';
    protected $append = ['order_status_text'];

    public function __construct($data = [])
    {
        $this->table = 'order';
        parent::__construct($data);
    }   

    public function shop(){
        return $this->belongsTo('app\common\model\shop\ShopModel','shop_id','id');
    }

    public function kefu(){
        return $this->belongsTo('app\common\model\admin\SysAdminModel','kefu_id','id');
    }
    public function getOrderStatusTextAttr($val, $data){
        $status_text=['0'=>'待分配','1'=>'待跟进','2'=>'已处理'];
        return $status_text[$data['order_status']]??'';
    }

    /**
     * 插入角色信息
     * @param $param
     * @return array
     */
    public function insert($param)
    {
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
     * 编辑角色信息
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
