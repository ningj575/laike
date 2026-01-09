<?php

/**
 * 系统后台 - 后台操作日志
 * young www.iasing.com
 */

namespace app\common\model\admin;

use app\common\model\BaseModel;

class SysLogModel extends BaseModel
{

    //开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    //定义时间戳字段名
    protected $createTime = 'c_time';
    protected $updateTime = false;
    protected $append = ['status_text', 'log_type_text'];

    public function __construct($data = [])
    {
        $this->table = 'log_sys_admin';
        parent::__construct($data);
    }

    /**
     * 字段 Status 属性
     * @param $val
     * @param $data
     * @return mixed|string
     */
    public function getStatusTextAttr($val, $data)
    {
        if (empty($data['state']))
            return '';
        $data_arr = [
            1 => '操作成功',
            2 => '操作失败'
        ];
        return $data_arr[$data['state']];
    }

    /**
     * 字段 State 属性
     * @param $val
     * @param $data
     * @return mixed|string
     */
    public function getLogTypeTextAttr($val, $data)
    {
        if (empty($data['log_type']))
            return '';
        $data_arr = [
            1 => '后台登录',
            2 => '系统管理'
        ];
        return $data_arr[$data['log_type']];
    }

    /**
     * 删除日志
     * @param $log_id
     * @return array
     * @throws \Exception
     */
    public function delLog($log_id)
    {
        try {
            $this->where('log_id', $log_id)->delete();
            return ['code' => 1000, 'data' => '', 'msg' => '删除日志成功'];
        } catch (\PDOException $e) {
            return ['code' => 1001, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

}
