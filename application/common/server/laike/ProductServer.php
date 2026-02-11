<?php

/**
  产品server
 */

namespace app\common\server\laike;

use app\common\server\BaseServer;
use app\common\model\product\ProductModel;
use app\common\server\laike\DouyinServer;
use app\common\model\shop\ShopModel;
use app\common\model\product\ProductInfoModel;

class ProductServer extends BaseServer {
    /*
     * 更新产品数据
     */

    public function productDo() {
        $shop_mod = new ShopModel();
        $shop_list = $shop_mod->where('is_del', 0)->field('id,account_id,name')->select();
        if (empty($shop_list)) {
            return returnPubData('没有商家信息');
        }
        foreach ($shop_list as $val) {
            if (empty($val['account_id'])) {
                continue;
            }
            $this->addProduct($val['account_id']);
        }
        return returnPubData('处理成功',1000);
    }

    private function addProduct($account_id, $cursor = '') {
        $ser = new DouyinServer();
        $res = $ser->queryProduct($account_id, $cursor);
        if (empty($res['data']['products'])) {
            return true;
        }
        $data = [];
        $add_data = [];
        $info_data=[];
        $product_mod = new ProductModel();
        $product_info_mod=new ProductInfoModel();
        foreach ($res['data']['products'] as $val) {
            $product = $val['product'];            
            $data = [
                'online_status' => $val['online_status'],
                'product_id' => $product['product_id'],
                'product_name' => $product['product_name'],
                'product_type' => $product['product_type'],
                'product_sub_type' => $product['product_sub_type'],
                'sold_end_time' => $product['sold_end_time'],
                'sold_start_time' => $product['sold_start_time'],
                'account_name' => $product['account_name'],
                'creator_account_id' => $product['creator_account_id'],
                'owner_account_id' => $product['owner_account_id'],
                'biz_line' => $product['biz_line'],
                'category_id' => $product['category_id'],
                'category_full_name' => $product['category_full_name'],
                'create_time' => $product['create_time'],
                 'update_time' => $product['update_time'],
                 'pois' => json_encode($product['pois']),               
            ];
            if(!empty($val['sku'])){
                $data['actual_amount']=$val['sku']['actual_amount'];
                $data['origin_amount']=$val['sku']['origin_amount'];
                $data['status']=$val['sku']['status'];
                $data['limit_type']=$val['sku']['stock']['limit_type'];
                $data['stock_qty']=$val['sku']['stock']['stock_qty'];
                $data['sku_id']=$val['sku']['sku_id'];
            }
            if(!empty($product['attr_key_value_map']['departure'])){
                $departure= json_decode($product['attr_key_value_map']['departure'],true);
                $data['departure']=$departure['departure'][0];
            }
            if(!empty($product['attr_key_value_map']['destination'])){
                $departure= json_decode($product['attr_key_value_map']['destination'],true);
                $data['destination']=$departure['destination'][0];
            }   
            if(!empty($product['attr_key_value_map']['apply_date'])){
                $apply_date= json_decode($product['attr_key_value_map']['apply_date'],true);
                $data['use_start_date']=$apply_date['use_start_date'];
                $data['use_end_date']=$apply_date['use_end_date'];
            }      
            $product_info = $product_mod->where('product_id', 'eq', $product['product_id'])->find();
            if (!empty($product_info)) {
                $product_info->save($data);
            } else {
                $add_data[] = $data;
            }
            if(!empty($product['attr_key_value_map'])){
                $product_info_id = $product_info_mod->where('product_id', 'eq', $product['product_id'])->value('id');
                if(!empty($product_info_id)){
                    $product['attr_key_value_map']['id']=$product_info_id;
                }
                $product['attr_key_value_map']['product_id']=$product['product_id'];
                $info_data[]=$product['attr_key_value_map'];
            }
        }
        if(!empty($add_data)){
            $product_mod->saveAll($add_data);
        }       
        if(!empty($info_data)){
            $product_info_mod->allowField(true)->saveAll($info_data);
        }
        if ($res['data']['has_more']) {
            $this->addProduct($account_id, $res['data']['next_cursor']);
        }
        return true;
    }

}
