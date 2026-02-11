<?php

/**
 * 智能派单
 */

namespace app\admin\controller\sales;

use app\common\server\admin\AdminServer;
use app\common\model\sales\SalesUserModel;
use app\common\model\admin\SysAdminModel;

class SalesUser extends AdminServer {

    public function index() {
        if (input('get.page')) {
            $mod = new SalesUserModel();
            $name = input('name');
            $where=[];
            if (!empty($name)) {
                $where[] = ['sys_admin.admin_name', 'like', '%' . $name . '%'];
            }
            $page = input('get.page/d', 1);
            $limit = input('get.limit/d', 10);
            $count = $mod->join('sys_admin','sales_user.admin_id = sys_admin.id')->where($where)->count();
            $res = $mod->join('sys_admin','sales_user.admin_id = sys_admin.id')->where($where)->page($page, $limit)->order('sys_admin.id desc')->select();
            $redis=new \think\cache\driver\Redis();
            $online_count=0;
            foreach ($res as &$val){
                $is_online=$redis->get("admin:active:".$val['admin_id']); 
                $is_online&&$online_count++;
                $val['online']=$is_online;
            }
            return json(['code' => 1000, 'data' => $res, 'count' => $count,'online_count'=>$online_count]);
        }
    }

    /**
     * 添加
     */
    public function add() {
        $sales_mod = new SalesUserModel();
        if (request()->isAjax()) {
            $param = input('post.');
            if (empty($param['password'])) {
                unset($param['password']);
            } else {
                $res = newPwd(md5(md5($param['password']) . config('other.auth_key')));
                $param['password'] = $res['password'];
                $param['pwd_key'] = $res['pwd_key'];
            }
            $sales_ser = new \app\common\server\laike\SalesServer();
            $sales = $sales_ser->getSales();
            $param['groupid'] = $sales['id'];
            $param['status']=1;
            $mod_admin = new SysAdminModel();
            $mod_admin->insertUser($param);
            $uid = (int) $mod_admin['id'];
            $accdata = array(
                'uid' => $uid,
                'group_id' => $sales['id'],
            );
            $mod_auth_access = new \app\common\model\admin\SysAuthGroupAccessModel();
            $mod_auth_access->insert($accdata);
            $sales_param = [
                'admin_id' => $uid,
            ];
            $sales_mod->insert($sales_param);
            return json(['code' => 1000, 'data' => [], 'msg' => '添加成功']);
        }
        $id = input('param.id');
        $this->assign([
            'sales' => $sales_mod->where('id', $id)->find()
        ]);
        return $this->fetch('sales/edit');
    }

}
