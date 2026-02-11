<?php

/**
 * 产品管理 
 */

namespace app\admin\controller\product;

use app\common\server\admin\AdminServer;
use app\common\model\product\ProductModel;
use app\common\model\shop\ShopModel;

class Product extends AdminServer {

    public function index() {
        //用于API - JSON
        if (input('get.page')) {
            $mod = new ProductModel();
            $product_id = input('get.product_id', '');
            $product_name = input('get.product_name', '');
            $account_id = input('get.account_id',0);
            $online_status = input('get.online_status',0);            
            $where=[];
            $mod =$mod->where('product_type', 12);
            if (!empty($product_id)) {
                $mod = $mod->where('product_id', $product_id);
            }
            if (!empty($product_name)) {
                $mod = $mod->whereLike('product_name', '%' . $product_name . '%');
            }
            if (!empty($account_id)) {
                $mod = $mod->where('owner_account_id', $account_id);
            }
            if (!empty($online_status)) {
                $where[]=['online_status','eq',$online_status];
            }
            $page = input('get.page/d', 1);
            $limit = input('get.limit/d', 10);
            $count1=$mod->count();
            $count = $mod->where($where)->count();
            $lists = $mod->where($where)->page($page, $limit)->order('id desc')->select();
            foreach ($lists as &$val){
                $val['actual_amount']=$val['actual_amount']/100;
                $val['origin_amount']=$val['origin_amount']/100;
                $val['stock_qty']=$val['limit_type']==2?'无限':$val['stock_qty'];
            }
            $data = ['msg' => '', 'code' => 1000, 'data' => $lists, 'count' => $count];
            $data['status_'.$online_status]=$count;
            $other_status=$online_status==1?2:1;
            $data['status_'.$other_status]=$count1-$count;
            return json($data);
        }
        $shop_mod = new ShopModel();
        $shop_list = $shop_mod->where('is_del', 0)->select();
        $this->assign([
            'shop_list' => $shop_list,
        ]);
        return $this->fetch('product/index');
    }

}
