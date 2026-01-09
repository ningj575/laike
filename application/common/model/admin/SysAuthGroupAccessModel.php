<?php

/**
 * 系统后台 - 用户管理
 * young www.iasing.com
 */

namespace app\common\model\admin;

use app\common\model\BaseModel;

class SysAuthGroupAccessModel extends BaseModel
{

    public function __construct($data = [])
    {
        $this->table = 'sys_auth_group_access';
        parent::__construct($data);
    }

}
