<?php

/**
 * 后台登陆类
 * young https://www.iasing.com
 */

namespace app\common\server\admin;

use app\common\server\BaseServer;

class LoginServer extends BaseServer
{

    /**
     * 生成/验证【图片/短信】验证码
     * @param $code_session_key
     * @param int $type
     * @param int $act
     * @param array $param
     * @return bool
     */
    public function verifycode($code_session_key, $act = 1, $param = [])
    {
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

            $captcha = new \think\captcha\Captcha($config);
            if (!$captcha->check($param['verify_code'], $param['verify_id'])) {
                return false;
            }
            return true;
        } else {
            //生成图片验证码
            $config = [];
            $config['fontSize'] = isset($param['fontSize']) ? $param['fontSize'] : 18;
            $config['useCurve'] = isset($param['useCurve']) ? $param['useCurve'] : FALSE;
            $config['useNoise'] = isset($param['useNoise']) ? $param['useNoise'] : TRUE;
            $config['imageH'] = isset($param['height']) ? $param['height'] : 36;
            $config['imageW'] = isset($param['width']) ? $param['width'] : 120;
            $config['length'] = isset($param['length']) ? $param['length'] : 4;
            $param['verify_id'] = isset($param['verify_id']) ? $param['verify_id'] : $code_session_key;
            $captcha = new \think\captcha\Captcha($config);
            return $captcha->entry($param['verify_id']);
        }
    }

}
