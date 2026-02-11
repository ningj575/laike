<?php

/**
 * 网站配置 
 */

namespace app\admin\controller\system;

use app\common\server\admin\AdminServer;
use app\common\model\admin\SysConfigModel;

class Config extends AdminServer {

    /**
     * 
     */
    public function set() {
        $mod = new SysConfigModel();
        if (request()->isAjax()) {
            $data = input();
            if (isset($data['file'])) {
                unset($data['file']);
            }
            $title_arr = ['app_logo' => '应用logo', 'app_id' => '应用ID', 'app_secret' => '应用密钥', 'app_name' => '应用名称'];
            $add_data = [];
            foreach ($data as $key => $val) {
                $set_info = $mod->where('name', $key)->find();
                if (!empty($set_info)) {
                    $set_info->save(['value' => $val]);
                } else {
                    $add_data[] = [
                        'name' => $key,
                        'value' => $val,
                        'status' => 1,
                        'title' => $title_arr[$key] ?? ""
                    ];
                }
            }
            if (!empty($add_data)) {
                $mod->saveAll($add_data);
            }
            return json(['code' => 1000, 'msg' => '修改成功']);
        }
        $config=$mod->sysConfig();
        $config['app_logo_url']= config('public.sites.static').'/'.$config['app_logo'];
        $this->assign('config',$config );
        return $this->fetch('admin/config');
    }

}
