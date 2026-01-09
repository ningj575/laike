<?php

/**
  客服server
 */

namespace app\common\server\laike;

use app\common\server\BaseServer;
use app\common\model\admin\SysAuthGroupModel;

class KefuServer extends BaseServer {
    /*
     * 获取客服账号
     */

    public function getKefu() {
        $mod = new SysAuthGroupModel();
        $kefu_list = $mod->with(['adminUser'])->where('title', 'kefu')->find();
        return $kefu_list;
    }

    /*
     * 订单随机分配客服客服
     */

    public function orderAssign($order) {
        $list = $this->getKefu();
        $kefu_list = [];
        foreach ($list['admin_user'] as $vv) {
            if ($vv['status'] == 1) {
                $kefu_list[] = $vv;
            }
        }
        foreach ($order as &$vv) {
            $randomKey = array_rand($kefu_list);
            $randomElement = $kefu_list[$randomKey];
            $vv['kefu_id'] = $randomElement['id'];
        }
        return $order;
    }

}
