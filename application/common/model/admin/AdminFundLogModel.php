<?php
/**
 *  资金变动记录
 */
namespace app\common\model\admin;

use app\common\model\BaseModel;

class AdminFundLogModel extends BaseModel
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'c_time';
    public function __construct($data = [])
    {
        $this->table = 'admin_fund_log';
        parent::__construct($data);
    }

    public function typeList(){
        $list = [
            1 => '试验场',
            2 => '聊天',
            3 => '充值',
            4 => '图像分析',
        ];
        return $list;
    }

    public function getTypeText($type){
        $list = self::typeList();
        return $list[$type] ?? '';
    }

    /**
     *  添加信息
     * @param array $param
     * @return array
     * @throws \think\Exception
     */
    public function insertFund($param = [])
    {
        try {
            $result = $this->allowField(true)->data($param)->isUpdate(false)->save();
            if (false === $result) {
                return ['code' => 1001, 'data' => '', 'msg' => 'fail'];
            } else {
                return ['code' => 1000, 'data' => $this->id, 'msg' => 'success'];
            }
        } catch (\PDOException $e) {
            return ['code' => 1001, 'data' => '', 'msg' => 'fail'];
        }
    }



}
