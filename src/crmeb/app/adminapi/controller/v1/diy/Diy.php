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
            ['type', ''],
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
        $value = is_string($data['value']) ? json_decode($data['value'], true) : $data['value'];
        if (isset($value['d_goodList']['selectConfig']['list'])) {
            unset($value['d_goodList']['selectConfig']['list']);
        }
        if (isset($value['d_goodList']['goodsList']['list'])) {
            $limitMax = config('database.page.limitMax', 50);
            if (isset($value['d_goodList']['numConfig']['val']) && isset($value['d_goodList']['tabConfig']['tabVal']) && $value['d_goodList']['tabConfig']['tabVal'] == 0 && $value['d_goodList']['numConfig']['val'] > $limitMax) {
                return app('json')->fail('您设置得商品个数超出系统限制,最大限制' . $limitMax . '个商品');
            }
            $value['d_goodList']['goodsList']['ids'] = array_column($value['d_goodList']['goodsList']['list'], 'id');
            unset($value['d_goodList']['goodsList']['list']);
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
        $this->services->setStatus($id);
        $name = $this->services->value(['id' => $id], 'template_name');
        FileService::delDir(runtime_path('wap'));
        FileService::delDir(public_path('pages'));
        FileService::delDir(public_path('static'));
        @unlink(public_path() . 'index.html');
        FileService::zipOpen(public_path('template') . $name . '.zip', public_path());
        return app('json')->success('设置成功');
    }

    /**
     * 获取一条数据
     * @param int $id
     * @return mixed
     */
    public function getInfo(int $id, StoreProductServices $services)
    {
        if (!$id) throw new AdminException('参数错误');
        $info = $this->services->get($id);
        if ($info) {
            $info = $info->toArray();
        } else {
            throw new AdminException('模板不存在');
        }
        $info['value'] = json_decode($info['value'], true);
        if ($info['value']) {
            if (isset($info['value']['d_goodList']['goodsList'])) {
                $info['value']['d_goodList']['goodsList']['list'] = [];
            }
            if (isset($info['value']['d_goodList']['goodsList']['ids']) && count($info['value']['d_goodList']['goodsList']['ids'])) {
                $info['value']['d_goodList']['goodsList']['list'] = $services->getSearchList(['ids' => $info['value']['d_goodList']['goodsList']['ids']]);
            }
        }
//        $info['value'] = json_decode($info['value'], true);
        return app('json')->success(compact('info'));
    }

    /**
     * 获取uni-app路径
     * @return mixed
     */
    public function getUrl()
    {
        /** @var CacheServices $cache */
        $cache = app()->make(CacheServices::class);
        $url = $cache->getDbCache('uni_app_url', null);
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
}
