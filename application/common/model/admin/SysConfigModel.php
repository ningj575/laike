<?php

/**
 * 系统后台 - 系统配置表
 * young www.iasing.com
 */

namespace app\common\model\admin;

use app\common\model\BaseModel;

class SysConfigModel extends BaseModel {

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'c_time';
    protected $updateTime = 'u_time';

    public function __construct(array $data = []) {
        $this->table = 'sys_config';   //对应表名
        parent::__construct($data);
    }

    /**
     * 获取配置列表
     * @return array
     */
    public function sysConfig() {
        $where_arr = array('status' => 1);
        $data = $this->where($where_arr)->field('type,name,value')->select();
        $config = array();
        if ($data) {
            foreach ($data as $value) {
                $config[$value['name']] = self::parse($value['type'], $value['value']);
            }
        }
        return $config;
    }

    /**
     * 根据配置类型解析配置
     * @param $type
     * @param $value
     * @return array|array[]|false|string[]
     */
    private static function parse($type, $value) {
        switch ($type) {
            case 3: //解析数组
                $array = preg_split('/[,;\r\n]+/', trim($value, ",;\r\n"));
                if (strpos($value, ':')) {
                    $value = array();
                    foreach ($array as $val) {
                        list($k, $v) = explode(':', $val);
                        $value[$k] = $v;
                    }
                } else {
                    $value = $array;
                }
                break;
        }
        return $value;
    }

}
