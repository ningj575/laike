<?php

/**
 * 菜单操作 
 * young https://www.iasing.com
 */

namespace app\admin\controller\system;

use app\common\server\admin\AdminServer;

class Menu extends AdminServer
{

    /**
     * 菜单列表
     * @return mixed
     */
    public function index()
    {       
        //用于API - JSON
        if (input('get.page')) {
            $mod = new \app\common\model\admin\SysAuthRuleModel();
            $admin_rule = $mod->getAllMenu()->toArray();
            $arr = $this->rule($admin_rule);
            $data = ['msg' => '', 'code' => 1000, 'data' => $arr, 'count' => 1];
            return json($data);
        }
        return $this->fetch('menu/index');
    }

    /**
     * 获取菜单
     * @param $cate
     * @param string $lefthtml
     * @param int $pid
     * @param int $lvl
     * @param int $leftpin
     * @return array
     */
    public function rule($cate, $lefthtml = '— — ', $pid = 0, $lvl = 0, $leftpin = 0)
    {
        $arr = array();
        foreach ($cate as $val) {
            if ($val['pid'] == $pid) {
                $val['lvl'] = $lvl + 1;
                $val['leftpin'] = $leftpin + 0; //左边距
                $val['lefthtml'] = str_repeat($lefthtml, $lvl);
                $arr[] = $val;
                $arr = array_merge($arr, $this->rule($cate, $lefthtml, $val['id'], $lvl + 1, $leftpin + 20));
            }
        }
        return $arr;
    }

    /**
     * 添加菜单
     * @return mixed|\think\response\Json
     * @throws \think\exception\PDOException
     */
    public function addRule()
    {
        if (request()->isAjax()) {
            $param = input('post.');
            $menu = new \app\common\model\admin\SysAuthRuleModel();
            $flag = $menu->insertMenu($param);
            if ($flag['code'] == 1000) {
                return $this->apiSuccess($flag['data'], $flag['msg']);
            }
            return $this->apiError($flag['msg'], $flag['data']);
        }
        return $this->fetch('menu/index');
    }

    /**
     * 编辑菜单
     * @return mixed|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function editMenu()
    {
        $menu = new \app\common\model\admin\SysAuthRuleModel();
        if (request()->isPost()) {
            $param = input('post.');
            if (empty($param['id'])) {
                return $this->addRule();
            }
             if (empty($param['status'])) {
                $param['status'] = 2;
            }
            $flag = $menu->editMenu($param);
            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }
        $id = input('param.id');
        $pid = $menu->getOneMenu($id);
        $pmenu = $menu->getOneMenu($pid['pid']);
        $admin_rule = $menu->getAllMenu();
        $arr = $this->rule($admin_rule);
        $this->assign('admin_rule', $arr);
        $this->assign('pmenu', $pmenu);
        $this->assign('menu', $menu->getOneMenu($id));
        return $this->fetch('menu/editMenu');
    }

    /**
     * 删除角色
     * @return \think\response\Json
     * @throws \think\exception\PDOException
     */
    public function delMenu()
    {
        $id = input('param.id');
        $menu = new \app\common\model\admin\SysAuthRuleModel();
        $flag = $menu->delMenu($id);
        return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
    }

    /**
     * 菜单排序
     * @return \think\response\Json
     */
    public function menuOrder()
    {
        if (request()->isAjax()) {
            $param = input('post.');
            $auth_rule = new \app\common\model\admin\SysAuthRuleModel();
            foreach ($param as $id => $sort) {
                $auth_rule->where(array('id' => $id))->setField('sort', $sort);
            }
            return $this->apiSuccess(array(), '排序更新成功');
        }
    }

    /**
     * 菜单状态
     * @return \think\response\Json
     */
    public function menuState()
    {
        $id = input('param.id');
        $menu = new \app\common\model\admin\SysAuthRuleModel();
        $status = $menu->where('id', $id)->value('status');    //判断当前状态
        if ($status == 1) {
            $flag = $menu->where('id', $id)->setField(['status' => 0]);
            return $this->apiSuccess($flag['data'], lang('close'));
        } else {
            $flag = $menu->where('id', $id)->setField(['status' => 1]);
            return $this->apiError(lang('open'));
        }
    }

}
