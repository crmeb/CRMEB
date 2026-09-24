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
namespace app\adminapi\controller\diy;

use app\adminapi\controller\AuthController;
use app\services\diy\DiyProServices;
use app\services\product\CenterProductServices;
use think\facade\App;

class DiyProController extends AuthController
{
    /**
     * @var DiyProServices
     */
    protected $services;


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
        // value 为空/非法 JSON 时降级为空数组，避免 foreach 空值报错
        $value = is_array($value) ? $value : [];
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
        $data['version'] = uniqid('', true);
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
            ['cate_id', []], //搜索分类
            ['salesOrder', ''], //销量排序
            ['priceOrder', ''], //价格排序
            ['store_label_id', []], //标签ID
            ['ids', ''], //商品ID
        ]);
        $where['is_show'] = 1;
        $where['is_del'] = 0;
        if (is_string($where['ids']) && $where['ids'] !== '') $where['ids'] = explode(',', $where['ids']);
        [$page, $limit] = $this->services->getPageValue();
        $list = app()->make(CenterProductServices::class)->getSearchList($where, $page, $limit, ['id,name as store_name,cate_id,image,IFNULL(sales, 0) + IFNULL(virtual_sales, 0) as sales,price,stock,original_price as ot_price,spec_type,recommend_image,unit_name,is_vip,vip_price']);
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

    public function textField()
    {
        $user = [
            ['label' => '用户名称', 'value' => 'nickname'],
            ['label' => '用户id', 'value' => 'uid'],
            ['label' => '用户头像', 'value' => 'image'],
            ['label' => '商品收藏', 'value' => 'collection_num'],
            ['label' => '商品加购', 'value' => 'cart_num'],
            ['label' => '订单总数', 'value' => 'order_num'],
            ['label' => '我的积分', 'value' => 'integral'],
            ['label' => '我的余额', 'value' => 'now_money'],
            ['label' => '我的佣金', 'value' => 'brokerage_price'],
            ['label' => '未读消息', 'value' => 'unread_msg_num'],
        ];

        $article = [
            ['label' => '文章标题', 'value' => 'title'],
            ['label' => '文章id', 'value' => 'id'],
            ['label' => '文章封面', 'value' => 'image'],
            ['label' => '文章分类', 'value' => 'cid_name'],
            ['label' => '文章简介', 'value' => 'synopsis'],
            ['label' => '文章浏览量', 'value' => 'visit'],
            ['label' => '添加时间', 'value' => 'add_time'],
        ];

        $coupon = [
            ['label' => '优惠券名称', 'value' => 'coupon_title'],
            ['label' => '优惠券id', 'value' => 'id'],
            ['label' => '优惠券类型', 'value' => 'type'],
            ['label' => '优惠券面值', 'value' => 'coupon_price'],
            ['label' => '优惠券状态', 'value' => 'status'],
            ['label' => '领取时间', 'value' => 'receive_time'],
            ['label' => '使用时间', 'value' => 'use_time'],
            ['label' => '使用门槛', 'value' => 'use_min_price'],
            ['label' => '发放数量', 'value' => 'receive_count'],
            ['label' => '添加时间', 'value' => 'add_time'],
        ];

        $product = [
            ['label' => '商品名称', 'value' => 'name'],
            ['label' => '商品id', 'value' => 'id'],
            ['label' => '商品图片', 'value' => 'image'],
            ['label' => '商品简介', 'value' => 'store_info'],
            ['label' => '商品单位', 'value' => 'unit_name'],
            ['label' => '商品分类', 'value' => 'cate_name'],
            ['label' => '商品库存', 'value' => 'stock'],
            ['label' => '商品售价', 'value' => 'price'],
            ['label' => '商品最高售价', 'value' => 'max_price'],
            ['label' => '商品最低售价', 'value' => 'min_price'],
            ['label' => '商品原价', 'value' => 'ot_price'],
            ['label' => '商品最高原价', 'value' => 'max_ot_price'],
            ['label' => '商品最低原价', 'value' => 'min_ot_price'],
            ['label' => '商品起购数量', 'value' => 'min_qty'],
            ['label' => '商品销量', 'value' => 'sales'],
            ['label' => '商品访问量', 'value' => 'browse'],
            ['label' => '商品添加时间', 'value' => 'add_time'],
        ];

        return app('json')->success(compact('user', 'article', 'coupon', 'product'));
    }
}
