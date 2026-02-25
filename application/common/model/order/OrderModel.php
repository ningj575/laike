<?php

/**
 * 订单model
 */

namespace app\common\model\order;
use app\common\model\BaseModel;
class OrderModel extends BaseModel
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'c_time';
    protected $updateTime = 'u_time';
    protected $append = ['order_status_text','fllow_status_text','tags_arr','departure_text','phone_fllow_text'];
    protected $type=[
        'pay_time_unix' => 'timestamp',      
    ];
    public function __construct($data = [])
    {
        $this->table = 'order';
        parent::__construct($data);
    }   
    
    public function record(){
        return $this->hasMany('OrderFllowRecordModel','order_id','order_id')->where('type',1)->order('id desc');
    }
    public function admin(){
        return $this->belongsTo('app\common\model\admin\SysAdminModel','sales_user_id','id');
    }

    public function business(){
        return $this->belongsTo('app\common\model\shop\ShopModel','account_id','account_id');
    }
     public function book(){
        return $this->hasOne('OrderBookModel','source_order_id','order_id')->where('book_status','gt',0);
    }
     public function product(){
        return $this->belongsTo('app\common\model\product\ProductModel','product_id','product_id');
    }
    public function customer(){
        return $this->belongsTo('CustomerModel','buyer_info_phone','buyer_info_phone');
    }

    public function info(){
        return $this->hasOne('OrderInfoModel','order_id','order_id');
    }
    
    public function getPhoneFllowTextAttr($val, $data){
        $status_text=['0'=>'未联系','1'=>'未接通','2'=>'已联已接','3'=>'未联已接','4'=>'空号','5'=>'停机','6'=>'关机'];
        return $status_text[$data['phone_fllow']]??'';
    }
    
    public function getWeixinFllowTextAttr($val, $data){
        $status_text=['0'=>'未添加','1'=>'已添加','2'=>'已申请未通过'];
        return $status_text[$data['weixin_fllow']]??'';
    }

    public function getOrderStatusTextAttr($val, $data){
        $status_text=['1'=>'未预约','2'=>'待接单','3'=>'预约成功','-1'=>'订单取消'];
        return $status_text[$data['order_status']]??'';
    }
    public function getDepartureTextAttr($val, $data){
        $sys_zone = new \app\common\model\admin\SysZoneModel();
        $departure_text = $sys_zone->where('zone_id', $data['departure'])->value('zone_name');
        return $departure_text??'';
    }
     public function getFllowStatusTextAttr($val, $data){
        $status_text=['1'=>'待分配','2'=>'待跟进','3'=>'已处理'];
        return $status_text[$data['fllow_status']]??'';
    }
      /**
     *
     * @param $val
     * @param $data
     * @return bool|string
     */
    public function getTagsArrAttr($val, $data) {
        if (empty($data['tags'])) {
            return [];
        }
        $imgArr = json_decode($data['tags'], true);

        if (empty($imgArr)) {
            return [];
        }
        $mod = new TagsModel();
        $ret = $mod->where([['id', 'in', $imgArr],['type','eq',2]])->column('name');
        return $ret ?? [];
    }

    /**
     * 插入角色信息
     * @param $param
     * @return array
     */
    public function insert($param)
    {
        try {
            $result = $this->allowField(true)->save($param);
            if (false === $result) {
                return ['code' => 1001, 'data' => '', 'msg' => $this->getError()];
            } else {
                return ['code' => 1000, 'data' => '', 'msg' => langCommon('add_success')];
            }
        } catch (\PDOException $e) {
            return ['code' => 1001, 'data' => '', 'msg' => $e->getMessage()];
        }
    }



    /**
     * 删除角色
     * @param $id
     * @return array
     * @throws \Exception
     */
    public function del($id)
    {
        try {
            $this->where('id', $id)->data('is_del',1)->update();
            return ['code' => 1000, 'data' => '', 'msg' => langCommon('delete_success')];
        } catch (\PDOException $e) {
            return ['code' => 1001, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

    /**
     * 编辑角色信息
     * @param $param
     * @return array
     */
    public function edit($param)
    {
        try {
            $result = $this->save($param, ['id' => $param['id']]);
            if ($result) {
                return ['code' => 1000, 'data' => '', 'msg' => langCommon('edit_success')];
            }
            return ['code' => 1001, 'data' => '', 'msg' => $this->getError()];
        } catch (\PDOException $e) {
            return ['code' => 1001, 'data' => '', 'msg' => $e->getMessage()];
        }
    }
}
