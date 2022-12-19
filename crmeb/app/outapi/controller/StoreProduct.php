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
namespace app\outapi\controller;

use app\services\order\StoreCartServices;
use app\services\product\product\StoreCategoryServices;
use app\services\product\product\OutStoreProductServices;
use think\facade\App;

/**
 * Class StoreProduct
 * @package aapp\outapi\controller
 */
class StoreProduct extends AuthController
{
    protected $services;

    public function __construct(App $app, OutStoreProductServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 显示资源列表
     * @return mixed
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['cate_id', ''],
            ['store_name', ''],
            ['type', 1],
            ['is_live', 0],
            ['is_new', ''],
            ['is_virtual', -1],
            ['is_presale', -1]
        ]);
        $where['is_del'] = 0;
        /** @var StoreCategoryServices $storeCategoryServices */
        $storeCategoryServices = app()->make(StoreCategoryServices::class);
        if ($where['cate_id'] !== '') {
            if ($storeCategoryServices->value(['id' => $where['cate_id']], 'pid')) {
                $where['sid'] = $where['cate_id'];
            } else {
                $where['cid'] = $where['cate_id'];
            }
        }

        unset($where['cate_id']);
        $list = $this->services->searchList($where);
        return app('json')->success($list);
    }

    /**
     * 修改状态
     * @param string $id
     * @param string $is_show
     */
    public function set_show($id = '', $is_show = '')
    {
        if ($id == '' || $is_show == '') return app('json')->fail(100100);
        $this->services->setShow((int)$id, (int)$is_show);
        return app('json')->success($is_show == 1 ? 100003 : 100004);
    }

    /**
     * 获取商品信息
     * @param $id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function read($id = 0)
    {
        return app('json')->success($this->services->getInfo((int)$id));
    }

    /**
     * 保存
     * @return mixed
     * @throws \Exception
     */
    public function save()
    {
        $data = $this->request->postMore([
            ['cate_id', []],//分类id
            ['store_name', ''],//商品名称
            ['keyword', ''],//关键字
            ['unit_name', '件'],//单位
            ['store_info', ''],//商品简介
            ['slider_image', []],//轮播图
            ['video_open', 0],//是否开启视频
            ['video_link', ''],//视频链接
            ['spec_type', 0],//单多规格
            ['items', []],//规格
            ['attrs', []],//规格
            ['description', ''],//商品详情
            ['description_images', []],//商品详情
            ['logistics', []],//物流方式
            ['freight', 1],//运费设置
            ['postage', 0],//邮费
            ['is_sub', 0],//佣金是单独还是默认
            ['is_vip', 0],//付费会员价
            ['recommend', []],//商品推荐
            ['temp_id', 0],//运费模版id
            ['give_integral', 0],//赠送积分
            ['presale', 0],//预售商品开关
            ['presale_time', 0],//预售时间
            ['presale_day', 0],//预售发货日
            ['vip_product', 0],//是否付费会员商品
            ['activity', []],//活动优先级
            ['command_word', ''],//商品口令
            ['is_show', 0],//是否上架
            ['ficti', 0],//虚拟销量
            ['sort', 0],//排序
            ['recommend_image', ''],//商品推荐图
            ['custom_form', []],//自定义表单
            ['is_limit', 0],//是否限购
            ['limit_type', 0],//限购类型
            ['limit_num', 0]//限购数量
        ]);
        $id = $this->services->save(0, $data);
        return app('json')->success(100000, ['id' => $id]);
    }

    /**
     * 更新
     * @param $id
     * @return mixed
     */
    public function update($id)
    {
        $data = $this->request->postMore([
            ['cate_id', []],//分类id
            ['store_name', ''],//商品名称
            ['keyword', ''],//关键字
            ['unit_name', '件'],//单位
            ['store_info', ''],//商品简介
            ['slider_image', []],//轮播图
            ['video_open', 0],//是否开启视频
            ['video_link', ''],//视频链接
            ['spec_type', 0],//单多规格
            ['items', []],//规格
            ['attrs', []],//规格
            ['description', ''],//商品详情
            ['description_images', []],//商品详情
            ['logistics', []],//物流方式
            ['freight', 1],//运费设置
            ['postage', 0],//邮费
            ['is_sub', 0],//佣金是单独还是默认
            ['is_vip', 0],//付费会员价
            ['recommend', []],//商品推荐
            ['temp_id', 0],//运费模版id
            ['give_integral', 0],//赠送积分
            ['presale', 0],//预售商品开关
            ['presale_time', 0],//预售时间
            ['presale_day', 0],//预售发货日
            ['vip_product', 0],//是否付费会员商品
            ['activity', []],//活动优先级
            ['command_word', ''],//商品口令
            ['is_show', 0],//是否上架
            ['ficti', 0],//虚拟销量
            ['sort', 0],//排序
            ['recommend_image', ''],//商品推荐图
            ['custom_form', []],//自定义表单
            ['is_limit', 0],//是否限购
            ['limit_type', 0],//限购类型
            ['limit_num', 0]//限购数量
        ]);
        $this->services->save((int)$id, $data);
        return app('json')->success(100001);
    }

    /**
     * 删除
     * @param int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //删除商品检测是否有参与活动
        $this->services->checkActivity($id);
        $res = $this->services->del($id);
        /** @var StoreCartServices $cartService */
        $cartService = app()->make(StoreCartServices::class);
        $cartService->changeStatus($id, 0);
        return app('json')->success($res);
    }

    /**
     * 同步库存
     * @return void
     */
    public function uploadStock()
    {
        [$items] = $this->request->postMore([['items', []]], true);

        foreach ($items as $item) {
            if (!isset($item['bar_code']) || !isset($item['qty'])) {
                return app('json')->fail(400742);
            }
        }

        if (count($items) > 100) {
            return app('json')->fail(400743);
        }

        $this->services->syncStock($items);
        return app('json')->success(100010);
    }
}
