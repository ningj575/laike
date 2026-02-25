<?php

/*
 *  定时分配产品
 *  执行命令 php think orderassign
 */

namespace app\common\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;

class CronOrderAssign extends Command {

    protected function configure() {
        $this->setName('CronOrderAssign')->setDescription('定时分配产品'); //描述
    }

    protected function execute(Input $input, Output $output) {
        $sales_ser = new \app\common\server\laike\SalesServer();
        $res1=$sales_ser->getTimeOutOrderAssign();
        $output->writeln($res1['msg']); //输出       
        $res = $sales_ser->getOrderAssign();
        $output->writeln($res['msg']); //输出       
    }

}
