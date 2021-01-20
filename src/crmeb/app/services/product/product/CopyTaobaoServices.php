<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare (strict_types=1);

namespace app\services\product\product;

use app\services\BaseServices;
use app\services\product\sku\StoreProductAttrServices;
use app\services\serve\ServeServices;
use app\services\system\attachment\SystemAttachmentCategoryServices;
use app\services\system\attachment\SystemAttachmentServices;
use crmeb\exceptions\AdminException;
use crmeb\services\CopyProductService;
use crmeb\services\UploadService;

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
    protected $AttachmentCategoryName = '远程下载';

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
        switch ((int)sys_config('system_product_copy_type')) {
            case 1://平台
                /** @var ServeServices $services */
                $services = app()->make(ServeServices::class);
                $resultData = $services->copy()->goods($url);
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
                if (!$apikey) throw new AdminException('请先配置接口密钥');
                if ((!$type || !$id) && $url) {
                    $url_arr = parse_url($url);
                    if (isset($url_arr['host'])) {
                        foreach ($this->host as $name) {
                            if (strpos($url_arr['host'], $name) !== false) {
                                $type = $name;
                            }
                        }
                    }
                    $type = ($type == 'pinduoduo' || $type == 'yangkeduo') ? 'pdd' : $type;
                    switch ($type) {
                        case 'taobao':
                        case 'tmall':
                            $params = [];
                            if (isset($url_arr['query']) && $url_arr['query']) {
                                $queryParts = explode('&', $url_arr['query']);
                                foreach ($queryParts as $param) {
                                    $item = explode('=', $param);
                                    if (isset($item[0]) && $item[1]) $params[$item[0]] = $item[1];
                                }
                            }
                            $id = $params['id'] ?? '';
                            break;
                        case 'jd':
                            $params = [];
                            if (isset($url_arr['path']) && $url_arr['path']) {
                                $path = str_replace('.html', '', $url_arr['path']);
                                $params = explode('/', $path);
                            }
                            $id = $params[1] ?? '';
                            break;
                        case 'pdd':
                            $params = [];
                            if (isset($url_arr['query']) && $url_arr['query']) {
                                $queryParts = explode('&', $url_arr['query']);
                                foreach ($queryParts as $param) {
                                    $item = explode('=', $param);
                                    if (isset($item[0]) && $item[1]) $params[$item[0]] = $item[1];
                                }
                            }
                            $id = $params['goods_id'] ?? $params['goodsId'] ?? '';
                            break;
                        case 'suning':
                            $params = [];
                            if (isset($url_arr['path']) && $url_arr['path']) {
                                $path = str_replace('.html', '', $url_arr['path']);
                                $params = explode('/', $path);
                            }
                            $id = $params[2] ?? '';
                            $shopid = $params[1] ?? '';
                            break;
                        case '1688':
                            $params = [];
                            if (isset($url_arr['query']) && $url_arr['query']) {
                                $path = str_replace('.html', '', $url_arr['path']);
                                $params = explode('/', $path);
                            }
                            $id = $params[2] ?? '';
                            $shopid = $params[1] ?? '';
                            break;

                    }
                }
                $result = CopyProductService::getInfo($type, ['itemid' => $id, 'shopid' => $shopid], $apikey);
                break;
        }
        if (isset($result['status']) && $result['status']) {
            return ['info' => $result['data']];
        } else {
            throw new AdminException($result['msg']);
        }
    }

    /**
     * 保存数据
     * @param array $data
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function save(array $data)
    {
        $detail = $data['attrs'];
        $attrs = $data['items'];
        $data['spec_type'] = $data['attrs'] ? 1 : 0;
        if (count($detail)) {
            $data['price'] = min(array_column($detail, 'price'));
            $data['ot_price'] = min(array_column($detail, 'ot_price'));
            $data['cost'] = min(array_column($detail, 'cost'));
            $data['stock'] = array_sum(array_column($detail, 'stock'));
        }
        unset($data['attrs'], $data['items'], $data['info']);
        if (!$data['cate_id']) throw new AdminException('请选择分类！');
        if (!$data['store_name']) throw new AdminException('请填写商品名称');
        if (!$data['unit_name']) throw new AdminException('请填写商品单位');
        if (!$data['temp_id']) throw new AdminException('请选择运费模板');
        //查询附件分类
        /** @var SystemAttachmentCategoryServices $systemAttachmentCategoryService */
        $systemAttachmentCategoryService = app()->make(SystemAttachmentCategoryServices::class);
        $AttachmentCategory = $systemAttachmentCategoryService->getOne(['name' => $this->AttachmentCategoryName]);
        //不存在则创建
        if (!$AttachmentCategory) $AttachmentCategory = $systemAttachmentCategoryService->save(['pid' => '0', 'name' => $this->AttachmentCategoryName, 'enname' => '']);
        //生成附件目录
        try {
            if (make_path('attach', 3, true) === '')
                throw new AdminException('无法创建文件夹，请检查您的上传目录权限：' . app()->getRootPath() . 'public' . DS . 'uploads' . DS . 'attach' . DS);

        } catch (\Exception $e) {
            throw new AdminException($e->getMessage() . '或无法创建文件夹，请检查您的上传目录权限：' . app()->getRootPath() . 'public' . DS . 'uploads' . DS . 'attach' . DS);
        }
        ini_set("max_execution_time", '600');
        /** @var StoreProductServices $storeProductServices */
        $storeProductServices = app()->make(StoreProductServices::class);
        /** @var StoreDescriptionServices $storeDescriptionServices */
        $storeDescriptionServices = app()->make(StoreDescriptionServices::class);
        /** @var StoreProductCateServices $storeProductCateServices */
        $storeProductCateServices = app()->make(StoreProductCateServices::class);
        /** @var StoreProductAttrServices $storeProductAttrServices */
        $storeProductAttrServices = app()->make(StoreProductAttrServices::class);
        /** @var StoreProductCouponServices $storeProductCouponServices */
        $storeProductCouponServices = app()->make(StoreProductCouponServices::class);
        /** @var StoreCategoryServices $storeCategoryServices */
        $storeCategoryServices = app()->make(StoreCategoryServices::class);
        //开始图片下载处理
        $this->transaction(function () use ($attrs, $detail, $data, $AttachmentCategory, $storeCategoryServices, $storeProductServices, $storeDescriptionServices, $storeProductCateServices, $storeProductAttrServices, $storeProductCouponServices) {
            //放入主图
            $images = [
                ['w' => 305, 'h' => 305, 'line' => $data['image'], 'valuename' => 'image']
            ];
            //放入轮播图
            foreach ($data['slider_image'] as $item) {
                $value = ['w' => 640, 'h' => 640, 'line' => $item, 'valuename' => 'slider_image', 'isTwoArray' => true];
                array_push($images, $value);
            }
            //执行下载
            $res = $this->uploadImage($images, false, 0, $AttachmentCategory['id']);
            if (!is_array($res)) throw new AdminException($this->errorInfo ? $this->errorInfo : '保存图片失败');
            if (isset($res['image'])) $data['image'] = $res['image'];
            if (isset($res['slider_image'])) $data['slider_image'] = $res['slider_image'];
            $data['image'] = str_replace('\\', '/', $data['image']);
            if (count($data['slider_image'])) {
                $data['slider_image'] = array_map(function ($item) {
                    $item = str_replace('\\', '/', $item);
                    return $item;
                }, $data['slider_image']);
            }
            $data['slider_image'] = count($data['slider_image']) ? json_encode($data['slider_image']) : '';
            //替换并下载详情里面的图片默认下载全部图片
            $data['description'] = preg_replace('#<style>.*?</style>#is', '', $data['description']);
            $data['description'] = $this->uploadImage($data['description_images'], $data['description'], 1, $AttachmentCategory['id']);
            unset($data['description_images']);
            $description = $data['description'];
            unset($data['description']);
            $data['add_time'] = time();
            $cate_id = $data['cate_id'];
            $data['cate_id'] = implode(',', $data['cate_id']);
            $productInfo = $storeProductServices->getOne(['soure_link' => $data['soure_link']]);
            if ($productInfo) {
                $productInfo->slider_image = $data['slider_image'];
                $productInfo->image = $data['image'];
                $productInfo->store_name = $data['store_name'];
                $res = $storeProductServices->update($productInfo->id, $data);
                if (!$res) throw new AdminException('保存失败');
                $storeDescriptionServices->saveDescription($productInfo->id, $description);
            } else {
                $data['code_path'] = '';
                $data['spu'] = $this->createSpu();
                if ($data['spec_type'] == 0) {
                    $attrs = [
                        [
                            'value' => '规格',
                            'detailValue' => '',
                            'attrHidden' => '',
                            'detail' => ['默认']
                        ]
                    ];
                    $detail[0]['value1'] = '规格';
                    $detail[0]['detail'] = ['规格' => '默认'];
                }
                $res = $storeProductServices->create($data);
                $storeDescriptionServices->saveDescription((int)$res['id'], $description);
                $cateData = [];
                $time = time();
                $cateGory = $storeCategoryServices->getColumn([['id', 'IN', $cate_id]], 'id,pid', 'id');
                foreach ($cate_id as $cid) {
                    if ($cid && isset($cateGory[$cid]['pid'])) {
                        $cateData[] = ['product_id' => $res['id'], 'cate_id' => $cid, 'cate_pid' => $cateGory[$cid]['pid'], 'status' => $data['is_show'], 'add_time' => $time];
                    }
                }
                $storeProductCateServices->change($res['id'], $cateData);
                $skuList = $storeProductServices->validateProductAttr($attrs, $detail, (int)$res['id']);
                $attrRes = $storeProductAttrServices->saveProductAttr($skuList, (int)$res['id'], 0);
                if (!empty($coupon_ids)) $storeProductCouponServices->setCoupon((int)$res['id'], $coupon_ids);
                if (!$attrRes) throw new AdminException('添加失败！');
            }
        });

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
                throw new AdminException('上传方式错误');
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
            $name = $downloadImageInfo['file_name'];
            if (!strlen(trim($name))) return '';
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
            if (ini_get('open_basedir') == '' && ini_get('safe_mode' == 'Off')) curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);//TODO 是否采集301、302之后的页面
            $content = curl_exec($ch);
            curl_close($ch);
        } else {
            try {
                ob_start();
                readfile($url);
                $content = ob_get_contents();
                ob_end_clean();
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }
        $size = strlen(trim($content));
        if (!$content || $size <= 2) return '图片流获取失败';
        $date_dir = date('Y') . '/' . date('m') . '/' . date('d');
        $upload_type = sys_config('upload_type', 1);
        $upload = UploadService::init();
        if ($upload->to('attach/' . $date_dir)->stream($content, $name) === false) {
            return $upload->getError();
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
}
