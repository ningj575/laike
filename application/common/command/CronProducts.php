<?php

/*
 *  定时更新产品
 *  执行命令 php think products
 */

namespace app\common\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;

class CronProducts extends Command {

    protected function configure() {
        $this->setName('CronProducts')->setDescription('定时更新产品'); //描述
    }

    protected function execute(Input $input, Output $output) {
        $product_ser = new \app\common\server\laike\ProductServer();
        $res = $product_ser->productDo();
        $output->writeln($res['msg']); //输出       
    }

}
