<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------
// 应用公共文件

use think\facade\Env;

function getCell($key) {
    $cells = config($key);
    if (empty($cells)) {
        return '';
    }
    foreach ($cells as &$cell) {
        !empty($cell['title']) && $cell['title'] = lang($cell['title']);
    }
    return json_encode($cells);
}

function langCommon($key) {
    $controller = request()->controller();
    if (strpos($controller, '.') !== false) {
        $controller = explode('.', $controller);
        $controller = end($controller);
    }
//    return lang($key, [lang($controller)]);
    return lang($key, ['']);
}

/**
 * 可以统计中文字符串长度的函数
 * @param $str 要计算长度的字符串
 * @param $type 计算长度类型，0(默认)表示一个中文算一个字符，1表示一个中文算两个字符
 *
 */
function abslength($str) {
    if (empty($str)) {
        return 0;
    }
    if (function_exists('mb_strlen')) {
        return mb_strlen($str, 'utf-8');
    } else {
        preg_match_all("/./u", $str, $ar);
        return count($ar[0]);
    }
}

/*
 *  秒转日期格式
 * */

function secsToTime($secs) {
    $h = floor($secs / 3600);
    $secs = $secs - $h * 3600;
    $m = floor($secs / 60);
    $secs = $secs - $m * 60;
    $s = $secs;
    if ($h < 10) {
        $h = '0' . $h;
    }
    if ($m < 10) {
        $m = '0' . $m;
    }
    if ($s < 10) {
        $s = '0' . $s;
    }
    return $h . ":" . $m . ":" . $s;
}

/**
 * 秒转天 小时
 * @param type $secs
 * @return string
 */
function secsToStr($secs) {
    $r = '';
    if ($secs >= 86400) {
        $days = floor($secs / 86400);
        $secs = $secs % 86400;
        $r = $days . '天';
    }

    if ($secs >= 3600) {
        $hours = floor($secs / 3600);
        $secs = $secs % 3600;
        $r .= $hours . '小时';
    }
    if (empty($r)) {
        $r = '0小时';
    }

    return $r;
}

/*
 * 身份证号码验证
 * @param $card_id 身份证号
 * @return bool 返回 True Flase
 */

function check_id_number($card_id) {
    $card_id = strtoupper($card_id);
    // 只能是18位
    if (strlen($card_id) != 18) {
        return false;
    }
    // 取出本体码
    $idcard_base = substr($card_id, 0, 17);
    // 取出校验码
    $verify_code = substr($card_id, 17, 1);
    // 加权因子
    $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
    // 校验码对应值
    $verify_code_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
    // 根据前17位计算校验码
    $total = 0;
    for ($i = 0; $i < 17; $i++) {
        $total += substr($idcard_base, $i, 1) * $factor[$i];
    }
    // 取模
    $mod = $total % 11;
    // 比较校验码
    if ($verify_code == $verify_code_list[$mod]) {
        return true;
    } else {
        return false;
    }
}

/**
 * @desc 不四舍五入截取浮点型字符串
 * @author 5058
 * @param float $f 金额含小数点
 * @param int $len 默认为4个小数点
 * @param bool $is_round 是否四舍五入 默认为false
 * @return string 返回具体结果
 */
function pointTwonum($f = '0.0000', $len = 2, $is_round = false) {
    if ($is_round) {
        //四舍五入版
        return number_format($f, $len);
    }

    //验证是否为int、float等类型
    if (!preg_match('/^(-?\d+)(\.\d+)?$/', $f)) {
        $str = '';
        for ($i = 0; $i < $len; $i++) {
            $str .= '0';
        }
        return '0.' . $str;
    }

    $loss = '';
    if ($f > -1 && $f < 0) {
        $loss = '-';
    }
    //小数点(.)有包含计算进去,所以程序长度需加1
    $len += 1;
    $tmpInt = (int) $f;
    $str = strstr($f, '.');
    if (!$str) {
        if ((int) $tmpInt != $f) {
            return $f;
        }
    }
    if (strlen($str) < $len) {
        $num = $len - strlen($str);
        //$str .= str_repeat("0",$num);
        for ($i = 0; $i < $num; $i++) {
            $str .= '0';
        }
    }

    //再次判断是否有包含小数点,预防$f为int的情况
    $str_temp = strstr($str, '.');
    if (!$str_temp) {
        $str = '.' . $str;
    }
    $str = substr($str, 0, $len);
    if ($str == '.') {
        $str = '';
    }
    return $loss . $tmpInt . $str;
}

//中文字符替换
function mstrReplace($str, $start = 0, $sLen = 0, $to = '*') {
    $strLen = mb_strlen($str, 'utf-8');
    if ($strLen <= $start) {
        return $str;
    } else {
        $strT = utf8Substr($str, 0, $start);
        $fLen = $strLen - $start - $sLen;
        $strF = '';
        if ($fLen <= 0) {
            $sLen = $strLen - $start;
        } else {
            $strF = utf8Substr($str, $start + $sLen, $fLen);
        }
        $strR = '';
        for ($i = 0; $i < $sLen; $i++) {
            $strR = $strR . "*";
        }
        return $strT . $strR . $strF;
    }
}

/**
 * 截取字符串(支持汉字)
 * @param type $str 字符串
 * @param type $site 起始位置
 * @param type $len 长度
 * @return str 
 */
function utf8Substr($str, $start, $len) {
    $str = preg_replace('#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,' . $start . '}' . '((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,' . $len . '}).*#s', '$1', $str);
    return $str;
}

/**
 * 生成/验证【图片/短信】验证码
 * @param string $code_session_key 验证码生成的session key【必填】
 * @param int $type 1图片验证码 2短信验证码
 * @param int $act 1生成 2验证
 * @param array $param['reset'] true 验证成功重置session， false不重置
 * @param array $param['verify_code'] 验证码
 * @param array $param['verify_id'] 同$code_session_key
 */
function verifycode($code_session_key, $type = 1, $act = 1, $param = []) {
    if (!$code_session_key) {
        return false;
    }
    //验证验证码
    if ($act == 2) {
        if (!$param['verify_code']) {
            return false;
        }
        $param['verify_id'] = isset($param['verify_id']) ? $param['verify_id'] : $code_session_key;
        $config['reset'] = isset($param['reset']) ? $param['reset'] : TRUE;
        $vcode = new \org\Verify($config);
        if (!$vcode->check($param['verify_code'], $param['verify_id'])) {
            return false;
        }
        return true;
    } else {
        if ($type == 1) {
            //生成图片验证码
            $config = [];
            $config['fontSize'] = isset($param['fontSize']) ? $param['fontSize'] : 14;
            $config['useCurve'] = isset($param['useCurve']) ? $param['useCurve'] : TRUE;
            $config['useNoise'] = isset($param['useNoise']) ? $param['useNoise'] : FALSE;
            $config['imageH'] = isset($param['height']) ? $param['height'] : 32;
            $config['imageW'] = isset($param['width']) ? $param['width'] : 100;
            $config['length'] = isset($param['length']) ? $param['length'] : 4;
            $vcode = new \org\Verify($config);
            $param['verify_id'] = isset($param['verify_id']) ? $param['verify_id'] : $code_session_key;
            return $vcode->entry($param['verify_id']);
        } else {
            //生成短信验证码
            $config = [];
            $vcode = new \org\Verify($config);
            $data['sms_code'] = $vcode->smscode($code_session_key);
            $sess = $vcode->getSmsSession($code_session_key);
            $data['send_num'] = $sess['send_num'];  //发送次数
            return $data;
        }
    }
}

/**
 * POST 模拟提交
 * @param string $url
 * @param array $postFields
 * @param array $setHeader
 * @return mixed
 */
function post_curl($url = '', $postFields = [], $setHeader = []) {
    if (!function_exists('curl_init')) {
        exit('php.ini php_curl must is Allow! ');
    }
    if (empty($url)) {
        exit('URL地址不能为空! ');
    }
    $url_ary = parse_url($url);
//    dump($url_ary);exit;
    $ch = curl_init();
    if ('https' == $url_ary['scheme']) {
        curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    }
//    dump($setHeader);
    if (!empty($setHeader)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $setHeader);
    }

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FAILONERROR, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    if (is_array($postFields) && 0 < count($postFields)) {
        $postBodyString = "";
        foreach ($postFields as $k => $v) {
            $postBodyString .= "$k=" . urlencode($v) . "&";
        }
        unset($k, $v);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, substr($postBodyString, 0, -1));
    } elseif ($postFields) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    }
    $reponse = curl_exec($ch);
    if ($reponse === false) {
        exit(curl_error($ch));
    } else {
        $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (200 !== $httpStatusCode) {
            if ($setHeader) {
                return $reponse;
            } else {
                exit($reponse . '&nbsp;code:' . $httpStatusCode);
            }
        }
    }
    curl_close($ch);
    return $reponse;
}

/**
 * 记录日志
 * @param  [type] $uid         [用户id]
 * @param  [type] $log_type    1.后台登录  2.系统管理 3.红包管理 4.交易管理 5.鲤鱼夺宝
 * @param  [type] $description [描述]
 * @param  [type] $status      [状态] => 1 成功 2 失败
 * @return [type]              [description]
 * @throws \think\Exception
 */
function adminlog($admin_id = 0, $log_type = 1, $description = '', $status = 1) {
    if (empty($admin_id)) {
        return false;
    }
    $admin_info = session('admin_info');
    $data['admin_id'] = $admin_id;
    $data['admin_name'] = $admin_info['admin_name'] ?: '';
    $data['log_type'] = $log_type;
    $data['description'] = $description;
    $data['state'] = $status;
    $data['ip'] = request()->ip();
    $data['c_time'] = time();
    $modLogSys = new \app\common\model\admin\SysLogModel();
    return $modLogSys->insert($data);
    //return \think\Db::connect()->name('log_sys_admin')->insert($data);
}

/**
 * 明文密码 + 系统秘钥 + 加密盐后的密码返回出去 1152
 * @param string $pwd 密码
 * @param string $auth_key 系统配置秘钥
 * @param string $pwd_key 加密盐
 * @return string
 */
function retPwd($pwd = '', $auth_key = '', $pwd_key = '') {
    return md5(md5(md5($pwd) . $auth_key) . md5($pwd_key));
}

/**
 * 加密盐 
 * @param string $password
 * @return string
 */
function newPwd($password) {
    if (!empty($password)) {
        $res['pwd_key'] = myRand(6);     //生成随机码
    }
    $res['pre_pwd'] = $password;     //原PWD
    $password = $password . md5($res['pwd_key']);
    $res['password'] = md5($password);                    //加密过的pwd  算法: （6位随机码md5 1次 + 原pwd） 之后 再md5 1次
    return $res;
}

/* * 密码验证
 * @param  $password  string 原始PWD
 * @param  $pwd_key  string pwd_key
 * @param  $pwmd5  string 数据库存的加密密码
 * @return bool 返回 True Flase
 */

function CheckPwd($password, $pwd_key, $pwmd5) {
    $newpw = getPwd($password, $pwd_key);
    if ($newpw == $pwmd5) {
        return True;
    }
    return false;
}

/**
 * 获取加密后的密码 算法: （6位随机码md5 1次 + 原pwd） 之后 再md5 1次
 * @param  $password string 原始PWD
 * @param  $pwd_key string pwd_key
 * @return string
 */
function getPwd($password, $pwd_key) {
    $password = $password . md5($pwd_key);
    $Pwmd5 = md5($password);
    return $Pwmd5;
}

/**
 * 生成十六进制随机码  1152
 * @param int $n
 * @return string
 */
function myRand($n = 0) {
    $num = '';
    //生成16进制随机码
    for ($i = 0; $i < $n; $i++) {
        @$num .= dechex(rand(0, 15));
    }
    return $num;
}

/**
 * 获取分类 前后台都有用到
 * @param $cate
 * @param string $lefthtml
 * @param int $pid
 * @param int $lvl
 * @param int $leftpin
 * @return array
 */
function rule($cate, $lefthtml = '— — ', $pid = 0, $lvl = 0, $leftpin = 0) {
    $arr = array();
    foreach ($cate as $val) {
        if ($val['parent_id'] == $pid) {
            $val['lvl'] = $lvl + 1;
            $val['leftpin'] = $leftpin + 0; //左边距
            $val['lefthtml'] = str_repeat($lefthtml, $lvl);
            $arr[] = $val;
            $arr = array_merge($arr, rule($cate, $lefthtml, $val['id'], $lvl + 1, $leftpin + 20));
        }
    }
    return $arr;
}

/**
 * 公共返回数据
 * @param string $msg
 * @param int $code 1001失败 1000成功
 * @param array $data
 */
function returnPubData($msg = '数据请求失败', $code = '1001', $data = [], $url_code = 0) {
    $data_arr = [
        'code' => $code,
        'data' => $data,
        'msg' => $msg,
        'url_code' => $url_code,
    ];
    return $data_arr;
}

/**
 * 判断手机号码格式是否正确
 * @param $mobilephone
 */
function is_mobilephone($mobilephone) {
    return strlen($mobilephone) == 11 && preg_match("/^13[0-9]{1}[0-9]{8}$|14[0-9]{1}[0-9]{8}$|15[0-9]{1}[0-9]{8}$|18[0-9]{1}[0-9]{8}|16[0-9]{1}[0-9]{8}$|17[0-9]{1}[0-9]{8}$|19[0-9]{1}[0-9]{8}$/", $mobilephone);
}

/**
 * 数组格式化输出 - 
 * @param type $arr 数组 必选
 * @param type $inputKey 要输出的key 必选
 * @param type $ouputKey 要输出的key改变key值 可选
 * @param type $keyName 布尔true 保持原有key输出  字符串(应与$arr字段名)  则以该字段名的值为下标
 * @return boolean
 */
function arrForm($arr = [], $inputKey = [], $ouputKey = null, $keyNmae = false) {
    $state = 1;
    if (empty($arr)) {
        return [];
    }
    if (is_array($ouputKey)) {
        if (count($inputKey) == count($ouputKey)) {
            $state = 2;
        }
    }
    foreach ($arr as $key => $val) {
        $temp = [];
        foreach ($inputKey as $k => $v) {
            if ($state == 1) {
                $temp[$v] = $val[$v];
            } else {
                if (is_array(explode('|', $ouputKey[$k]))) {
                    foreach (explode('|', $ouputKey[$k]) as $exv) {
                        $temp[$exv] = $val[$v];
                    }
                } else {
                    $temp[$ouputKey[$k]] = $val[$v];
                }
            }
        }
        if (is_bool($keyNmae) && $keyNmae == true) {//布尔 
            $result[$key] = $temp;
        } elseif (is_string($keyNmae)) {
            $result[$val[$keyNmae]] = $temp;
        } else {
            $result[] = $temp;
        }
    }
    return $result;
}

function file_get_contents_ext($url, $secondes = 60) {
    //404页面的可以特殊处理；
    $opts = array(
        'http' => array(
            'method' => "GET",
            'timeout' => $secondes,
        )
    );
    $ip = i2c_realip();
    if (!empty($ip)) {
        $iplong = $ip;
        $iplong || $iplong = 0;
        if (strstr($url, '?')) {
            $url .= '&iplong=' . $iplong;
        } else {
            $url .= '?iplong=' . $iplong;
        }
    }
    $context = stream_context_create($opts);
    $resp = file_get_contents($url, false, $context);
    return $resp;
}

/**
 * 获取客户端IP地址[已集成 CDN获取底层用户IP]
 *
 * @return 返回IP地址
 */
function i2c_realip() {
    $ip = FALSE;
    if (isset($_SERVER["HTTP_CDN_SRC_IP"])) {
        return $_SERVER["HTTP_CDN_SRC_IP"];
    }
    // If HTTP_CLIENT_IP is set, then give it priority
    if (!empty($_SERVER ["HTTP_CLIENT_IP"])) {
        $ip = $_SERVER ["HTTP_CLIENT_IP"];
    }
    // User is behind a proxy and check that we discard RFC1918 IP addresses
    // if they are behind a proxy then only figure out which IP belongs to the
    // user.  Might not need any more hackin if there is a squid reverse proxy
    // infront of apache.
    if (!empty($_SERVER ['HTTP_X_FORWARDED_FOR'])) {

        // Put the IP's into an array which we shall work with shortly.
        $ips = explode(", ", $_SERVER ['HTTP_X_FORWARDED_FOR']);
        if ($ip) {
            array_unshift($ips, $ip);
            $ip = FALSE;
        }

        for ($i = 0; $i < count($ips); $i ++) {
            // Skip RFC 1918 IP's 10.0.0.0/8, 172.16.0.0/12 and
            // 192.168.0.0/16
            if (!preg_match('/^(?:10|172\.(?:1[6-9]|2\d|3[01])|192\.168)\./', $ips [$i])) {
                if (version_compare(phpversion(), "5.0.0", ">=")) {
                    if (ip2long($ips [$i]) != false) {
                        $ip = $ips [$i];
                        break;
                    }
                } else {
                    if (ip2long($ips [$i]) != - 1) {
                        $ip = $ips [$i];
                        break;
                    }
                }
            }
        }
    }
    // Return with the found IP or the remote address
    return ($ip ? $ip : $_SERVER ['REMOTE_ADDR']);
}

/**
 * 判断当前是否公司内部IP访问
 *
 * @param string $ipaddress
 * @return boolen
 */
function is_internal_ip($ipaddress = '') {
    if (!$ipaddress) {
        $ipaddress = i2c_realip();
    }
    if (!$ipaddress) {
        return false;
    }
    $internal_ips = config('public.internal_ip');
    if (is_array($internal_ips) && in_array($ipaddress, $internal_ips)) {
        return true;
    }
    return false;
}

/*
 * 订单编号生成
 * @return $orderSn 订单编号 
 */

function getOrderSn($rand_num = 6) {
    $orderSn = time() . random($rand_num);
    return $orderSn;
}

/**
 * 产生随机字符串
 *
 * @param    int        $length  输出长度
 * @param    string     $chars   可选的 ，默认为 0123456789
 * @return   string     字符串
 */
function random($length, $chars = '0123456789') {
    $hash = '';
    $max = strlen($chars) - 1;
    for ($i = 0; $i < $length; $i++) {
        $hash .= $chars[mt_rand(0, $max)];
    }
    return $hash;
}

function desData($des_data, $key) {
    if (empty($key)) {
        return $des_data;
    }

    return $des_data[$key] ?? '';
}

/**
 * POST 模拟提交
 * @param string $url
 * @param array $postFields
 * @param array $setHeader
 * @return mixed
 */
function curl($url, $postFields = null, $setHeader = null) {
    if (!function_exists('curl_init')) {
        exit('php.ini php_curl must is Allow! ');
    }
    $url_ary = parse_url($url);
    $ch = curl_init();
    if ('https' == $url_ary['scheme']) {
        curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    }
    if ($setHeader) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $setHeader);
    }

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FAILONERROR, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    if (is_array($postFields) && 0 < count($postFields)) {
        $postBodyString = "";
        foreach ($postFields as $k => $v) {
            $postBodyString .= "$k=" . urlencode($v) . "&";
        }
        unset($k, $v);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, substr($postBodyString, 0, -1));
    } elseif ($postFields) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    }
    $reponse = curl_exec($ch);
    if ($reponse === false) {

        exit(curl_error($ch));
    } else {

        $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        //print_r($httpStatusCode);
        if (200 !== $httpStatusCode) {
            if ($setHeader) {
                return $reponse;
            } else {
                return $reponse;
                exit($reponse . '&nbsp;code:' . $httpStatusCode);
            }
        }
    }
    curl_close($ch);
    return $reponse;
}

/**
 * 二进制 转 字符串10进制 例如0011001 变成 1,8,16
 * @param type $platform
 * @return type
 */
function bittobcd($platform) {
    $arr = [];
    $str = '';
    $len = strlen($platform);

    for ($i = 0; $i < $len; $i++) {
        if (substr($platform, $len - 1 - $i, 1) > 0) {
            $str .= (substr($platform, $len - 1 - $i, 1) << $i) . ',';
        }
    }
    $str = trim($str, ',');
    return $str;
}

/**
 * 补0
 * @param type $recommend_all
 * @param type $bit
 * @return type
 */
function padBit($recommend_all, $bit = 12) {

    if (is_array($recommend_all)) {
        $num = array_sum($recommend_all);
    } else {
        $num = $recommend_all;
    }
    return str_pad(decbin($num), $bit, 0, STR_PAD_LEFT);
}

//过滤字符串
function replace_specialChar($str) {
    $preg_str = "/\/|\~|\!|\@|\#|\\$|\%|\^|\&|\*|\(|\)|\_|\+|\{|\}|\:|\<|\>|\?|\[|\]|\,|\.|\/|\;|\'|\`|\-|\=|\\\|\|/";
    return preg_replace($preg_str, "", $str);
}

/**
 * 省份名称转换
 *
 * @param string $province 当$province值为空时，直接返回省份数组信息
 * @param int $type 1：'北京市'=>'北京'，2：'北京'=>'北京市'
 * @return string or array
 */
function replace_province($province, $type = 1) {
    $tmparr = array('北京' => '北京', '安徽省' => '安徽', '福建省' => '福建', '甘肃省' => '甘肃', '广东省' => '广东', '广西壮族自治区' => '广西', '贵州省' => '贵州', '海南省' => '海南', '河北省' => '河北', '河南省' => '河南', '黑龙江省' => '黑龙江', '湖北省' => '湖北', '湖南省' => '湖南', '吉林省' => '吉林', '江苏省' => '江苏', '江西省' => '江西', '辽宁省' => '辽宁', '内蒙古自治区' => '内蒙古', '宁夏回族自治区' => '宁夏', '青海省' => '青海', '山东省' => '山东', '山西省' => '山西', '陕西省' => '陕西', '上海' => '上海', '四川省' => '四川', '天津' => '天津', '西藏自治区' => '西藏', '新疆维吾尔自治区' => '新疆', '云南省' => '云南', '浙江省' => '浙江', '重庆' => '重庆', '香港特别行政区' => '香港', '澳门特别行政区' => '澳门', '台湾省' => '台湾', '其它' => '其他');
    if ($province) {
        if ($type == 2) {
            $tmparr = array_flip($tmparr);
        }
        return !empty($tmparr[$province]) ? $tmparr[$province] : $province;
    }
    return $tmparr;
}

/*
 * 判断是不是公司手机号
 * @param $tel 手机号
 * @return bool true 是，false 不是
 */

function isCompanyTel($tel) {
    return true;
    $telList = [18905077163, 15985821242, 15900976547, 18516691214, 18650571553, 18650571554, 18650571555, 13023977083, 18659264730, 15005034183, 13696516804, 13606097353, 18760187828, 13779987523, 15750739163, 13159200225, 18559221001, 18059878778, 13395998170, 15960263379, 18259087573, 13400975099, 18959263873, 15980909158, 15980822207, 18250763484, 15280230089, 15959659976, 13696970420, 13774507102, 15980928030, 18850223260, 18650176723, 15960818962, 13850090959, 18650571553, 18205925892, 15359557052, 15160052594, 13859803437, 18650421113, 18962513824, 17750603451, 13459256227, 18060075323, 18850170804, 13959255953, 13178200017, 15705987709, 18030271890, 13055243289, 15759563070, 13850750726, 13960263710, 13615057054, 13720894515, 15980609507, 13779962455, 15138661172, 13459246498, 18205952517, 15158585428, 13695022612, 13599681985, 13860179659, 13328320991, 15259538731, 13559477520, 13959249238, 15980892336, 15959271261, 18259260589, 15880285223, 15060785626, 13666015027, 15859216171, 15960371108, 13859338185, 13003992754, 18695764186, 13720870922];
    return in_array($tel, $telList) ? true : false;
}

/**
 * 阿里云上传单文件上传
 *  $file=request()->file('file');
 * $fileName 文件名
 * @param $file
 * @return 文件url|string
 */
function aliyuUploadFile($file = null, $fileName = '') {
    // 尝试执行
    try {
        if (empty($file)) {
            $file = request()->file('file');
        }
        $config = config('other.aliyun_oss'); //获取Oss的配置
        $file_info = $file->getInfo();
        //实例化对象 将配置传入
        $ossClient = new \OSS\OssClient($config['KeyId'], $config['KeySecret'], $config['Endpoint']);
        if (empty($fileName)) {
            //这里是有sha1加密 生成文件名 之后连接上后缀
            $fileName = sha1(date('YmdHis', time()) . uniqid()) . '.' . pathinfo($file_info['name'], PATHINFO_EXTENSION);
        }
        //执行阿里云上传
        $result = $ossClient->uploadFile($config['Bucket'], $fileName, $file_info['tmp_name']);
        return $result['info']['url'];
    } catch (OssException $e) {
        return $e->getMessage();
    }
}

/*
 * 获取链接中的参数值
 * */

function getUrlParm($url, $key = '') {

    if (empty($url)) {
        return '';
    }
    $data = parse_url(urldecode($url));
    parse_str($data['query'], $arr);

    if (empty($key)) {
        return $arr;
    }

    $return_value = '';
    if (count($arr) > 0) {
        foreach ($arr as $k => $v) {
            if ($k == $key) {
                $return_value = $v;
                break;
            }
        }
    }
    return $return_value;
}

function isUrl($str) {
    $pattern = "/^((https|http)?:\/\/)[^\s]+/"; //正则表达式
    if (preg_match($pattern, $str)) {
        return true;
    } else {
        return false;
    }
}

function http_request($url, $data = null) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    if (!empty($data)) {
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curl);
    curl_close($curl);
    return $output;
}

/*
 *   防止注入 js
 * */

function check_input_text($value) {
    if (empty($value)) {
        return $value;
    }
    // 去除反斜杠斜杠
    if (get_magic_quotes_gpc()) {
        $value = stripslashes($value);
    }
    //过滤掉html标签
    $value = strip_tags($value);
    return $value;
}

/*
 *  删除过期文件
 * */
function delExpireFile($dirfile_name, $expire_time = 0){
    try{
        $base_path = Env::get('root_path');
        $dir_file = $base_path."/wwwroot/uploads/pdf/{$dirfile_name}/";
        if(!file_exists($dir_file) || !is_dir($dir_file)){
            return false;
        }
        $p = scandir($dir_file);
        if(count($p) <= 2){
            return false;
        }
        foreach ($p as $k => $val){
            //排除目录中的.和..
            if($val !="." && $val !=".."){
                if (filectime($dir_file.$val)<(time()-$expire_time)){
                    unlink($dir_file.$val);
                }
            }
        }
    }catch (\Exception $e){
    }
    return true;
}

/*
 *  删除PDF
 * */
function delPdfFile($uid, $file_name){
    try{
        $base_path = Env::get('root_path');
        $file_path = $base_path."/wwwroot/uploads/pdf/{$uid}/{$file_name}.pdf";
        if(file_exists($file_path)){
            unlink($file_path);
        }
    }catch (\Exception $e){
    }
    return true;
}

/*
 *  单位换算
 *  cm => inch
 * */
function cmToInch($cm){
    return round($cm*0.3937,2);
}

/*
 *  重量单位换算
 *  kg => lbs
 * */
function kgToLbs($kg){
    return round($kg*2.2046);
}
function lbsToKg($lbs){
    return round($lbs*0.4536,2);
}


/*
 *  时间格式转化
 * */
function timeToFormat($date,$format=\DateTime::ATOM){
    $date = new \DateTime($date);
    return $date->format(\DateTime::ATOM);
}

/*
 *  内容过滤处理
 * */
function characterFilter($str){
    $str = preg_replace("/(\s+)|(　+)+/", " ", $str);//替代空格,换行,tab,中文空格
    $str = preg_replace( "/(^\s*)|(\s*$)/ ", "",$str);//去除首尾空格
    $str = preg_replace("/(\s+)/", " ", $str);//替换多个空格为一个空格
    return $str;
}

/*
 *  星期列表
 * */
function weekList(){
    $list = array("", "星期一", "星期二", "星期三", "星期四","星期五","星期六","星期日");
    return array_filter($list);
}

/*
 *  获取今日星期key
 * */
function getWeekKey($time = ''){
    if(empty($time)) $time = time();
    $w = date("w",$time);
    if($w==0) $w = 7;
    return $w;
}

/*
 *  获取ISO 8601 格式时间
 * */
function isoNowDate(){
    $dateTime = new \DateTime();
    $dateTime->setTimezone(new DateTimeZone('UTC'));
    // 格式化日期时间为 ISO 8601 格式，包括毫秒
    return $dateTime->format('Y-m-d\TH:i:s.u\Z');
}

/*
 *  生成用户唯一标识
 *  */
function createUserUniqid() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? "";
    $uniqid = uniqid();
    return md5($userAgent .$uniqid);
}

/*
 *  检测邮箱格式
 *  return bool  true:有效
 * */
function is_valid_email($email) {
    // 正则表达式用于验证邮箱格式
    $pattern = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';
    return preg_match($pattern, $email);
}