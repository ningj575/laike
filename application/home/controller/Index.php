<?php

namespace app\home\controller;

use app\common\server\home\HomeServer;

class Index extends HomeServer
{

    private $user_uuid;
    public function __construct()
    {
        parent::__construct();
        $this->user_uuid = $this->getUserUuid();
    }

    public function index()
    {
        $ser = new \app\common\server\home\IndexServer();
        $this->assign("page_data", $ser->indexPageData($this->user_uuid));
        $my_prize = $ser->getMyPrize($this->user_uuid);
        $this->assign("my_prize", $my_prize);
        return $this->fetch('/index/newindex');
    }
    public function rule()
    {        
        return $this->fetch('/index/rule');
    }

    /*
     *  开奖
     * */
    public function openLottery(){

        if($this->request->isAjax()){
            $ser = new \app\common\server\home\IndexServer();
            $res = $ser->openLottery($this->user_uuid);
            if($res['code'] == 1000){
                return $this->apiSuccess($res['data']);
            }else{
                return $this->apiError($res['msg']);
            }
        }
    }

    /*
     *  领取奖品
     * */
    public function receivePrize(){

        if($this->request->isAjax()){
            $ser = new \app\common\server\home\IndexServer();
            $res = $ser->receivePrize($this->user_uuid,$this->request->post("email",""),$this->request->post("lottery_id",""));
            if($res['code'] == 1000){
                return $this->apiSuccess($res['data']);
            }else{
                return $this->apiError($res['msg']);
            }
        }
    }

}