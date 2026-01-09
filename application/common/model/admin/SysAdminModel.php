<?php

/**
 * 系统后台 - 用户管理
 * young www.iasing.com
 */

namespace app\common\model\admin;

use app\common\model\BaseModel;

class SysAdminModel extends BaseModel
{

    private $ADMIN_INFO;
    protected $append = ['status_text', 'portrait_text'];
    protected $dateFormat = 'Y-m-d';
    protected $autoWriteTimestamp = ['lock_time'];

    public function __construct($data = [])
    {
        $this->table = 'sys_admin';
        parent::__construct($data);
        if (empty($this->ADMIN_INFO)) {
            $this->ADMIN_INFO = session('admin_info');
        }
    }

    /**
     * 字段status属性
     * @param $val
     * @param $data
     * @return mixed|string
     */
    public function getStatusTextAttr($val, $data)
    {
        if (empty($data['status']))
            return '';
        $resdata = $this->status_list();
        return $resdata[$data['status']];
    }

    public function status_list(){
        return [
            0 => '未认证',
            1 => '审批通过',
            2 => '锁定',
            3 => '审批失败',
            4 => '禁用',
        ];
    }

    /**
     * 字段status属性
     * @param $val
     * @param $data
     * @return mixed|string
     */
    public function getPortraitTextAttr($val, $data)
    {
        if (empty($data['portrait']))
            return '';
        if (strpos($data['portrait'], 'http') !== false) {
            //包含Http
            return $data['portrait'];
        } else {
            //未包含Http
            $rootUrl = config('public.sites.static');
            return $rootUrl . DS . $data['portrait'];
        }
    }

    /**
     * 获取单条管理员信息
     * @param $where
     * @return array
     * @throws \think\Exception\DbException
     */
    public function getFind($where = [])
    {
        $info = self::get($where);
        return $info->toArray();
    }

    /**
     * 根据搜索条件获取用户列表信息
     */
    public function getUsersByWhere($where, $Nowpage, $limits)
    {
        return $this->field('sys_admin.*,title')
                        ->join('sys_auth_group', 'sys_admin.groupid = sys_auth_group.id')
                        ->where($where)
                        ->page($Nowpage, $limits)
                        ->order('id desc')
                        ->select();
    }

    /**
     * 根据搜索条件获取所有的用户数量
     * @param $where
     * @return int|string
     */
    public function getAllUsers($where)
    {
        return $this->where($where)->count();
    }

    /**
     * 插入管理员信息
     * @param array $param
     * @return array
     * @throws \think\Exception
     */
    public function insertUser($param = [])
    {
        try {
            $result = $this->allowField(true)->save($param);
            if (false === $result) {
                return ['code' => 1001, 'data' => '', 'msg' => $this->getError()];
            } else {                
                adminlog($this->ADMIN_INFO['uid'], 2, '用户【' . $param['admin_name'] . '】添加成功', 1);
                return ['code' => 1000, 'data' => '', 'msg' => langCommon('add_success')];
            }
        } catch (\PDOException $e) {
            return ['code' => 1001, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

    /**
     * 编辑管理员信息
     * @param array $param
     * @return array
     */
    public function editUser($param = [])
    {
        try {
            $result = $this->allowField(true)->save($param, ['id' => $param['id']]);
            if (false === $result) {
                return ['code' => 1001, 'data' => '', 'msg' => $this->getError()];
            } else {
                return ['code' => 1000, 'data' => '', 'msg' =>  langCommon('edit_success')];
            }
        } catch (\PDOException $e) {
            return ['code' => 1001, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

    /**
     * 根据id 获取管理员信息
     * @param type $id
     * @return type
     */
    public function getOneUser($id)
    {
        return $this->where('id', $id)->find();
    }

    /**
     * 删除管理员
     * @param $id
     * @return array
     * @throws \Exception
     */
    public function delUser($id)
    {
        try {
            $this->where('id', $id)->delete();
            $mod_auth_access = new SysAuthGroupAccessModel();
            $mod_auth_access->where('uid', $id)->delete();
            return ['code' => 1000, 'data' => '', 'msg' =>  langCommon('delete_success')];
        } catch (\PDOException $e) {
            return ['code' => 1001, 'data' => '', 'msg' => $e->getMessage()];
        }
    }



}
