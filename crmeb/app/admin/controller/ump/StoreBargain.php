<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/16 0016
 * Time: 10:39
 */

namespace app\admin\controller\ump;

use app\admin\controller\AuthController;
use app\admin\model\store\{StoreCategory,
    StoreDescription,
    StoreProductAttr,
    StoreProductAttrResult,
    StoreProduct as ProductModel,
    StoreProductAttrValue
};
use crmeb\traits\CurdControllerTrait;
use think\facade\Route as Url;
use app\admin\model\system\{SystemAttachment, ShippingTemplates};
use app\admin\model\ump\StoreBargain as StoreBargainModel;
use crmeb\services\{UtilService as Util, FormBuilder as Form, JsonService as Json};

//砍价
class StoreBargain extends AuthController
{
    use CurdControllerTrait;

    protected $bindModel = StoreBargainModel::class;

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $where = Util::getMore([
            ['status', ''],
            ['store_name', ''],
            ['export', 0],
            ['data', ''],
        ], $this->request);
        $limitTimeList = [
            'today' => implode(' - ', [date('Y/m/d'), date('Y/m/d', strtotime('+1 day'))]),
            'week' => implode(' - ', [
                date('Y/m/d', (time() - ((date('w') == 0 ? 7 : date('w')) - 1) * 24 * 3600)),
                date('Y/m/d', (time() + (7 - (date('w') == 0 ? 7 : date('w'))) * 24 * 3600))
            ]),
            'month' => implode(' - ', [date('Y/m') . '/01', date('Y/m') . '/' . date('t')]),
            'quarter' => implode(' - ', [
                date('Y') . '/' . (ceil((date('n')) / 3) * 3 - 3 + 1) . '/01',
                date('Y') . '/' . (ceil((date('n')) / 3) * 3) . '/' . date('t', mktime(0, 0, 0, (ceil((date('n')) / 3) * 3), 1, date('Y')))
            ]),
            'year' => implode(' - ', [
                date('Y') . '/01/01', date('Y/m/d', strtotime(date('Y') . '/01/01 + 1year -1 day'))
            ])
        ];
        $this->assign('where', $where);
        $this->assign('countBargain', StoreBargainModel::getCountBargain());
        $this->assign('limitTimeList', $limitTimeList);
        $this->assign(StoreBargainModel::systemPage($where));
        $this->assign('bargainId', StoreBargainModel::getBargainIdAll($where));
        return $this->fetch();
    }

    /**
     * 异步获取砍价数据
     */
    public function get_bargain_list()
    {
        $where = Util::getMore([
            ['page', 1],
            ['limit', 20],
            ['export', 0],
            ['store_name', ''],
            ['status', ''],
            ['data', '']
        ]);
        $bargainList = StoreBargainModel::systemPage($where);
        if (is_object($bargainList['list'])) $bargainList['list'] = $bargainList['list']->toArray();
        $data = $bargainList['list']['data'];
        foreach ($data as $k => $v) {
            $data[$k]['_stop_time'] = date('Y/m/d H:i:s', $v['stop_time']);
        }
        return Json::successlayui(['count' => $bargainList['list']['total'], 'data' => $data]);
    }


    /**
     * 添加砍价
     * @param int $id
     * @return \think\Response
     */
    public function create()
    {
        $f = [];
        $f[] = Form::frameImageOne('product', '选择商品', Url::buildUrl('productList', array('fodder' => 'product')))->icon('plus')->width('100%')->height('500px');
        $f[] = Form::hidden('product_id', '');
        $f[] = Form::input('title', '砍价活动名称');
        $f[] = Form::input('info', '砍价活动简介')->type('textarea');
        $f[] = Form::input('unit_name', '单位')->placeholder('个、位');
        $f[] = Form::select('temp_id', '砍价运费模板')->setOptions(function () {
            $list = ShippingTemplates::getList(['page' => 1, 'limit' => 20]);
            $menus = [];
            foreach ($list['data'] as $menu) {
                $menus[] = ['value' => $menu['id'], 'label' => $menu['name']];
            }
            return $menus;
        })->filterable(1)->col(12);
        $f[] = Form::dateTimeRange('section_time', '活动时间');//->format("yyyy-MM-dd HH:mm:ss");
        $f[] = Form::frameImageOne('image', '商品主图片(305*305px)', Url::buildUrl('admin/widget.images/index', array('fodder' => 'image')))->icon('image')->width('100%')->height('500px');
        $f[] = Form::frameImages('images', '商品轮播图(640*640px)', Url::buildUrl('admin/widget.images/index', array('fodder' => 'images')))->maxLength(5)->icon('images')->width('100%')->height('500px');
        $f[] = Form::number('bargain_max_price', '单次砍价的最大金额', 10)->min(0)->col(12);
        $f[] = Form::number('bargain_min_price', '单次砍价的最小金额', 0.01)->min(0)->col(12);
        $f[] = Form::number('sort', '排序')->col(12);
        $f[] = Form::number('give_integral', '赠送积分')->min(0)->col(12);
        $f[] = Form::radio('is_hot', '热门推荐', 1)->options([['label' => '开启', 'value' => 1], ['label' => '关闭', 'value' => 0]])->col(12);
        $form = Form::make_post_form('开启砍价活动', $f, Url::buildUrl('update'));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param int $id
     * @return \think\Response
     */
    public function edit($id)
    {
        if (!$id) return $this->failed('数据不存在');
        $product = StoreBargainModel::get($id);
        if (!$product) return $this->failed('数据不存在!');
        $f = [];
        $f[] = Form::input('product_id', '产品ID', $product->getData('product_id'))->disabled(true);
        $f[] = Form::input('title', '砍价活动名称', $product->getData('title'));
        $f[] = Form::input('info', '砍价活动简介', $product->getData('info'))->type('textarea');
        $f[] = Form::input('unit_name', '单位', $product->getData('unit_name'))->placeholder('个、位');
        $f[] = Form::select('temp_id', '砍价运费模板', (string)$product->getData('temp_id'))->setOptions(function () {
            $list = ShippingTemplates::getList(['page' => 1, 'limit' => 20]);
            $menus = [];
            foreach ($list['data'] as $menu) {
                $menus[] = ['value' => $menu['id'], 'label' => $menu['name']];
            }
            return $menus;
        })->filterable(1)->col(12);
        $f[] = Form::dateTimeRange('section_time', '活动时间', date("Y-m-d H:i:s", $product->getData('start_time')), date("Y-m-d H:i:s", $product->getData('stop_time')));//->format("yyyy-MM-dd HH:mm:ss");
        $f[] = Form::frameImageOne('image', '商品主图片(305*305px)', Url::buildUrl('admin/widget.images/index', array('fodder' => 'image')), $product->getData('image'))->icon('image')->width('100%')->height('500px');
        $f[] = Form::frameImages('images', '商品轮播图(640*640px)', Url::buildUrl('admin/widget.images/index', array('fodder' => 'images')), json_decode($product->getData('images'), 1))->maxLength(5)->icon('images')->width('100%')->height('500px');
        $f[] = Form::number('bargain_max_price', '单次砍价的最大金额', $product->getData('bargain_max_price'))->min(0)->col(12);
        $f[] = Form::number('bargain_min_price', '单次砍价的最小金额', $product->getData('bargain_min_price'))->min(0)->col(12);
        $f[] = Form::hidden('stock', $product->getData('stock'));
        $f[] = Form::hidden('sales', $product->getData('sales'));
        $f[] = Form::number('sort', '排序', $product->getData('sort'))->col(12);
        $f[] = Form::number('give_integral', '赠送积分', $product->getData('give_integral'))->min(0)->col(12);
        $f[] = Form::radio('is_hot', '热门推荐', $product->getData('is_hot'))->options([['label' => '开启', 'value' => 1], ['label' => '关闭', 'value' => 0]])->col(12);
        $f[] = Form::hidden('status', $product->getData('status'));
        $form = Form::make_post_form('添加用户通知', $f, Url::buildUrl('update', array('id' => $id)));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 保存更新的资源
     * @param string $id
     */
    public function update($id = '')
    {
        $data = Util::postMore([
            ['title', ''],
            ['info', ''],
            ['store_name', ''],
            ['unit_name', ''],
            ['section_time', []],
            ['image', ''],
            ['images', []],
            ['price', 0],
            ['min_price', 0],
            ['bargain_max_price', 0],
            ['bargain_min_price', 0],
            ['cost', 0],
            ['bargain_num', 1],
            ['stock', 0],
            ['sales', 0],
            ['sort', 0],
            ['num', 1],
            ['give_integral', 0],
            ['postage', 0],
            ['is_postage', 0],
            ['is_hot', 0],
            ['status', 0],
            ['product_id', 0],
            ['temp_id', ''],
            ['weight', ''],
            ['volume', ''],
        ]);
        $data['description'] = StoreDescription::getDescription($data['product_id']);
        $data['store_name'] = $data['title'];
        if ($data['title'] == '') return Json::fail('请输入砍价活动名称');
        if ($data['info'] == '') return Json::fail('请输入砍价活动简介');
        if ($data['store_name'] == '') return Json::fail('请输入砍价商品名称');
        if ($data['unit_name'] == '') return Json::fail('请输入商品单位');
        if (count($data['section_time']) < 1) return Json::fail('请选择活动时间');
        if (!$data['section_time'][0]) return Json::fail('请选择活动时间');
        if (!$data['section_time'][1]) return Json::fail('请选择活动时间');
        $data['start_time'] = strtotime($data['section_time'][0]);
        $data['stop_time'] = strtotime($data['section_time'][1]);
        unset($data['section_time']);
        if (!($data['image'])) return Json::fail('请选择推荐图');
        if (count($data['images']) < 1) return Json::fail('请选择轮播图');
        $data['images'] = json_encode($data['images']);
        if ($data['bargain_max_price'] == '' || $data['bargain_max_price'] < 0) return Json::fail('请输入用户单次砍价的最大金额');
        if ($data['bargain_min_price'] == '' || $data['bargain_min_price'] < 0) return Json::fail('请输入用户单次砍价的最小金额');
        if ($data['bargain_num'] == '' || $data['bargain_num'] < 0) return Json::fail('请输入用户单次砍价的次数');
        if ($data['num'] == '' || $data['num'] < 0) return Json::fail('请输入单次购买的砍价商品数量');
        unset($data['img']);
        if ($id) {
            $product = StoreBargainModel::get($id);
            if (!$product) return Json::fail('数据不存在!');
            unset($data['price'], $data['min_price']);
            $res = StoreBargainModel::edit($data, $id);
            if ($res) return Json::successful('修改成功');
            else return Json::fail('修改失败');
        } else {
            $data['add_time'] = time();
            $res = StoreBargainModel::create($data);
            $description['product_id'] = $res['id'];
            $description['description'] = htmlspecialchars_decode($data['description']);
            $description['type'] = 2;
            StoreDescription::create($description);
            if ($res) return Json::successful('添加成功');
            else return Json::fail('添加成功');
        }


    }

    /**
     * 删除指定资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        if (!$id) return Json::fail('数据不存在');
        $product = StoreBargainModel::get($id);
        if (!$product) return Json::fail('数据不存在!');
        if ($product['is_del']) return Json::fail('已删除!');
        $data['is_del'] = 1;
        if (StoreBargainModel::edit($data, $id))
            return Json::successful('删除成功!');
        else
            return Json::fail(StoreBargainModel::getErrorInfo('删除失败,请稍候再试!'));
    }

    /**
     * 显示内容窗口
     * @param $id
     * @return mixed|\think\response\Json|void
     */
    public function edit_content($id)
    {
        if (!$id) return $this->failed('数据不存在');
        $seckill = StoreBargainModel::get($id);
        if (!$seckill) return $this->failed('数据不存在');
        $this->assign([
            'content' => htmlspecialchars_decode(StoreDescription::getDescription($id, 2)),
            'field' => 'description',
            'action' => Url::buildUrl('change_field', ['id' => $id, 'field' => 'description'])
        ]);
        return $this->fetch('public/edit_content');
    }

    public function change_field($id, $field)
    {
        if (!$id) return $this->failed('数据不存在');
        $bargain = StoreBargainModel::get($id);
        if (!$bargain) return Json::fail('数据不存在!');
        if ($field == 'rule') {
            $data['rule'] = request()->post('rule');
        } else {
            $data['description'] = request()->post('description');
            StoreDescription::saveDescription($data['description'], $id, 2);
        }
        $res = StoreBargainModel::edit($data, $id);
        if ($res)
            return Json::successful('添加成功');
        else
            return Json::fail('添加失败');
    }

    public function edit_rule($id)
    {
        if (!$id) return $this->failed('数据不存在');
        $seckill = StoreBargainModel::get($id);
        if (!$seckill) return $this->failed('数据不存在');
        $this->assign([
            'content' => htmlspecialchars_decode(StoreBargainModel::where('id', $id)->value('rule')),
            'field' => 'rule',
            'action' => Url::buildUrl('change_field', ['id' => $id, 'field' => 'rule'])
        ]);
        return $this->fetch('public/edit_content');
    }

    /**
     * 开启砍价商品
     * @param int $id
     * @return mixed|\think\response\Json|void
     */
    public function bargain($id = 0)
    {
        if (!$id) return $this->failed('数据不存在');
        $product = ProductModel::get($id);
        if (!$product) return Json::fail('数据不存在!');
        $f = [];
        $f[] = Form::input('title', '砍价活动名称');
        $f[] = Form::input('info', '砍价活动简介')->type('textarea');
        $f[] = Form::hidden('product_id', $product->getData('id'));
        $f[] = Form::input('store_name', '砍价商品名称', $product->getData('store_name'));
        $f[] = Form::input('unit_name', '单位', $product->getData('unit_name'))->placeholder('个、位');
        $f[] = Form::select('temp_id', '砍价运费模板', (string)$product->getData('temp_id'))->setOptions(function () {
            $list = ShippingTemplates::getList(['page' => 1, 'limit' => 20]);
            $menus = [];
            foreach ($list['data'] as $menu) {
                $menus[] = ['value' => $menu['id'], 'label' => $menu['name']];
            }
            return $menus;
        })->filterable(1)->col(12);
        $f[] = Form::dateTimeRange('section_time', '活动时间');//->format("yyyy-MM-dd HH:mm:ss");
        $f[] = Form::frameImageOne('image', '商品主图片(305*305px)', Url::buildUrl('admin/widget.images/index', array('fodder' => 'image')), $product->getData('image'))->icon('image')->width('100%')->height('500px');
        $f[] = Form::frameImages('images', '商品轮播图(640*640px)', Url::buildUrl('admin/widget.images/index', array('fodder' => 'images')), json_decode($product->getData('slider_image'), 1))->maxLength(5)->icon('images')->width('100%')->height('500px');
        $f[] = Form::number('price', '砍价金额')->min(0)->col(12);
        $f[] = Form::number('min_price', '砍价最低金额', 0)->min(0)->col(12);
        $f[] = Form::number('bargain_max_price', '单次砍价的最大金额', 10)->min(0)->col(12);
        $f[] = Form::number('bargain_min_price', '单次砍价的最小金额', 0.01)->min(0)->precision(2)->col(12);
        $f[] = Form::number('cost', '成本价', $product->getData('cost'))->min(0)->col(12);
        $f[] = Form::number('bargain_num', '单次砍价的次数', 1)->min(0)->col(12);
        $f[] = Form::number('stock', '库存', $product->getData('stock'))->min(1)->col(12);
        $f[] = Form::number('sales', '销量', $product->getData('sales'))->min(0)->col(12);
        $f[] = Form::number('sort', '排序', $product->getData('sort'))->col(12);
        $f[] = Form::number('num', '单次购买的砍价商品数量', 1)->col(12);
        $f[] = Form::number('give_integral', '赠送积分', $product->getData('give_integral'))->min(0)->col(12);
        $f[] = Form::number('weight', '重量', 0)->min(0)->col(12);
        $f[] = Form::number('volume', '体积', 0)->min(0)->col(12);
        $f[] = Form::radio('is_hot', '热门推荐', $product->getData('is_hot'))->options([['label' => '开启', 'value' => 1], ['label' => '关闭', 'value' => 0]])->col(12);
        $f[] = Form::radio('status', '活动状态', 1)->options([['label' => '开启', 'value' => 1], ['label' => '关闭', 'value' => 0]])->col(12);
        $form = Form::make_post_form('开启砍价活动', $f, Url::buildUrl('update'));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 修改砍价状态
     * @param $status
     * @param int $id
     */
    public function set_bargain_status($status, $id = 0)
    {
        if (!$id) return Json::fail('参数错误');
        $res = StoreProductAttrValue::where('product_id', $id)->where('type', 2)->find();
        if (!$res) return Json::fail('请先配置规格');
        $res = StoreBargainModel::edit(['status' => $status], $id);
        if ($res) return Json::successful('修改成功');
        else return Json::fail('修改失败');
    }

    /**
     * 砍价属性选择页面
     * @param $id
     * @return string|void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function attr_list($id)
    {
        if (!$id) return $this->failed('数据不存在!');
        $bargainInfo = StoreBargainModel::where('id', $id)->find();
        $bargainResult = StoreProductAttrResult::where('product_id', $id)->where('type', 2)->value('result');
        $productResult = StoreProductAttrResult::where('product_id', $bargainInfo['product_id'])->where('type', 0)->value('result');
        if ($productResult) {
            $attr = json_decode($productResult, true)['attr'];
            $productAttr = $this->get_attr($attr, $bargainInfo['product_id'], 0);
            $bargainAttr = $this->get_attr($attr, $id, 2);
            foreach ($productAttr as $pk => $pv) {
                foreach ($bargainAttr as $sv) {
                    if ($pv['detail'] == $sv['detail']) {
                        $productAttr[$pk] = $sv;
                    }
                }
            }
        } else {
            if ($bargainResult) {
                $attr = json_decode($bargainResult, true)['attr'];
                $productAttr = $this->get_attr($attr, $id, 2);
            } else {
                $attr[0]['value'] = '默认';
                $attr[0]['detailValue'] = '';
                $attr[0]['attrHidden'] = '';
                $attr[0]['detail'][0] = '默认';
                $productAttr[0]['value1'] = '默认';
                $productAttr[0]['detail'] = json_encode(['默认' => '默认']);
                $productAttr[0]['pic'] = $bargainInfo['image'];
                $productAttr[0]['price'] = $bargainInfo['price'];
                $productAttr[0]['min_price'] = 0;
                $productAttr[0]['cost'] = $bargainInfo['cost'];
                $productAttr[0]['ot_price'] = $bargainInfo['ot_price'];
                $productAttr[0]['stock'] = $bargainInfo['stock'];
                $productAttr[0]['quota'] = 0;
                $productAttr[0]['bar_code'] = $bargainInfo['bar_code'];
                $productAttr[0]['weight'] = 0;
                $productAttr[0]['volume'] = 0;
                $productAttr[0]['brokerage'] = 0;
                $productAttr[0]['brokerage_two'] = 0;
                $productAttr[0]['check'] = 0;
            }
        }
        $attrs['attr'] = $attr;
        $attrs['value'] = $productAttr;
        $this->assign('attr', $attrs);
        $this->assign('id', $id);
        return $this->fetch();
    }

    /**
     * 砍价属性添加
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function save_attr()
    {
        $data = Util::postMore([
            ['attr', []],
            ['ids', []],
            ['id', 0],
        ]);
        if (!$data['id']) return Json::fail('数据不存在!');
        if (!$data['ids']) return Json::fail('你没有选择任何规格!');
        $productId = StoreBargainModel::where('id', $data['id'])->value('product_id');
        $attr = json_decode(StoreProductAttrResult::where('product_id', $productId)->where('type', 0)->value('result'), true)['attr'];
        foreach ($data['attr'] as $k => $v) {
            if (in_array($k, $data['ids'])) {
                $v['detail'] = json_decode(htmlspecialchars_decode($v['detail']), true);
                $min_price = $v['min_price'];
                unset($v['min_price']);
                $detail[$k] = $v;
                break;
            }
        }
        if (min(array_column($detail, 'quota')) == 0) return Json::fail('限购不能为0');
        $price = min(array_column($detail, 'price'));
        $quota = array_sum(array_column($detail, 'quota'));
        $stock = array_sum(array_column($detail, 'stock'));
        if (!$attr) {
            $attr[0]['value'] = '默认';
            $attr[0]['detailValue'] = '';
            $attr[0]['attrHidden'] = '';
            $attr[0]['detail'][0] = '默认';
        }
        StoreProductAttr::createProductAttr($attr, $detail, $data['id'], 2);
        StoreBargainModel::where('id', $data['id'])->update(['stock' => $stock, 'quota' => $quota, 'quota_show' => $quota, 'price' => $price, 'min_price' => $min_price]);
        return Json::successful('添加成功!');
    }

    /**
     * 添加砍价获取商品列表
     * @return string
     * @throws \Exception
     */
    public function productList()
    {
        $cate = StoreCategory::getTierList(null, 1);
        $this->assign('cate', $cate);
        return $this->fetch();
    }

    /**
     * 获取砍价商品规格
     * @param $attr
     * @param $id
     * @param $type
     * @return array
     */
    public function get_attr($attr, $id, $type)
    {
        $value = attr_format($attr)[1];
        $valueNew = [];
        $count = 0;
        $min_price = StoreBargainModel::where('id', $id)->value('min_price');
        foreach ($value as $key => $item) {
            $detail = $item['detail'];
            sort($item['detail'], SORT_STRING);
            $suk = implode(',', $item['detail']);
            $sukValue = StoreProductAttrValue::where('product_id', $id)->where('type', $type)->where('suk', $suk)->column('bar_code,cost,price,ot_price,stock,image as pic,weight,volume,brokerage,brokerage_two,quota', 'suk');
            if (count($sukValue)) {
                foreach (array_values($detail) as $k => $v) {
                    $valueNew[$count]['value' . ($k + 1)] = $v;
                }
                $valueNew[$count]['detail'] = json_encode($detail);
                $valueNew[$count]['pic'] = $sukValue[$suk]['pic'] ?? '';
                $valueNew[$count]['price'] = isset($sukValue[$suk]['price']) ? floatval($sukValue[$suk]['price']) : 0;
                $valueNew[$count]['min_price'] = $min_price;
                $valueNew[$count]['cost'] = isset($sukValue[$suk]['cost']) ? floatval($sukValue[$suk]['cost']) : 0;
                $valueNew[$count]['ot_price'] = isset($sukValue[$suk]['ot_price']) ? floatval($sukValue[$suk]['ot_price']) : 0;
                $valueNew[$count]['stock'] = isset($sukValue[$suk]['stock']) ? intval($sukValue[$suk]['stock']) : 0;
                $valueNew[$count]['quota'] = isset($sukValue[$suk]['quota']) ? intval($sukValue[$suk]['quota']) : 0;
                $valueNew[$count]['bar_code'] = $sukValue[$suk]['bar_code'] ?? '';
                $valueNew[$count]['weight'] = $sukValue[$suk]['weight'] ?? 0;
                $valueNew[$count]['volume'] = $sukValue[$suk]['volume'] ?? 0;
                $valueNew[$count]['brokerage'] = $sukValue[$suk]['brokerage'] ?? 0;
                $valueNew[$count]['brokerage_two'] = $sukValue[$suk]['brokerage_two'] ?? 0;
                $valueNew[$count]['check'] = $type != 0 ? 1 : 0;
                $count++;
            }
        }
        return $valueNew;
    }
}