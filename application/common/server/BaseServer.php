<?php

/**
 * server基础类 young www.iaisng.com
 */

namespace app\common\server;

use think\Controller;
use think\facade\Env;
use think\facade\Request;

class BaseServer extends Controller {

    protected function initialize() {
//        echo 2;exit;
        //底层预埋 前端跨域请求处理
        parent::initialize();   
//             header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Methods:GET, HEAD, POST, PUT, DELETE, TRACE, OPTIONS, PATCH');
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
        header('Access-Control-Allow-Credentials:true');
    }  

    /**
     * 接口 - 数据请求成功
     * @param array $data
     * @param string $msg
     */
    protected function apiSuccess($data = [], $msg = '', $url_code = 0) {
        $data_arr = [
            'code' => 1000,
            'data' => $data,
            'msg' => $msg?:lang('request_success'),
        ];
        if (is_numeric($url_code)) {
            $data_arr['url_code'] = $url_code;
        } else {
            $data_arr['url'] = $url_code;
        }
        return json($data_arr);
    }

    /**
     * 接口 - 数据请求失败
     * @param array $data
     * @param string $msg
     */
    protected function apiError($msg = '', $data = [], $url_code = 0) {
        $data_arr = [
            'code' => 1001,
            'data' => $data,
            'msg' => $msg?:lang('request_fail'),
        ];
        if (is_numeric($url_code)) {
            $data_arr['url_code'] = $url_code;
        } else {
            $data_arr['url'] = $url_code;
        }
        return json($data_arr);
    }

    /**
     * mysql 加密
     * 双重君子锁，锁君子不锁小人
     * @param type $tel
     * @param type $key
     * @return type
     */
    public function mySqlEncode($tel, $key = 'wpyEncode') {
        $str = 'erghea !rzcgl(fgegbhccre(ova2urk(onfr64_qrpbqr(bcraffy_rapelcg($gry, "nrf-128-rpo", $xrl))))) ? fgegbhccre(ova2urk(onfr64_qrpbqr(bcraffy_rapelcg($gry, "nrf-128-rpo", $xrl)))) : "";';
        return eval(str_rot13($str));
    }

    /**
     * mysql 解密
     * 双重君子锁，锁君子不锁小人
     * @param type $enTel
     * @param type $key
     * @return type
     */
    public function mySqlDecode($enTel, $key = 'wpyEncode') {
        if (empty($enTel) || strlen($enTel) % 2 > 0) {
            return '';
        }
        $str = 'erghea !rzcgl(bcraffy_qrpelcg(onfr64_rapbqr(urk2ova($raGry)), "nrf-128-rpo", $xrl)) ? bcraffy_qrpelcg(onfr64_rapbqr(urk2ova($raGry)), "nrf-128-rpo", $xrl) : "";';
        return eval(str_rot13($str));
    }

    /**
     * 
     * @param type $type 1为激活，2为注册
     * @param type $param
     * @return type
     */
    public function aiclkLog($type = 1, $param = []) {
//        echo 1;exit;
        $where['type'] = 0;
        if ($param['platform'] == 1) {
            if (empty($param['idfa'])) {
                return;
            }
            $where['os'] = 1;
            $where['idfa'] = $param['idfa'];
        } elseif ($param['platform'] == 2) {
            if (empty($param['android_id'])) {
                return;
            }
            $where['os'] = 0;
            $where['androidid'] = $param['android_id'];
        }

//        $modLog = db('log_aiclk');
        if ($type == 1) {
            $where['jh'] = 1;
            $chk = db('log_aiclk')->where($where)->find();

            if (!empty($chk)) {
                return;
            }
            unset($where['jh']);

            $res = db('log_aiclk')->where($where)->order('id desc')->find();

            if (empty($res)) {
                return;
            }

            //这里写发起激活请求
            curl($res['callback_url'] . '&op2=0');

            db('log_aiclk')->where(['id' => $res['id']])->update(['jh' => 1]);
        } else {
            $res = db('log_aiclk')->where($where)->order('id desc')->find();
            if (empty($res)) {
                return;
            }
            //这里写发起激活请求
            curl($res['callback_url'] . '&op2=1');
            db('log_aiclk')->where(['id' => $res['id']])->setInc('reg');
            return true;
        }
    }

    public function getIpAddr($ipaddress) {
        header('Content-type: text/html; charset=UTF-8');
        $result = file_get_contents_ext("http://whois.pconline.com.cn/ipJson.jsp?callback=testJson&ip=" . $ipaddress);
        $result = iconv("GBK", "UTF-8", $result);
        $result = trim($result);
        $result = str_replace(array('if(window.testJson) {testJson(', ');}'), '', $result);
        $info = json_decode($result, 1);
        $info['pro'] = empty($info['pro']) ? '未知' : str_replace('省', '', replace_province($info['pro'], 1));
        $info['pro'] = empty($info['pro']) ? '未知' : str_replace('市', '', replace_province($info['pro'], 1));
        $info['city'] = empty($info['city']) ? '未知' : str_replace('市', '', replace_province($info['city'], 1));
        //统一数据返回格式
        $rtndata = [
            'province' => $info['pro'],
            'province_id' => $info['proCode'],
            'city' => $info['city'],
            'city_id' => $info['cityCode'],
        ];
        return $rtndata;
    }

    /*
     *  二维码
     *  $dir -- 文件夹名称
     *  $filename -- 二维码图片名称
     *   $value -- 二维码内容
     * */

    public function comQrcode($value, $filename = '', $dir = '', $is_r = 1) {

        require_once Env::get('root_path') . 'vendor/phpqrcode/phpqrcode.php';
        $errorCorrectionLevel = 'L';  //容错级别
        $matrixPointSize = 5;      //生成图片大小
        //生成二维码图片
        // 判断是否有这个文件夹  没有的话就创建一个
        //设置二维码文件名
        $saveFileName = 'qrcode';
        if (!is_dir($saveFileName)) {
            // 创建文件加
            mkdir($saveFileName);
        }

        if (!empty($dir)) {
            $saveFileName .= '/' . $dir;
            if (!is_dir($saveFileName)) {
                // 创建文件加
                mkdir($saveFileName);
            }
        }
        $filename = empty($filename) ? time() . rand(10000, 9999999) : $filename;
        $saveFileName .= '/' . $filename . '.png';
        //生成二维码
        \QRcode::png($value, $saveFileName, $errorCorrectionLevel, $matrixPointSize, 2);
        $image_data = chunk_split(base64_encode(fread(fopen($saveFileName, 'r'), filesize($saveFileName))));

        $domin = config('public.sites.mapi');
        $qrcode_url = $domin . '/' . $saveFileName;
        if ($is_r == 1) {
            $qrcode_url .= '?r=' . time();
        }
        return $qrcode_url;
    }

    /*
     *  转短链 https://api.d5.nz/apidetail/1.html
     * */

    public function shortUrlApi($long_url) {
        $url = 'https://d5.nz/shorten';
        $postFields['url'] = $long_url;
//        $postFields['type'] = 'direct';
        $res = http_request($url, $postFields);
        $res = json_decode($res, true);
        return $res['short'] ?? '';
    }

   
}
