<?php

/**
 * 系统后台 - 帐户管理
 * User: Tx1123
 * Date: 2017/3/8
 */

namespace app\admin\controller\system;

use app\common\server\admin\AdminServer;

class Update extends AdminServer
{

    public function index()
    {
        $p = input('get.page');
        $page = $p ? $p : 0;
        $pagesize = 20;
        if ($page) {
            $where = [];
            $state = input('state');
            if ($state) {
                $where[] = ['state', 'eq', $state];
            }
            $serUpdata = new \app\common\model\extend\SysUpdateModel();
            $ret['list'] = $serUpdata->getList($where, $page, $pagesize, 'id desc');
            $ret['count'] = $serUpdata->count($where);
            $data = ['msg' => '数据获取成功', 'code' => 1000, 'data' => $ret['list'], 'count' => $ret['count']];
            return json($data);
        }
        return $this->fetch('update/index');
    }

    public function addEdit()
    {
        $modUpdata = new \app\common\model\extend\SysUpdateModel();
        if (request()->isPost()) {
            $param = input('post.');
            if (!isset($param['state'])) {
                $param['state'] = 2;
            }
            if (!isset($param['compel'])) {
                $param['compel'] = 2;
            }
            if (!isset($param['platform'])) {
                return json(returnPubData('请选择系统平台'));
            }
            if (mb_strlen($param['title'], 'utf8') > 50) {
                return json(['code' => 1001, 'msg' => '升级标签不要超过50个字符(标点符号算一个字)']);
            }
            if (empty($param['id'])) {
                $result = $modUpdata->allowField(true)->save($param);
                $msg = '添加';
            } else {
                $result = $modUpdata->allowField(true)->save($param, ['id' => $param['id']]);
                $msg = '编辑';
            }
            if (false === $result) {
                $retArr = ['code' => 1001, 'data' => '', 'msg' => $modUpdata->getError()];
            } else {
                $cacheRedis = new \think\cache\driver\Redis(['select' => 1]);
                $cacheRedis->rm('updata:app_updata_platform:1');
                $cacheRedis->rm('updata:app_updata_platform:2');
                $retArr = ['code' => 1000, 'data' => '', 'msg' => $msg . '成功'];
            }
            return json($retArr);
        }
        $id = input('param.id');

        if ($id) {
            $res = $modUpdata->where([['id', 'eq', $id]])->find();
        } else {
            $res = $modUpdata->getInitFile();
        }


        $this->assign('res', $res);
        return $this->fetch('update/addEdit');
    }

    public function delete()
    {
        $id = input('param.id');
        if ($id) {
            $modUpdata = new \app\common\model\extend\SysUpdateModel();
            $res = $modUpdata->where([['id', 'eq', $id]])->delete();
            if ($res) {
                $cacheRedis = new \think\cache\driver\Redis(['select' => 1]);
                $cacheRedis->rm('updata:app_updata_platform:1');
                $cacheRedis->rm('updata:app_updata_platform:2');
                return json(returnPubData('删除成功', 1000));
            } else {
                return json(returnPubData($modUpdata->getError()
                ));
            }
        }
        return json(['code' => 1001, 'msg' => '错误']);
    }

}
