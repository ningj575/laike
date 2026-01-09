<?php

/**
 * model 基础类 
 * young https://www.iasing.com
 */

namespace app\common\model;

use think\Model;

class BaseModel extends Model {

    //开启自动写入时间戳字段
    protected $autoWriteTimestamp = false;
    protected $auto = [];
    protected $insert = [];
    protected $update = [];

    public function __construct($data = []) {
        parent::__construct($data);
    }

    /**
     * 初始字段 - 添加页面使用
     * @return array
     */
    public function getInitFile() {
        //获取表字段
        $file_arr = $this->getTableFields($this->table);
        $result = [];
        foreach ($file_arr as $val) {
            $result[$val] = '';
        }
        return $result;
    }

    /**
     * 获取当前表信息 
     * return 
     */
    public function showTable() {
        $showData = $this->query('show table status ');
        $ret = [];
        foreach ($showData as $val) {
            if ($val['Name'] == $this->table) {
                $ret = $val;
                break;
            }
        }
        return $ret;
    }

    /**
     * 获取表信息 单个字段
     * @param type $field 需要获取的字段
     * @return type //类型
     */
    public function getTableFile($field) {
        $database = config('database.database');
        $sql = "  SELECT $field
        FROM information_schema.TABLES
        WHERE Table_Schema = '$database'
        AND table_name = '$this->table'";
        return $this->query($sql)[0][$field];
    }

    /**
     * 获取列表 带分页
     * @param type $where 条件
     * @param type $page 分页
     * @param type $page_num 分页条数
     * @param type $order 排序
     * @param type $fields 需要的字段
     * @param type $group 分组
     * @return type
     */
    public function getList($where = [], $page = 1, $page_num = 10, $order = '', $fields = '*', $group = '') {        

        $res = $this->where($where)->page($page, $page_num)->group($group)->order($order)->field($fields)->select()->toArray();

        return $res;
    }

    /**
     * 获取单笔
     * @param $where
     * @param string $field
     * @param string $order
     * @return array|false|\PDOStatement|string|Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getOne($where = [], $field = '*', $order = '') {
        if (!empty($order)) {
            $this->order($order);
        }
        $info = $this->order($order)->where($where)->field($field)->find();
        return $info;
    }

    /**
     * 获取列表 
     * @param type $where 条件
     * @param type $field 字段过滤
     * @param type $order 排序
     * @param type $group 分组
     * @return type array
     */
    public function getAll($where = [], $field = '*', $order = '', $group = '') {
        if (!empty($order)) {
            $this->order($order);
        }
        if (!empty($group)) {
            $this->group($group);
        }
        $res = $this->where($where)->field($field)->select()->toArray();
        return $res;
    }

    /**
     * 列表数据条数
     * @param type $where 条件
     * @return int 
     */
    public function count($where = []) {
        return $this->where($where)->count();
    }

}
