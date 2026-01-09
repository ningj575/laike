<?php

namespace app\admin\controller;

use app\common\server\BaseServer;
use think\App;
use app\common\model\ZupuModel;

class Test extends BaseServer {

    public function __construct(App $app = null) {
        parent::__construct($app);
    }

    public function test() {
        echo 1;
        exit;
    }
}
