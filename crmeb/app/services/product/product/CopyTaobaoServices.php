<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare (strict_types=1);

namespace app\services\product\product;

use app\services\BaseServices;
use app\services\serve\ServeServices;
use app\services\system\attachment\SystemAttachmentCategoryServices;
use app\services\system\attachment\SystemAttachmentServices;
use crmeb\exceptions\AdminException;
use app\services\other\UploadService;

/**
 *
 * Class CopyTaobaoServices
 * @package app\services\product\product
 */
class CopyTaobaoServices extends BaseServices
{
    /**
     * @var bool
     */
    protected $errorInfo = true;

    /**
     * @var string
     */
    public $AttachmentCategoryName = '远程下载';

    /**
     * @var string[]
     */
    protected $host = ['taobao', 'tmall', 'jd', 'pinduoduo', 'suning', 'yangkeduo', '1688'];

    /**
     * @param $type
     * @param $id
     * @param $shopid
     * @param $url
     * @return array
     */
    public function copyProduct($type, $id, $shopid, $url)
    {
        $result = [];
        switch ((int)sys_config('system_product_copy_type')) {
            case 1://平台
                /** @var ServeServices $services */
                $services = app()->make(ServeServices::class);
                $resultData = $services->copy('copy')->goods($url);
                if (isset($resultData['description_image']) && is_string($resultData['description_image'])) {
                    $resultData['description_image'] = json_decode($resultData['description_image'], true);
                }
                if (isset($resultData['slider_image']) && is_string($resultData['slider_image'])) {
                    $resultData['slider_image'] = json_decode($resultData['slider_image'], true);
                }
                $result['status'] = 200;
                $result['data'] = $resultData;
                break;
            case 2://99API
                $apikey = sys_config('copy_product_apikey');
                if (!$apikey) throw new AdminException(400554);
                /** @var ServeServices $services */
                $services = app()->make(ServeServices::class);
                $result = $services->copy('copy99api')->goods($url, [
                    'apikey' => $apikey,
                ]);
                break;
        }
        if (isset($result['status']) && $result['status']) {

            /** @var StoreProductServices $ProductServices */
            $ProductServices = app()->make(StoreProductServices::class);
            /** @var StoreCategoryServices $storeCatecoryService */
            $storeCatecoryService = app()->make(StoreCategoryServices::class);
            $data = [];
            $productInfo = $result['data'];
            if (count($productInfo['slider_image'])) {
                $productInfo['slider_image'] = array_map(function ($item) {
                    $item = str_replace('\\', '/', $item);
                    return $item;
                }, $productInfo['slider_image']);
            }
            $data['tempList'] = $ProductServices->getTemp();
            $menus = [];
            foreach ($storeCatecoryService->getTierList(1) as $menu) {
                $menus[] = ['value' => $menu['id'], 'label' => $menu['html'] . $menu['cate_name'], 'disabled' => $menu['pid'] == 0 ? 0 : 1];//,'disabled'=>$menu['pid']== 0];
            }
            $data['cateList'] = $menus;
            $productInfo['attrs'] = $result['data']['info']['value'];
            $productInfo['activity'] = ['默认', '秒杀', '砍价', '拼团'];
            $productInfo['bar_code'] = '';
            $productInfo['browse'] = 0;
            $productInfo['cate_id'] = [];
            $productInfo['code_path'] = '';
            $productInfo['command_word'] = '';
            $productInfo['coupons'] = [];
            $productInfo['is_bargain'] = '';
            $productInfo['is_benefit'] = 0;
            $productInfo['is_best'] = 0;
            $productInfo['is_del'] = 0;
            $productInfo['is_good'] = 0;
            $productInfo['is_hot'] = 0;
            $productInfo['is_new'] = 0;
            $productInfo['is_postage'] = 0;
            $productInfo['is_seckill'] = 0;
            $productInfo['is_show'] = 0;
            $productInfo['is_show'] = 0;
            $productInfo['is_sub'] = [];
            $productInfo['is_vip'] = 0;
            $productInfo['is_vip'] = 0;
            $productInfo['label_id'] = [];
            $productInfo['mer_id'] = 0;
            $productInfo['mer_use'] = 0;
            $productInfo['recommend_image'] = '';
            $productInfo['sales'] = '';
            $productInfo['sort'] = 0;
            $productInfo['spec_type'] = 1;
            $productInfo['is_virtual'] = 0;
            $productInfo['virtual_type'] = 0;
            $productInfo['spu'] = '';
            $productInfo['id'] = 0;
            $productInfo['temp_id'] = 1;
            $productInfo['freight'] = 3;
            $data['productInfo'] = $productInfo;
            return $data;
        } else {
            throw new AdminException($result['msg']);
        }
    }

    /**
     * 下载商品详情图片
     * @param int $id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function uploadDescriptionImage(int $id)
    {
        //查询附件分类
        /** @var SystemAttachmentCategoryServices $systemAttachmentCategoryService */
        $systemAttachmentCategoryService = app()->make(SystemAttachmentCategoryServices::class);
        /** @var StoreDescriptionServices $storeDescriptionServices */
        $storeDescriptionServices = app()->make(StoreDescriptionServices::class);
        $AttachmentCategory = $systemAttachmentCategoryService->getOne(['name' => $this->AttachmentCategoryName]);
        //不存在则创建
        if (!$AttachmentCategory) $AttachmentCategory = $systemAttachmentCategoryService->save(['pid' => '0', 'name' => $this->AttachmentCategoryName, 'enname' => '']);
        //生成附件目录
        try {
            if (make_path('attach', 3, true) === '')
                throw new AdminException(400555);
        } catch (\Exception $e) {
            throw new AdminException(400555);
        }
        $description = $storeDescriptionServices->getDescription(['product_id ' => $id, 'type' => 0]);
        if (!$description) throw new AdminException(400556);
        //替换并下载详情里面的图片默认下载全部图片
        $description = preg_replace('#<style>.*?</style>#is', '', $description);
        $description = $this->uploadImage([], $description, 1, $AttachmentCategory['id']);
        $storeDescriptionServices->saveDescription((int)$id, $description);
        return true;
    }

    /**
     * 上传图片处理
     * @param array $images
     * @param string $html
     * @param int $uploadType
     * @param int $AttachmentCategoryId
     * @return array|bool|string|string[]|null
     */
    public function uploadImage(array $images = [], $html = '', $uploadType = 0, $AttachmentCategoryId = 0)
    {
        /** @var SystemAttachmentServices $systemAttachmentService */
        $systemAttachmentService = app()->make(SystemAttachmentServices::class);
        $uploadImage = [];
        $siteUrl = sys_config('site_url');
        switch ($uploadType) {
            case 0:
                foreach ($images as $item) {
                    //下载图片文件
                    if ($item['w'] && $item['h'])
                        $uploadValue = $this->downloadImage($item['line'], '', 0, 30, $item['w'], $item['h']);
                    else
                        $uploadValue = $this->downloadImage($item['line']);
                    //下载成功更新数据库
                    if (is_array($uploadValue)) {
                        //TODO 拼接图片地址
                        if ($uploadValue['image_type'] == 1) $imagePath = $siteUrl . $uploadValue['path'];
                        else $imagePath = $uploadValue['path'];
                        //写入数据库
                        if (!$uploadValue['is_exists'] && $AttachmentCategoryId) {
                            $systemAttachmentService->save([
                                'name' => $uploadValue['name'],
                                'real_name' => $uploadValue['name'],
                                'att_dir' => $imagePath,
                                'satt_dir' => $imagePath,
                                'att_size' => $uploadValue['size'],
                                'att_type' => $uploadValue['mime'],
                                'image_type' => $uploadValue['image_type'],
                                'module_type' => 1,
                                'time' => time(),
                                'pid' => $AttachmentCategoryId
                            ]);
                        }
                        //组装数组
                        if (isset($item['isTwoArray']) && $item['isTwoArray'])
                            $uploadImage[$item['valuename']][] = $imagePath;
                        else
                            $uploadImage[$item['valuename']] = $imagePath;
                    }
                }
                break;
            case 1:
                preg_match_all('#<img.*?src="([^"]*)"[^>]*>#i', $html, $match);
                if (isset($match[1])) {
                    foreach ($match[1] as $item) {
                        if (is_int(strpos($item, 'http')))
                            $arcurl = $item;
                        else
                            $arcurl = 'http://' . ltrim($item, '\//');
                        $uploadValue = $this->downloadImage($arcurl);
                        //下载成功更新数据库
                        if (is_array($uploadValue)) {
                            //TODO 拼接图片地址
                            if ($uploadValue['image_type'] == 1) $imagePath = $siteUrl . $uploadValue['path'];
                            else $imagePath = $uploadValue['path'];
                            //写入数据库
                            if (!$uploadValue['is_exists'] && $AttachmentCategoryId) {
                                $systemAttachmentService->save([
                                    'name' => $uploadValue['name'],
                                    'real_name' => $uploadValue['name'],
                                    'att_dir' => $imagePath,
                                    'satt_dir' => $imagePath,
                                    'att_size' => $uploadValue['size'],
                                    'att_type' => $uploadValue['mime'],
                                    'image_type' => $uploadValue['image_type'],
                                    'module_type' => 1,
                                    'time' => time(),
                                    'pid' => $AttachmentCategoryId
                                ]);
                            }
                            //替换图片
                            $html = str_replace($item, $imagePath, $html);
                        } else {
                            //替换掉没有下载下来的图片
                            $html = preg_replace('#<img.*?src="' . $item . '"*>#i', '', $html);
                        }
                    }
                }
                return $html;
                break;
            default:
                throw new AdminException(400557);
                break;
        }
        return $uploadImage;
    }

    /**
     * @param string $url
     * @param string $name
     * @param int $type
     * @param int $timeout
     * @param int $w
     * @param int $h
     * @return string
     */
    public function downloadImage($url = '', $name = '', $type = 0, $timeout = 30, $w = 0, $h = 0)
    {
        if (!strlen(trim($url))) return '';
        if (!strlen(trim($name))) {
            //TODO 获取要下载的文件名称
            $downloadImageInfo = $this->getImageExtname($url);
            $ext = $downloadImageInfo['ext_name'];
            $name = $downloadImageInfo['file_name'];
            if (!strlen(trim($name))) return '';
        } else {
            $ext = $this->getImageExtname($name)['ext_name'];
        }
        if (in_array($ext, ['php', 'js', 'html'])) {
            throw new AdminException(400558);
        }
        //TODO 获取远程文件所采用的方法
        if ($type) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //TODO 跳过证书检查
            if (stripos($url, "https://") !== FALSE) curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);  //TODO 从证书中检查SSL加密算法是否存在
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('user-agent:' . $_SERVER['HTTP_USER_AGENT']));
            if (ini_get('open_basedir') == '' && ini_get('safe_mode') == 'Off') curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);//TODO 是否采集301、302之后的页面
            $content = curl_exec($ch);
            curl_close($ch);
        } else {
            try {
                ob_start();
                if (substr($url, 0, 2) == '//') {
                    $url = "https:" . $url;
                }
                readfile($url);
                $content = ob_get_contents();
                ob_end_clean();
            } catch (\Exception $e) {
                throw new AdminException($e->getMessage());
            }
        }
        $size = strlen(trim($content));
        if (!$content || $size <= 2) throw new AdminException(400559);
        $date_dir = date('Y') . '/' . date('m') . '/' . date('d');
        $upload_type = sys_config('upload_type', 1);
        $upload = UploadService::init($upload_type);
        if ($upload->to('attach/' . $date_dir)->validate()->setAuthThumb(false)->stream($content, $name) === false) {
            throw new AdminException($upload->getError());
        }
        $imageInfo = $upload->getUploadInfo();
        $date['path'] = $imageInfo['dir'];
        $date['name'] = $imageInfo['name'];
        $date['size'] = $imageInfo['size'];
        $date['mime'] = $imageInfo['type'];
        $date['image_type'] = $upload_type;
        $date['is_exists'] = false;
        return $date;
    }

    /**
     * 获取即将要下载的图片扩展名
     * @param string $url
     * @param string $ex
     * @return array|string[]
     */
    public function getImageExtname($url = '', $ex = 'jpg')
    {
        $_empty = ['file_name' => '', 'ext_name' => $ex];
        if (!$url) return $_empty;
        if (strpos($url, '?')) {
            $_tarr = explode('?', $url);
            $url = trim($_tarr[0]);
        }
        $arr = explode('.', $url);
        if (!is_array($arr) || count($arr) <= 1) return $_empty;
        $ext_name = trim($arr[count($arr) - 1]);
        $ext_name = !$ext_name ? $ex : $ext_name;
        return ['file_name' => md5($url) . '.' . $ext_name, 'ext_name' => $ext_name];
    }

    /**
     * SPU
     * @return string
     */
    public function createSpu()
    {
        return substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8) . str_pad((string)mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    }

    /**
     * 下载远程图片并上传
     * @param $image
     * @return false|mixed|string
     * @throws \Exception
     */
    public function downloadCopyImage($image)
    {
        //查询附件分类
        /** @var SystemAttachmentCategoryServices $systemAttachmentCategoryService */
        $systemAttachmentCategoryService = app()->make(SystemAttachmentCategoryServices::class);
        $AttachmentCategory = $systemAttachmentCategoryService->getOne(['name' => '远程下载']);
        //不存在则创建
        if (!$AttachmentCategory) {
            $AttachmentCategory = $systemAttachmentCategoryService->save(['pid' => '0', 'name' => '远程下载', 'enname' => '']);
        }

        //生成附件目录
        if (make_path('attach', 3, true) === '') {
            throw new AdminException(400555);
        }

        //上传图片
        /** @var SystemAttachmentServices $systemAttachmentService */
        $systemAttachmentService = app()->make(SystemAttachmentServices::class);
        $siteUrl = sys_config('site_url');
        $uploadValue = $this->downloadImage($image);
        if (is_array($uploadValue)) {
            //TODO 拼接图片地址
            if ($uploadValue['image_type'] == 1) {
                $imagePath = $siteUrl . $uploadValue['path'];
            } else {
                $imagePath = $uploadValue['path'];
            }
            //写入数据库
            if (!$uploadValue['is_exists'] && $AttachmentCategory['id']) {
                $systemAttachmentService->save([
                    'name' => $uploadValue['name'],
                    'real_name' => $uploadValue['name'],
                    'att_dir' => $imagePath,
                    'satt_dir' => $imagePath,
                    'att_size' => $uploadValue['size'],
                    'att_type' => $uploadValue['mime'],
                    'image_type' => $uploadValue['image_type'],
                    'module_type' => 1,
                    'time' => time(),
                    'pid' => $AttachmentCategory['id']
                ]);
            }
            return $imagePath;
        }
        return false;
    }
}
