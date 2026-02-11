<?php

/**
 * 出行人model
 */

namespace app\common\model\order;

use app\common\model\BaseModel;
class OrderBookTravelerModel extends BaseModel
{

   
 protected $append = ['license_type_text'];
    public function __construct($data = [])
    {
        $this->table = 'order_book_traveler';
        parent::__construct($data);
    }   
   
    public function getLicenseTypeTextAttr($val, $data){
        $license_text=['1'=>'身份证','2'=>'港澳通行证','3'=>'台湾通行证','4'=>'回乡证','5'=>'台胞证','6'=>'护照','7'=>'外籍护照'];
        return $license_text[$data['license_type']]??'';
    }

    /**
     * 插入角色信息
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
