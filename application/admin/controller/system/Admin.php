<?php

/**
 * 账户管理 
 * young https://www.iasing.com
 */

namespace app\admin\controller\system;

use app\common\server\admin\AdminServer;

class Admin extends AdminServer {

    private $UserSession;

    /**
     * 账户列表
     * @return type
     */
    public function index() {
        //用于API - JSON
        if (input('get.page')) {
            $page = input('get.page/d', 1);
            $limit = input('get.limit/d', 10);

            $modAdmin = new \app\common\model\admin\SysAdminModel();
            $where = [];
            $admin_name = input('get.admin_name');
            $groupid = input('get.groupid');

            if (!empty($admin_name)) {
                $modAdmin = $modAdmin->whereLike('admin_name', $admin_name . '%');
            }
            if (!empty($groupid)) {
                $where['groupid'] = $groupid;
            }

            $lists = $modAdmin->field('sys_admin.*,title')->join('sys_auth_group', 'sys_admin.groupid = sys_auth_group.id')->where($where)->page($page, $limit)->order('sys_admin.id desc')->select();
            //列表时间转化
            foreach ($lists as $key => $val) {
                if ($val->last_login_time) $val->last_login_time = date('Y-m-d H:i:s', $val->last_login_time);
                $lists[$key] = $val;
            }
            $count = $modAdmin->where($where)->count(); //计算总页面
            $data = ['msg' => '', 'code' => 1000, 'data' => $lists, 'count' => $count];
            return json($data);
        }
        $role = new \app\common\model\admin\SysAuthGroupModel();
        $this->assign('role', $role->getRole());
        return $this->fetch('admin/index');
    }

    /**
     * 添加帐户
     * @return mixed|\think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function userAdd() {
        if (request()->isAjax()) {
            
        }
    }

    /**
     * 帐户编辑
     * @return mixed|\think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function userEdit() {
        $mod_admin = new \app\common\model\admin\SysAdminModel();
        if (request()->isAjax()) {
            $param = input('post.');
            if (empty($param['password'])) {
                unset($param['password']);
            } else {
                $res = newPwd(md5(md5($param['password']) . config('other.auth_key')));
                $param['password'] = $res['password'];
                $param['pwd_key'] = $res['pwd_key'];
            }

            if (!empty($param['status'])) {
                $param['status'] = 1;
            } else {
                $param['status'] = 2;
            }

            if (empty($param['id'])) {//添加
                $flag = $mod_admin->insertUser($param);
                $uid = (int) $mod_admin['id'];
                if (empty($uid)) return json(['code' => 1001, 'data' => [], 'msg' => '添加失败']);
                $uid_key = md5(md5($uid . date("Y-m-d")));
                $mod_admin->where("id", $uid)->update([
                    'uid_key' => $uid_key
                ]);
                $accdata = array(
                    'uid' => $uid,
                    'group_id' => $param['groupid'],
                );
                $mod_auth_access = new \app\common\model\admin\SysAuthGroupAccessModel();

                $mod_auth_access->insert($accdata);
                return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
            } else {//更新
                $flag = $mod_admin->editUser($param);
                $mod_auth_access = new \app\common\model\admin\SysAuthGroupAccessModel();
                $auth_group_info = $mod_auth_access->get(['uid' => $mod_admin['id']]);
                if (!empty($auth_group_info)) {
                    $mod_auth_access->where('uid', $mod_admin['id'])->update(['group_id' => $param['groupid']]);
                } else {
                    $accdata = array(
                        'uid' => (int) $mod_admin['id'],
                        'group_id' => $param['groupid'],
                    );
                    $mod_auth_access->insert($accdata);
                }
                return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
            }
        }

        $id = input('param.id');
        $role = new \app\common\model\admin\SysAuthGroupModel();
        $this->assign([
            'user' => $mod_admin->getOneUser($id),
            'role' => $role->getRole(),
            'admin_id' => $id,
        ]);
        return $this->fetch('admin/editUser');
    }

    /**
     * 删除帐户
     * @return \think\response\Json
     * @throws \Exception
     */
    public function userDel() {
        $id = input('id');
        $idStr = input('get.idstr');

        $mod_admin = new \app\common\model\admin\SysAdminModel();
        if (!empty($idStr) && empty($id)) {
            $arrId = explode(',', $idStr);
            $arrId = array_filter($arrId);
            $key = array_search(1, $arrId);
            if ($key !== false) {
                array_splice($arrId, $key, 1);
            }
            foreach ($arrId as $val) {
                $flag = $mod_admin->delUser($val);
            }
        } else {
            $flag = $mod_admin->delUser($id);
        }

        return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
    }

    /**
     * 帐户状态
     * @return \think\response\Json
     */
    public function userState() {
        $id = input('param.id');
        $mod_admin = new \libs\model\admin\SysAdminModel();
        $status = $mod_admin->where('id', $id)->value('status'); //判断当前状态情况
        if ($status == 1) {
            $flag = $mod_admin->where('id', $id)->setField(['status' => 0]);
            return json(['code' => 1000, 'data' => $flag['data'], 'msg' => lang('close')]);
        } else {
            $flag = $mod_admin->where('id', $id)->setField(['status' => 1]);
            return json(['code' => 1001, 'data' => $flag['data'], 'msg' => lang('open')]);
        }
    }

    /**
     * 密码修改
     * @return mixed|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function admininfo() {
        $mod_admin = new \libs\model\admin\SysAdminModel();
        if (request()->isAjax()) {

            $param = input('post.');
            // dump($param);
            if (empty($param['password'])) {
                unset($param['password']);
            } else {
                $param['password'] = md5(md5($param['password']) . config('other.auth_key'));
            }
            $flag = $mod_admin->editUser($param);

            json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
            return $this->apiSuccess([], lang('password_success'));
        }

        $this->UserSession = session('admin_info');
        $id = $this->UserSession['uid'];
        $role = new \app\common\model\admin\SysAuthGroupModel();
        $userinf = $mod_admin->getOneUser($id);
        $userGroup = $role->getOneRole($userinf['groupid']);
        $this->assign([
            'user' => $mod_admin->getOneUser($id),
            'role' => $userGroup['title']
        ]);

        return $this->fetch('admin/admininfo');
    }

    /**
     * 基本资料修改
     */
    public function basic() {

        $admin_id = session('admin_info')['admin_id'];
        if (empty($admin_id)) {
            return json(returnPubData(lang('no_login')));
        }
        $mod_admin = new \app\common\model\admin\SysAdminModel();
        if (request()->isAjax()) {

            $where = [
                    ['id', 'eq', $admin_id]
            ];
            $admin_info = $mod_admin->where($where)->find();
            if (empty($admin_info)) {
                return $this->apiError(lang('no_admin'));
            }
            $param['id'] = $admin_id;
            $param['real_name'] = input('post.real_name');
            $param['sex'] = input('post.sex');
            $param['portrait'] = input('post.portrait');

            $ret = $mod_admin->editUser($param);
            return json($ret);
        }

        $role = new \app\common\model\admin\SysAuthGroupModel();
        $this->assign([
            'user' => $mod_admin->getOneUser($admin_id),
            'role' => $role->select(),
            'admin_id' => $admin_id
        ]);
        return $this->fetch('admin/basic');
    }

    /**
     * 修改密码
     */
    public function changePwd() {

        if (request()->isAjax()) {
            $mod_admin = new \app\common\model\admin\SysAdminModel();
            $admin_id = session('admin_info')['admin_id'];
            if (empty($admin_id)) {
                return json(returnPubData(lang('no_login')));
            }
            $where = [
                    ['id', 'eq', $admin_id]
            ];
            $admin_info = $mod_admin->where($where)->find();
            if (empty($admin_info)) {
                return $this->apiError(lang('no_admin'));
            }
            $old_password = input('post.oldpassword');
            $password = input('post.password');
            if (retPwd($old_password, config('other.auth_key'), $admin_info['pwd_key']) != $admin_info['password']) {
                adminlog($admin_info['id'], 1, '用户【' . $admin_info['admin_name'] . '】修改密码失败：原密码错误', 2);
                return $this->apiError(lang('fail_password'));
            }

            if (1 != $admin_info['status']) {
                adminlog($admin_info['id'], 1, '用户【' . $admin_info['admin_name'] . '】修改密码失败：该账号被禁用', 2);
                return $this->apiError(lang('account_disable'));
            }

            $param = newPwd(md5(md5($password) . config('other.auth_key')));
            $param['id'] = $admin_id;
            $save_data['password'] = $param['password'];
            $save_data['pwd_key'] = $param['pwd_key'];
            $ret = $mod_admin->editUser($param);
            return json($ret);
        }
        return $this->fetch('admin/changePwd');
    }

    public function tree() {
        $zupu_mod = new \app\common\model\ZupuModel();
        $list = $zupu_mod->select()->toArray();
        $list1 = $this->child($list);
        $this->assign('list', json_encode($list1, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $this->fetch('admin/tree');
    }

    public function child($list, $pid = 0) {
        $arr = array();
        foreach ($list as $val) {
            $arr_lins = [];
            if ($val['fatherId'] == $pid) {
                $arr_lins['id'] = $val['id'];
                $arr_lins['sex'] = $val['sex'];
                $arr_lins['title'] = $val['name'].($val['spouse']?'</br><span style="font-size:15px;">配偶:'.$val['spouse'].'</span>':'');
                $arr_lins['spread'] = true;
                $res = $this->child($list, $val['id']);
                if ($res) {
                    $arr_lins['children'] = $res;
                }
                $arr[] = $arr_lins;
            }
        }
        return $arr;
    }

}
