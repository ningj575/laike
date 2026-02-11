<?php

/**
 * 产品model
 */

namespace app\common\model\product;

use app\common\model\BaseModel;

class ProductModel extends BaseModel
{
    protected $type=[
        'sold_start_time' => 'timestamp',
        'sold_end_time' => 'timestamp',
        'create_time' => 'timestamp',
        'update_time' => 'timestamp',
    ];
    protected $append = ['product_type_text'];


    public function __construct($data = [])
    {
        $this->table = 'product';
        parent::__construct($data);
    }

    public function getProductTypeTextAttr($val, $data){
        $product_text=['12'=>'预售券','2'=>'预定券'];
        return $product_text[$data['product_type']]??'';
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
                return ['code' => 1000, 'data' => '', 'msg' => '新增成功'];
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
            return ['code' => 1000, 'data' => '', 'msg' => '删除成功'];
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
                return ['code' => 1000, 'data' => '', 'msg' => '修改成功'];
            }
            return ['code' => 1001, 'data' => '', 'msg' => $this->getError()];
        } catch (\PDOException $e) {
            return ['code' => 1001, 'data' => '', 'msg' => $e->getMessage()];
        }
    }
}
