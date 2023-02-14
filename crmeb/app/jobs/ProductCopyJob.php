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

namespace app\jobs;


use app\services\product\product\CopyTaobaoServices;
use app\services\product\product\StoreDescriptionServices;
use app\services\product\product\StoreProductServices;
use app\services\product\sku\StoreProductAttrValueServices;
use crmeb\basic\BaseJobs;
use crmeb\services\CacheService;
use crmeb\traits\QueueTrait;
use think\facade\Log;

/**
 * 复制商品
 * Class ProductCopyJob
 * @package app\jobs
 */
class ProductCopyJob extends BaseJobs
{
    use QueueTrait;

    /**
     * 下载商品详情图片
     * @param $id
     * @return bool
     */
    public function copyDescriptionImage($id, $description, $image, $count)
    {
        try {
            /** @var CopyTaobaoServices $copyTaobao */
            $copyTaobao = app()->make(CopyTaobaoServices::class);
            /** @var StoreDescriptionServices $storeDescriptionServices */
            $storeDescriptionServices = app()->make(StoreDescriptionServices::class);
            if (is_int(strpos($image, 'http'))) {
                $d_image = $image;
            } else {
                $d_image = 'http://' . ltrim($image, '\//');
            }
            $description_cache = CacheService::get('desc_images_' . $id);
            if ($description_cache === null) {
                $description_cache = $description;
                CacheService::set('desc_images_count' . $id, 0);
            }
            $res = $copyTaobao->downloadCopyImage($d_image);
            $description_cache = str_replace($image, $res, $description_cache);
            $desc_count = CacheService::get('desc_images_count' . $id) + 1;
            if ($desc_count == $count) {
                CacheService::delete('desc_images_' . $id);
                CacheService::delete('desc_images_count' . $id);
                $storeDescriptionServices->saveDescription((int)$id, $description_cache);
            } else {
                CacheService::set('desc_images_' . $id, $description_cache);
                CacheService::set('desc_images_count' . $id, $desc_count);
            }
        } catch (\Throwable $e) {
            Log::error('下载商品详情图片失败，失败原因:' . $e->getMessage());
        }
        return true;
    }

    /**
     * 下载商品轮播图片
     * @param $id
     * @return bool
     */
    public function copySliderImage($id, $image, $count)
    {
        try {
            /** @var CopyTaobaoServices $copyTaobao */
            $copyTaobao = app()->make(CopyTaobaoServices::class);
            /** @var StoreProductServices $StoreProductServices */
            $StoreProductServices = app()->make(StoreProductServices::class);
            /** @var StoreProductAttrValueServices $StoreProductAttrValueServices */
            $StoreProductAttrValueServices = app()->make(StoreProductAttrValueServices::class);
            //下载图片
            $res = $copyTaobao->downloadCopyImage($image);
            //获取缓存中的轮播图
            $slider_images = CacheService::get('slider_images_' . $id);
            //缓存为null则赋值[]
            if ($slider_images === null) $slider_images = [];
            //将下载的图片插入数组
            array_push($slider_images, $res);
            //如果$slider_images中图片数量和传入的$count相等，说明已经下载完成，写入商品表，如果不等则继续插入缓存
            if (count($slider_images) == $count) {
                CacheService::delete('slider_images_' . $id);
                $image = $slider_images[0];
                $slider_images = $slider_images ? json_encode($slider_images) : '';
                $StoreProductServices->update($id, ['slider_image' => $slider_images, 'image' => $image]);
                $StoreProductAttrValueServices->update(['product_id' => $id], ['image' => $image]);
            } else {
                CacheService::set('slider_images_' . $id, $slider_images);
            }
        } catch (\Throwable $e) {
            Log::error('下载商品轮播图片失败，失败原因:' . $e->getMessage());
        }
        return true;
    }
}
