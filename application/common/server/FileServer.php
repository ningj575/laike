<?php

/**
 * 文件操作基类
 * User: Tx1123
 * Date: 2018/11/2
 */

namespace app\common\server;

use think\File;

class FileServer extends BaseServer
{

    public $BASE_URL;               //基本文件地址
    protected $FULL_FILE_URL;       //正式完整文件地址
    protected $FULL_FILE_TEMP_URL;  //临时完整文件地址
    protected $FILE_TEMP_URL;       //临时相对文件地址
    protected $FILE_URL;            //正式相对文件地址
    protected $IMAGE_URL;           //正式相对图片地址
    protected $IMAGE_TEMP_URL;      //临时相对图片地址
    public $STATIC_URL;

    protected function initialize()
    {
        $this->BASE_URL = ROOT_PATH .'wwwroot' . DS . 'static' . DS;
        $this->FULL_FILE_URL = ROOT_PATH . 'public' . DS . 'uploads' . DS;
        $this->FILE_URL = 'uploads' . DS;
        $this->IMAGE_URL = 'uploads' . DS . 'images' . DS;
        $this->IMAGE_TEMP_URL = 'uploads' . DS . 'images_temp' . DS;
        $this->FULL_FILE_TEMP_URL = ROOT_PATH . 'public' . DS . 'app_temp' . DS;
        $this->FILE_TEMP_URL = DS . 'app_temp' . DS;

        //图片地址域名
        $this->STATIC_URL = config('public.sites.static');

        parent::initialize();
    }

    /**
     * 获取文件信息
     * @param string $iamge_url
     * @return array|string
     */
    public function getImageInfo($iamge_url = '')
    {
        $filename = $this->BASE_URL . $iamge_url;
        if (!file_exists($filename)) {
            return returnPubData('上传文件失败');
        }
        $mod_file = new File($filename);
        return $mod_file->getInfo();
    }

    /**
     * 移动图片(多张图片) - 公用
     * @param array $form_arr 图片来源，多张图片
     * @param int $type  1.图片地址  2.文件地址
     * @param string $to_dir  自定义-目标路径
     * @return array
     */
    public function move_file_arr($form_arr = [], $type = 1, $to_dir = '')
    {
        if (empty($form_arr)) {
            return ['code' => 1001, 'msg' => '源文件地址不能为空！'];
        }
        if (!is_array($form_arr)) {
            $form_arr = array_filter(explode('||', $form_arr));
        }
        $to_url = '';
        $error_num = 0;
        foreach ($form_arr as $key => $form_url) {
            $is_continue = false;
            //当未指定目标目录时，进行自动转换
            if (empty($to_dir)) {
                //continue不能在switch中使用
                switch ($type) {
                    case 1:
                    case 2:
                        if (strstr($form_url, 'images_temp')) {
                            $to_url = str_replace('images_temp', 'images', $form_url);
                        }
                        if (strstr($form_url, 'file_temp')) {
                            $to_url = str_replace('file_temp', 'file', $form_url);
                        }
                        if (strstr($form_url, 'app_temp')) {
                            $to_url = str_replace('app_temp', 'app', $form_url);
                        }
                        if (empty($to_url)) {
                            $error_num++;
                            $is_continue = true;
                        }
                        break;
                }
            }
            if ($is_continue)
                continue;

            //源文件不存在
            if (!file_exists($this->BASE_URL . $form_url)) {
                $error_num++;
                continue;
            }
            //目标目录
            $target_dir = $target_url = '';
            if (!empty($to_dir)) {
                //目标路径(目录)
                $target_dir = $this->BASE_URL . $to_dir;
                $target_url = $to_dir . basename($form_url);
            } else {
                //具体文件路径 - 处理
                $target_dir = dirname($this->BASE_URL . $to_url);
                $target_url = $to_url;
            }
            if (!is_dir($target_dir)) {
                if (!mkdir($target_dir, 0755, true)) {
                    $error_num++;
                    continue;
                }
            }
            //echo $this->BASE_URL.$form_url,'<br>';
            //echo $this->BASE_URL.$target_url,'<br>';
            if ($this->BASE_URL . $form_url == $this->BASE_URL . $target_url) {
                continue;
            }
            //拷贝到新目录
            $result = copy($this->BASE_URL . $form_url, $this->BASE_URL . $target_url);

            //删除旧目录下的文件
//            unlink($this->BASE_URL . $form_url);
            $form_arr[$key] = $target_url;
        }
        return ['code' => 1000, 'msg' => '文件移动完成,其中失败' . $error_num . '个文件！', 'data' => $form_arr];
    }

    //验证图片路径是否正确
    public function chkImg($form_url)
    {
        if (!file_exists($this->BASE_URL . $form_url)) {
            return returnPubData('文件不存在');
        } else {
            return returnPubData('OK', 1000);
        }
    }

    /**
     * @param null $file
     * @return array
     */
    protected function upload_file_app($file = null)
    {
        if (!$file) {
            $file = request()->file('file');
        }
        //templates/public/app_temp目录下
        $info = $file->move($this->FULL_FILE_TEMP_URL);
        if ($info) {
            $data = [
                'url' => $this->FILE_TEMP_URL . $info->getSaveName(),
                'full_url' => $this->FULL_FILE_TEMP_URL . $info->getSaveName(),
                'info' => $info->getInfo(),
            ];
            return returnPubData('上传成功', 1000, $data);
        }
        return returnPubData('APK文件上传出错');
    }

    //文件上传 - 继承
    public function upload_file_sup($file = null)
    {
        if (!$file) {
            $file = request()->file('file');
        }
        $file_url = $this->FULL_FILE_URL . 'file_temp' . DS;
        $info = $file->move($file_url);
        if ($info) {
            return $this->FILE_URL . 'file_temp' . DS . $info->getSaveName();
        } else {
            return $file->getError();
        }
    }

    /**
     * 图片上传  - 继承
     * @param null $file 文件源
     * @return mixed|string
     */
    public function upload_image($file = null)
    {
        if (!$file) {
            $file = request()->file('file');
        }
        $info = $file->move($this->BASE_URL . $this->IMAGE_TEMP_URL);
        if ($info) {
            return returnPubData("success",1000,$this->IMAGE_TEMP_URL . $info->getSaveName());
        } else {
            return returnPubData($file->getError());
        }
    }

    /**
     * 图片上传  - 继承
     * @param null $file 文件源
     * @return mixed|string
     */
    public function upload_image1($file = null)
    {
        if (!$file) {
            $file = request()->file('file');
        }
        $info = $file->move($this->BASE_URL . $this->IMAGE_TEMP_URL);
        if ($info) {
            return  ['code' => 1000,'msg' => '', 'data' => $this->IMAGE_TEMP_URL . $info->getSaveName()];
        } else {
            return  ['code' => 1001, 'msg' => $file->getError(), 'data' => ''];
        }
    }

    /**
     * 删除多个文件
     * @param array $file_url_arr
     * @return int
     */
    public function delete_file_arr($file_url_arr = [])
    {
        $is_ok = 0;
        if (empty($file_url_arr)) {
            return $is_ok;
        }
        if (!is_array($file_url_arr)) {
            $file_url_arr = array_filter(explode('||', $file_url_arr));
        }
        foreach ($file_url_arr as $file_url) {
            //包含Http - 网络文件 无法删除
            if (strpos($file_url, 'http') !== false) {
                continue;
            }


            /**
             * 爬的图片，不再修改
             */
            if (strpos($file_url, 'qlfile') !== false) {
                continue;
            }

            $file_url = $this->BASE_URL . $file_url;
            if (file_exists($file_url)) {
                unlink($file_url);
                $is_ok = 1;
            }
        }
        return $is_ok;
    }

    /**
     * 删除单个文件
     * @param string $file_url
     * @return int
     */
    public function delete_file($file_url = '')
    {
        //包含Http - 网络文件 无法删除
        if (strpos($file_url, 'http') !== false) {
            return 0;
        }
        $is_ok = 0;
        if ($file_url) {
            $file_url = $this->BASE_URL . $file_url;
            if (file_exists($file_url)) {
                unlink($file_url);
                $is_ok = 1;
            }
        }
        return $is_ok;
    }

    /**
     * 移动文件 正式保存路径 - 单个
     * @param string $image
     * @return mixed
     */
    public function set_one_file($file = '', $type = 1, $to_dir = '')
    {
        if (empty($file))
            return '';

        //包含Http - 网络文件 无法移动
        if (strpos($file, 'http') !== false) {
            return $file;
        }
        $file_arr = array_filter(explode('||', $file));
        $result = $this->move_file_arr($file_arr, $type, $to_dir);
        if ($result['code'] == 1000) {
            $file_arr = $result['data'];
        }
        return empty($file_arr[0]) ? $file : $file_arr[0];
    }

    /**
     * 移动文件 正式保存路径 - 多个
     * @param string $image
     * @return string
     */
    public function set_many_file($file_str = '', $type = 1, $to_dir = '')
    {
        if (empty($file_str))
            return '';
        $file_arr_temp = array_filter(explode('||', $file_str));
        $file_arr = [];
        $yy = [];
        foreach ($file_arr_temp as $file) {
            //包含Http - 网络文件 无法移动
            if (strpos($file, 'http') !== false) {
                continue;
            }

            /**
             * 爬的图片，不再修改
             */
            if (strpos($file, 'qlfile') !== false) {
                $yy[] = ltrim($file, '/');

                continue;
            }

            if (strpos($file, $to_dir) !== false) {
                $yy[] = ltrim($file, '/');

                continue;
            }
            $file_arr[] = $file;
        }

        if (empty($file_str))
            return '';
        $result = $this->move_file_arr($file_arr, $type, $to_dir);
        if ($result['code'] == 1000) {
            $file_arr = $result['data'];
        }
//        dump($result);//exit;
        foreach ($file_arr as $val) {
            $yy[] = ltrim($val, '/');
        }
//        dump($yy);
//        exit;
        return empty($yy) ? $file_str : json_encode($yy);
    }

    /**
     * 保存64位编码图片
     * @param $base64_image_content
     * @return array
     * @author 5058
     */
    function saveBase64Image($base64_image_content)
    {
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)) {
            //图片后缀
            $type = $result[2];

            //保存位置--图片名
            $image_name = date('His') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT) . "." . $type;
            $image_url = $this->BASE_URL . $this->IMAGE_TEMP_URL . date('Ymd') . DS . $image_name;
            if (!is_dir(dirname($image_url))) {
                mkdir(dirname($image_url), 0755, true);
                chmod(dirname($image_url), 0755);
            }

            //解码
            $decode = base64_decode(str_replace($result[1], '', $base64_image_content));
            if (file_put_contents($image_url, $decode)) {
                $data = [
                    'fullUrl' => $this->STATIC_URL . $this->IMAGE_TEMP_URL . date('Ymd') . DS . $image_name,
                    'url' => $this->IMAGE_TEMP_URL . date('Ymd') . DS . $image_name
                ];
                return returnPubData('图片保存成功', 1000, $data);
            } else {
                return returnPubData('图片保存失败');
            }
        } else {
            return returnPubData('base64图片格式有误');
        }
    }

}
