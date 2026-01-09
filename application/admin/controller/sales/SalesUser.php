<?php

/**
 * 智能派单
 */

namespace app\admin\controller\sales;

use app\common\server\admin\AdminServer;
use app\common\model\sales\SalesUserModel;

class SalesUser extends AdminServer {

    public function index() {       
       if (input('get.page')) {
           $mod=new SalesUserModel();           
           $where[]=['is_del','eq',0];
           $name = input('name');
           if(!empty($name)){
               $where[]=['name','like','%'.$name.'%'];
           }
            $page = input('get.page/d', 1);
            $limit = input('get.limit/d', 10);
            $count = $mod->count();
           $res=$mod->where($where)->page($page, $limit)->order('id desc')->select();
           return json(['code'=>1000,'data'=>$res,'count'=>$count]);
       }
    }
    
     /**
     * 添加
     */
    public function add()
    {
        $mod = new SalesUserModel();
        if (request()->isAjax()) {
            $param = input('post.');
            if (empty($param['status'])) {
                $param['status'] = 0;
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
            'sales' => $mod->where('id',$id)->find()
        ]);
        return $this->fetch('sales/edit');
    }

   
}
