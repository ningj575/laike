<?php

/**
 * 系统后台 - 用户管理
 * young www.iasing.com
 */

namespace app\common\model\admin;

use app\common\model\BaseModel;
class SysAuthGroupModel extends BaseModel
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'c_time';
    protected $updateTime = 'u_time';

    public function __construct($data = [])
    {
        $this->table = 'sys_auth_group';
        parent::__construct($data);
    }
    public function adminUser(){
        return $this->hasMany('SysAdminModel','groupid','id')->order('status asc');
    }

    /**
     * 获取角色信息
     */
    public function getRoleInfo($group_id = 0)
    {
        $result = $this->where('id', $group_id)->find()->toArray();
        $mod_auth_group = new SysAuthRuleModel();
        if (empty($result['rules'])) {
            $res = $mod_auth_group::select();
        } else {
            $res = $mod_auth_group::all($result['rules']);
        }
        //$res = collection($res)->toArray();
        foreach ($res as $key => $vo) {
            if ('#' != $vo['name']) {
                $result['name'][] = $vo['name'];
            }
        }
        return $result;
    }

    /**
     * 插入角色信息
     * @param $param
     * @return array
     */
    public function insertRole($param)
    {
        try {
            $result = $this->save($param);
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
     * 根据id 获取角色信息
     * @param type $id
     * @return type
     */
    public function getOneRole($id)
    {
        return $this->where('id', $id)->find();
    }

    /**
     * 删除角色
     * @param $id
     * @return array
     * @throws \Exception
     */
    public function delRole($id)
    {
        try {
            $this->where('id', $id)->delete();
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
    public function editRole($param)
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

    /**
     * 获取所有的角色信息
     */
    public function getRole()
    {
        return $this->where('status', '=', 1)->select();
    }

    /**
     * 获取角色的权限节点
     * @param $id
     */
    public function getRuleById($id)
    {
        $res = $this->field('rules')->where('id', $id)->find();
        return $res['rules'];
    }

    /**
     * 分配权限
     * @param $param
     * @return array
     */
    public function editAccess($param)
    {
        try {
            $this->save($param, ['id' => $param['id']]);
            return ['code' => 1000, 'data' => '', 'msg' => lang('permission_success')];
        } catch (\PDOException $e) {
            return ['code' => 1001, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

    /**
     * 根据条件获取所有的角色数量
     * @param $where
     * @return int|string
     */
    public function getAllRole($where)
    {
        return $this->where($where)->count();
    }

}
