<?php

namespace app\home\controller;

use app\common\server\BaseServer;
use Abraham\TwitterOAuth\TwitterOAuth;
class Test extends BaseServer
{
    /*
    *  生成海报图
    * */
    public function index()
    {
        require_once ROOT_PATH. '/vendor/abraham/twitteroauth/autoload.php';
       $consumerKey='xTofIHpwtkxlBqXjmsU9fcDUL';
       $consumerSecret='aGa1Tt196FSr88Q56mUUwoNJsSkEyCjPOg3r0R2m2r9rwK9FAC';
       $accessToken='1644228822114664449-8IycWJ9T26s2FwlRQ5YQ5MT5rtZYFY';
       $accessTokenSecret='FKPdmS8FxJ566hDygwcZr0nw0ed7pcQox1aUNLktAcJeT';
       $connect=new TwitterOAuth($consumerKey,$consumerSecret,$accessToken,$accessTokenSecret);
       $content=$connect->get('users/me');
       dump($content);
    }


}
