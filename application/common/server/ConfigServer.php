<?php

namespace app\common\server;

use app\common\server\BaseServer;

class ConfigServer extends BaseServer
{
    /*
     *  获取接口授权令牌
     * */
    public function getApiToken(){
        return config("other.api_token");
    }
}