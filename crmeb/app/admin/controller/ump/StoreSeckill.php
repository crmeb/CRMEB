<?php

namespace app\admin\controller\ump;

use app\admin\controller\AuthController;
use app\admin\model\store\{StoreDescription,
    StoreProductAttr,
    StoreProductAttrResult,
    StoreProduct as ProductModel,
    StoreProductAttrValue};
use crmeb\traits\CurdControllerTrait;
use think\Exception;
use think\exception\ErrorException;
use think\exception\ValidateException;
use think\facade\Route as Url;
use app\admin\model\system\{SystemAttachment, SystemGroupData, ShippingTemplates};
use app\admin\model\ump\{StoreSeckillAttr, StoreSeckillAttrResult, StoreSeckill as StoreSeckillModel, StoreSeckillTime};
use crmeb\services\{
    FormBuilder as Form, UtilService as Util, JsonService as Json
};
use app\admin\model\store\StoreCategory;

/**
 * 限时秒杀  控制器
 * Class StoreSeckill
 * @package app\admin\controller\store
 */
class StoreSeckill extends AuthController
{

    use CurdControllerTrait;

    protected $bindModel = StoreSeckillModel::class;

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $this->assign('countSeckill', StoreSeckillModel::getSeckillCount());
        $this->assign('seckillId', StoreSeckillModel::getSeckillIdAll());
        return $this->fetch();
    }

    public function save_excel()
    {
        $where = Util::getMore([
            ['status', ''],
            ['store_name', '']
        ]);
        StoreSeckillModel::SaveExcel($where);
    }

    /**
     * 异步获取砍价数据
     */
    public function get_seckill_list()
    {
        $where = Util::getMore([
            ['page', 1],
            ['limit', 20],
            ['status', ''],
            ['store_name', '']
        ]);
        $seckillList = StoreSeckillModel::systemPage($where);
        if (is_object($seckillList['list'])) $seckillList['list'] = $seckillList['list']->toArray();
        $data = $seckillList['list']['data'];
        foreach ($data as $k => $v) {
            $end_time = $v['stop_time'] ? date('Y/m/d', $v['stop_time']) : '';
            if ($end_time) {
                $config = SystemGroupData::get($v['time_id']);
                if ($config) {
                    $arr = json_decode($config->value, true);
                    $start_hour = intval($arr['time']['value']);
                    $continued = intval($arr['continued']['value']);
                    $end_hour = $start_hour + $continued;
                    $end_time = $end_time . ' ' . $end_hour . ':00:00';
                }
            }
            $data[$k]['_stop_time'] = $end_time;
        }
        return Json::successlayui(['count' => $seckillList['list']['total'], 'data' => $data]);
    }

    public function get_seckill_id()
    {
        return Json::successlayui(StoreSeckillModel::getSeckillIdAll());
    }

    /**
     * 添加秒杀商品
     * @return form-builder
     */
    public function create()
    {
        $f = array();
        $f[] = Form::frameImageOne('product', '选择商品', Url::buildUrl('productList', array('fodder' => 'product')))->icon('plus')->width('100%')->height('500px');
        $f[] = Form::hidden('product_id', '');
        $f[] = Form::hidden('description', '');
        $f[] = Form::input('title', '商品标题');
        $f[] = Form::input('info', '秒杀活动简介')->type('textarea');
        $f[] = Form::input('unit_name', '单位')->placeholder('个、位');
        $f[] = Form::select('temp_id', '秒杀运费模板')->setOptions(function () {
            $list = ShippingTemplates::getList(['page' => 1, 'limit' => 20]);
            $menus = [];
            foreach ($list['data'] as $menu) {
                $menus[] = ['value' => $menu['id'], 'label' => $menu['name']];
            }
            return $menus;
        })->filterable(1)->col(12);
        $f[] = Form::dateRange('section_time', '活动日期');
        $f[] = Form::select('time_id', '开始时间')->setOptions(function () {
            $list = SystemGroupData::getGroupData('routine_seckill_time', 20);
            $menus = [];
            foreach ($list['data'] as $menu) {
                $menus[] = ['value' => $menu['id'], 'label' => $menu['time'] . '点开始，持续' . $menu['continued'] . '小时'];//,'disabled'=>$menu['pid']== 0];
            }
            return $menus;
        })->filterable(1)->col(12);
        $f[] = Form::frameImageOne('image', '商品主图片(305*305px)', Url::buildUrl('admin/widget.images/index', array('fodder' => 'image')))->icon('image')->width('100%')->height('500px');
        $f[] = Form::frameImages('images', '商品轮播图(640*640px)', Url::buildUrl('admin/widget.images/index', array('fodder' => 'images')))->maxLength(5)->icon('images')->width('100%')->height('500px');
        $f[] = Form::number('sort', '排序')->col(12);
        $f[] = Form::number('num', '单次购买商品个数')->precision(0)->col(12);
        $f[] = Form::number('give_integral', '赠送积分')->min(0)->precision(0)->col(12);
        $f[] = Form::radio('is_hot', '热门推荐', 1)->options([['label' => '开启', 'value' => 1], ['label' => '关闭', 'value' => 0]])->col(12);
        $form = Form::make_post_form('添加用户通知', $f, Url::buildUrl('save'));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 保存秒杀商品
     * @param int $id
     */
    public function save($id = 0)
    {
        $data = Util::postMore([
            'title',
            'product_id',
            'info',
            'unit_name',
            ['image', ''],
            ['images', []],
            ['price', 0],
            ['ot_price', 0],
            ['cost', 0],
            ['sales', 0],
            ['stock', 0],
            ['sort', 0],
            ['give_integral', 0],
            ['postage', 0],
            ['section_time', []],
            ['is_postage', 0],
            ['cost', 0],
            ['is_hot', 0],
            ['status', 0],
            ['num', 0],
            'time_id',
            'temp_id',
            ['weight', 0],
            ['volume', 0],
        ]);
        $data['description'] = StoreDescription::getDescription($data['product_id']);
        if (!$data['title']) return Json::fail('请输入商品标题');
        if (!$data['unit_name']) return Json::fail('请输入商品单位');
        if (!$data['product_id']) return Json::fail('商品ID不能为空');
        if (count($data['section_time']) < 1) return Json::fail('请选择活动时间');
        if (!$data['time_id']) return Json::fail('时间段不能为空');
        $data['start_time'] = strtotime($data['section_time'][0] .' 00:00:00');
        $data['stop_time'] = strtotime($data['section_time'][1].' 23:59:59');
        unset($data['section_time']);
        if (!$data['image']) return Json::fail('请选择推荐图');
        if (count($data['images']) < 1) return Json::fail('请选择轮播图');
        $data['images'] = json_encode($data['images']);
        if ($data['num'] < 1) return Json::fail('请输入单次秒杀个数');
        if ($id) {
            unset($data['description']);
            $product = StoreSeckillModel::get($id);
            if (!$product) return Json::fail('数据不存在!');
            StoreSeckillModel::edit($data, $id);
            return Json::successful('编辑成功!');
        } else {
            if(StoreSeckillModel::checkSeckill($data['product_id'],$data['time_id'])) return Json::fail('该商品当前时间段已有秒杀活动');
            $data['add_time'] = time();
            $res = StoreSeckillModel::create($data);
            $description['product_id'] = $res['id'];
            $description['description'] = htmlspecialchars_decode($data['description']);
            $description['type'] = 1;
            StoreDescription::create($description);
            return Json::successful('添加成功!');
        }

    }

    /** 开启秒杀
     * @param $id
     * @return mixed|void
     */
    public function seckill($id)
    {
        if (!$id) return $this->failed('数据不存在');
        $product = ProductModel::get($id);
        if (!$product) return Json::fail('数据不存在!');
        $f = array();
        $f[] = Form::input('title', '商品标题', $product->getData('store_name'));
        $f[] = Form::hidden('product_id', $id);
        $f[] = Form::input('info', '秒杀活动简介', $product->getData('store_info'))->type('textarea');
        $f[] = Form::input('unit_name', '单位', $product->getData('unit_name'))->placeholder('个、位');
        $f[] = Form::select('temp_id', '秒杀运费模板', (string)$product->getData('temp_id'))->setOptions(function () {
            $list = ShippingTemplates::getList(['page' => 1, 'limit' => 20]);
            $menus = [];
            foreach ($list['data'] as $menu) {
                $menus[] = ['value' => $menu['id'], 'label' => $menu['name']];
            }
            return $menus;
        })->filterable(1)->col(12);
        $f[] = Form::dateRange('section_time', '活动日期');
        $f[] = Form::select('time_id', '开始时间')->setOptions(function () {
            $list = SystemGroupData::getGroupData('routine_seckill_time', 20);
            $menus = [];
            foreach ($list['data'] as $menu) {
                $menus[] = ['value' => $menu['id'], 'label' => $menu['time'] . '点开始，持续' . $menu['continued'] . '小时'];//,'disabled'=>$menu['pid']== 0];
            }
            return $menus;
        })->filterable(1)->col(12);
        $f[] = Form::frameImageOne('image', '商品主图片(305*305px)', Url::buildUrl('admin/widget.images/index', array('fodder' => 'image')), $product->getData('image'))->icon('image')->width('100%')->height('500px');
        $f[] = Form::frameImages('images', '商品轮播图(640*640px)', Url::buildUrl('admin/widget.images/index', array('fodder' => 'images')), json_decode($product->getData('slider_image')))->maxLength(5)->icon('images')->width('100%')->height('500px');
        $f[] = Form::number('price', '秒杀价')->min(0)->col(12);
        $f[] = Form::number('ot_price', '原价', $product->getData('price'))->min(0)->col(12);
        $f[] = Form::number('cost', '成本价', $product->getData('cost'))->min(0)->col(12);
        $f[] = Form::number('stock', '库存', $product->getData('stock'))->min(0)->precision(0)->col(12);
        $f[] = Form::number('sales', '销量', $product->getData('sales'))->min(0)->precision(0)->col(12);
        $f[] = Form::number('sort', '排序', $product->getData('sort'))->col(12);
        $f[] = Form::number('num', '单次购买商品个数', 1)->precision(0)->col(12);
        $f[] = Form::number('give_integral', '赠送积分', $product->getData('give_integral'))->min(0)->precision(0)->col(12);
        $f[] = Form::number('weight', '重量', 0)->min(0)->col(12);
        $f[] = Form::number('volume', '体积', 0)->min(0)->col(12);
        $f[] = Form::radio('is_hot', '热门推荐', 1)->options([['label' => '开启', 'value' => 1], ['label' => '关闭', 'value' => 0]])->col(12);
        $f[] = Form::radio('status', '活动状态', 1)->options([['label' => '开启', 'value' => 1], ['label' => '关闭', 'value' => 0]])->col(12);
        $form = Form::make_post_form('添加用户通知', $f, Url::buildUrl('save'));
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
        $product = StoreSeckillModel::get($id);
//        $time = StoreSeckillTime::getSeckillTime($id);
        if (!$product) return Json::fail('数据不存在!');
        $f = array();
        $f[] = Form::input('product_id','产品ID', $product->getData('product_id'))->disabled(true);
        $f[] = Form::input('title', '商品标题', $product->getData('title'));
        $f[] = Form::input('info', '秒杀活动简介', $product->getData('info'))->type('textarea');
        $f[] = Form::input('unit_name', '单位', $product->getData('unit_name'))->placeholder('个、位');
        $f[] = Form::select('temp_id', '秒杀运费模板', (string)$product->getData('temp_id'))->setOptions(function () {
            $list = ShippingTemplates::getList(['page' => 1, 'limit' => 20]);
            $menus = [];
            foreach ($list['data'] as $menu) {
                $menus[] = ['value' => $menu['id'], 'label' => $menu['name']];
            }
            return $menus;
        })->filterable(1)->col(12);
        $f[] = Form::dateRange('section_time', '活动时间', date('Y-m-d H:i:s', (int)$product->getData('start_time')), date('Y-m-d H:i:s', (int)$product->getData('stop_time')));
        $f[] = Form::select('time_id', '开始时间', (string)$product->getData('time_id'))->setOptions(function () {
            $list = SystemGroupData::getGroupData('routine_seckill_time', 20);
            $menus = [];
            foreach ($list['data'] as $menu) {
                $menus[] = ['value' => $menu['id'], 'label' => $menu['time'] . '点开始，持续' . $menu['continued'] . '小时'];//,'disabled'=>$menu['pid']== 0];
            }
            return $menus;
        })->filterable(1)->col(12);
        $f[] = Form::frameImageOne('image', '商品主图片(305*305px)', Url::buildUrl('admin/widget.images/index', array('fodder' => 'image')), $product->getData('image'))->icon('image')->width('100%')->height('500px');
        $f[] = Form::frameImages('images', '商品轮播图(640*640px)', Url::buildUrl('admin/widget.images/index', array('fodder' => 'images')), json_decode($product->getData('images')))->maxLength(5)->icon('images')->width('100%')->height('500px');
        $f[] = Form::number('sort', '排序', $product->getData('sort'))->col(12);
        $f[] = Form::hidden('stock', $product->getData('stock'));
        $f[] = Form::hidden('price', $product->getData('price'));
        $f[] = Form::hidden('ot_price', $product->getData('ot_price'));
        $f[] = Form::hidden('sales', $product->getData('sales'));
        $f[] = Form::number('num', '单次购买商品个数', $product->getData('num'))->precision(0)->col(12);
        $f[] = Form::number('give_integral', '赠送积分', $product->getData('give_integral'))->min(0)->precision(0)->col(12);
        $f[] = Form::radio('is_hot', '热门推荐', $product->getData('is_hot'))->options([['label' => '开启', 'value' => 1], ['label' => '关闭', 'value' => 0]])->col(12);
        $f[] = Form::hidden('status', $product->getData('status'));
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
        $product = StoreSeckillModel::get($id);
        if (!$product) return Json::fail('数据不存在!');
        if ($product['is_del']) return Json::fail('已删除!');
        $data['is_del'] = 1;
        if (!StoreSeckillModel::edit($data, $id))
            return Json::fail(StoreSeckillModel::getErrorInfo('删除失败,请稍候再试!'));
        else
            return Json::successful('删除成功!');
    }

    public function edit_content($id)
    {
        if (!$id) return $this->failed('数据不存在');
        $seckill = StoreSeckillModel::get($id);
        if (!$seckill) return Json::fail('数据不存在!');
        $this->assign([
            'content' => htmlspecialchars_decode(StoreDescription::getDescription($id, 1)),
            'field' => 'description',
            'action' => Url::buildUrl('change_field', ['id' => $id, 'field' => 'description'])
        ]);
        return $this->fetch('public/edit_content');
    }

    public function change_field($id)
    {
        if (!$id) return $this->failed('数据不存在');
        $seckill = StoreSeckillModel::get($id);
        if (!$seckill) return Json::fail('数据不存在!');
        $data['description'] = request()->post('description');
        StoreDescription::saveDescription($data['description'], $id, 1);
        $res = StoreSeckillModel::edit($data, $id);
        if ($res)
            return Json::successful('添加成功');
        else
            return Json::fail('添加失败');
    }

    /**
     * 属性页面
     * @param $id
     * @return mixed|void
     */
    public function attr($id)
    {
        if (!$id) return $this->failed('数据不存在!');
        $result = StoreSeckillAttrResult::getResult($id);
        $image = StoreSeckillModel::where('id', $id)->value('image');
        $this->assign(compact('id', 'result', 'image'));
        return $this->fetch();
    }

    /**
     * 秒杀属性选择页面
     * @param $id
     * @return string|void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function attr_list($id)
    {
        if (!$id) return $this->failed('数据不存在!');
        $seckillInfo = StoreSeckillModel::where('id', $id)->find();
        $seckillResult = StoreProductAttrResult::where('product_id', $id)->where('type', 1)->value('result');
        $productResult = StoreProductAttrResult::where('product_id', $seckillInfo['product_id'])->where('type', 0)->value('result');
        if ($productResult) {
            $attr = json_decode($productResult, true)['attr'];
            $productAttr = $this->get_attr($attr, $seckillInfo['product_id'], 0);
            $seckillAttr = $this->get_attr($attr, $id, 1);
            foreach ($productAttr as $pk => $pv) {
                foreach ($seckillAttr as $sv) {
                    if ($pv['detail'] == $sv['detail']) {
                        $productAttr[$pk] = $sv;
                    }
                }
            }
        } else {
            if ($seckillResult) {
                $attr = json_decode($seckillResult, true)['attr'];
                $productAttr = $this->get_attr($attr, $id, 1);
            } else {
                $attr[0]['value'] = '默认';
                $attr[0]['detailValue'] = '';
                $attr[0]['attrHidden'] = '';
                $attr[0]['detail'][0] = '默认';
                $productAttr[0]['value1'] = '默认';
                $productAttr[0]['detail'] = json_encode(['默认' => '默认']);
                $productAttr[0]['pic'] = $seckillInfo['image'];
                $productAttr[0]['price'] = $seckillInfo['price'];
                $productAttr[0]['cost'] = $seckillInfo['cost'];
                $productAttr[0]['ot_price'] = $seckillInfo['ot_price'];
                $productAttr[0]['stock'] = $seckillInfo['stock'];
                $productAttr[0]['quota'] = 0;
                $productAttr[0]['bar_code'] = $seckillInfo['bar_code'];
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
     * 秒杀属性保存页面
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
        $productId = StoreSeckillModel::where('id', $data['id'])->value('product_id');
        $attr = json_decode(StoreProductAttrResult::where('product_id', $productId)->where('type', 0)->value('result'), true)['attr'];
        foreach ($data['attr'] as $k => $v) {
            if (in_array($k, $data['ids'])) {
                $v['detail'] = json_decode(htmlspecialchars_decode($v['detail']), true);
                $detail[$k] = $v;
            }
        }
        if (min(array_column($detail, 'quota')) == 0) return Json::fail('限购不能为0');
        $price = min(array_column($detail, 'price'));
        $otPrice = min(array_column($detail, 'ot_price'));
        $quota = array_sum(array_column($detail, 'quota'));
        $stock = array_sum(array_column($detail, 'stock'));
        if (!$attr) {
            $attr[0]['value'] = '默认';
            $attr[0]['detailValue'] = '';
            $attr[0]['attrHidden'] = '';
            $attr[0]['detail'][0] = '默认';
        }
        StoreProductAttr::createProductAttr($attr, $detail, $data['id'], 1);
        StoreSeckillModel::where('id', $data['id'])->update(['stock' => $stock, 'quota' => $quota, 'quota_show' => $quota, 'price' => $price, 'ot_price' => $otPrice]);
        return Json::successful('修改成功!');
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
        $product = StoreSeckillModel::get($id);
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
        $res = StoreSeckillAttr::createProductAttr($attr, $detail, $id);
        if ($res)
            return $this->successful('编辑属性成功!');
        else
            return $this->failed(StoreSeckillAttr::getErrorInfo());
    }

    /**
     * 清除属性
     * @param $id
     */
    public function clear_attr($id)
    {
        if (!$id) return $this->failed('商品不存在!');
        if (false !== StoreSeckillAttr::clearProductAttr($id) && false !== StoreSeckillAttrResult::clearResult($id))
            return $this->successful('清空商品属性成功!');
        else
            return $this->failed(StoreSeckillAttr::getErrorInfo('清空商品属性失败!'));
    }

    /**
     * 修改秒杀商品状态
     * @param $status
     * @param int $id
     */
    public function set_seckill_status($status, $id = 0)
    {
        if (!$id) return Json::fail('参数错误');
        $res = StoreProductAttrValue::where('product_id', $id)->where('type', 1)->find();
        if (!$res) return Json::fail('请先配置规格');
        $res = StoreSeckillModel::edit(['status' => $status], $id);
        if ($res) return Json::successful('修改成功');
        else return Json::fail('修改失败');
    }

    /**
     * 秒杀获取商品列表
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
     * 获取秒杀商品规格
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
