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
namespace app\adminapi\controller\v1\product;


use app\adminapi\controller\AuthController;
use app\services\order\StoreCartServices;
use app\services\other\CacheServices;
use app\services\product\product\StoreCategoryServices;
use app\services\product\product\StoreProductServices;
use crmeb\services\FileService;
use crmeb\services\UploadService;
use think\facade\App;

/**
 * Class StoreProduct
 * @package app\adminapi\controller\v1\product
 */
class StoreProduct extends AuthController
{
    protected $service;

    public function __construct(App $app, StoreProductServices $service)
    {
        parent::__construct($app);
        $this->service = $service;
    }

    /**
     * 显示资源列表头部
     * @return mixed
     */
    public function type_header()
    {
        $list = $this->service->getHeader();
        return app('json')->success(compact('list'));
    }

    /**
     * 获取退出未保存的数据
     * @param CacheServices $services
     * @return mixed
     */
    public function getCacheData(CacheServices $services)
    {
        return app('json')->success(['info' => $services->getDbCache($this->adminId . '_product_data', [])]);
    }

    /**
     * 1分钟保存一次产品数据
     * @param CacheServices $services
     * @return mixed
     */
    public function saveCacheData(CacheServices $services)
    {
        $data = $this->request->postMore([
            ['cate_id', []],
            ['store_name', ''],
            ['store_info', ''],
            ['keyword', ''],
            ['unit_name', '件'],
            ['image', []],
            ['recommend_image', ''],
            ['slider_image', []],
            ['postage', 0],
            ['is_sub', []],//佣金是单独还是默认
            ['sort', 0],
            ['sales', 0],
            ['ficti', 100],
            ['give_integral', 0],
            ['is_show', 0],
            ['temp_id', 0],
            ['is_hot', 0],
            ['is_benefit', 0],
            ['is_best', 0],
            ['is_new', 0],
            ['mer_use', 0],
            ['is_postage', 0],
            ['is_good', 0],
            ['description', ''],
            ['spec_type', 0],
            ['video_link', ''],
            ['items', []],
            ['attrs', []],
            ['activity', []],
            ['coupon_ids', []],
            ['label_id', []],
            ['command_word', ''],
            ['tao_words', ''],
            ['type', 0]
        ]);
        $services->setDbCache($this->adminId . '_product_data', $data, 68400);
        return app('json')->success();
    }

    /**
     * 删除数据缓存
     * @param CacheServices $services
     * @return mixed
     */
    public function deleteCacheData(CacheServices $services)
    {
        $services->delectDbCache($this->adminId . '_product_data');
        return app('json')->success();
    }

    /**
     * 显示资源列表
     * @return mixed
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['store_name', ''],
            ['cate_id', ''],
            ['type', 1],
            ['sales', 'normal']
        ]);
        $data = $this->service->getList($where);
        return app('json')->success($data);
    }

    /**
     * 修改状态
     * @param string $is_show
     * @param string $id
     * @return mixed
     */
    public function set_show($is_show = '', $id = '')
    {
        $this->service->setShow([$id], $is_show);
        return app('json')->success($is_show == 1 ? '上架成功' : '下架成功');
    }

    /**
     * 设置批量商品上架
     * @return mixed
     */
    public function product_show()
    {
        [$ids] = $this->request->postMore([
            ['ids', []]
        ], true);
        if (empty($ids)) return app('json')->fail('请选择需要上架的商品');
        $this->service->setShow($ids, 1);
        return app('json')->success('上架成功');
    }

    /**
     * 设置批量商品下架
     * @return mixed
     */
    public function product_unshow()
    {
        [$ids] = $this->request->postMore([
            ['ids', []]
        ], true);
        if (empty($ids)) return app('json')->fail('请选择需要下架的商品');
        $this->service->setShow($ids, 0);
        return app('json')->success('下架成功');
    }

    /**
     * 获取规格模板
     * @return mixed
     */
    public function get_rule()
    {
        $list = $this->service->getRule();
        return app('json')->success($list);
    }

    /**
     * 获取商品详细信息
     * @param int $id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function get_product_info($id = 0)
    {
        return app('json')->success($this->service->getInfo((int)$id));
    }

    /**
     * 保存新建或编辑
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    public function save($id)
    {
        $data = $this->request->postMore([
            ['cate_id', []],
            ['store_name', ''],
            ['store_info', ''],
            ['keyword', ''],
            ['unit_name', '件'],
            ['image', []],
            ['recommend_image', ''],
            ['slider_image', []],
            ['postage', 0],
            ['is_sub', []],//佣金是单独还是默认
            ['sort', 0],
            ['sales', 0],
            ['ficti', 100],
            ['give_integral', 0],
            ['is_show', 0],
            ['temp_id', 0],
            ['is_hot', 0],
            ['is_benefit', 0],
            ['is_best', 0],
            ['is_new', 0],
            ['mer_use', 0],
            ['is_postage', 0],
            ['is_good', 0],
            ['is_virtual', 0],// 是否是虚拟商品
            ['virtual_type', 0],// 虚拟商品类型
            ['description', ''],
            ['spec_type', 0],
            ['video_link', ''],
            ['items', []],
            ['attrs', []],
            ['activity', []],
            ['coupon_ids', []],
            ['label_id', []],
            ['command_word', ''],
            ['tao_words', ''],
            ['type', 0],
            ['recommend_list', []]
        ]);
        $this->service->save((int)$id, $data);
        return app('json')->success('添加商品成功!');
    }

    /**
     * 删除指定资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //删除商品检测是否有参与活动
        $this->service->checkActivity($id);
        $res = $this->service->del($id);
        /** @var StoreCartServices $cartService */
        $cartService = app()->make(StoreCartServices::class);
        $cartService->changeStatus($id, 0);
        return app('json')->success($res);
    }

    /**
     * 生成规格列表
     * @param int $id
     * @param int $type
     * @return mixed
     */
    public function is_format_attr($id = 0, $type = 0)
    {
        $data = $this->request->postMore([
            ['attrs', []],
            ['items', []],
            ['is_virtual', 0],
            ['virtual_type', 0]
        ]);
        if ($id > 0 && $type == 1) $this->service->checkActivity($id);
        $info = $this->service->getAttr($data, $id, $type);
        return app('json')->success(compact('info'));
    }


    /**
     * 获取选择的商品列表
     * @return mixed
     */
    public function search_list()
    {
        $where = $this->request->getMore([
            ['cate_id', ''],
            ['store_name', ''],
            ['type', 1],
            ['is_live', 0],
            ['is_new', ''],
            ['is_virtual', -1]
        ]);
        $where['is_show'] = 1;
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
        $list = $this->service->searchList($where);
        return app('json')->success($list);
    }

    /**
     * 获取某个商品规格
     * @return mixed
     */
    public function get_attrs()
    {
        [$id, $type] = $this->request->getMore([
            [['id', 'd'], 0],
            [['type', 'd'], 0],
        ], true);
        $info = $this->service->getProductRules($id, $type);
        return app('json')->success(compact('info'));
    }

    /**
     * 获取运费模板列表
     * @return mixed
     */
    public function get_template()
    {
        return app('json')->success($this->service->getTemp());
    }

    /**
     * 获取视频上传token
     * @return mixed
     * @throws \Exception
     */
    public function getTempKeys()
    {
        $upload = UploadService::init();
        $re = $upload->getTempKeys();
        return $re ? app('json')->success($re) : app('json')->fail($upload->getError());
    }

    /**
     * 检测商品是否开活动
     * @param $id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function check_activity($id)
    {
        $this->service->checkActivity($id);
        return app('json')->success('删除成功');
    }

    public function import_card()
    {
        $data = $this->request->getMore([
            ['file', ""]
        ]);
        if (!$data['file']) return app('json')->fail('请上传文件');
        $file = public_path() . substr($data['file'], 1);
        /** @var FileService $readExcelService */
        $readExcelService = app()->make(FileService::class);
        $cardData = $readExcelService->readExcel($file);
        return app('json')->success($cardData);
    }
}
