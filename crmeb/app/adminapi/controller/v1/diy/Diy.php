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

namespace app\adminapi\controller\v1\diy;


use app\adminapi\controller\AuthController;
use app\services\activity\StoreBargainServices;
use app\services\activity\StoreCombinationServices;
use app\services\activity\StoreSeckillServices;
use app\services\diy\DiyServices;
use app\services\other\CacheServices;
use app\services\product\product\StoreCategoryServices;
use app\services\product\product\StoreProductServices;
use crmeb\exceptions\AdminException;
use crmeb\services\FileService;
use think\facade\App;

class Diy extends AuthController
{
    protected $services;

    public function __construct(App $app, DiyServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * DIY列表
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList()
    {
        $where = $this->request->getMore([
            ['status', ''],
            ['type', 0],
            ['name', ''],
            ['version', ''],
        ]);
        $data = $this->services->getDiyList($where);
        return app('json')->success($data);
    }

    /**
     * 保存资源
     * @param int $id
     * @return mixed
     */
    public function saveData(int $id = 0)
    {
        $data = $this->request->postMore([
            ['value', ''],
        ]);
        $value_config = ['seckill', 'bargain', 'combination', 'goodList'];
        $value = is_string($data['value']) ? json_decode($data['value'], true) : $data['value'];
        foreach ($value as $key => &$val) {
            if (in_array($key, $value_config) && is_array($val)) {
                foreach ($val as $k => &$v) {
                    if (isset($v['selectConfig']['list']) && $v['selectConfig']['list']) {
                        $v['selectConfig']['list'] = [];
                    }
                    if (isset($v['goodsList']['list']) && $v['goodsList']['list'] && $v['tabConfig']['tabVal'] == 1) {
                        $limitMax = config('database.page.limitMax', 50);
                        if (count($v['goodsList']['list']) > $limitMax) {
                            return app('json')->fail('您设置得商品个数超出系统限制,最大限制' . $limitMax . '个商品');
                        }
                        $v['ids'] = array_column($v['goodsList']['list'], 'id');
                        $v['goodsList']['list'] = [];
                    }
                }
            }
        }
        $data['value'] = json_encode($value);
        $this->services->saveData($id, $data);
        return app('json')->success('保存成功');
    }

    /**
     * 删除模板
     * @param $id
     * @return mixed
     */
    public function del($id)
    {
        $this->services->del($id);
        return app('json')->success('删除成功');
    }

    /**
     * 使用模板
     * @param $id
     * @return mixed
     */
    public function setStatus($id)
    {
        $name = $this->services->value(['id' => $id], 'template_name');
        if (!is_file(public_path('template') . $name . '.zip')) {
            throw new AdminException('请上传模板压缩包');
        }
        FileService::delDir(runtime_path('wap'));
        FileService::delDir(public_path('pages'));
        FileService::delDir(public_path('static'));
        @unlink(public_path() . 'index.html');
        $this->services->setStatus($id);
        FileService::zipOpen(public_path('template') . $name . '.zip', public_path());
        return app('json')->success('设置成功');
    }

    /**
     * 获取一条数据
     * @param int $id
     * @return mixed
     */
    public function getInfo(int $id, StoreProductServices $services, StoreSeckillServices $seckillServices, StoreCombinationServices $combinationServices, StoreBargainServices $bargainServices)
    {
        if (!$id) throw new AdminException('参数错误');
        $info = $this->services->get($id);
        if ($info) {
            $info = $info->toArray();
        } else {
            throw new AdminException('模板不存在');
        }
        if (!$info['value']) return app('json')->success(compact('info'));
        $info['value'] = json_decode($info['value'], true);
        $value_config = ['seckill', 'bargain', 'combination', 'goodList'];
        foreach ($info['value'] as $key => &$val) {
            if (in_array($key, $value_config) && is_array($val)) {
                if ($key == 'goodList') {
                    foreach ($val as $k => &$v) {
                        if (isset($v['ids']) && $v['ids'] && $v['tabConfig']['tabVal'] == 1) {
                            $v['goodsList']['list'] = $services->getSearchList(['ids' => $v['ids']]);
                        }
                    }
                }
                if ($key == "seckill") {
                    foreach ($val as $k => &$v) {
                        if (isset($v['ids']) && $v['ids'] && $v['tabConfig']['tabVal'] == 1) {
                            $v['goodsList']['list'] = $seckillServices->getDiySeckillList(['ids' => $v['ids']])['list'];
                        }
                    }
                }
                if ($key == "bargain") {
                    foreach ($val as $k => &$v) {
                        if (isset($v['ids']) && $v['ids'] && $v['tabConfig']['tabVal'] == 1) {
                            $v['goodsList']['list'] = $bargainServices->getHomeList(['ids' => $v['ids']])['list'];
                        }
                    }
                }
                if ($key == "combination") {
                    foreach ($val as $k => &$v) {
                        if (isset($v['ids']) && $v['ids'] && $v['tabConfig']['tabVal'] == 1) {
                            $v['goodsList']['list'] = $combinationServices->getHomeList(['ids' => $v['ids']])['list'];
                        }
                    }
                }

            }
        }
        return app('json')->success(compact('info'));
    }

    /**
     * 获取uni-app路径
     * @return mixed
     */
    public function getUrl()
    {
        $url = sys_data('uni_app_link');
        if ($url) {
            foreach ($url as &$link) {
                $link['url'] = $link['link'];
                $link['parameter'] = trim($link['param']);
            }
        } else {
            /** @var CacheServices $cache */
            $cache = app()->make(CacheServices::class);
            $url = $cache->getDbCache('uni_app_url', null);
        }
        return app('json')->success(compact('url'));
    }

    /**
     * 获取商品分类
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getCategory()
    {
        /** @var StoreCategoryServices $categoryService */
        $categoryService = app()->make(StoreCategoryServices::class);
        $list = $categoryService->getTierList(1, 1);
        $data = [];
        foreach ($list as $value) {
            $data[] = [
                'id' => $value['id'],
                'title' => $value['html'] . $value['cate_name']
            ];
        }
        return app('json')->success($data);
    }

    /**
     * 获取商品
     * @return mixed
     */
    public function getProduct()
    {
        $where = $this->request->getMore([
            ['id', 0],
            ['salesOrder', ''],
            ['priceOrder', ''],
        ]);
        $id = $where['id'];
        unset($where['id']);
        /** @var StoreCategoryServices $storeCategoryServices */
        $storeCategoryServices = app()->make(StoreCategoryServices::class);
        if ($storeCategoryServices->value(['id' => $id], 'pid')) {
            $where['sid'] = $id;
        } else {
            $where['cid'] = $id;
        }
        [$page, $limit] = $this->services->getPageValue();
        /** @var StoreProductServices $productService */
        $productService = app()->make(StoreProductServices::class);
        $list = $productService->getSearchList($where, $page, $limit);
        return app('json')->success($list);
    }

    /**
     * 获取提货点自提开启状态
     * @return mixed
     */
    public function getStoreStatus()
    {
        $data['store_status'] = sys_config('store_self_mention', 0);
        return app('json')->success($data);
    }

    /**
     * 还原模板数据
     * @param $id
     * @return mixed
     */
    public function Recovery($id)
    {
        if (!$id) throw new AdminException('参数错误');
        $info = $this->services->get($id);
        if ($info) {
            $info->value = $info->default_value;
            $info->update_time = time();
            $info->save();
            return app('json')->success('还原成功');
        } else {
            throw new AdminException('模板不存在');
        }
    }

    /**
     * 获取二级分类
     * @return mixed
     */
    public function getByCategory()
    {
        $where = $this->request->getMore([
            ['pid', -1],
            ['name', '']
        ]);
        /** @var StoreCategoryServices $categoryServices */
        $categoryServices = app()->make(StoreCategoryServices::class);
        return app('json')->success($categoryServices->getALlByIndex($where));
    }

    /**
     * 添加页面
     * @return mixed
     */
    public function create()
    {
        return app('json')->success($this->services->createForm());
    }

    /**
     * 保存页面
     * @return mixed
     */
    public function save()
    {
        $data = $this->request->postMore([
            ['name', ''],
            ['template_name', ''],
        ]);
        if (!$data['name']) throw new AdminException('请输入页面名称');
        if (!$data['template_name']) throw new AdminException('请输入页面类型');
        $data['version'] = '1.0';
        $data['add_time'] = time();
        $data['type'] = 2;
        $this->services->save($data);
        return app('json')->success('保存成功！');
    }

    /**
     * 设置默认数据
     * @param $id
     * @return mixed
     */
    public function setRecovery($id)
    {
        if (!$id) throw new AdminException('参数错误');
        $info = $this->services->get($id);
        if ($info) {
            $info->default_value = $info->value;
            $info->update_time = time();
            $info->save();
            return app('json')->success('设置成功');
        } else {
            throw new AdminException('模板不存在');
        }
    }

    /**
     * 获取商品列表
     * @return mixed
     */
    public function getProductList()
    {
        $where = $this->request->getMore([
            ['cate_id', ''],
            ['store_name', ''],
            ['type', 0],
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
        $list = $this->services->ProductList($where);
        return app('json')->success($list);
    }

    /**
     * 分类、个人中心、一键换色
     * @param $type
     * @return mixed
     */
    public function getColorChange($type)
    {
        $status = (int)$this->services->getColorChange((string)$type);
        return app('json')->success(compact('status'));
    }

    /**
     * 保存分类、个人中心、一键换色
     * @param $status
     * @param $type
     * @return mixed
     */
    public function colorChange($status, $type)
    {
        if (!$status) throw new AdminException('参数错误');
        $info = $this->services->get(['template_name' => $type, 'type' => 1]);
        if ($info) {
            $info->value = $status;
            $info->update_time = time();
            $info->save();
            return app('json')->success('设置成功');
        } else {
            throw new AdminException('模板不存在');
        }
    }

    /**
     * 获取个人中心数据
     * @return mixed
     */
    public function getMember()
    {
        $data = $this->services->getMemberData();
        return app('json')->success($data);
    }

    /**
     * 保存个人中心数据
     * @return mixed
     */
    public function memberSaveData()
    {
        $data = $this->request->postMore([
            ['status', 0],
            ['order_status', 0],
            ['my_banner_status', 0],
            ['routine_my_banner', []],
            ['routine_my_menus', []]
        ]);
        $this->services->memberSaveData($data);
        return app('json')->success('保存成功');
    }
}
