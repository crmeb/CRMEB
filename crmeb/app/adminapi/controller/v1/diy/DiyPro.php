<?php

namespace app\adminapi\controller\v1\diy;

use app\adminapi\controller\AuthController;
use app\services\diy\DiyProServices;
use app\services\product\product\StoreProductServices;
use think\facade\App;

class DiyPro extends AuthController
{
    public function __construct(App $app, DiyProServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    public function getList()
    {
        return app('json')->success($this->services->getList());
    }

    public function getInfo($id = 0)
    {
        if ($id == 0) return app('json')->fail('参数错误');
        return app('json')->success($this->services->getInfo($id));
    }

    public function saveInfo($id = 0)
    {
        $data = $this->request->postMore([
            ['name', ''],
            ['title', ''],
            ['value', ''],
            ['type', 1],
            ['cover_image', ''],
            ['is_show', 0],
            ['is_bg_color', 0],
            ['is_bg_pic', 0],
            ['bg_tab_val', 0],
            ['color_picker', ''],
            ['bg_pic', ''],
            ['is_diy', 1],
            ['is_pro', 1],
            ['type', 2],
        ]);
        $value = is_string($data['value']) ? json_decode($data['value'], true) : $data['value'];
        foreach ($value as &$item) {
            if ($item['name'] === 'goodList') {
                if (isset($item['selectConfig']['list'])) {
                    unset($item['selectConfig']['list']);
                }
                if (isset($item['goodsList']['list']) && is_array($item['goodsList']['list'])) {
                    $limitMax = config('database.page.limitMax', 50);
                    if (isset($item['numConfig']['val']) && isset($item['tabConfig']['tabVal']) && $item['tabConfig']['tabVal'] == 0 && $item['numConfig']['val'] > $limitMax) {
                        return app('json')->fail('您设置得商品个数超出系统限制,最大限制' . $limitMax . '个商品');
                    }
                    $item['goodsList']['ids'] = array_column($item['goodsList']['list'], 'id');
                    unset($item['goodsList']['list'], $item['productList']['list']);
                }
            } elseif ($item['name'] === 'articleList') {
                if (isset($item['selectList']['list']) && is_array($item['selectList']['list'])) {
                    unset($item['selectList']['list']);
                }
            } elseif ($item['name'] === 'promotionList') {
                if (isset($item['tabConfig']['list']) && $item['tabConfig']['list']) {
                    $list = $item['tabConfig']['list'];
                    foreach ($list as &$tabValue) {
                        if (isset($tabValue['goodsList']['list']) && is_array($tabValue['goodsList']['list'])) {
                            $limitMax = config('database.page.limitMax', 50);
                            if (isset($tabValue['numConfig']['val']) && isset($tabValue['tabConfig']['tabVal']) && $tabValue['tabConfig']['tabVal'] == 0 && $tabValue['numConfig']['val'] > $limitMax) {
                                return app('json')->fail('您设置得商品个数超出系统限制,最大限制' . $limitMax . '个商品');
                            }
                            $tabValue['goodsList']['ids'] = array_column($tabValue['goodsList']['list'], 'id');
                        }
                        unset($tabValue['goodsList']['list'], $tabValue['productList']['list']);
                    }
                    $item['tabConfig']['list'] = $list;
                }
            } elseif ($item['name'] === 'newVip') {
                unset($item['newVipList']['list']);
            } elseif ($item['name'] === 'shortVideo') {
                unset($item['videoList']);
            }
        }
        $data['value'] = json_encode($value);
        $data['version'] = uniqid();
        return app('json')->success($id ? '修改成功' : '保存成功', ['id' => $this->services->saveInfo($id, $data)]);
    }

    public function delInfo($id)
    {
        $this->services->delInfo($id);
        return app('json')->success('删除成功');
    }

    public function setInfoStatus($id)
    {
        return app('json')->success($this->services->setInfoStatus($id));
    }

    public function getProduct()
    {
        $where = $this->request->getMore([
            ['cate_id', []],//搜索分类
            ['salesOrder', ''],//销量排序
            ['priceOrder', ''],//价格排序
            ['store_label_id', []],//标签ID
            ['ids', []],//商品ID
        ]);
        $where['is_show'] = 1;
        $where['is_del'] = 0;
        [$page, $limit] = $this->services->getPageValue();
        $list = app()->make(StoreProductServices::class)->getSearchList($where, $page, $limit, ['id,store_name,cate_id,image,IFNULL(sales, 0) + IFNULL(ficti, 0) as sales,price,stock,activity,ot_price,spec_type,recommend_image,unit_name,is_vip,vip_price']);
        return app('json')->success($list);
    }

    public function updateName($id = 0)
    {
        [$name] = $this->request->postMore([
            ['name', '']
        ], true);
        if (!$name) return app('json')->fail('请输入名称');
        $this->services->updateName($id, $name);
        return app('json')->success('修改成功');
    }

    public function exportDIYData($id)
    {
        $value = $this->services->exportDIYData($id);
        $filename = 'DIY数据_' . date('YmdHis', time()) . '.txt';
        return app('json')->success('导出成功', ['value' => $value, 'filename' => $filename]);
    }

    public function importDIYData()
    {
        // 获取文件
        $file = $this->request->file('file');
        if (!$file) return app('json')->fail('请上传文件');

        // 获取文件的临时路径
        $tempPath = $file->getRealPath();

        // 使用文件流读取内容
        $content = file_get_contents($tempPath);

        // 保存内容
        $this->services->importDIYData($content);
        return app('json')->success('导入成功');
    }
}
