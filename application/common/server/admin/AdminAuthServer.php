<?php

/**
 * 系统后台 - 用户权限类
 * young www.iasing.com
 */

namespace app\common\server\admin;

use libs\model\admin\SysAdminModel;

class AdminAuthServer
{

    //默认配置
    protected $_config = array(
        'auth_on' => true, // 认证开关
        'auth_type' => 1, // 认证方式，1为实时认证；2为登录认证。
        'auth_group' => 'sys_auth_group', // 用户组数据表名
        'auth_group_access' => 'sys_auth_group_access', // 用户-用户组关系表
        'auth_rule' => 'sys_auth_rule', // 权限规则表
        'auth_user' => 'sys_admin'             // 用户信息表
    );

    /**
     * 检查权限
     * @param $name
     * @param $uid
     * @param int $type
     * @param string $mode
     * @param string $relation
     * @return boolean           通过验证返回true;失败返回false
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function adminCheck($name, $uid, $type = 1, $mode = 'url', $relation = 'or')
    {
        if (!$this->_config['auth_on'])
            return true;

        $authList = $this->getAuthList($uid, $type); //获取用户需要验证的所有有效规则列表
//        dump($authList);
        if (is_string($name)) {
            $name = strtolower($name);
            if (strpos($name, ',') !== false) {
                $name = explode(',', $name);
            } else {
                $name = array($name);
            }
        }

        $list = array(); //保存验证通过的规则名
        if ($mode == 'url') {
            $REQUEST = unserialize(strtolower(serialize($_REQUEST)));
        }
        foreach ($authList as $auth) {
            $query = preg_replace('/^.+\?/U', '', $auth);
            if ($mode == 'url' && $query != $auth) {
                parse_str($query, $param); //解析规则中的param
                $intersect = array_intersect_assoc($REQUEST, $param);
                $auth = preg_replace('/\?.*$/U', '', $auth);
                if (in_array($auth, $name) && $intersect == $param) {  //如果节点相符且url参数满足
                    $list[] = $auth;
                }
            } else if (in_array($auth, $name)) {
                $list[] = $auth;
            }
        }
        if ($relation == 'or' and ! empty($list)) {
            return true;
        }
        $diff = array_diff($name, $list);
        if ($relation == 'and' and empty($diff)) {
            return true;
        }

        return false;
    }

    /**
     * 根据用户id获取用户组,返回值为数组
     * @param $uid 用户id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getGroups($uid)
    {
        static $groups = array();
        if (isset($groups[$uid])) {
            return $groups[$uid];
        }
        $mod_auth_access = new \app\common\model\admin\SysAuthGroupAccessModel();
        $user_groups = $mod_auth_access
                        ->alias('a')
                        ->join("sys_auth_group g", "g.id=a.group_id")
                        ->where("a.uid='$uid' and g.status='1'")
                        ->field('uid,group_id,title,rules')->select();
        $groups[$uid] = $user_groups ? $user_groups : array();
        return $groups[$uid];
    }

    /**
     * 获得权限列表
     * @param $uid
     * @param $type
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function getAuthList($uid, $type)
    {

        static $_authList = array(); //保存用户验证通过的权限列表
        $t = implode(',', (array) $type);

        if (isset($_authList[$uid . $t])) {
            return $_authList[$uid . $t];
        }

        if ($this->_config['auth_type'] == 2 && \think\Session::get('_auth_list_' . $uid . $t)) {
            return \think\Session::get('_auth_list_' . $uid . $t);
        }


        //读取用户所属用户组
        $groups = $this->getGroups($uid);

        $ids = array(); //保存用户所属用户组设置的所有权限规则id
        foreach ($groups as $g) {
            $ids = array_merge($ids, explode(',', trim($g['rules'], ',')));
        }
        $ids = array_unique($ids);
        if (empty($ids)) {
            $_authList[$uid . $t] = array();
            return array();
        }

        $map = [
                ['id', 'in', $ids],
                ['type', 'eq', $type],
                ['status', 'eq', 1]
        ];
        //读取用户组所有权限规则
        $mod_auth_rule = new \app\common\model\admin\SysAuthRuleModel();
        $rules = $mod_auth_rule->where($map)->field('condition,name')->select();

        //循环规则，判断结果。
        $authList = array();
        foreach ($rules as $rule) {
            if (!empty($rule['condition'])) { //根据condition进行验证
                $command = preg_replace('/\{(\w*?)\}/', '$user[\'\\1\']', $rule['condition']);
                $condition = '';
                @(eval('$condition=(' . $command . ');'));
                if ($condition) {
                    $authList[] = strtolower(str_replace("_", "", $rule['name']));
                }
            } else {
                //只要存在就记录
                $authList[] = strtolower(str_replace("_", "", $rule['name']));
            }
        }
        $_authList[$uid . $t] = $authList;
        if ($this->_config['auth_type'] == 2) {
            //规则列表结果保存到session
            \think\Session::set('_auth_list_' . $uid . $t, $authList);
        }
        return array_unique($authList);
    }

    /**
     * 获得用户资料,根据自己的情况读取数据库
     * @param $uid
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function getUserInfo($uid)
    {
        static $userinfo = array();
        if (!isset($userinfo[$uid])) {
            $modAdmin = new SysAdminModel();
            $userinfo[$uid] = $modAdmin->where('id', $uid)->find();
        }
        return $userinfo[$uid];
    }

}
