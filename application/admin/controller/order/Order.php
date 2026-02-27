<?php

/**
 * 订单管理 
 */

namespace app\admin\controller\order;

use app\common\server\admin\AdminServer;
use app\common\model\order\OrderModel;
use app\common\model\shop\ShopModel;
use app\common\server\laike\SalesServer;
use app\common\model\product\ProductModel;
use app\common\model\admin\SysZoneModel;
use app\common\model\order\OrderBookModel;
use app\common\model\order\OrderFllowRecordModel;
use app\common\model\order\TagsModel;
use app\common\model\order\OrderAssignModel;

class Order extends AdminServer {

    public function index() {
        //用于API - JSON
        if (input('get.page')) {
            $mod = new OrderModel();
            $order_id = input('get.order_id', '');
            $product_name = input('get.product_name', '');
            $buy_phone = input('get.buy_phone', '');
            $order_status = input('get.order_status');
            $fllow_status = input('get.fllow_status');
            $sales_user_id = input('get.sales_user_id');
            $account_id = input('get.account_id');
            $buyer_info_name = input('get.buyer_info_name');
            $buyer_info_phone = input('get.buyer_info_phone');
            $departure = input('get.departure');
            $book_start_date = input('get.book_start_date');
            $book_date = input('get.book_date');
            $complet_date = input('get.complet_date');
            $tags_id = input('get.tags_id', '');
            $where = [
            ];
            $admin_id = $this->ADMIN_INFO['uid'];
            $sale_user_mod = new \app\common\model\sales\SalesUserModel();
            $is_sales = $sale_user_mod->where('admin_id', $admin_id)->find();
            if ($is_sales) {
                $mod = $mod->where('sales_user_id', $admin_id);
            }
            if (!empty($product_name)) {
                $product_mod = new ProductModel();
                $product_ids = $product_mod->whereLike('product_name', '%' . $product_name . '%')->column('product_id');
                if (!empty($product_ids)) {
                    $mod = $mod->where('product_id', 'in', $product_ids);
                } else {
                    $mod = $mod->where('id', 0);
                }
            }
            if (!empty($order_id)) {
                $mod = $mod->where('order_id', $order_id);
            }
            if (!empty($buy_phone)) {
                $mod = $mod->where('buy_phone', $buy_phone);
            }
            if (!empty($account_id)) {
                $mod = $mod->where('account_id', $account_id);
            }
            if (!empty($sales_user_id)) {
                $mod = $mod->where('sales_user_id', $sales_user_id);
            }
            if (!empty($order_status)) {
                $mod = $mod->where('order_status', $order_status);
            }
            if (!empty($fllow_status)) {
                $mod = $mod->where('fllow_status', $fllow_status);
            }
            if (!empty($buyer_info_name)) {
                $mod = $mod->where('buyer_info_name', $buyer_info_name);
            }
            if (!empty($buyer_info_phone)) {
                $mod = $mod->where('buyer_info_phone', $buyer_info_phone);
            }
            if (!empty($departure)) {
                $mod = $mod->where('departure', $departure);
            }
            if (!empty($book_start_date)) {
                $book_mod = new OrderBookModel();
                $book_column = $book_mod->where('book_start_date', $book_start_date)->column('source_order_id');
                if (!empty($book_column)) {
                    $mod = $mod->where('order_id', 'in', $book_column);
                } else {
                    $mod = $mod->where('id', 0);
                }
            }
            if (!empty($book_date)) {
                $book_mod = new OrderBookModel();
                $book_column = $book_mod->where('create_order_time_unix', 'between', [strtotime($book_date), strtotime($book_date) + 86400])->column('source_order_id');
                if (!empty($book_column)) {
                    $mod = $mod->where('order_id', 'in', $book_column);
                } else {
                    $mod = $mod->where('id', 0);
                }
            }
            if (!empty($complet_date)) {
                $mod = $mod->where('complet_date', strtotime($complet_date));
            }
            if (!empty($tags_id)) {
                $tags_arr = explode(',', $tags_id);
                $sql = '';
                foreach ($tags_arr as $k => $v) {
                    if ($k != 0) {
                        $sql = $sql . ' and ';
                    }
                    $sql = $sql . 'JSON_CONTAINS(tags, \'"' . $v . '"\')';
                }
                $where = 'tags!="" and (' . $sql . ')';
            }
            $page = input('get.page/d', 1);
            $limit = input('get.limit/d', 10);
            $count = $mod->where($where)->count();
            $lists = $mod->with(['business', 'product', 'book', 'admin'])->where($where)->page($page, $limit)->order('id desc')->select();
            foreach ($lists as &$val) {
                $val['buyer_info'] = $val['buyer_info_name'] ? ($val['buyer_info_name'] . "\n") : '' . $val['buyer_info_phone'];
                $val['actual_amount'] = number_format($val['actual_amount'] / 100, 2);
                $val['time'] = '支付时间:' . $val['pay_time_unix'];
                if ($val['book']) {
                    $val['time'] = $val['time'] . "\n" . '预约时间:' . $val['book']['create_order_time_unix'];
                    $val['time'] = $val['time'] . "\n" . '出行日期:' . $val['book']['book_start_date'];
                }
                if($val['order_status']==2){
                    $time_diff= strtotime($val['book']['create_order_time_unix'])+$val['book']['order_accept_hour']*3600-time();
                    $time_diff>0&&$val['order_accept_time']= $this->formatSecondsAsTime($time_diff);
                }
            }
            $data = ['msg' => '', 'code' => 1000, 'data' => $lists, 'count' => $count];
            return json($data);
        }
        $shop_mod = new ShopModel();
        $shop_list = $shop_mod->where('is_del', 0)->select();
        $sales_list = (new SalesServer())->getSales();
        $tags_mod = new TagsModel();
        $order_status = input('order_status', 0);
        $fllow_status = input('fllow_status', 0);
        $buyer_info_phone = input('get.buyer_info_phone', '');
        $this->assign([
            'tags_list' => $tags_mod->where([['type', 'eq', 2], ['status', 'eq', 1]])->select(),
            'shop_list' => $shop_list,
            'city_list' => (new SysZoneModel())->where([['Rank', 'eq', 1], ['zone_id', 'gt', 1]])->field('zone_id,zone_name')->select()->toArray(),
            'sales_list' => !empty($sales_list['admin_user']) ? $sales_list['admin_user'] : [],
            'order_status' => $order_status,
            'fllow_status' => $fllow_status,
            'buyer_info_phone' => $buyer_info_phone
        ]);
        return $this->fetch('order/index');
    }    

    /**
     * 编辑
     */
    public function edit() {
        $mod = new OrderModel();
        if (request()->isAjax()) {
            $param = input('post.');
//            $param['tag']=json_encode($param['tag'],JSON_UNESCAPED_UNICODE);
            if (empty($param['id'])) {
                $flag = $mod->insert($param);
            } else {
                $flag = $mod->edit($param);
            }
            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }
        $id = input('param.id');
        $shop_mod = new ShopModel();
        $shop_list = $shop_mod->where('is_del', 0)->select()->toArray();
        $sales_list = (new SalesServer())->getKefu();
        $this->assign([
            'order' => $mod->where('id', $id)->find(),
            'shop_list' => $shop_list,
            'sales_list' => !empty($sales_list['admin_user']) ? $sales_list['admin_user'] : []
        ]);
        return $this->fetch('order/edit');
    }

    /**
     * 详情
     */
    public function info() {
        $mod = new OrderModel();
        $tags_mod = new TagsModel();
        $id = input('param.id');
        $shop_mod = new ShopModel();
        $shop_list = $shop_mod->where('is_del', 0)->select()->toArray();
        $sales_ser = new SalesServer();
        $sales_list = $sales_ser->getSales();
        $order_info = $mod->with(['product', 'book.traveler', 'info', 'record.admin', 'customer'])->where('id', $id)->find();
        $order_info['all_actual_amount'] = number_format(($order_info['actual_amount'] + $order_info['book']['actual_amount']) / 100, 2);
        if (!empty($order_info['book'])) {
            $order_info['book']['actual_amount'] = number_format($order_info['book']['actual_amount'] / 100, 2);
        }
        $order_info['actual_amount'] = number_format($order_info['actual_amount'] / 100, 2);
        $order_info['total_amount'] = number_format($order_info['total_amount'] / 100, 2);
        $order_info['all_merchant_discount_amount'] = number_format(($order_info['merchant_discount_amount'] + $order_info['book']['merchant_discount_amount']) / 100, 2);
        $order_info['fee_amount'] = number_format($order_info['all_actual_amount'] * 0.05, 2);
        $order_info['all_fee_amount'] = $order_info['fee_amount'];
        $order_info['jiesuan_amount'] = number_format($order_info['all_actual_amount'] - $order_info['fee_amount'], 2);
        if (!empty($order_info['record'])) {
            foreach ($order_info['record'] as &$val) {
                if ($val['manual_fllow'] == 1 && $val['out_fllow_time'] > time() && $val['out_fllow_time'] > 0) {
                    $time_diff = $val['out_fllow_time'] - time();
                    $val->time_out = $this->formatSecondsAsTime($time_diff);
                }
            }
        }
        $this->assign([
            'order' => $order_info,
            'business_list' => $shop_list,
            'sales_list' => !empty($sales_list['admin_user']) ? $sales_list['admin_user'] : [],
            'tags_list' => $tags_mod->where([['type', 'eq', 2], ['status', 'eq', 1]])->select(),
            'customer_tags_list' => $tags_mod->where([['type', 'eq', 1], ['status', 'eq', 1]])->select(),
            'icon_color' => $this->icon_color,
            'order_static' => $sales_ser->getCustomerStatic($order_info['customer']['id']),
            'depart_date_arr' => $this->depart_date_arr()
        ]);
        return $this->fetch('order/info');
    }

    private function formatSecondsAsTime($seconds,$type=1) {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds / 60) % 60);
        $seconds = $seconds % 60;
        $str = '';
        $hours && $str = $str . $hours . '小时';
        $minutes && $str = $str . $minutes . '分';
        $type==1&&$seconds && $str = $str . $seconds . '秒';
        return $str;
    }

    private function depart_date_arr() {
        $year = date('Y');
        return [
            $year . '年第一季度（1-3月）',
            $year . '年第二季度（4-6月）',
            $year . '年第三季度（7-9月）',
            $year . '年第四季度（10-12月）',
        ];
    }

    /**
     * 删除
     */
    public function del() {
        $id = input('param.id');
        $mod = new OrderModel();
        $flag = $mod->del($id);
        return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
    }

    /*
     * 添加记录
     */

    public function addRecord() {
        if (request()->isAjax()) {
            $param = input('param.');
            $order_mod = new OrderModel();
            $order = $order_mod->where('order_id', $param['order_id'])->find();
            if (!$order) {
                return json(['code' => 1001, 'data' => [], 'msg' => '失败']);
            }
            if ($this->ADMIN_INFO['uid'] == $order['sales_user_id']&&$order['out_fllow_time']>0) {
                $order_assign_mod = new OrderAssignModel();
                $order_assign_mod->where([['order_id', 'eq', $param['order_id']], ['type', 'eq', 1], ['sales_user_id', 'eq', $order['sales_user_id']]])->order('id desc')->limit(1)->update(['is_reallocate' => 1]);
                $order->save(['out_fllow_time' => 0]);
            }
            $param['admin_id'] = $this->ADMIN_INFO['uid'];
            $param['type'] = 1;
            $param['manual_fllow'] = 2;
            $OrderFllowRecordModel = new OrderFllowRecordModel();
            $OrderFllowRecordModel->insert($param);
            return json(['code' => 1000, 'data' => [], 'msg' => '添加成功']);
        }
        return $this->fetch('order/add_record');
    }

    /*
     * 导入订单
     */

    public function importOrder() {
        if ($this->request->isAjax()) {
            $shop_id = $this->request->post('shop_id');
            if (empty($shop_id)) {
                return json(['code' => 1001, 'msg' => '请选择店铺']);
            }
            $file = $this->request->file('file');
            $file_check = $file->getInfo()['name']; //获取文件名称
            $file_type = substr($file_check, strripos($file_check, ".") + 1);
            if (!in_array($file_type, ['xls', 'xlsx', 'csv'])) {
                return json(['code' => 1001, 'msg' => '上传文件格式有误，只允许excel', 'data' => []]);
            }
            $file_base_path = $file->getPath();
            $file_name = $file->getFilename();
            $file_path = $file_base_path . DS . $file_name;
            $excelServer = new \app\common\server\common\ExcelServer(); //           
            if ($file_type == 'csv') {
                $excel_data = $this->manualCsvParse($file_path);
            } else {
                $excel_data = $excelServer->readExecl($file_path);
            }
            if (empty($excel_data)) {
                return json(['code' => 1001, 'msg' => '没读取到数据']);
            }
            $order_data = [];
            $order_mod = new OrderModel();
            $sales_ser = new SalesServer();
            foreach ($excel_data as $val) {
                if (empty($val['A'])) {
                    continue;
                }
                $val['A'] = preg_replace("/[\x7f-\xff]+/", '', $val['A']);
                $order_info = $order_mod->where('order_num', $val['A'])->find();
                if ($order_info) {
                    return json(['code' => 1001, 'msg' => '订单号:' . $val['A'] . '已经存在了']);
                }
                $order_data[] = [
                    'shop_id' => $shop_id,
                    'order_num' => $val['A'],
                    'item_name' => $val['B'],
                    'item_cate' => $val['C'],
                    'item_type' => $val['D'],
                    'buy_num' => $val['E'],
                    'receive_amount' => ltrim($val['F'], '￥'),
                    'pay_time' => $val['G'],
                    'buy_phone' => $val['H'],
                ];
            }
            $order_list = $sales_ser->orderAssign($order_data);
            $order_mod->saveAll($order_list);
            return json(['code' => 1000, 'msg' => '导入成功', 'data' => $excel_data]);
        }
        $shop_mod = new ShopModel();
        $shop_list = $shop_mod->where('is_del', 0)->select()->toArray();
        $this->assign([
            'shop_list' => $shop_list,
        ]);
        return $this->fetch('order/import');
    }

    private function manualCsvParse($filePath) {
        $data = [];
        // 直接读取 CSV 文件查看原始结构
        $fileContent = file_get_contents($filePath);
        $lines = explode("\n", $fileContent);
        for ($i = 1; $i < count($lines); $i++) {
            $data[] = explode(',', $lines[$i]);
        }
        $excel_data = [];
        $cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S',
            'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL',
            'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ',);
        foreach ($data as $k => $v) {
            if ($k == 0) {
                continue;
            }
            $linshi_data = [];
            foreach ($v as $kk => $vv) {
                $linshi_data[$cellName[$kk]] = $vv;
            }
            $excel_data[] = $linshi_data;
        }
        return $excel_data;
    }

    /*
     * 
     */

    public function updataValue() {
        $id = input('param.id');
        $field = input('param.field');
        $value = input('param.value');
        $mod = new OrderModel();
        if (is_array($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        }
        $order = $mod->where('id', $id)->find();
        if (strpos($field, '-') !== false) {
            $key_arr = explode('-', $field);
            $param = [
                $key_arr[1] => $value
            ];
            switch ($key_arr[0]) {
                case 'info':
                    $this->updateOrderInfo($order->order_id, $param);
                    break;
                case 'customer':
                    if ($key_arr[1] == 'id_card' && !check_id_number($value)) {
                        return (['code' => 1001, 'msg' => '身份证号有误']);
                    }
                    $customer_mod = new \app\common\model\order\CustomerModel();
                    $customer_mod->where('buyer_info_phone', $order['buyer_info_phone'])->update($param);
                    break;
            }
        } else {
            $order->save(["$field" => $value]);
            if ($field == 'sales_user_id') {
                if ($order['fllow_status'] == 3) {
                    return json(['code' => 1001, 'msg' => '该订单已处理，不能更改销售员']);
                }
                $out_fllow_time = time() + 7200;
                $order->save(["fllow_status" => 2, 'out_fllow_time' => $out_fllow_time]);
                $order_assign_mod = new OrderAssignModel();
                $assign_param = [
                    'order_id' => $order['order_id'],
                    'sales_user_id' => $value,
                    'admin_id' => $this->ADMIN_INFO['uid'],
                    'rule_id' => -1,
                    'out_fllow_time' => $out_fllow_time
                ];
                $order_assign_mod->insert($assign_param);
                $admin_mod = new \app\common\model\admin\SysAdminModel();
                $admin_name = $admin_mod->where('id', $value)->value('admin_name');
                $param = [
                    'admin_id' => $this->ADMIN_INFO['uid'],
                    'order_id' => $order['order_id'],
                    'fllow_time' => time(),
                    'fllow_content' => '手动分配给:' . $admin_name,
                    'out_fllow_time' => $out_fllow_time,
                ];
                $fllow_mod = new \app\common\model\order\OrderFllowRecordModel();
                $fllow_mod->insert($param);
            }
        }
        return json(['code' => 1000, 'data' => [], 'msg' => '修改成功']);
    }

    private function updateOrderInfo($order_id, $param) {
        $order_info_mod = new \app\common\model\order\OrderInfoModel();
        $order_info = $order_info_mod->where('order_id', $order_id)->find();
        if (empty($order_info)) {
            $param['order_id'] = $order_id;
            $order_info_mod->save($param);
        } else {
            $order_info->save($param);
        }
    }

    /*
     * 批量修改
     */

    public function batchUpdate() {
        $ids = input('param.ids');
        $field = input('param.field');
        $value = input('param.value');
        $mod = new OrderModel();
        $mod->save(["$field" => $value], [['id', 'in', $ids]]);
        if ($field == 'sales_user_id') {
            $out_fllow_time = time() + 7200;
            $mod->save(["fllow_status" => 2, 'out_fllow_time' => $out_fllow_time], [['id', 'in', $ids], ['fllow_status', 'eq', 1]]);
            $order_assign_mod = new OrderAssignModel();
            $admin_mod = new \app\common\model\admin\SysAdminModel();
            $admin_name = $admin_mod->where('id', $value)->value('admin_name');            
            foreach ($ids as $vv) {
                $order_id = $mod->where('id', $vv)->value('order_id');
                $assign_param[] = [
                    'order_id' => $order_id,
                    'sales_user_id' => $value,
                    'admin_id' => $this->ADMIN_INFO['uid'],
                    'rule_id' => -1,
                    'out_fllow_time' => $out_fllow_time
                ];
                $fllow_record[] = [
                    'admin_id' => $this->ADMIN_INFO['uid'],
                    'order_id' => $order_id,
                    'fllow_time' => time(),
                    'fllow_content' => '手动分配给:' . $admin_name,
                    'out_fllow_time' => $out_fllow_time,
                ];
            }
            $order_assign_mod->saveAll($assign_param);
            $fllow_mod = new \app\common\model\order\OrderFllowRecordModel();
            $fllow_mod->saveAll($fllow_record);
        }
        return json(['code' => 1000, 'data' => [], 'msg' => '修改成功']);
    }

    /*
     * 确认接单
     */

    public function orderConfirm() {
        if (request()->isAjax()) {
            $confirm_result = input('confirm_result');
            $reject_code = input('reject_code', 0);
            $order_id = input('order_id');
//            $param['admin_id'] = $this->ADMIN_INFO['uid'];
            $order_ser = new \app\common\server\laike\OrderServer();
            $confirm_res = $order_ser->orderConfirm($order_id, $confirm_result, $reject_code);
            return $confirm_res;
        }
        $order_id = input('order_id');
        $order_book_mod = new OrderBookModel();
        $order_book_info = $order_book_mod->where('order_id', $order_id)->find();
        $this->assign('order_book_info', $order_book_info);
        return $this->fetch('order/order_confirm');
    }

}
