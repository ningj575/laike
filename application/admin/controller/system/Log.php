<?php

/**
 * 操作日志
 * young https://www.iasing.com
 */

namespace app\admin\controller\system;

//use Zhuzhichao\IpLocationZh\Ip;  
//use extend\ipLocation\IpLocation;
use app\common\server\admin\AdminServer;

class Log extends AdminServer
{

    /**
     * 操作日志
     * @return mixed|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function operate_log()
    {
        //用于API - JSON
        if (input('get.page')) {
            $mod = new \app\common\model\admin\SysLogModel();
//            $ext_Ip = new IpLocation(); // 实例化类 参数表示IP地址库文件
            //搜索条件
            $admin_id = input('get.admin_id', 0);
            if (!empty($admin_id)) {
                $mod = $mod->where('admin_id', $admin_id);
            }
            $ip = input('get.ip', '');
            if (!empty($ip)) {
                $mod = $mod->whereLike('ip', $ip . '%');
            }

            $page = input('get.page/d', 1);
            $limit = input('get.limit/d', 10);

            $count = $mod->count();
            $lists = $mod->page($page, $limit)->order('log_id desc')->select();
            //echo $mod->getLastSql();
            foreach ($lists as $k => $v) {
                if ($v['ip'] == '127.0.0.1') {
                    $lists[$k]['ipaddr'] = '本机地址';
                    continue;
                }
                if ($v['ip'] == '10.0.0.1') {
                    $lists[$k]['ipaddr'] = '本地局域网';
                    continue;
                }
                if (!empty($ip_arr) && in_array($v['ip'], $ip_arr)) {
                    $lists[$k]['ipaddr'] = $ip_arr[$v['ip']];
                } else {
                    $ip_addr = \Zhuzhichao\IpLocationZh\Ip::find($v['ip']);


//                    $ip_addr = $ext_Ip->getLocation($v['ip'], true);
                    $country = $ip_addr[0] ?? '';
                    $province = $ip_addr[1] ?? '';
                    $city = $ip_addr[2] ?? '';

                    $lists[$k]['ipaddr'] = $country . $province . $city;
//                    $ip_arr[$v['ip']] = $ip_addr['country'];
                }
            }
            $data = ['msg' => '数据获取成功', 'code' => 1000, 'data' => $lists, 'count' => $count];
            return json($data);
        }
        $mod_admin = new \app\common\model\admin\SysAdminModel();
        $arr = $mod_admin->column("id,admin_name"); //获取用户列表
        $this->assign("adminArr", $arr);
        return $this->fetch('log/indexSys');
    }

    /**
     * 删除日志
     * @return \think\response\Json
     * @throws \Exception
     */
    public function delLog()
    {
        $id = input('param.id');
        $log = new \app\common\model\admin\SysLogModel();
        $flag = $log->delLog($id);
        return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
    }

}
