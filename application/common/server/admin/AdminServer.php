<?php

/**
 * 后台server young www.iaisng.com
 */

namespace app\common\server\admin;

use app\common\server\BaseServer;
use think\App;

class AdminServer extends BaseServer
{

    protected $ADMIN_INFO;

    public function __construct(App $app = null)
    {
        header('Access-Control-Allow-Origin: *'); //设置http://www.baidu.com允许跨域访问
        header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With'); //设置允许的跨域header
        header('Access-Control-Allow-Methods:GET, HEAD, POST, PUT, DELETE, TRACE, OPTIONS, PATCH');
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
        header('Access-Control-Allow-Credentials:true');
        parent::__construct($app);

        $this->ADMIN_INFO = session('admin_info');
        if (empty($this->ADMIN_INFO) || empty($this->ADMIN_INFO['uid'])) {
            $this->redirect('/login.html');
        }
        $modAdmin = new \app\common\model\admin\SysAdminModel();
        if(!$modAdmin->where('id',$this->ADMIN_INFO['uid'])->count()){
            session('admin_info',null);
            $this->redirect('/login.html');
        }
        //获取用户对应权限
        $auth = new AdminAuthServer();
        //$module = strtolower(request()->module());
        $controller = explode('.', strtolower(request()->controller()));
        $action = strtolower(request()->action());

        if (count($controller) >= 2) {
            $url = $controller[0] . "/" . $controller[1] . "/" . $action;
        } else {
            $url = $controller[0] . "/" . $action;
        }

        //跳过检测以及主页权限 - 过滤 admin
        if ($this->ADMIN_INFO['uid'] != 1) {
            $noRuleArr = [
                'index/index', 'index/indexpage', 'system/admin/changepwd', 'system/admin/basic',
                'index/getsysstatic'
            ];

            if (!in_array($url, $noRuleArr)) {
                $url = str_replace("_", "", $url);
                if (!$auth->adminCheck($url, $this->ADMIN_INFO['uid'])) {

                    $this->redirect('/login/ruleError');
                }
            }
        }

        //缓存配置
        //$cacheOption = ['prefix' => 'admin_', 'select' => 0, 'type' => 'redis', 'expire' => 86400];
        $menuArr = cache('admin:menuArr_' . $this->ADMIN_INFO['admin_id']);
        $sysConfig = cache('admin:sysConfig');

        //获取用户对应菜单
        if (empty($menuArr)) {
            $modSysAuthRule = new \app\common\model\admin\SysAuthRuleModel();
            $menuArr = $modSysAuthRule->getMenu($this->ADMIN_INFO['rule']);
            cache('admin:menuArr_' . $this->ADMIN_INFO['uid'], $menuArr);
        }

        if (empty($sysConfig)) {
            $mod_sys_con = new \app\common\model\admin\SysConfigModel();
            $sysConfig = $mod_sys_con->sysConfig();
            cache('admin:sysConfig', $sysConfig);
        }

        //调用模板
        $seo_arr = [
            'title' => $sysConfig['web_site_title'],
            'des' => $sysConfig['web_site_description'],
            'keyword' => $sysConfig['web_site_keyword'],
        ];
        $cell_key='cell.'.strtolower(str_replace('.', '_', $this->request->controller()).'_'. $this->request->action());
        $this->assign([
            'cell'=>$cell_key,
            'admin_name' => $this->ADMIN_INFO['admin_name'],
            'portrait' => $this->ADMIN_INFO['portrait'],
            'rolename' => $this->ADMIN_INFO['rolename'],
            'menu' => $menuArr,
            'seo_arr' => $seo_arr,
            'www_site' => config('public.sites.home')
        ]);
    }

}
