<?php

namespace app\admin\controller\system;

use app\common\server\FileServer;

class Upload extends FileServer
{
    //初始化
    public function initialize()
    {
        $admin_info = session('admin_info');
        if(empty($admin_info)){
            $this->redirect('/login.html');
        }
        parent::initialize();
    }

    //上传文件 - 附件
    public function upload_file()
    {
        $file = request()->file('file');
        echo $this->upload_file_sup($file);
        exit;
    }

    /**
     * 上传 - 默认图片上传
     * @return \think\response\Json
     */
    public function upload(){
        $file = request()->file('file');        
        $staticUrl = config('public.sites.static');
        $imgUrl_res = $this->upload_image($file);
        if($imgUrl_res['code'] != 1000){
            return $this->apiError($imgUrl_res['msg']);
        }
        $imgUrl = $imgUrl_res['data'];
        if (strstr($imgUrl, '\\')){
            $imgUrl = str_replace('\\','/',$imgUrl);
        }      

        $fullUrl = $staticUrl."/".$imgUrl;
        $data = [
            'src' => $imgUrl,
            'fullSrc' => $fullUrl,
        ];
        return $this->apiSuccess($data);
    } 
    /**
     * 上传视频
     * @return \think\response\Json
     */
    public function uploadVideo(){
        $file = request()->file('file');

        $imgUrl = aliyuUploadFile($file);
        if (strstr($imgUrl, '\\')){
            $imgUrl = str_replace('\\','/',$imgUrl);
        }
        $data = [
            'src' => $imgUrl,
        ];
        return $this->apiSuccess($data);
    }



    /**
     * 上传 - 默认图片上传
     * @return \think\response\Json
     */
    public function uploadLayEdit(){
        $file = request()->file('file');
        $staticUrl = config('public.sites.static');
        $imgUrl = $this->upload_image($file);
        if (strstr($imgUrl, '\\')){
            $imgUrl = str_replace('\\','/',$imgUrl);
        }
        $fullUrl = $staticUrl.DS.$imgUrl;
        $data = [
            'code' => 0,
            'msg' => '图片上传成功',
            'data' => ['src'=>$fullUrl,'title'=>''],
        ];
        return json($data);
    }


    /**
     * 删除文件
     * @return \think\response\Json
     */
    public function delete(){
        $file_url = input('file_url');
        if(strpos($file_url,'http') !== false){
            return $this->apiSuccess([],'网络图片无法物理删除！');
        }
        $result = $this->delete_file($file_url);
        return $this->apiSuccess(['result'=>$result],'删除文件成功');
    }

    //会员头像上传 - 只用于后台会员
    public function uploadface(){
        $file = request()->file('file');
        $info = $file->move($this->FULL_FILE_URL.'sys_face');
        if($info){
            $src = 'public'.DS.'uploads'.DS.'sys_face'.DS.$info->getSaveName();
            return $this->apiSuccess(['src'=>$src,'full_src'=>$this->STATIC_URL.DS.$src]);
        }else{
            echo $file->getError();
        }
        exit;
    }


}