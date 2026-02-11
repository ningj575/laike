<?php

/**
 * 标签管理 
 */

namespace app\admin\controller\product;

use app\common\server\admin\AdminServer;
use app\common\model\order\TagsModel;

class Tags extends AdminServer
{

    public function index()
    {
        //用于API - JSON
        if (input('get.page')) {
            $mod = new TagsModel();
            $name = input('get.name', '');
            $type = input('get.type', 0);
            $where=[
                ['is_del','eq',0]
            ];
            if (!empty($name)) {
                $mod = $mod->whereLike('name', '%'.$name . '%');
            }
            if (!empty($type)) {
                $mod = $mod->where('type', $type);
            }
            $page = input('get.page/d', 1);
            $limit = input('get.limit/d', 10);
            $count = $mod->count();
            $lists = $mod->where($where)->page($page, $limit)->order('id desc')->select();
            $data = ['msg' => '', 'code' => 1000, 'data' => $lists, 'count' => $count];
            return json($data);
        }
        return $this->fetch('product/tags/index');
    }

    /**
     * 编辑
     */
    public function edit()
    {
        $mod = new TagsModel();
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
            'tags' => $mod->where('id',$id)->find()
        ]);
        return $this->fetch('product/tags/edit');
    }

    /**
     * 删除
     */
    public function del()
    {
        $id = input('param.id');
        $mod = new TagsModel;
        $flag = $mod->del($id);
        return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
    }

   


}
