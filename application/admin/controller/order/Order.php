<?php

/**
 * 订单管理 
 */

namespace app\admin\controller\order;

use app\common\server\admin\AdminServer;
use app\common\model\order\OrderModel;
use app\common\model\shop\ShopModel;
use app\common\server\laike\KefuServer;

;

class Order extends AdminServer {

    public function index() {
        //用于API - JSON
        if (input('get.page')) {
            $mod = new OrderModel();
            $order_num = input('get.order_num', '');
            $item_name = input('get.item_name', '');
            $buy_phone = input('get.buy_phone', '');
            $order_status = input('get.order_status');
            $kefu_id = input('get.kefu_id');
            $shop_id = input('get.shop_id');
            $where = [
            ];
            if (!empty($item_name)) {
                $mod = $mod->whereLike('item_name', '%' . $item_name . '%');
            }
            if (!empty($order_num)) {
                $mod = $mod->where('order_num', $order_num);
            }
            if (!empty($buy_phone)) {
                $mod = $mod->where('buy_phone', $buy_phone);
            }
            if (!empty($shop_id)) {
                $mod = $mod->where('shop_id', $shop_id);
            }
            if (!empty($kefu_id)) {
                $mod = $mod->where('kefu_id', $kefu_id);
            }
            if (isset($order_status)) {
                $mod = $mod->where('order_status', $order_status);
            }
            $page = input('get.page/d', 1);
            $limit = input('get.limit/d', 10);
            $count = $mod->count();
            $lists = $mod->with(['shop', 'kefu'])->where($where)->page($page, $limit)->order('id desc')->select();         
            $data = ['msg' => '', 'code' => 1000, 'data' => $lists, 'count' => $count];
            return json($data);
        }
        $shop_mod = new ShopModel();
        $shop_list = $shop_mod->where('is_del', 0)->select();
        $kefu_list = (new KefuServer())->getKefu();
        $this->assign([      
            'shop_list' => $shop_list,
            'kefu_list' => !empty($kefu_list['admin_user']) ? $kefu_list['admin_user'] : []
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
        $kefu_list = (new KefuServer())->getKefu();
        $this->assign([
            'order' => $mod->where('id', $id)->find(),
            'shop_list' => $shop_list,
            'kefu_list' => !empty($kefu_list['admin_user']) ? $kefu_list['admin_user'] : []
        ]);
        return $this->fetch('order/edit');
    }

    /**
     * 详情
     */
    public function info() {
        $mod = new OrderModel();
        $id = input('param.id');
        $shop_mod = new ShopModel();
        $shop_list = $shop_mod->where('is_del', 0)->select()->toArray();
        $kefu_list = (new KefuServer())->getKefu();
        $order_info=$mod->where('id', $id)->find();
        $order_info['tags_arr']= explode(',', $order_info['tags']);
        $this->assign([
            'order' => $order_info,
            'shop_list' => $shop_list,
            'kefu_list' => !empty($kefu_list['admin_user']) ? $kefu_list['admin_user'] : []
        ]);
        return $this->fetch('order/info');
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
            $kefu_ser = new KefuServer();
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
            $order_list = $kefu_ser->orderAssign($order_data);
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
        $mod->save(["$field"=>$value], ['id'=>$id]);
        return json(['code' => 1000, 'data' => [], 'msg' => '修改成功']);
    }
    /*
     * 批量修改
     */
    public function batchUpdate() {
        $ids = input('param.ids');
        $field = input('param.field');
        $value = input('param.value');
        $mod = new OrderModel();
        $mod->save(["$field"=>$value],[['id','in',$ids]]);
        return json(['code' => 1000, 'data' => [], 'msg' => '修改成功']);
    }

}
