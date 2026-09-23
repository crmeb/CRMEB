<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare (strict_types=1);

namespace app\services\product\product;

use crmeb\basic\BaseService;
use app\services\product\CenterProductServices;
use app\services\system\attachment\SystemAttachmentCategoryServices;
use app\services\system\attachment\SystemAttachmentServices;
use crmeb\exceptions\AdminException;
use crmeb\exceptions\UploadException;
use crmeb\utils\DownloadImage;
use app\services\driver\upload\UploadService;
use app\services\driver\copyproduct\CopyDriverServices;
use crmeb\services\driver\DriverRegistry;
use think\facade\Config;

/**
 *
 * Class CopyProductServices
 * @package app\services\product\product
 */
class CopyProductServices extends BaseService
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
        /** @var CopyDriverServices $copyDriverServices */
        $copyDriverServices = app()->make(CopyDriverServices::class);
        $enabledDriver = $copyDriverServices->getEnabledDrivers()[0] ?? [];
        // 无启用驱动时回退内置一号通驱动
        $driverKey = (string)($enabledDriver['driver_key'] ?? '') ?: 'yihaotong';
        // 经驱动注册中心统一分发，新增采集驱动无需改动此处
        $result = $this->invokeCopyDriver($driverKey, $enabledDriver, (string)$url);

        if (isset($result['status']) && $result['status']) {

            /** @var CenterProductServices $ProductServices */
            $ProductServices = app()->make(CenterProductServices::class);
            /** @var StoreCategoryServices $storeCatecoryService */
            $storeCatecoryService = app()->make(StoreCategoryServices::class);
            $data = [];
            $productInfo = $result['data'];
            if (count($productInfo['slider_image'] ?? [])) {
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
            $productInfo['attrs'] = $result['data']['info']['value'] ?? [];
            foreach ($productInfo['attrs'] as $attrs_k => $attrs_v) {
                $productInfo['attrs'][$attrs_k]['attr_arr'] = array_values($attrs_v['detail']);
                $productInfo['attrs'][$attrs_k]['is_show'] = 1;
            }
            $productInfo['activity'] = ['默认', '秒杀', '砍价', '拼团'];
            $productInfo['bar_code'] = '';
            $productInfo['browse'] = 0;
            $productInfo['cate_id'] = [];
            $productInfo['code_path'] = '';
            $productInfo['command_word'] = '';
            $productInfo['coupons'] = [];
            $productInfo['is_bargain'] = '';
            $productInfo['is_del'] = 0;
            $productInfo['is_postage'] = 0;
            $productInfo['is_seckill'] = 0;
            $productInfo['is_show'] = 0;
            // is_benefit/is_best/is_good/is_hot/is_new 推荐位字段已删除；is_sub/is_vip 已迁移到 eb_product_center 保留
            $productInfo['label_id'] = [];
            $productInfo['mer_id'] = 0;
            $productInfo['mer_use'] = 0;
            $productInfo['recommend_image'] = '';
            $productInfo['sales'] = '';
            $productInfo['sort'] = 0;
            $productInfo['spec_type'] = 1;
            $productInfo['is_virtual'] = 0;
            $productInfo['product_type'] = 'normal';
            $productInfo['spu'] = '';
            $productInfo['id'] = 0;
            $productInfo['temp_id'] = 1;
            $productInfo['freight'] = 3;
            $productInfo['recommend'] = [];
            $productInfo['logistics'] = ['1', '2'];
            $productInfo['params_list'] = [];
            $productInfo['label_list'] = [];
            $productInfo['protection_list'] = [];
            // 注意：`?? []` 表达式与引用遍历组合会触发写时分离（修改作用于临时副本后丢弃），
            // 必须先取变量、遍历修改后再写回，否则规格明细转换不生效
            $copyItems = $productInfo['items'] ?? [];
            foreach ($copyItems as &$items) {
                $details = [];
                foreach ($items['detail'] ?? [] as $detail) {
                    $details[] = [
                        'value' => $detail,
                        'pic' => '',
                    ];
                }
                $items['detail'] = $details;
                $items['add_pic'] = 0;
            }
            unset($items);
            $productInfo['items'] = $copyItems;
            $data['productInfo'] = $productInfo;
            return $data;
        } else {
            throw new AdminException($result['msg'] ?? '商品采集失败');
        }
    }

    /**
     * 调用采集驱动
     * 配置来源约定：内置一号通驱动取全局一号通配置，其余驱动取驱动表保存的配置字段；
     * 全程经 DriverRegistry 分发，不硬编码任何驱动类
     * @param string $driverKey 驱动标识
     * @param array $driver 驱动表中启用的驱动记录
     * @param string $url 商品链接
     * @return array 驱动返回结构 ['status' => bool, 'msg' => string, 'data' => array]
     */
    protected function invokeCopyDriver(string $driverKey, array $driver, string $url): array
    {
        $class = DriverRegistry::getDriverClass('copyproduct', $driverKey);
        if (!$class || !class_exists($class)) {
            throw new AdminException('采集驱动不可用，请检查驱动配置');
        }
        // 一号通驱动的账号密钥取自系统配置，其余驱动取驱动表配置字段
        $config = $driverKey === 'yihaotong' ? yihaotong_config() : ($driver['config_fields'] ?? []);
        return app()->make($class, [$config])->copy($url);
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
                throw new AdminException('无法创建文件夹，请检查您的上传目录权限');
        } catch (\Exception $e) {
            throw new AdminException('无法创建文件夹，请检查您的上传目录权限');
        }
        $description = $storeDescriptionServices->getDescription(['product_id' => $id, 'activity_type' => 'normal']);
        if (!$description) throw new AdminException('商品参数错误');
        //替换并下载详情里面的图片默认下载全部图片
        $description = preg_replace('#<style>.*?</style>#is', '', $description);
        $description = $this->uploadImage([], $description, 1, $AttachmentCategory['id']);
        $storeDescriptionServices->saveDescription((int)$id, $description, 'normal');
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
     * @return array|string
     */
    public function downloadImage($url = '', $name = '', $type = 0, $timeout = 30, $w = 0, $h = 0)
    {
        if (!is_string($url) || !is_string($name)) {
            throw new AdminException('图片地址或文件名格式错误');
        }
        if (trim($url) === '') return '';
        $url = str_starts_with($url, '//') ? 'https:' . $url : $url;
        $autoName = trim($name) === '';
        $name = $autoName ? $this->getImageExtname($url)['file_name'] : $name;
        if (!$name || basename($name) !== $name || str_contains($name, '\\') || preg_match('/[\x00-\x20\x7f?#]/', $name)) {
            throw new AdminException('图片文件名格式错误');
        }
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $imageExtensions = array_intersect(array_map('strtolower', Config::get('upload.fileExt', [])), ['jpg', 'jpeg', 'png', 'gif', 'webp', 'ico']);
        if (!in_array($ext, $imageExtensions, true)) {
            throw new AdminException('格式错误');
        }
        $headers = [];
        foreach ($this->getAntiHotlinkingHeaders($url) as $header) {
            [$key, $value] = explode(':', $header, 2);
            if (strcasecmp($key, 'Accept-Encoding') !== 0) $headers[$key] = trim($value);
        }
        // 网络获取统一复用核心的公网、TLS、超时与内容大小校验，再上传至默认启用空间。
        $content = app()->make(DownloadImage::class)->readImage($url, $headers, max(1, min((int)$timeout, 30)));
        $image = @getimagesizefromstring($content);
        $mimeExtensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp',
            'image/x-icon' => 'ico', 'image/vnd.microsoft.icon' => 'ico'];
        $actualExt = $mimeExtensions[$image['mime'] ?? ''] ?? '';
        if (!$actualExt || !in_array($actualExt, $imageExtensions, true)) {
            throw new UploadException('图片内容格式错误');
        }
        if ($ext !== $actualExt && !($ext === 'jpeg' && $actualExt === 'jpg')) {
            if (!$autoName) throw new AdminException('图片扩展名与内容不匹配');
            $name = md5($url) . '.' . $actualExt;
        }
        $date_dir = date('Y') . '/' . date('m') . '/' . date('d');
        // 从 eb_driver 表获取当前启用的驱动，不再使用旧的 sys_config('upload_type')
        /** @var \app\services\driver\upload\UploadDriverServices $driverServices */
        $driverServices = app()->make(\app\services\driver\upload\UploadDriverServices::class);
        $enabledDrivers = $driverServices->getEnabledDrivers();
        $driverKey = !empty($enabledDrivers) ? $enabledDrivers[0]['driver_key'] : 'local';
        $upload_type = $driverServices->getTypeByDriverKey($driverKey);
        $upload = UploadService::init($driverKey);
        if ($upload->to('attach/' . $date_dir)->validate()->setAuthThumb(false)->stream($content, $name) === false) {
            throw new UploadException($upload->getError());
        }
        $imageInfo = $upload->getUploadInfo();
        $date['path'] = $imageInfo['dir'];
        $date['name'] = $imageInfo['name'];
        $date['real_name'] = $imageInfo['real_name'] ?? $name;
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
        return app()->make(DownloadImage::class)->getImageExtname((string)$url, (string)$ex);
    }

    /**
     * SPU
     * @return string
     */
    public function createSpu()
    {
        return substr(implode('', array_map('ord', str_split(substr(uniqid('', true), 7, 13), 1))), 0, 8) . str_pad((string)mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
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
            throw new AdminException('无法创建文件夹，请检查您的上传目录权限');
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

    /**
     * 根据URL识别平台并获取对应的防盗链headers
     * @param string $url 图片URL
     * @return array 返回对应的HTTP headers
     */
    public function getAntiHotlinkingHeaders(string $url): array
    {
        // 基础headers
        $baseHeaders = [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept: image/webp,image/apng,image/*,*/*;q=0.8',
            'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8',
            'Accept-Encoding: gzip, deflate, br',
            'Connection: keep-alive',
        ];

        // 根据URL识别平台
        if (stripos($url, 'alicdn.com') !== false || stripos($url, 'taobao.com') !== false) {
            // 淘宝/天猫/1688的图片 (通常在alicdn.com域名下)
            return array_merge($baseHeaders, [
                'Referer: https://buyer.taobao.com/',  // 淘宝买家中心
            ]);
        } elseif (stripos($url, 'tmall.com') !== false) {
            // 天猫的图片
            return array_merge($baseHeaders, [
                'Referer: https://www.tmall.com/',  // 天猫首页
            ]);
        } elseif (stripos($url, 'jd.com') !== false || stripos($url, 'jdstatic.com') !== false) {
            // 京东的图片
            return array_merge($baseHeaders, [
                'Referer: https://www.jd.com/',  // 京东首页
            ]);
        } elseif (stripos($url, '1688.com') !== false) {
            // 1688的图片
            return array_merge($baseHeaders, [
                'Referer: https://www.1688.com/',  // 1688首页
            ]);
        } elseif (stripos($url, 'baidu.com') !== false) {
            // 百度图片
            return array_merge($baseHeaders, [
                'Referer: https://image.baidu.com/',  // 百度图片
            ]);
        } elseif (stripos($url, 'sinaimg.cn') !== false) {
            // 新浪图片
            return array_merge($baseHeaders, [
                'Referer: https://weibo.com/',  // 新浪微博
            ]);
        } else {
            // 默认headers，不设置Referer
            return $baseHeaders;
        }
    }
}
