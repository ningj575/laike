<?php

/**
 * 登陆
 */

namespace app\admin\controller;

//use extend\Verify;
use app\common\server\admin\LoginServer;
use app\common\server\BaseServer;
use think\App;
use think\facade\Request;

class Login extends BaseServer
{

    public $CACHE_PATH, $TEMP_PATH, $LOG_PATH;

    public function __construct(App $app = null)
    {
        parent::__construct($app);

        $this->CACHE_PATH = ROOT_PATH . 'runtime' . DS . 'cache' . DS;
        $this->TEMP_PATH = ROOT_PATH . 'runtime' . DS . 'temp' . DS;
        $this->LOG_PATH = ROOT_PATH . 'runtime' . DS . 'log' . DS;
    }
    public function index()
    {
        $mod_sys_con = new \app\common\model\admin\SysConfigModel();
        $sysConfig = $mod_sys_con->sysConfig();
        //调用模板
        $seo_arr = [
            'title' => $sysConfig['web_site_title'],
            'des' => $sysConfig['web_site_description'],
            'keyword' => $sysConfig['web_site_keyword'],
        ];
        $this->assign('seo_arr', $seo_arr);        
        return $this->fetch('/login');
    }

    /**
     * 执行登录操作
     */
    public function dologin()
    {
        // 是否为 POST 请求
        if (!Request::isPost()) {
            return $this->redirect('/login.html');
        }

        $admin_name = input("post.admin_name");
        $password = input("post.password");
        $remember=input("post.remember");        
//        $code = input("post.code");

        //验证用户名与密码
        if (empty($admin_name) || empty($password)) {
            return $this->apiError(lang('login_msg'));
        }

        //验证 - 验证码是否正确
//        $verify = new Verify();
//        if (!$code) {
//            return $this->apiError('请输入验证码');
//        }
//        $captcha = new \think\captcha\Captcha();
//
//        if (!$captcha->check($code, 'YOUNG_ADMIN')) {
//            return $this->apiError('验证码错误');
//        }


        //model: 静态获取 帐户数据

        $admin_info = \app\common\model\admin\SysAdminModel::where('admin_name', $admin_name)->find();
        if (empty($admin_info)) {
            return $this->apiError(lang('no_admin'));
        }

        if (retPwd($password, config('other.auth_key'), $admin_info['pwd_key']) != $admin_info['password']) {
            adminlog($admin_info['id'], 1, '用户【' . $admin_name . '】登录失败：密码错误', 2);
            return $this->apiError(lang('fail_password'));
        }

        if (1 != $admin_info['status']) {
            adminlog($admin_info['id'], 1, '用户【' . $admin_name . '】登录失败：该账号被禁用', 2);
            return $this->apiError(lang('account_disable'));
        }
        if(!empty($remember)){
            cookie("admin_name", $admin_name); 
            cookie("password", $password); 
        }

        //获取该管理员的角色信息
        $user = new \app\common\model\admin\SysAuthGroupModel();
        $info = $user->getRoleInfo($admin_info['groupid']);
        $data_info = [
            'uid' => $admin_info['id'], //用户ID
            'uid_key' => $admin_info['uid_key'], //用户标识
            'admin_id' => $admin_info['id'], //用户ID
            'admin_name' => $admin_info['admin_name'], //用户名
            'portrait' => $admin_info['portrait'], //用户头像
            'rolename' => $info['title'], //角色名
            'rule' => $info['rules'], //角色节点
            'rule_url' => $info['name'], //角色权限
        ];

        //过期时间-默认3600 1小时
        session('admin_info', $data_info);

        //更新管理员状态
        $param = [
            'loginnum' => $admin_info['loginnum'] + 1,
            'last_login_ip' => request()->ip(),
            'last_login_time' => time()
        ];

        \app\common\model\admin\SysAdminModel::where('id', $admin_info['id'])->update($param);
        adminlog($admin_info['id'], 1, '用户【' . $admin_info['admin_name'] . '】登录成功', 1);
        return $this->apiSuccess([], lang('login_success'));
    }

    /**
     * 显示图片验证码
     * @return string|void
     */
    public function checkverify()
    {
        $serLogin = new LoginServer();
        return $serLogin->verifycode('YOUNG_ADMIN');
    }

    //退出操作
    public function loginOut()
    {
        $adminInfo = session('admin_info');
        cache('adminMenu:menuArr_' . $adminInfo['admin_id'], null);
        session(null);
        cache('menuArr', null);

        //清除runtime等缓存数据
        @array_map('unlink', glob($this->TEMP_PATH . '/*.php'));
        if (is_dir($this->TEMP_PATH)) {
            rmdir($this->TEMP_PATH);
        }

        //清除缓存
        $this->delete_dir_file($this->CACHE_PATH);
        $this->delete_dir_file($this->TEMP_PATH);
        $this->clear();
        $this->redirect(url('/index'));
    }

    public function ruleError()
    {
        $this->assign('msg', '抱歉，您没有操作权限');
        return $this->fetch('public/error');
    }

    /**
     * 循环删除目录和文件
     * @param string $dir_name
     * @return bool
     */
    private function delete_dir_file($dir_name)
    {
        $result = false;
        if (is_dir($dir_name)) {
            if ($handle = opendir($dir_name)) {
                while (false !== ($item = readdir($handle))) {
                    if ($item != '.' && $item != '..') {
                        if (is_dir($dir_name . DS . $item)) {
                            $this->delete_dir_file($dir_name . DS . $item);
                        } else {
                            unlink($dir_name . DS . $item);
                        }
                    }
                }
                closedir($handle);
                if (rmdir($dir_name)) {
                    $result = true;
                }
            }
        }
        return $result;
    }

    /**
     * 清除缓存
     */
    public function clear()
    {
        $cache = new \think\cache\driver\Redis();
        $res = $cache->handler()->keys('admin:*');
        foreach ($res as $val) {
            $cache->rm($val);
        }
        if ($this->delete_dir_file($this->CACHE_PATH) || $this->delete_dir_file($this->TEMP_PATH) || $this->delete_dir_file($this->LOG_PATH)) {
            return $this->apiSuccess([], '清除缓存成功');
        } else {
            return $this->apiError('清除缓存失败');
        }
    }

}
