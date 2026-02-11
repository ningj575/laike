<?php

/**
  抖音开发server
 */

namespace app\common\server\laike;

use app\common\server\BaseServer;
use app\common\model\admin\SysConfigModel;
class DouyinServer extends BaseServer {

    private $client_key;
    private $client_secret;

    public function __construct() {
        $config=(new SysConfigModel())->sysConfig();   
        if(empty($config['app_id'])||empty($config['app_secret'])){
            throw new \Exception('请先配置应用信息');
        }        
        $this->client_key = $config['app_id'];
        $this->client_secret = $config['app_secret'];
    }

    /*
     * 生活消息推送验签
     * https://partner.open-douyin.com/docs/resource/zh-CN/local-life/develop/OpenAPI/preparation/massages.push
     */

    public function verifySignature($signature, $body) {
        // 将appSecret与body内容拼接后进行sha1哈希处理
        $sign = sha1($this->client_secret . $body);
        // 验证签名是否匹配
        if ($sign !== $signature) {
            return false;
        }
        // 如果验签通过，则继续处理业务逻辑
        return true;
    }

    /*
     * spi验签
     * https://partner.open-douyin.com/docs/resource/zh-CN/local-life/develop/OpenAPI/preparation/signruleintroduce
     */

    public function spiSignature($query, $body, $signature) {
        ksort($query);
        $str = $this->client_secret;
        foreach ($query as $key => $value) {
            if ($key === 'sign') {
                continue;
            }
            $str = $str . '&' . $key . '=' . $value;
        }
        $signStr = $str . '&http_body=' . $body;
        $sign = hash('sha256', $signStr);
        if ($sign !== $signature) {
            return false;
        }
        return true;
    }

    /*
     * 获取token
     * https://partner.open-douyin.com/docs/resource/zh-CN/local-life/develop/OpenAPI/preparation/client_token
     */

    public function getToken() {
        $redis = new \think\cache\driver\Redis();
        $redis_key = 'douyin:access_token';
        $access_token = $redis->get($redis_key);
        if ($access_token) {
            return $access_token;
        }
        $url = 'https://open.douyin.com/oauth/client_token/';
        $header = [
            'content-type: application/json',
        ];
        $post_data = json_encode([
            'client_secret' => $this->client_secret,
            'grant_type' => 'client_credential',
            'client_key' => $this->client_key,
        ]);
        $json_res = curl($url, $post_data, $header);
        $res = json_decode($json_res, true);
        if (!empty($res['message']) && $res['message'] == 'success') {
            $redis->set($redis_key, $res['data']['access_token'], 5400);
            return $res['data']['access_token'];
        } else {
            return $res['message'];
        }
    }

    /*
     * 商品查询接口
     * https://partner.open-douyin.com/docs/resource/zh-CN/local-life/develop/OpenAPI/general-capabilities/product-query/online.query
     * $account_id 商家id
     */

    public function queryProduct($account_id,$cursor='') {
        $url = 'https://open.douyin.com/goodlife/v1/goods/product/online/query/';
        $header = [
            'content-type: application/json',
            'access-token:' . $this->getToken()
        ];
        $post_data = json_encode([
            'account_id' => $account_id,
            'goods_creator_type'=>1,
            'goods_query_type'=>3,
            'count'=>50,
            'cursor'=>$cursor
        ]);
        $json_res = curl($url, $post_data, $header);
        $res = json_decode($json_res, true);
        return $res;
    }

    /*
     * 旅行社交易确认接单接口
     * https://partner.open-douyin.com/docs/resource/zh-CN/local-life/develop/OpenAPI/JiuLv/vacation/presale_coupon/travel-agency-confirm/travel-order-confirm-api
     * $source_order_id 预约订单归属的预售订单ID
     * $order_id 抖音侧订单号（预约订单号）
     * $confirm_result 1：接单 2：拒单
     */

    public function order_confirm($source_order_id, $order_id, $confirm_info) {
        $url = 'https://open.douyin.com/goodlife/v1/trip/trade/travelagency/order/confirm/';
        $header = [
            'content-type: application/json',
            'access-token:' . $this->getToken()
        ];
        $confirm_data=[
            'order_id' => $order_id, //预约订单号
            'source_order_id' => $source_order_id, //预约订单归属的预售订单ID
            'confirm_info' => $confirm_info
        ];
        $post_data = json_encode($confirm_data);
        $json_res = curl($url, $post_data, $header);
        $res = json_decode($json_res, true);
        return $res;
    }

    /*
     * 查询订单商品快照的poi
      https://partner.open-douyin.com/docs/resource/zh-CN/local-life/develop/OpenAPI/JiuLv/vacation/presale_coupon/poi-query/query-order-snapshot
     */

    public function poiQuery($account_id,$order_id) {
        $url = 'https://open.douyin.com/goodlife/v1/trip/trade/travelagency/order/poi/query/';
        $header = [
            'content-type: application/json',
            'access-token:' . $this->getToken()
        ];
        $post_data = json_encode([
            'account_id' => $account_id,
            'order_id' => $order_id,
        ]);
        $json_res = curl($url, $post_data, $header);
        $res = json_decode($json_res, true);
        return $res;
    }

    /*
     * 参数解密
     */

    public function decryptAES($data) {
        if(empty($data)){
            return $data;
        }
        $secret = self::fillSecret($this->client_secret);
        $key = self::cutSecret($secret);
        $iv = substr($key, 16);
        $encrypted = base64_decode($data); // 先用base64解密
        $decrypted = openssl_decrypt(
                $encrypted, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv
        );
        return $decrypted;
    }

    private static function cutSecret($secret) {
        if (strlen($secret) <= 32) {
            return $secret;
        }
        $rightCnt = (int) ((strlen($secret) - 32) / 2);
        $leftCnt = strlen($secret) - 32 - $rightCnt;
        return substr($secret, $leftCnt, 32);
    }

    private static function fillSecret($secret) {
        if (strlen($secret) >= 32) {
            return $secret;
        }
        $rightCnt = (int) ((32 - strlen($secret)) / 2);
        $leftCnt = 32 - strlen($secret) - $rightCnt;
        $sb = str_repeat('#', $leftCnt);
        $sb .= $secret;
        $sb .= str_repeat('#', $rightCnt);
        return $sb;
    }

}
