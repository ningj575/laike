<?php

namespace app\admin\controller;

use app\common\model\admin\SysAdminModel;
use app\common\server\admin\AdminServer;
use app\common\server\admin\SystemInfoServer;
use think\App;

class Index extends AdminServer
{

    public function __construct(App $app = null)
    {
        parent::__construct($app);
    }



    /**
     * 框架首页
     * @return mixed 
     */
    public function index()
    {
        return $this->fetch('/index');
    }

    /**
     * 首页内容
     * @return mixed
     * @throws \think\db\exception\BindParamException
     * @throws \think\exception\PDOException
     */
    public function indexPage()
    {
        $mod = new SysAdminModel();
        $mysqlV = $mod->query('select VERSION() as version');
        $info = array(
            'web_server' => $_SERVER['SERVER_SOFTWARE'], //服务器系统
            'onload' => ini_get('upload_max_filesize'), //最大文件上传数
            'think_v' => App::VERSION, //TP版本
            'phpversion' => phpversion(), //PHP版本
            'mysql_v' => $mysqlV[0]['version'],
            'is_linux' => 1
        );
        $sysInfo = php_uname('s');
        if (strpos($sysInfo, 'Windows') !== false) {
            //包含Windows
            $info['is_linux'] = 0;
        }
        $uid = $this->ADMIN_INFO['admin_id'] ?? 0;
        return $this->fetch('indexpage');
    }

    /**
     * 获取Windows Cpu、内存情况
     * @return array
     */
    public function getSysStatic()
    {
        $cpu = $memory = 0;
        if (PHP_OS == 'WINNT') {
            $serSys = new SystemInfoServer();
            $cpu = $serSys->getCpuUsage();
            $memory = $serSys->getMemoryUsage();
        } else {
            
        }

        //Array ( [TotalVisibleMemorySize] => 8305212 [FreePhysicalMemory] => 2753916 [usage] => 67 )
        return ['cpu' => $cpu, 'memory' => $memory];
    }

    /**
     * 获取Linux Cpu、内存使用情况
     * @return array
     */
    private function getSysLinux()
    {
        //获取某一时刻系统cpu和内存使用情况
        $fp = popen('top -b -n 2 | grep -E "^(Cpu|Mem|Tasks)"', "r");
        $rs = "";
        while (!feof($fp)) {
            $rs .= fread($fp, 1024);
        }
        pclose($fp);
        $sys_info = explode("\n", $rs);
        $tast_info = explode(",", $sys_info[3]); //进程 数组
        $cpu_info = explode(",", $sys_info[4]); //CPU占有量 数组
        $mem_info = explode(",", $sys_info[5]); //内存占有量 数组
        //正在运行的进程数
        $tast_running = trim(trim($tast_info[1], 'running'));

        //CPU占有量
        $cpu_usage = trim(trim($cpu_info[0], 'Cpu(s): '), '%us'); //百分比
        //内存占有量
        $mem_total = trim(trim($mem_info[0], 'Mem: '), 'k total');
        $mem_used = trim($mem_info[1], 'k used');
        $mem_usage = round(100 * intval($mem_used) / intval($mem_total), 2); //百分比

        /* 硬盘使用率 begin */
        $fp = popen('df -lh | grep -E "^(/)"', "r");
        $rs = fread($fp, 1024);
        pclose($fp);
        $rs = preg_replace("/\s{2,}/", ' ', $rs); //把多个空格换成 “_”
        $hd = explode(" ", $rs);
        $hd_avail = trim($hd[3], 'G'); //磁盘可用空间大小 单位G
        $hd_usage = trim($hd[4], '%'); //挂载点 百分比
        /* 硬盘使用率 end */

        //检测时间
        $fp = popen("date +\"%Y-%m-%d %H:%M\"", "r");
        $rs = fread($fp, 1024);
        pclose($fp);
        $detection_time = trim($rs);

        return ['cpu_usage' => $cpu_usage, 'mem_usage' => $mem_usage, 'hd_avail' => $hd_avail, 'hd_usage' => $hd_usage, 'tast_running' => $tast_running, 'detection_time' => $detection_time];
    }

}
