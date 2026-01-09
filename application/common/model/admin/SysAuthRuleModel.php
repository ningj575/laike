<?php

/**
 * 系统后台 - 角色权限类
 * young www.iasing.com
 */

namespace app\common\model\admin;

use app\common\model\BaseModel;

class SysAuthRuleModel extends BaseModel
{

    public $ADMIN_INFO;
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'c_time';

    public function __construct($data = [])
    {
        $this->table = 'sys_auth_rule';
        parent::__construct($data);
        if (empty($this->ADMIN_INFO)) {
            $this->ADMIN_INFO = session('admin_info');
        }
    }

    /**
     * 获取节点数据
     * @param $id
     * @return string
     */
    public function getNodeInfo($id)
    {
        $result = $this->where('status = 1')->field('id,title,pid')->select();
        $str = "";
        $role = new SysAuthGroupModel();
        $rule = $role->getRuleById($id);
        if (!empty($rule)) {
            $rule = explode(',', $rule);
        }
        foreach ($result as $key => $vo) {
            $str .= '{ "id": "' . $vo['id'] . '", "pId":"' . $vo['pid'] . '", "name":"' . lang($vo['title']) . '"';
            if (!empty($rule) && in_array($vo['id'], $rule)) {
                $str .= ' ,"checked":1';
            }
            $str .= '},';
        }
        return "[" . substr($str, 0, -1) . "]";
    }

    /**
     * 根据节点数据获取对应的菜单
     * @param string $nodeStr
     * @return array
     */
    public function getMenu($nodeStr = '')
    {
        //超级管理员没有节点数组
        $where = empty($nodeStr) ? 'status = 1' : 'status = 1 and id in(' . $nodeStr . ')';
        $result = $this->where($where)->order('sort')->select();
        $result = $result->toArray();
        $menu = $this->prepareMenu($result);
        return $menu;
    }

    /**
     * 整理菜单树方法
     * @param $param
     * @return array
     */
    function prepareMenu($param)
    {
        $parent = []; //父类
        $child = [];  //子类
        foreach ($param as $key => $val) {
            $val['title']=lang($val['title']);
            if ($val['pid'] == 0) {
                $val['href'] = '#';
                $parent[] = $val;
            } else {
                $val['href'] = url($val['name'], $val['param']); //跳转地址
                $child[] = $val;
            }
        }

        foreach ($parent as $key => $val) {
            foreach ($child as $k => $v) {
                if ($v['pid'] == $val['id']) {
                    $parent[$key]['child'][] = $v;
                }
            }
        }
        unset($child);
        return $parent;
    }

    /**
     * [getAllMenu 获取全部菜单]
     * @author Tx1123
     */
    public function getAllMenu()
    {
        return $this->order('id asc')->select();
    }

    /**
     * 添加菜单
     * @param $param
     * @return array
     * @throws \think\exception\PDOException
     */
    public function insertMenu($param)
    {
        $this->startTrans();
        try {
            $result = $this->save($param);
            $this->commit();
            if (false === $result) {
                return ['code' => 1001, 'data' => '', 'msg' => $this->getError()];
            } else {
                return ['code' => 1000, 'data' => '', 'msg' => langCommon('add_success')];
            }
        } catch (\PDOException $e) {
            $this->rollback();
            return ['code' => 1001, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

    /**
     * 编辑菜单
     * @param $param
     * @return array
     */
    public function editMenu($param)
    {
        try {
            $result = $this->save($param, ['id' => $param['id']]);
            if (false === $result) {
                return ['code' => 1001, 'data' => '', 'msg' => $this->getError()];
            } else {
                return ['code' => 1000, 'data' => '', 'msg' => langCommon('edit_success')];
            }
        } catch (\PDOException $e) {
            return ['code' => 1001, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

    /**
     * 根据菜单id获取一条信息
     * @param $id
     */
    public function getOneMenu($id)
    {
        return $this->where('id', $id)->find();
    }

    /**
     * 删除菜单
     * @param $id
     * @return array
     * @throws \think\exception\PDOException
     */
    public function delMenu($id)
    {
        $this->startTrans();
        try {
            $this->where('id', $id)->delete();
            $this->commit();
            return ['code' => 1000, 'data' => '', 'msg' => langCommon('delete_success')];
        } catch (\PDOException $e) {
            $this->rollback();
            return ['code' => 1001, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

}
