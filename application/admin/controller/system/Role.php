<?php

/**
 * 账户管理 
 * young https://www.iasing.com
 */

namespace app\admin\controller\system;

use app\common\server\admin\AdminServer;

class Role extends AdminServer
{

    public function index()
    {
        //用于API - JSON
        if (input('get.page')) {
            $mod = new \app\common\model\admin\SysAuthGroupModel();
            $title = input('get.title', '');
            if (!empty($title)) {
                $mod = $mod->whereLike('title', $title . '%');
            }
            $page = input('get.page/d', 1);
            $limit = input('get.limit/d', 10);

            $count = $mod->count();
            $lists = $mod->page($page, $limit)->order('id desc')->select();
            $data = ['msg' => '', 'code' => 1000, 'data' => $lists, 'count' => $count];
            return json($data);
        }
        return $this->fetch('role/index');
    }

    /**
     * 编辑角色
     * @return mixed|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function roleEdit()
    {
        $role = new \app\common\model\admin\SysAuthGroupModel();
        if (request()->isAjax()) {
            $param = input('post.');

            if (empty($param['status'])) {
                $param['status'] = 2;
            }
            if (empty($param['id'])) {
                $flag = $role->insertRole($param);
            } else {
                $flag = $role->editRole($param);
            }
            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }
        $id = input('param.id');
        $this->assign([
            'role' => $role->getOneRole($id)
        ]);
        return $this->fetch('role/editRole');
    }

    /**
     * 删除角色
     * @return \think\response\Json
     * @throws \Exception
     */
    public function roleDel()
    {
        $id = input('param.id');
        $role = new \app\common\model\admin\SysAuthGroupModel();
        $flag = $role->delRole($id);
        return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
    }

    /**
     * 用户状态
     * @return \think\response\Json
     */
    public function role_state()
    {
        $id = input('param.id');
        $mod_role = new \app\common\model\admin\SysAuthGroupModel();
        $status = $mod_role->where('id', $id)->value('status'); //判断当前状态情况
        if ($status == 1) {
            $flag = $mod_role->where('id', $id)->setField(['status' => 2]);
            return $this->apiSuccess($flag['data'], lang('close'));
        } else {
            $flag = $mod_role->where('id', $id)->setField(['status' => 1]);
            return $this->apiError($flag['data'], lang('close'));
        }
    }

    /**
     * 分配权限
     * @return \think\response\Json
     */
    public function giveAccess()
    {
        $param = input('param.');
        $node = new \app\common\model\admin\SysAuthRuleModel();

        //获取现在的权限
        if ('get' == $param['type']) {
            $nodeStr = $node->getNodeInfo($param['id']);
            return json(['code' => 1000, 'data' => $nodeStr, 'msg' => 'success']);
        }
        //分配新权限
        if ('give' == $param['type']) {

            $doparam = [
                'id' => $param['id'],
                'rules' => $param['rule']
            ];
            $user = new \app\common\model\admin\SysAuthGroupModel();
            $flag = $user->editAccess($doparam);
            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }
    }

}
