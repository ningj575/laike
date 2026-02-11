<?php

/**
 * 系统后台 - 城市
 * 
 */

namespace app\common\model\admin;

use app\common\model\BaseModel;

class SysZoneModel extends BaseModel
{


    public function __construct($data = [])
    {
        $this->table = 'sys_zone';
        parent::__construct($data);      
    }

   

}
