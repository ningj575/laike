<?php

/**
 * 店铺管理 
 */

namespace app\admin\controller\shop;

use app\common\server\admin\AdminServer;
use app\common\model\shop\ShopModel;

class Shop extends AdminServer
{

    public function index()
    {
        //用于API - JSON
        if (input('get.page')) {
            $mod = new ShopModel();
            $name = input('get.name', '');
            $where=[
                ['is_del','eq',0]
            ];
            if (!empty($name)) {
                $mod = $mod->whereLike('name', '%'.$name . '%');
            }
            $page = input('get.page/d', 1);
            $limit = input('get.limit/d', 10);
            $count = $mod->count();
            $lists = $mod->where($where)->page($page, $limit)->order('id desc')->select();
            $data = ['msg' => '', 'code' => 1000, 'data' => $lists, 'count' => $count];
            return json($data);
        }
        return $this->fetch('shop/index');
    }

    /**
     * 编辑
     */
    public function edit()
    {
        $mod = new ShopModel();
        if (request()->isAjax()) {
            $param = input('post.');
            if (empty($param['status'])) {
                $param['status'] = 2;
            }
            if (empty($param['id'])) {               
                $flag = $mod->insert($param);
            } else {
                $flag = $mod->edit($param);
            }
            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }
        $id = input('param.id');
        $this->assign([
            'shop' => $mod->where('id',$id)->find()
        ]);
        return $this->fetch('shop/edit');
    }

    /**
     * 删除
     */
    public function del()
    {
        $id = input('param.id');
        $mod = new ShopModel;
        $flag = $mod->del($id);
        return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
    }

   


}
