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
namespace app\api\controller\v1\user;

use app\Request;
use app\services\product\product\StoreProductRelationServices;


/**
 * 用户收藏
 * Class UserCollectController
 * @package app\api\controller\v1\user
 */
class UserCollectController
{
    /**
     * @var StoreProductRelationServices
     */
    protected $services;

    /**
     * UserCollectController constructor.
     * @param StoreProductRelationServices $services
     */
    public function __construct(StoreProductRelationServices $services)
    {
        $this->services = $services;
    }


    /**
     * 获取收藏商品
     * @param Request $request
     * @return mixed
     */
    public function collectUser(Request $request)
    {
        $uid = (int)$request->uid();
        return app('json')->success($this->services->getUserCollectProduct($uid));
    }

    /**
     * 添加收藏
     * @param Request $request
     * @return mixed
     */
    public function collectAdd(Request $request)
    {
        [$id, $category] = $request->postMore([
            ['id', 0],
            ['category', 'product']
        ], true);
        if (!$id || !is_numeric($id)) return app('json')->fail('参数错误');
        $res = $this->services->productRelation((int)$id, $request->uid(), 'collect', $category);
        if (!$res) {
            return app('json')->fail('收藏失败');
        } else {
            return app('json')->success('收藏成功');
        }
    }

    /**
     * 取消收藏
     * @param Request $request
     * @return mixed
     * @throws \Exception
     */
    public function collectDel(Request $request)
    {
        [$id, $category] = $request->postMore([
            ['id', []],
            ['category', 'product']
        ], true);
        $uid = (int)$request->uid();
        $res = $this->services->unProductRelation($id, $uid, 'collect', $category);
        if (!$res) return app('json')->fail('取消失败');
        else return app('json')->success('取消成功');
    }

    /**
     * 批量收藏
     * @param Request $request
     * @return mixed
     */
    public function collectAll(Request $request)
    {
        $collectInfo = $request->postMore([
            ['id', ''],
            ['category', 'product'],
        ]);
        $collectInfo['id'] = explode(',', $collectInfo['id']);
        if (!count($collectInfo['id'])) {
            return app('json')->fail('参数错误');
        }
        $uid = (int)$request->uid();
        $productIdS = $collectInfo['id'];
        $res = $this->services->productRelationAll($productIdS, $uid, 'collect', $collectInfo['category']);
        if (!$res) {
            return app('json')->fail('收藏失败');
        } else {
            return app('json')->success('收藏成功');
        }
    }
}
