<?php
/**
 * 服务设置

 */

namespace app\admin\controller\system;

use app\common\model\service\ServiceSetModel;
use app\common\server\admin\AdminServer;

class Service extends AdminServer
{

    /**
     * 列表
     * @return type
     */
    public function index()
    {
        $status = input('status');
        if (input('get.page')) {
            $mod = new \app\common\model\service\ServiceSetModel();
            $where = [];

            if (!empty($status)) {
                $where[] = ['status', 'eq', $status];
            }

            $where[] = ['status', 'neq', 99];
            $page = input('get.page/d', 1);
            $limit = input('get.limit/d', 10);
            $count = $mod->where($where)->count();
            $lists = $mod->getList($where, $page, $limit, 'id desc');
            $data = ['msg' => '数据获取成功', 'code' => 1000, 'data' => $lists, 'count' => $count];
            return json($data);
        }

        $this->assign('status', $status ?? '');
        return $this->fetch('/service/index');
    }

    public function service_add(){
        if (request()->isAjax()) {
            $param = input('post.');
            $service_mod = new ServiceSetModel();
            //移动文件 正式保存路径
            if ($param['img']) {
                $image_arr = array_filter(explode('||', $param['img']));
                $ser_upload = new \app\common\server\FileServer();
                $result_img = $ser_upload->move_file_arr($image_arr);
                if ($result_img['code'] == 1000) {
                    $image_arr = $result_img['data'];
                }
                $param['img'] = $image_arr[0];
            }
            if (!isset($param['status'])) {
                $param['status'] = 2;
            }
            unset($param['file']);


            $flag = $service_mod->insertservice($param);
            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }
        return $this->fetch('/service/index');
    }
    /**
     * 编辑
     * @return mixed|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function edit_service() {
        $service_mod = new ServiceSetModel();

        //Save
        if (request()->isPost()) {
            $param = input('post.');

            if (empty($param['id'])) {
                return $this->service_add();
            }
            //移动文件 正式保存路径
            if (!empty($param['img'])) {
                $image_arr = array_filter(explode('||', $param['img']));
                $ser_upload = new \app\common\server\FileServer();
                $result_img = $ser_upload->move_file_arr($image_arr);
                if ($result_img['code'] == 1000) {
                    $image_arr = $result_img['data'];
                }
                $param['img'] = $image_arr[0];
            }
            if (!isset($param['status'])) {
                $param['status'] = 2;
            }
            unset($param['file']);
            $flag = $service_mod->editservice($param);
            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }
        $id = input('param.id');
        $service_info = $service_mod->getOneService($id);
        if (empty($service_info)) {
            $service_info = $service_mod->getInitFile();
        }

        $this->assign('service', $service_info);

        return $this->fetch('/service/editService');
    }

    /**
     * 状态
     * @return \think\response\Json
     */
    public function status_service() {
        $id = input('param.id');
        $mod_service = new ServiceSetModel();
        $status = $mod_service->where(array('id' => $id))->value('status'); //判断当前状态情况
        if ($status == 1) {
            $flag = $mod_service->where(array('id' => $id))->setField(['status' => 2]);
            return json(['code' => 1000, 'data' => $flag['data'], 'msg' => '关闭']);
        } else {
            $flag = $mod_service->where(array('id' => $id))->setField(['status' => 1]);
            return json(['code' => 1001, 'data' => $flag['data'], 'msg' => '开启']);
        }
    }
    /**
     * 删除
     * @return \think\response\Json
     */
    public function del_service() {
        $mod_spces = new ServiceSetModel();
        $id = input('param.id');
        $flag = $mod_spces->delservice($id);
        return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
    }

}

