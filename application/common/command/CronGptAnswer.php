<?php

/*
 *  定时获取GPT答案(30秒一次)
 *  执行命令 php think getgptanswer
 */
namespace app\common\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;

class CronGptAnswer extends Command {

    protected function configure() {
        $this->setName('CronGptAnswer')->setDescription('定时获取GPT答案(30秒一次)'); //描述
    }

    protected function execute(Input $input, Output $output) {
        $ser = new \app\common\server\cli\CliServer();
        $res = $ser->cronGetAiAnswer();
        if(empty($res)){
            $output->writeln('执行完成'); //输出
        }else{
            $output->writeln(trim($res));
        }
    }

}
