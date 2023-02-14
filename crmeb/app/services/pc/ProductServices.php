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

namespace app\services\pc;

use app\services\BaseServices;
use app\services\other\PosterServices;
use app\services\product\product\StoreProductServices;
use app\services\system\attachment\SystemAttachmentServices;
use app\services\user\UserServices;
use crmeb\services\app\MiniProgramService;
use app\services\other\UploadService;
use Guzzle\Http\EntityBody;

class ProductServices extends BaseServices
{

    /**
     * PC端获取商品列表
     * @param array $where
     * @param int $uid
     * @return mixed
     */
    public function getProductList(array $where, int $uid)
    {
        /** @var StoreProductServices $product */
        $product = app()->make(StoreProductServices::class);

        $where['is_show'] = 1;
        $where['is_del'] = 0;
        $where['vip_user'] = $uid ? app()->make(UserServices::class)->value(['uid' => $uid], 'is_money_level') : 0;
        $data['count'] = $product->getCount($where);
        [$page, $limit] = $this->getPageValue();
        $list = $product->getSearchList($where + ['star' => 1], $page, $limit, ['id,store_name,cate_id,image,IFNULL(sales, 0) + IFNULL(ficti, 0) as sales,price,stock,activity,ot_price,spec_type,recommend_image,unit_name,presale']);
        foreach ($list as &$item) {
            if (count($item['star'])) {
                $item['star'] = bcdiv((string)array_sum(array_column($item['star'], 'product_score')), (string)count($item['star']), 1);
            } else {
                $item['star'] = '3.0';
            }
        }
        $list = $product->getActivityList($list);
        $data['list'] = get_thumb_water($product->getProduceOtherList($list, $uid, !!$where['type']), 'mid');
        return $data;
    }

    /**
     * PC端商品详情小程序码
     * @param int $product_id
     * @param string $type
     * @return false|mixed|string
     */
    public function getProductRoutineCode(int $product_id, string $type = 'product')
    {
        try {
            $namePath = $type == 'product' ? 'routine_product_' . $product_id . '.jpg' : 'routine_seckill_product_' . $product_id . '.jpg';
            $data = 'id=' . $product_id;
            /** @var SystemAttachmentServices $systemAttachmentService */
            $systemAttachmentService = app()->make(SystemAttachmentServices::class);
            $imageInfo = $systemAttachmentService->getOne(['name' => $namePath]);
            $siteUrl = sys_config('site_url');
            if (!$imageInfo) {
                $page = $type == 'product' ? 'pages/goods_details/index' : 'pages/activity/goods_seckill_details/index';
                $res = MiniProgramService::appCodeUnlimitService($data, $page, 280);
                if (!$res) return false;
                $uploadType = (int)sys_config('upload_type', 1);
                $upload = UploadService::init();
                $res = (string)EntityBody::factory($res);
                $res = $upload->to('routine/product')->validate()->setAuthThumb(false)->stream($res, $namePath);
                if ($res === false) {
                    return false;
                }
                $imageInfo = $upload->getUploadInfo();
                $imageInfo['image_type'] = $uploadType;
                if ($imageInfo['image_type'] == 1) $remoteImage = PosterServices::remoteImage($siteUrl . $imageInfo['dir']);
                else $remoteImage = PosterServices::remoteImage($imageInfo['dir']);
                if (!$remoteImage['status']) return false;
                $systemAttachmentService->save([
                    'name' => $imageInfo['name'],
                    'att_dir' => $imageInfo['dir'],
                    'satt_dir' => $imageInfo['thumb_path'],
                    'att_size' => $imageInfo['size'],
                    'att_type' => $imageInfo['type'],
                    'image_type' => $imageInfo['image_type'],
                    'module_type' => 2,
                    'time' => time(),
                    'pid' => 1,
                    'type' => 2
                ]);
                $url = $imageInfo['dir'];
            } else $url = $imageInfo['att_dir'];
            if ($imageInfo['image_type'] == 1) $url = $siteUrl . $url;
            return $url;
        } catch (\Exception $e) {
            return '';
        }
    }
}
