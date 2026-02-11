<?php

namespace app\task\behavior;

/**
 * 更新销售用户分配数量
 * Class DealerOrder
 * @package app\task\behavior
 */
class SalesOrders {
    /* @var LiveRoomModel $model */


    /**
     * 执行函数
     * @param $model
     * @return bool
     * @throws \think\exception\PDOException
     */
    public function run($model) {
        $model->execute('update sales_user as a INNER JOIN(SELECT sales_user_id,count(id) as orders from `order` GROUP BY sales_user_id) as b on a.admin_id=b.sales_user_id set a.orders=b.orders;');
        return true;
    }

}
