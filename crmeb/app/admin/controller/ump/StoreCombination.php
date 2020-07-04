<?php

namespace app\admin\controller\ump;

use app\admin\controller\AuthController;
use app\admin\model\store\{StoreCategory,
    StoreDescription,
    StoreProductAttr,
    StoreProductAttrResult,
    StoreProduct as ProductModel,
    StoreProductAttrValue};
use crmeb\traits\CurdControllerTrait;
use app\admin\model\ump\{StorePink,
    StoreCombinationAttr,
    StoreCombinationAttrResult,
    StoreCombination as StoreCombinationModel};
use think\facade\Route as Url;
use app\admin\model\system\{SystemAttachment, ShippingTemplates};
use crmeb\services\{FormBuilder as Form, UtilService as Util, JsonService as Json};

/**
 * 拼团管理
 * Class StoreCombination
 * @package app\admin\controller\store
 */
class StoreCombination extends AuthController
{

    use CurdControllerTrait;

    protected $bindModel = StoreCombinationModel::class;

    /**
     * @return mixed
     */
    public function index()
    {
        $this->assign('countCombination', StoreCombinationModel::getCombinationCount());
        $this->assign(StoreCombinationModel::getStatistics());
        $this->assign('combinationId', StoreCombinationModel::getCombinationIdAll());
        return $this->fetch();
    }

    public function save_excel()
    {
        $where = Util::getMore([
            ['is_show', ''],
            ['store_name', ''],
        ]);
        StoreCombinationModel::SaveExcel($where);
    }

    /**
     * 异步获取拼团数据
     */
    public function get_combination_list()
    {
        $where = Util::getMore([
            ['page', 1],
            ['limit', 20],
            ['export', 0],
            ['is_show', ''],
            ['is_host', ''],
            ['store_name', '']
        ]);
        $combinationList = StoreCombinationModel::systemPage($where);
        if (is_object($combinationList['list'])) $combinationList['list'] = $combinationList['list']->toArray();
        $data = $combinationList['list']['data'];
        foreach ($data as $k => $v) {
            $data[$k]['_stop_time'] = date('Y/m/d H:i:s', $v['stop_time']);
        }
        return Json::successlayui(['count' => $combinationList['list']['total'], 'data' => $data]);
    }

    public function combination($id = 0)
    {
        if (!$id) return $this->failed('数据不存在');
        $product = ProductModel::get($id);
        if (!$product) return Json::fail('数据不存在!');
        $f = [];
        $f[] = Form::hidden('product_id', $id);
        $f[] = Form::input('title', '拼团名称', $product->getData('store_name'));
        $f[] = Form::input('info', '拼团简介', $product->getData('store_info'))->type('textarea');
        $f[] = Form::input('unit_name', '单位', $product->getData('unit_name'))->placeholder('个、位');
        $f[] = Form::select('temp_id', '砍价运费模板', (string)$product->getData('temp_id'))->setOptions(function () {
            $list = ShippingTemplates::getList(['page' => 1, 'limit' => 20]);
            $menus = [];
            foreach ($list['data'] as $menu) {
                $menus[] = ['value' => $menu['id'], 'label' => $menu['name']];
            }
            return $menus;
        })->filterable(1)->col(12);
        $f[] = Form::dateTimeRange('section_time', '拼团时间');
        $f[] = Form::frameImageOne('image', '商品主图片(305*305px)', Url::buildUrl('admin/widget.images/index', array('fodder' => 'image')), $product->getData('image'))->icon('image')->width('100%')->height('500px');
        $f[] = Form::frameImages('images', '商品轮播图(640*640px)', Url::buildUrl('admin/widget.images/index', array('fodder' => 'images')), json_decode($product->getData('slider_image')))->maxLength(5)->icon('images')->width('100%')->height('500px');
        $f[] = Form::number('effective_time', '拼团时效(单位 小时)', 24)->placeholder('请输入拼团有效时间，单位：小时');
        $f[] = Form::number('price', '拼团价')->min(0)->col(12);
        $f[] = Form::number('people', '拼团人数')->min(2)->col(12);
        $f[] = Form::number('stock', '库存', $product->getData('stock'))->min(0)->precision(0)->col(12);
        $f[] = Form::number('sales', '销量', $product->getData('sales'))->min(0)->precision(0)->col(12);
        $f[] = Form::number('sort', '排序')->col(12);
        $f[] = Form::number('weight', '重量', 0)->min(0)->col(12);
        $f[] = Form::number('volume', '体积', 0)->min(0)->col(12);
        $f[] = Form::radio('is_host', '热门推荐', 1)->options([['label' => '开启', 'value' => 1], ['label' => '关闭', 'value' => 0]])->col(12);
        $f[] = Form::radio('is_show', '活动状态', 1)->options([['label' => '开启', 'value' => 1], ['label' => '关闭', 'value' => 0]])->col(12);
        $form = Form::make_post_form('添加用户通知', $f, Url::buildUrl('save'));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        $f = [];
        $f[] = Form::frameImageOne('product', '选择商品', Url::buildUrl('productList', array('fodder' => 'product')))->icon('plus')->width('100%')->height('500px');
        $f[] = Form::hidden('product_id', '');
        $f[] = Form::input('title', '拼团名称');
        $f[] = Form::input('info', '拼团简介')->type('textarea');
        $f[] = Form::input('unit_name', '单位')->placeholder('个、位');
        $f[] = Form::select('temp_id', '秒杀运费模板')->setOptions(function () {
            $list = ShippingTemplates::getList(['page' => 1, 'limit' => 20]);
            $menus = [];
            foreach ($list['data'] as $menu) {
                $menus[] = ['value' => $menu['id'], 'label' => $menu['name']];
            }
            return $menus;
        })->filterable(1)->col(12);
        $f[] = Form::dateTimeRange('section_time', '拼团时间');
        $f[] = Form::frameImageOne('image', '商品主图片(305*305px)', Url::buildUrl('admin/widget.images/index', array('fodder' => 'image')))->icon('image')->width('100%')->height('500px');
        $f[] = Form::frameImages('images', '商品轮播图(640*640px)', Url::buildUrl('admin/widget.images/index', array('fodder' => 'images')))->maxLength(5)->icon('images')->width('100%')->height('500px');
        $f[] = Form::number('effective_time', '拼团时效', '24')->placeholder('请输入拼团订单有效时间，单位：小时')->col(12);
        $f[] = Form::number('people', '拼团人数', 2)->min(2)->col(12);
        $f[] = Form::number('num', '单次购买商品个数', 1)->min(1)->col(12);
        $f[] = Form::number('sort', '排序')->col(12);
        $f[] = Form::radio('is_host', '热门推荐', 1)->options([['label' => '开启', 'value' => 1], ['label' => '关闭', 'value' => 0]])->col(12);
        $form = Form::make_post_form('添加用户通知', $f, Url::buildUrl('save'));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 保存新建的资源
     * @param int $id
     */
    public function save($id = 0)
    {
        $data = Util::postMore([
            'product_id',
            'title',
            'info',
            ['unit_name', '个'],
            ['image', ''],
            ['images', []],
            ['section_time', []],
            ['effective_time', 0],
            ['postage', 0],
            ['price', 0],
            ['people', 0],
            ['sort', 0],
            ['stock', 0],
            ['sales', 0],
            ['is_show', 0],
            ['is_host', 0],
            ['is_postage', 0],
            ['temp_id', ''],
            ['weight', ''],
            ['volume', ''],
            ['num', 1],
        ]);
        $data['description'] = StoreDescription::getDescription($data['product_id']);
        if (!$data['title']) return Json::fail('请输入拼团名称');
        if (!$data['info']) return Json::fail('请输入拼团简介');
        if (!$data['image']) return Json::fail('请上传商品图片');
        if (count($data['images']) < 1) return Json::fail('请上传商品轮播图');
        if ($data['effective_time'] == 0 || $data['effective_time'] < 0) return Json::fail('请输入拼团有效时间');
        if ($data['people'] == '' || $data['people'] < 1) return Json::fail('请输入拼团人数');
        if (count($data['section_time']) < 1) return Json::fail('请选择活动时间');
        $data['images'] = json_encode($data['images']);
        $data['start_time'] = strtotime($data['section_time'][0]);
        $data['stop_time'] = strtotime($data['section_time'][1]);
        unset($data['section_time']);
        if ($id) {
            unset($data['description']);
            $product = StoreCombinationModel::get($id);
            if (!$product) return Json::fail('数据不存在!');
            $data['product_id'] = $product['product_id'];
            StoreCombinationModel::edit($data, $id);
            return Json::successful('编辑成功!');
        } else {
            $data['add_time'] = time();
            $res = StoreCombinationModel::create($data);
            $description['product_id'] = $res['id'];
            $description['description'] = htmlspecialchars_decode($data['description']);
            $description['type'] = 3;
            StoreDescription::create($description);
            return Json::successful('添加拼团成功!');
        }

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
        $product = StoreCombinationModel::get($id);
        if (!$product) return Json::fail('数据不存在!');
        $f = [];
        $f[] = Form::input('product_id', '产品ID', $product->getData('product_id'))->disabled(true);
        $f[] = Form::input('title', '拼团名称', $product->getData('title'));
        $f[] = Form::input('info', '拼团简介', $product->getData('info'))->type('textarea');
        $f[] = Form::input('unit_name', '单位', $product->getData('unit_name'))->placeholder('个、位');
        $f[] = Form::select('temp_id', '砍价运费模板', (string)$product->getData('temp_id'))->setOptions(function () {
            $list = ShippingTemplates::getList(['page' => 1, 'limit' => 20]);
            $menus = [];
            foreach ($list['data'] as $menu) {
                $menus[] = ['value' => $menu['id'], 'label' => $menu['name']];
            }
            return $menus;
        })->filterable(1)->col(12);
        $f[] = Form::dateTimeRange('section_time', '拼团时间', date("Y-m-d H:i:s", $product->getData('start_time')), date("Y-m-d H:i:s", $product->getData('stop_time')));
        $f[] = Form::frameImageOne('image', '商品主图片(305*305px)', Url::buildUrl('admin/widget.images/index', array('fodder' => 'image')), $product->getData('image'))->icon('image')->width('100%')->height('500px');
        $f[] = Form::frameImages('images', '商品轮播图(640*640px)', Url::buildUrl('admin/widget.images/index', array('fodder' => 'images')), json_decode($product->getData('images')))->maxLength(5)->icon('images')->width('100%')->height('500px');
        $f[] = Form::number('effective_time', '拼团时效(单位 小时)', $product->getData('effective_time'))->placeholder('请输入拼团订单有效时间，单位：小时')->col(12);
        $f[] = Form::hidden('price', $product->getData('price'));
        $f[] = Form::number('people', '拼团人数', $product->getData('people'))->min(2)->col(12);
        $f[] = Form::number('num', '单次购买商品个数', $product->getData('num'))->min(1)->col(12);
        $f[] = Form::hidden('stock', $product->getData('stock'));
        $f[] = Form::hidden('sales', $product->getData('sales'));
        $f[] = Form::number('sort', '排序', $product->getData('sort'))->col(12);
        $f[] = Form::radio('is_host', '热门推荐', $product->getData('is_host'))->options([['label' => '开启', 'value' => 1], ['label' => '关闭', 'value' => 0]])->col(12);
        $f[] = Form::hidden('is_show', $product->getData('is_show'));
        $form = Form::make_post_form('添加用户通知', $f, Url::buildUrl('save', compact('id')));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 删除指定资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        if (!$id) return $this->failed('数据不存在');
        $product = StoreCombinationModel::get($id);
        if (!$product) return Json::fail('数据不存在!');
        if ($product['is_del']) return Json::fail('已删除!');
        $data['is_del'] = 1;
        if (!StoreCombinationModel::edit($data, $id))
            return Json::fail(StoreCombinationModel::getErrorInfo('删除失败,请稍候再试!'));
        else
            return Json::successful('删除成功!');
    }

    /**
     * 属性页面
     * @param $id
     * @return mixed|void
     */
    public function attr($id)
    {
        if (!$id) return $this->failed('数据不存在!');
        $result = StoreCombinationAttrResult::getResult($id);
        $image = StoreCombinationModel::where('id', $id)->value('image');
        $this->assign(compact('id', 'result', 'product', 'image'));
        return $this->fetch();
    }

    /**
     * 拼团属性选择页面
     * @param $id
     * @return string|void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function attr_list($id)
    {
        if (!$id) return $this->failed('数据不存在!');
        $combinationInfo = StoreCombinationModel::where('id', $id)->find();
        $combinationResult = StoreProductAttrResult::where('product_id', $id)->where('type', 3)->value('result');
        $productResult = StoreProductAttrResult::where('product_id', $combinationInfo['product_id'])->where('type', 0)->value('result');
        if ($productResult) {
            $attr = json_decode($productResult, true)['attr'];
            $productAttr = $this->get_attr($attr, $combinationInfo['product_id'], 0);
            $combinationAttr = $this->get_attr($attr, $id, 3);
            foreach ($productAttr as $pk => $pv) {
                foreach ($combinationAttr as $sv) {
                    if ($pv['detail'] == $sv['detail']) {
                        $productAttr[$pk] = $sv;
                    }
                }
            }
        } else {
            if ($combinationResult) {
                $attr = json_decode($combinationResult, true)['attr'];
                $productAttr = $this->get_attr($attr, $id, 3);
            } else {
                $attr[0]['value'] = '默认';
                $attr[0]['detailValue'] = '';
                $attr[0]['attrHidden'] = '';
                $attr[0]['detail'][0] = '默认';
                $productAttr[0]['value1'] = '默认';
                $productAttr[0]['detail'] = json_encode(['默认' => '默认']);
                $productAttr[0]['pic'] = $combinationInfo['image'];
                $productAttr[0]['price'] = $combinationInfo['price'];
                $productAttr[0]['cost'] = $combinationInfo['cost'];
                $productAttr[0]['ot_price'] = $combinationInfo['ot_price'];
                $productAttr[0]['stock'] = $combinationInfo['stock'];
                $productAttr[0]['quota'] = 0;
                $productAttr[0]['bar_code'] = $combinationInfo['bar_code'];
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
     * 拼团属性添加
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
        $productId = StoreCombinationModel::where('id', $data['id'])->value('product_id');
        $attr = json_decode(StoreProductAttrResult::where('product_id', $productId)->where('type', 0)->value('result'), true)['attr'];
        foreach ($data['attr'] as $k => $v) {
            if (in_array($k, $data['ids'])) {
                $v['detail'] = json_decode(htmlspecialchars_decode($v['detail']), true);
                if($v['price'] > $v['ot_price']){
                    return Json::fail('售价不能大于原价');
                }
                $detail[$k] = $v;
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
        StoreProductAttr::createProductAttr($attr, $detail, $data['id'], 3);
        StoreCombinationModel::where('id', $data['id'])->update(['stock' => $stock, 'quota' => $quota, 'quota_show' => $quota, 'price' => $price]);
        return Json::successful('添加成功!');
    }

    /**
     * 生成属性
     * @param int $id
     */
    public function is_format_attr($id = 0)
    {
        if (!$id) return Json::fail('商品不存在');
        list($attr, $detail) = Util::postMore([
            ['items', []],
            ['attrs', []]
        ], $this->request, true);
        $product = StoreCombinationModel::get($id);
        if (!$product) return Json::fail('商品不存在');
        $attrFormat = attr_format($attr)[1];
        if (count($detail)) {
            foreach ($attrFormat as $k => $v) {
                foreach ($detail as $kk => $vv) {
                    if ($v['detail'] == $vv['detail']) {
                        $attrFormat[$k]['price'] = $vv['price'];
                        $attrFormat[$k]['sales'] = $vv['sales'];
                        $attrFormat[$k]['pic'] = $vv['pic'];
                        $attrFormat[$k]['check'] = false;
                        break;
                    } else {
                        $attrFormat[$k]['price'] = '';
                        $attrFormat[$k]['sales'] = '';
                        $attrFormat[$k]['pic'] = $product['image'];
                        $attrFormat[$k]['check'] = true;
                    }
                }
            }
        } else {
            foreach ($attrFormat as $k => $v) {
                $attrFormat[$k]['price'] = $product['price'];
                $attrFormat[$k]['sales'] = $product['stock'];
                $attrFormat[$k]['pic'] = $product['image'];
                $attrFormat[$k]['check'] = false;
            }
        }
        return Json::successful($attrFormat);
    }

    /**
     * 添加 修改属性
     * @param $id
     */
    public function set_attr($id)
    {
        if (!$id) return $this->failed('商品不存在!');
        list($attr, $detail) = Util::postMore([
            ['items', []],
            ['attrs', []]
        ], $this->request, true);
        $res = StoreCombinationAttr::createProductAttr($attr, $detail, $id);
        if ($res)
            return $this->successful('编辑属性成功!');
        else
            return $this->failed(StoreCombinationAttr::getErrorInfo());
    }

    /**
     * 清除属性
     * @param $id
     */
    public function clear_attr($id)
    {
        if (!$id) return $this->failed('商品不存在!');
        if (false !== StoreCombinationAttr::clearProductAttr($id) && false !== StoreCombinationAttrResult::clearResult($id))
            return $this->successful('清空商品属性成功!');
        else
            return $this->failed(StoreCombinationAttr::getErrorInfo('清空商品属性失败!'));
    }

    public function edit_content($id)
    {
        if (!$id) return $this->failed('数据不存在');
        $product = StoreCombinationModel::get($id);
        if (!$product) return Json::fail('数据不存在!');
        $this->assign([
            'content' => htmlspecialchars_decode(StoreDescription::getDescription($id, 3)),
            'field' => 'description',
            'action' => Url::buildUrl('change_field', ['id' => $id, 'field' => 'description'])
        ]);
        return $this->fetch('public/edit_content');
    }

    public function change_field($id)
    {
        if (!$id) return $this->failed('数据不存在');
        $combination = StoreCombinationModel::get($id);
        if (!$combination) return Json::fail('数据不存在!');
        $data['description'] = request()->post('description');
        StoreDescription::saveDescription($data['description'], $id, 3);
        $res = StoreCombinationModel::edit($data, $id);
        if ($res)
            return Json::successful('添加成功');
        else
            return Json::fail('添加失败');
    }

    /**拼团列表
     * @return mixed
     */
    public function combina_list()
    {
        $where = Util::getMore([
            ['status', ''],
            ['data', ''],
        ], $this->request);
        $this->assign('where', $where);
        $this->assign(StorePink::systemPage($where));

        return $this->fetch();
    }

    /**拼团人列表
     * @return mixed
     */
    public function order_pink($id)
    {
        if (!$id) return $this->failed('数据不存在');
        $StorePink = StorePink::getPinkUserOne($id);
        if (!$StorePink) return $this->failed('数据不存在!');
        $list = StorePink::getPinkMember($id);
        $list[] = $StorePink;
        $this->assign('list', $list);
        return $this->fetch();
    }

    /**
     * 修改拼团状态
     * @param $status
     * @param int $idd
     */
    public function set_combination_status($status, $id = 0)
    {
        if (!$id) return Json::fail('参数错误');
        $res = StoreProductAttrValue::where('product_id', $id)->where('type', 3)->find();
        if (!$res) return Json::fail('请先配置规格');
        $res = StoreCombinationModel::edit(['is_show' => $status], $id);
        if ($res) return Json::successful('修改成功');
        else return Json::fail('修改失败');
    }

    /**
     * 添加拼团获取商品列表
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
     * 获取拼团商品规格
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
